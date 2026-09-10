<?php

namespace App\Services;

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
 * Mendukung tiga periode sewa (harga diisi sekali oleh pemilik, 3-in-1):
 *   - 'bulanan'  : tagihan dibuat per bulan sesuai durasi bulan.
 *   - 'mingguan' : tagihan dibuat satu kali untuk total durasi minggu,
 *                  dan tanggal_keluar otomatis diisi (masuk + minggu*7 - 1 hari).
 *   - 'harian'   : tagihan dibuat satu kali untuk total durasi hari,
 *                  dan tanggal_keluar otomatis diisi (masuk + durasi - 1 hari).
 */
class PenyewaanService
{
    public const ATURAN_KTP = ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:2048'];

    public static function pesanKtp(): array
    {
        return [
            'ktp.required' => 'Foto KTP wajib diunggah sebelum memesan kamar.',
            'ktp.file' => 'Foto KTP tidak valid.',
            'ktp.mimes' => 'Foto KTP harus berupa gambar (JPG, PNG, WEBP) atau PDF.',
            'ktp.max' => 'Ukuran foto KTP maksimal 2MB.',
        ];
    }

    public function sewaKamar(User $user, Kamar $kamar, string $tanggalMasuk, int $durasiBulan = 1, ?int $durasiHari = null, ?string $ktpPath = null, ?int $durasiMinggu = null): Penyewaan
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
        $tanggalKeluars = $isHarian
            ? $tanggalMasukCarbon->copy()->addDays($durasiHari - 1)
            : ($isMingguan ? $tanggalMasukCarbon->copy()->addDays($durasiMinggu * 7 - 1) : null);

        $penyewaan = null;

        DB::transaction(function () use ($user, $kamar, $tanggalMasuk, $tanggalMasukCarbon, $tanggalKeluars, $durasiBulan, $durasiHari, $durasiMinggu, $isHarian, $isMingguan, $ktpPath, &$penyewaan) {
            // Kunci baris kamar (SELECT ... FOR UPDATE) sebelum dicek agar dua
            // booking bersamaan pada kamar yang sama tidak saling menimpa
            // (double-booking protection). Transaksi kedua menunggu pemilik lock.
            $kamarTerkunci = Kamar::with('properti')
                ->whereKey($kamar->id)
                ->lockForUpdate()
                ->first();

            if (! $kamarTerkunci || $kamarTerkunci->status !== 'tersedia') {
                throw new DomainException('Kamar ini sudah terisi dan tidak dapat dipesan.');
            }

            if ($isHarian && (! $kamarTerkunci->harga_sewa_harian || (float) $kamarTerkunci->harga_sewa_harian <= 0)) {
                throw new DomainException('Kamar ini belum menetapkan harga sewa harian.');
            }

            if ($isMingguan && (! $kamarTerkunci->harga_sewa_mingguan || (float) $kamarTerkunci->harga_sewa_mingguan <= 0)) {
                throw new DomainException('Kamar ini belum menetapkan harga sewa mingguan.');
            }

            if (! $ktpPath) {
                throw new DomainException('Foto KTP wajib diunggah sebelum memesan kamar.');
            }

            $kamarTerkunci->update(['status' => 'terisi']);

            $penyewaan = Penyewaan::create([
                'anak_kos_id' => $user->id,
                'kamar_id' => $kamarTerkunci->id,
                'properti_id' => $kamarTerkunci->properti_id,
                'tanggal_masuk' => $tanggalMasuk,
                'tanggal_keluar' => $tanggalKeluars?->toDateString(),
                'status' => 'aktif',
                'ktp_path' => $ktpPath,
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
                        .'. Tagihan sewa akan otomatis dibuat untuk dibayar. Terima kasih.',
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
                        .'. Tagihan sewa akan otomatis dibuat untuk dibayar. Terima kasih.',
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
                    .'. Tagihan sewa akan otomatis dibuat untuk dibayar. Terima kasih.',
            ]);
        });

        return $penyewaan;
    }
}
