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
 * Mendukung dua periode sewa:
 *   - 'bulanan'  : tagihan dibuat per bulan sesuai durasi bulan.
 *   - 'harian'   : tagihan dibuat satu kali untuk total durasi hari,
 *                  dan tanggal_keluar otomatis diisi (masuk + durasi - 1 hari).
 */
class PenyewaanService
{
    public function sewaKamar(User $user, Kamar $kamar, string $tanggalMasuk, int $durasiBulan = 1, ?int $durasiHari = null): Penyewaan
    {
        $isHarian = $durasiHari !== null && $durasiHari > 0;

        if ($isHarian) {
            $durasiHari = min(90, max(1, $durasiHari));
        } else {
            $durasiBulan = min(12, max(1, $durasiBulan));
        }

        $tanggalMasukCarbon = Carbon::parse($tanggalMasuk);
        $tanggalKeluars = $isHarian
            ? $tanggalMasukCarbon->copy()->addDays($durasiHari - 1)
            : null;

        $penyewaan = null;

        DB::transaction(function () use ($user, $kamar, $tanggalMasuk, $tanggalMasukCarbon, $tanggalKeluars, $durasiBulan, $durasiHari, $isHarian, &$penyewaan) {
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

            if ($isHarian && ! $kamarTerkunci->harga_sewa_harian) {
                throw new DomainException('Kamar ini belum menetapkan harga sewa harian.');
            }

            $kamarTerkunci->update(['status' => 'terisi']);

            $penyewaan = Penyewaan::create([
                'anak_kos_id' => $user->id,
                'kamar_id' => $kamarTerkunci->id,
                'properti_id' => $kamarTerkunci->properti_id,
                'tanggal_masuk' => $tanggalMasuk,
                'tanggal_keluar' => $tanggalKeluars?->toDateString(),
                'status' => 'aktif',
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
