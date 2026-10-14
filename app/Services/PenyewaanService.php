<?php

namespace App\Services;

use App\Enums\KamarStatus;
use App\Models\ChatPesan;
use App\Models\Kamar;
use App\Models\Pengaturan;
use App\Models\Penyewaan;
use App\Models\User;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Logika sewa kamar yang dipakai bersama oleh API (mobile) dan Livewire (web).
 *
 * Sewa BULANAN memakai booking terverifikasi: anak kos wajib transfer DP
 * Rp100.000 ke rekening pemilik dan melampirkan bukti transfer. Kamar
 * ditahan (terisi) tapi penyewaan berstatus menunggu_verifikasi — BELUM
 * otomatis tersewa. Sewa baru aktif setelah pemilik/admin memverifikasi
 * bukti DP. Bila ditolak, booking dibatalkan dan kamar dilepas lagi.
 *
 * Privasi: booking TIDAK meminta foto KTP (dokumen identitas terlalu sensitif
 * untuk dibagikan ke pemilik). Pengaman booking adalah Uang Muka (DP),
 * yaitu pembayaran tagihan pertama oleh anak kos.
 *
 * Mendukung tiga periode sewa (harga diisi sekali oleh pemilik, 3-in-1):
 *   - 'bulanan'  : tagihan DP + sisa per bulan sesuai durasi bulan.
 *   - 'mingguan' : tagihan dibuat satu kali untuk total durasi minggu,
 *                  dan tanggal_keluar otomatis diisi (masuk + minggu*7 - 1 hari).
 *   - 'harian'   : tagihan dibuat satu kali untuk total durasi hari,
 *                  dan tanggal_keluar otomatis diisi (masuk + durasi - 1 hari).
 */
class PenyewaanService
{
    /**
     * Uang Muka (DP) tetap Rp100.000 untuk sewa bulanan.
     * DP mengurangi tagihan bulan pertama: sisa = harga - DP.
     */
    public const DP_NOMINAL = 100000;

    /**
     * DP yang berlaku untuk satu harga bulanan (dijepit agar tak melebihi harga).
     */
    public static function dpUntuk(float $hargaBulanan): float
    {
        if ($hargaBulanan <= 0) {
            return 0;
        }

        return (float) min(self::DP_NOMINAL, $hargaBulanan);
    }

