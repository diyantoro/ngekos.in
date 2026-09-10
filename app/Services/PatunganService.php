<?php

namespace App\Services;

use App\Models\Penyewaan;
use App\Models\PenyewaanAnggota;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Aturan patungan 50/50 satu kamar dua penghuni.
 * - Tagihan tetap 1 per periode nominal penuh.
 * - Anggota keluar wajib lunas porsinya dulu (termasuk denda proporsional).
 * - Setelah keluar, porsi yang stay jadi 100%.
 */
class PatunganService
{
    /**
     * Tambah teman sekamar sebagai anggota aktif.
     */
    public static function tambahAnggota(Penyewaan $penyewaan, User $user, ?string $ktpPath = null): PenyewaanAnggota
    {
        $penyewaan->loadMissing(['kamar', 'anggotas']);

        if ($penyewaan->status !== 'aktif') {
            throw new DomainException('Hanya penyewaan aktif yang bisa ditambah anggota.');
        }

        if ((int) ($penyewaan->kamar?->kapasitas ?? 1) < 2) {
            throw new DomainException('Kamar ini kapasitasnya 1 orang, tidak bisa patungan.');
        }

        if ($user->id === $penyewaan->anak_kos_id) {
            throw new DomainException('User ini sudah menjadi penyewa utama kamar tersebut.');
        }

        if ($penyewaan->anggotas->where('status', 'aktif')->count() >= 1) {
            throw new DomainException('Kamar ini sudah terisi 2 orang (penuh untuk patungan).');
        }

        $sudahAktif = PenyewaanAnggota::where('penyewaan_id', $penyewaan->id)
            ->where('user_id', $user->id)
            ->where('status', 'aktif')
            ->exists();

        if ($sudahAktif) {
            throw new DomainException('User ini sudah menjadi anggota kamar tersebut.');
        }

        $punyaSewaAktif = $penyewaan->kamar_id
            ? \App\Models\Penyewaan::where('kamar_id', $penyewaan->kamar_id)
                ->where('status', 'aktif')
                ->where('id', '!=', $penyewaan->id)
                ->where(fn ($q) => $q->where('anak_kos_id', $user->id)
                    ->orWhereHas('anggotas', fn ($w) => $w->where('user_id', $user->id)->where('status', 'aktif')))
                ->exists()
            : false;

        if ($punyaSewaAktif) {
            throw new DomainException('User ini sudah punya sewa aktif di kamar tersebut.');
        }

        return DB::transaction(function () use ($penyewaan, $user, $ktpPath) {
            $anggota = PenyewaanAnggota::updateOrCreate(
                ['penyewaan_id' => $penyewaan->id, 'user_id' => $user->id],
                [
                    'porsi_persen' => 50,
                    'status' => 'aktif',
                    'ktp_path' => $ktpPath ?? PenyewaanAnggota::where('penyewaan_id', $penyewaan->id)->where('user_id', $user->id)->value('ktp_path'),
                    'tanggal_keluar' => null,
                ]
            );

            $penyewaan->update(['mode_hunian' => 'patungan']);

            return $anggota->refresh();
        });
    }

    /**
     * Hitung sisa porsi user per tagihan belum lunas.
     * @return array<int, array{tagihan_id: int, periode: string, porsi: float, sudah_bayar: float, sisa: float}>
     */
    public static function sisaPorsi(Penyewaan $penyewaan, int $userId): array
    {
        $penyewaan->loadMissing(['tagihans.pembayarans', 'anggotas']);

        $hasil = [];

        foreach ($penyewaan->tagihans->where('status', '!=', 'lunas') as $tagihan) {
            $porsi = self::porsiTagihan($penyewaan, $tagihan);

            $sudah = (float) $tagihan->pembayarans
                ->where('status', 'diverifikasi')
                ->where('anak_kos_id', $userId)
                ->sum('jumlah');

            $sisa = max(0, $porsi - $sudah);

            if ($sisa > 0) {
                $hasil[] = [
                    'tagihan_id' => $tagihan->id,
                    'periode' => $tagihan->periode,
                    'porsi' => $porsi,
                    'sudah_bayar' => $sudah,
                    'sisa' => $sisa,
                ];
            }
        }

        return $hasil;
    }

