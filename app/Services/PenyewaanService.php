<?php

namespace App\Services;

use App\Models\ChatPesan;
use App\Models\Kamar;
use App\Models\Penyewaan;
use App\Models\User;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Logika sewa kamar yang dipakai bersama oleh API (mobile) dan Livewire (web).
 * Sewa langsung terkunci: kamar di-set terisi, penyewaan dibuat aktif,
 * dan pemilik diberitahu lewat chat — tanpa menunggu konfirmasi.
 */
class PenyewaanService
{
    public function sewaKamar(User $user, Kamar $kamar, string $tanggalMasuk): Penyewaan
    {
        $tanggalLabel = Carbon::parse($tanggalMasuk)->locale('id')->translatedFormat('d F Y');

        $penyewaan = null;

        DB::transaction(function () use ($user, $kamar, $tanggalMasuk, $tanggalLabel, &$penyewaan) {
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

            $kamarTerkunci->update(['status' => 'terisi']);

            $penyewaan = Penyewaan::create([
                'anak_kos_id' => $user->id,
                'kamar_id' => $kamarTerkunci->id,
                'properti_id' => $kamarTerkunci->properti_id,
                'tanggal_masuk' => $tanggalMasuk,
                'status' => 'aktif',
            ]);

            ChatPesan::create([
                'properti_id' => $kamarTerkunci->properti_id,
                'anak_kos_id' => $user->id,
                'pengirim_id' => $user->id,
                'isi' => 'Saya sudah memesan kamar '.$kamarTerkunci->nama.' di '.$kamarTerkunci->properti->nama
                    .' (Rp'.number_format((float) $kamarTerkunci->harga_sewa_bulanan, 0, ',', '.').'/bulan)'
                    .' dan rencana masuk tanggal '.$tanggalLabel.'. Terima kasih.',
            ]);
        });

        return $penyewaan;
    }
}