    /**
     * True bila tagihan adalah tagihan DP (kolom jenis, fallback periode lama).
     */
    public static function isDp(\App\Models\Tagihan $tagihan): bool
    {
        if (($tagihan->getAttribute('jenis') ?? 'sewa') === 'dp') {
            return true;
        }

        return str_starts_with((string) $tagihan->periode, 'DP - ');
    }
    /**
     * @param  string|null  $buktiDpPath  Path bukti transfer DP (wajib untuk bulanan).
     */
    public function sewaKamar(User $user, Kamar $kamar, string $tanggalMasuk, int $durasiBulan = 1, ?int $durasiHari = null, ?int $durasiMinggu = null, ?string $buktiDpPath = null): Penyewaan
    {
        $isHarian = $durasiHari !== null && $durasiHari > 0;
        $isMingguan = ! $isHarian && $durasiMinggu !== null && $durasiMinggu > 0;

        if ($isHarian) {
            $durasiHari = min(90, max(1, $durasiHari));
        } elseif ($isMingguan) {
            $durasiMinggu = min(12, max(1, $durasiMinggu));
        } else {
            $durasiBulan = min(12, max(1, $durasiBulan));
        }

        $tanggalMasukCarbon = Carbon::parse($tanggalMasuk);

        // Pertahanan lapis kedua: tanggal masuk maksimal 10 hari ke depan
        // (validasi form/API sudah membatasi, ini mencegah bypass langsung ke service).
        if ($tanggalMasukCarbon->startOfDay()->gt(Carbon::today()->addDays(10))) {
            throw new DomainException('Tanggal masuk maksimal 10 hari ke depan.');
        }

        if ($tanggalMasukCarbon->startOfDay()->lt(Carbon::today())) {
            throw new DomainException('Tanggal masuk tidak boleh mundur dari hari ini.');
        }

        $tanggalKeluars = $isHarian
            ? $tanggalMasukCarbon->copy()->addDays($durasiHari - 1)
            : ($isMingguan ? $tanggalMasukCarbon->copy()->addDays($durasiMinggu * 7 - 1) : null);

        $penyewaan = null;

        DB::transaction(function () use ($user, $kamar, $tanggalMasuk, $tanggalMasukCarbon, $tanggalKeluars, $durasiBulan, $durasiHari, $durasiMinggu, $isHarian, $isMingguan, $buktiDpPath, &$penyewaan) {
            // Kunci baris kamar (SELECT ... FOR UPDATE) sebelum dicek agar dua
            // booking bersamaan pada kamar yang sama tidak saling menimpa
            // (double-booking protection). Transaksi kedua menunggu pemilik lock.
            $kamarTerkunci = Kamar::with('properti')
                ->whereKey($kamar->id)
                ->lockForUpdate()
                ->first();

            if (! $kamarTerkunci || $kamarTerkunci->status !== KamarStatus::Tersedia) {
                throw new DomainException('Kamar ini sudah terisi dan tidak dapat dipesan.');
            }

            if ($isHarian && (! $kamarTerkunci->harga_sewa_harian || (float) $kamarTerkunci->harga_sewa_harian <= 0)) {
                throw new DomainException('Kamar ini belum menetapkan harga sewa harian.');
            }

            if ($isMingguan && (! $kamarTerkunci->harga_sewa_mingguan || (float) $kamarTerkunci->harga_sewa_mingguan <= 0)) {
                throw new DomainException('Kamar ini belum menetapkan harga sewa mingguan.');
            }

            if (! $isHarian && ! $isMingguan && (! $kamarTerkunci->harga_sewa_bulanan || (float) $kamarTerkunci->harga_sewa_bulanan <= 0)) {
                throw new DomainException('Kamar ini belum menetapkan harga sewa bulanan.');
            }

            // Booking bulanan wajib transfer DP + bukti: tanpa keduanya sewa
            // tidak jalan (validasi web/API sudah meminta, ini anti-bypass).
            if (! $isHarian && ! $isMingguan) {
                if (! $kamarTerkunci->properti || ! $kamarTerkunci->properti->punyaRekening()) {
                    throw new DomainException('Pemilik kos belum mencantumkan nomor rekening, jadi booking bulanan belum bisa dilakukan. Coba hubungi pemilik lewat chat.');
                }

                if (blank($buktiDpPath)) {
                    throw new DomainException('Lampirkan bukti transfer DP Rp100.000 terlebih dahulu sebelum menekan Sewa.');
                }
            }

            $kamarTerkunci->update(['status' => KamarStatus::Terisi]);

            // Sewa bulanan berlaku 1 periode = tanggal masuk + durasi bulan - 1 hari.
            // Dipakai tawaran perpanjangan H-3 & auto-kosong saat berakhir.
            $tanggalBerakhir = (! $isHarian && ! $isMingguan)
                ? $tanggalMasukCarbon->copy()->addMonthsNoOverflow($durasiBulan)->subDay()
                : $tanggalKeluars;

            $penyewaan = Penyewaan::create([
                'anak_kos_id' => $user->id,
                'kamar_id' => $kamarTerkunci->id,
                'properti_id' => $kamarTerkunci->properti_id,
                'tanggal_masuk' => $tanggalMasuk,
                'tanggal_keluar' => $tanggalKeluars?->toDateString(),
                'tanggal_berakhir' => $tanggalBerakhir?->toDateString(),
                'perpanjangan_status' => null,
                'perpanjangan_ditawarkan_pada' => null,
                // Bulanan = menunggu verifikasi bukti DP (belum otomatis tersewa).
                'status' => (! $isHarian && ! $isMingguan) ? 'menunggu_verifikasi' : 'aktif',
                'ktp_path' => null,
                'mode_hunian' => 'tunggal',
            ]);

            $tanggalLabel = $tanggalMasukCarbon->locale('id')->translatedFormat('d F Y');

            if ($isHarian) {
                $jumlahHarian = (float) $kamarTerkunci->harga_sewa_harian;
                $total = $jumlahHarian * $durasiHari;

                $penyewaan->tagihans()->create([
                    'periode' => $tanggalMasukCarbon->copy()->locale('id')->translatedFormat('d F Y')
                        .' - '.$tanggalKeluars->locale('id')->translatedFormat('d F Y'),
                    'jumlah' => $total,
                    'denda' => 0,
                    'jatuh_tempo' => $tanggalKeluars->toDateString(),
                    'status' => 'belum_bayar',
                ]);

                ChatPesan::create([
                    'properti_id' => $kamarTerkunci->properti_id,
                    'anak_kos_id' => $user->id,
                    'pengirim_id' => $user->id,
                    'isi' => 'Saya sudah memesan kamar '.$kamarTerkunci->nama.' di '.$kamarTerkunci->properti->nama
                        .' (Rp'.number_format($jumlahHarian, 0, ',', '.').'/hari)'
                        .' untuk '.$durasiHari.' hari dan rencana masuk tanggal '.$tanggalLabel
                        .'. Tagihan sewa sudah otomatis dibuat — segera bayar Uang Muka (DP) agar booking fiks. Terima kasih.',
                ]);

                return;
            }

            if ($isMingguan) {
                $jumlahMingguan = (float) $kamarTerkunci->harga_sewa_mingguan;
                $total = $jumlahMingguan * $durasiMinggu;

                $penyewaan->tagihans()->create([
                    'periode' => $tanggalMasukCarbon->copy()->locale('id')->translatedFormat('d F Y')
                        .' - '.$tanggalKeluars->locale('id')->translatedFormat('d F Y'),
                    'jumlah' => $total,
                    'denda' => 0,
                    'jatuh_tempo' => $tanggalKeluars->toDateString(),
                    'status' => 'belum_bayar',
                ]);

                ChatPesan::create([
                    'properti_id' => $kamarTerkunci->properti_id,
                    'anak_kos_id' => $user->id,
                    'pengirim_id' => $user->id,
                    'isi' => 'Saya sudah memesan kamar '.$kamarTerkunci->nama.' di '.$kamarTerkunci->properti->nama
                        .' (Rp'.number_format($jumlahMingguan, 0, ',', '.').'/minggu)'
                        .' untuk '.$durasiMinggu.' minggu dan rencana masuk tanggal '.$tanggalLabel
                        .'. Tagihan sewa sudah otomatis dibuat — segera bayar Uang Muka (DP) agar booking fiks. Terima kasih.',
                ]);

                return;
            }

            // Buat tagihan sewa untuk setiap bulan sesuai durasi yang dipilih
            // (format sama dengan command ProsesTagihan, jadi tidak dobel/diubah).
            // Bulan pertama dipecah: DP Rp100.000 + sisa (harga - DP).
            $hargaBulanan = (float) $kamarTerkunci->harga_sewa_bulanan;
            $dp = self::dpUntuk($hargaBulanan);
            $sisaPertama = round($hargaBulanan - $dp, 2);

            $bulanAwal = $tanggalMasukCarbon->copy()->startOfMonth();
            $tagihanDp = null;
            for ($i = 0; $i < $durasiBulan; $i++) {
                $bulan = $bulanAwal->copy()->addMonthsNoOverflow($i);
                $label = $bulan->locale('id')->translatedFormat('F Y');
                $jatuh = Pengaturan::jatuhTempoUntuk($bulan)->toDateString();

                if ($i === 0 && $dp > 0) {
                    $tagihanDp = $penyewaan->tagihans()->create([
                        'periode' => 'DP - '.$label,
                        'jumlah' => $dp,
                        'jenis' => 'dp',
                        'denda' => 0,
                        'jatuh_tempo' => $tanggalMasukCarbon->toDateString(),
                        'status' => 'belum_bayar',
                    ]);
                }

                $jumlahBulan = $i === 0 ? $sisaPertama : $hargaBulanan;

                if ($jumlahBulan > 0) {
                    $penyewaan->tagihans()->create([
                        'periode' => $i === 0 && $dp > 0 ? 'Sisa '.$label : $label,
                        'jumlah' => $jumlahBulan,
                        'jenis' => 'sewa',
                        'denda' => 0,
                        'jatuh_tempo' => $jatuh,
                        'status' => 'belum_bayar',
                    ]);
                }
            }

            // Bukti DP langsung tercatat sebagai pembayaran menunggu verifikasi.
            // Sewa BELUM aktif sampai pemilik/admin memverifikasinya.
            if ($tagihanDp) {
                \App\Models\Pembayaran::create([
                    'tagihan_id' => $tagihanDp->id,
                    'anak_kos_id' => $user->id,
                    'metode' => 'transfer',
                    'jumlah' => $dp,
                    'bukti' => $buktiDpPath,
                    'status' => 'menunggu_verifikasi',
                ]);
            }

            $rekening = $kamarTerkunci->properti?->labelRekening();

            ChatPesan::create([
                'properti_id' => $kamarTerkunci->properti_id,
                'anak_kos_id' => $user->id,
                'pengirim_id' => $user->id,
                'isi' => 'Saya mengajukan booking kamar '.$kamarTerkunci->nama.' di '.$kamarTerkunci->properti->nama
                    .' (Rp'.number_format($hargaBulanan, 0, ',', '.').'/bulan)'
                        .' untuk '.$durasiBulan.' bulan, rencana masuk '.$tanggalLabel
                        .' sampai '.$tanggalBerakhir->locale('id')->translatedFormat('d F Y')
                        .'. Saya sudah transfer DP Rp'.number_format($dp, 0, ',', '.')
                        .($rekening ? ' ke '.$rekening : '')
                        .' dan melampirkan bukti transfer. Mohon diverifikasi agar sewa saya aktif. Terima kasih.',
            ]);
        });

        return $penyewaan;
    }