    /**
     * Keluarkan anggota (atau penyewa utama bila masih ada yang stay).
     * Menolak bila user masih punya sisa porsi di tagihan belum lunas.
     */
    public static function keluarkan(Penyewaan $penyewaan, int $userId): void
    {
        $penyewaan->loadMissing(['tagihans.pembayarans', 'anggotas', 'kamar']);

        $sisa = self::sisaPorsi($penyewaan, $userId);

        if ($sisa !== []) {
            $total = array_sum(array_column($sisa, 'sisa'));
            throw new DomainException(
                'Lunasi dulu porsi patunganmu sebesar Rp'.number_format($total, 0, ',', '.')
                .' ('.count($sisa).' tagihan) sebelum keluar.'
            );
        }

        DB::transaction(function () use ($penyewaan, $userId) {
            $isUtama = $penyewaan->anak_kos_id === $userId;

            if ($isUtama) {
                $pengganti = $penyewaan->anggotas()
                    ->where('status', 'aktif')
                    ->where('user_id', '!=', $userId)
                    ->first();

                if ($pengganti) {
                    // Promosikan anggota yang stay menjadi penyewa utama.
                    $penyewaan->update(['anak_kos_id' => $pengganti->user_id]);
                    $pengganti->update(['status' => 'keluar', 'tanggal_keluar' => now()->toDateString()]);
                } else {
                    // Tidak ada yang stay: full checkout seperti alur tunggal.
                    $penyewaan->update([
                        'tanggal_keluar' => now()->toDateString(),
                        'status' => 'selesai',
                    ]);
                    optional($penyewaan->kamar)->update(['status' => 'tersedia']);

                    return;
                }
            } else {
                $penyewaan->anggotas()
                    ->where('user_id', $userId)
                    ->where('status', 'aktif')
                    ->update(['status' => 'keluar', 'tanggal_keluar' => now()->toDateString()]);
            }

            // Bila tidak ada lagi anggota aktif tersisa, kembalikan ke mode tunggal (porsi stay = 100%).
            $masihAda = $penyewaan->anggotas()->where('status', 'aktif')->exists();

            if (! $masihAda) {
                $penyewaan->update(['mode_hunian' => 'tunggal']);
            }

            // Kamar tetap terisi karena masih ada yang stay — tidak diubah ke tersedia.
        });
    }

    /**
     * Porsi satu tagihan untuk tiap penghuni: dibagi rata sesuai jumlah penghuni aktif.
     * 1 orang => 100%, 2 orang => 50% (termasuk denda proporsional).
     */
    public static function porsiTagihan(Penyewaan $penyewaan, $tagihan): float
    {
        $jumlahPenghuni = 1 + $penyewaan->anggotas->where('status', 'aktif')->count();

        if (! $penyewaan->isPatungan() || $jumlahPenghuni < 2) {
            return (float) $tagihan->jumlah + (float) $tagihan->denda;
        }

        return round(((float) $tagihan->jumlah + (float) $tagihan->denda) / $jumlahPenghuni, 2);
    }

    /**
     * Ringkasan porsi per tagihan untuk UI (siapa bayar berapa).
     */
    public static function ringkasanTagihan(Penyewaan $penyewaan): array
    {
        $penyewaan->loadMissing(['tagihans.pembayarans', 'anggotas', 'anakKos']);

        return $penyewaan->tagihans->map(function ($tagihan) use ($penyewaan) {
            $porsi = self::porsiTagihan($penyewaan, $tagihan);
            $bayarPerOrang = $tagihan->pembayarans
                ->where('status', 'diverifikasi')
                ->groupBy('anak_kos_id')
                ->map(fn ($rows) => (float) $rows->sum('jumlah'));

            return [
                'id' => $tagihan->id,
                'periode' => $tagihan->periode,
                'jumlah' => (float) $tagihan->jumlah + (float) $tagihan->denda,
                'status' => $tagihan->status,
                'porsi_per_orang' => $porsi,
                'bayar_per_orang' => $bayarPerOrang->all(),
            ];
        })->values()->all();
    }
}
