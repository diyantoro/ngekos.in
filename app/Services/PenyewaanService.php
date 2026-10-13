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
 * Sewa langsung terkunci: kamar di-set terisi, penyewaan dibuat aktif,
 * dan pemilik diberitahu lewat chat — tanpa menunggu konfirmasi.
 *
 * Privasi: booking TIDAK meminta foto KTP (dokumen identitas terlalu sensitif
 * untuk dibagikan ke pemilik). Pengaman booking adalah Uang Muka (DP),
 * yaitu pembayaran tagihan pertama oleh anak kos.
 *
 * Mendukung tiga periode sewa (harga diisi sekali oleh pemilik, 3-in-1):
 *   - 'bulanan'  : tagihan dibuat per bulan sesuai durasi bulan.
 *   - 'mingguan' : tagihan dibuat satu kali untuk total durasi minggu,
 *                  dan tanggal_keluar otomatis diisi (masuk + minggu*7 - 1 hari).
 *   - 'harian'   : tagihan dibuat satu kali untuk total durasi hari,
 *                  dan tanggal_keluar otomatis diisi (masuk + durasi - 1 hari).
 */
class PenyewaanService
{
    public function sewaKamar(User $user, Kamar $kamar, string $tanggalMasuk, int $durasiBulan = 1, ?int $durasiHari = null, ?int $durasiMinggu = null): Penyewaan
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

        DB::transaction(function () use ($user, $kamar, $tanggalMasuk, $tanggalMasukCarbon, $tanggalKeluars, $durasiBulan, $durasiHari, $durasiMinggu, $isHarian, $isMingguan, &$penyewaan) {
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

            $kamarTerkunci->update(['status' => KamarStatus::Terisi]);

            $penyewaan = Penyewaan::create([
                'anak_kos_id' => $user->id,
                'kamar_id' => $kamarTerkunci->id,
                'properti_id' => $kamarTerkunci->properti_id,
                'tanggal_masuk' => $tanggalMasuk,
                'tanggal_keluar' => $tanggalKeluars?->toDateString(),
                'status' => 'aktif',
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
            $bulanAwal = $tanggalMasukCarbon->copy()->startOfMonth();
            for ($i = 0; $i < $durasiBulan; $i++) {
                $bulan = $bulanAwal->copy()->addMonthsNoOverflow($i);
                $penyewaan->tagihans()->create([
                    'periode' => $bulan->translatedFormat('F Y'),
                    'jumlah' => $kamarTerkunci->harga_sewa_bulanan,
                    'denda' => 0,
                    'jatuh_tempo' => Pengaturan::jatuhTempoUntuk($bulan)->toDateString(),
                    'status' => 'belum_bayar',
                ]);
            }

            ChatPesan::create([
                'properti_id' => $kamarTerkunci->properti_id,
                'anak_kos_id' => $user->id,
                'pengirim_id' => $user->id,
                'isi' => 'Saya sudah memesan kamar '.$kamarTerkunci->nama.' di '.$kamarTerkunci->properti->nama
                    .' (Rp'.number_format((float) $kamarTerkunci->harga_sewa_bulanan, 0, ',', '.').'/bulan)'
                        .' untuk '.$durasiBulan.' bulan dan rencana masuk tanggal '.$tanggalLabel
                        .'. Tagihan sewa sudah otomatis dibuat — segera bayar Uang Muka (DP) agar booking fiks. Terima kasih.',
            ]);
        });

        return $penyewaan;
    }
}