    /**
     * Pemilik/admin MENYETUJUI booking: verifikasi pembayaran DP lewat pintu
     * resmi (PembayaranService) — hook di sana yang mengaktifkan sewanya.
     *
     * @return array{pembayaran: \App\Models\Pembayaran, tagihan_lunas: bool, kwitansi_url: ?string}
     */
    public function setujuiBooking(Penyewaan $penyewaan, int $verifikatorId, bool $verifikatorAdalahPengelola = false): array
    {
        $penyewaan->loadMissing(['properti', 'tagihans.pembayarans']);

        if ($penyewaan->status !== 'menunggu_verifikasi') {
            throw new DomainException('Booking ini tidak menunggu verifikasi.');
        }

        if (! $verifikatorAdalahPengelola && (int) $penyewaan->properti?->pemilik_id !== $verifikatorId) {
            throw new DomainException('Bukan properti kelolaanmu.');
        }

        $dp = $penyewaan->tagihans->first(fn ($t) => self::isDp($t));

        if (! $dp) {
            throw new DomainException('Tagihan DP booking ini tidak ditemukan.');
        }

        $bayar = $dp->pembayarans->firstWhere('status', 'menunggu_verifikasi')
            ?? \App\Models\Pembayaran::where('tagihan_id', $dp->id)
                ->where('status', 'menunggu_verifikasi')
                ->first();

        if (! $bayar) {
            throw new DomainException('Tidak ada bukti DP yang menunggu verifikasi untuk booking ini.');
        }

        return PembayaranService::verifikasi($bayar, $verifikatorId, 'diverifikasi');
    }

    /**
     * Pemilik/admin MENOLAK booking: pembayaran DP ditolak lewat pintu resmi
     * (PembayaranService) — hook di sana yang membatalkan booking dan
     * melepas kamar kembali tersedia.
     */
    public function tolakBooking(Penyewaan $penyewaan, int $verifikatorId, bool $verifikatorAdalahPengelola = false): void
    {
        $penyewaan->loadMissing(['properti', 'tagihans.pembayarans']);

        if ($penyewaan->status !== 'menunggu_verifikasi') {
            throw new DomainException('Booking ini tidak menunggu verifikasi.');
        }

        if (! $verifikatorAdalahPengelola && (int) $penyewaan->properti?->pemilik_id !== $verifikatorId) {
            throw new DomainException('Bukan properti kelolaanmu.');
        }

        $dp = $penyewaan->tagihans->first(fn ($t) => self::isDp($t));

        $bayar = $dp
            ? ($dp->pembayarans->firstWhere('status', 'menunggu_verifikasi')
                ?? \App\Models\Pembayaran::where('tagihan_id', $dp->id)
                    ->where('status', 'menunggu_verifikasi')
                    ->first())
            : null;

        if (! $bayar) {
            throw new DomainException('Tidak ada bukti DP yang menunggu verifikasi untuk booking ini.');
        }

        PembayaranService::verifikasi($bayar, $verifikatorId, 'ditolak');
    }
}
