<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatPesan extends Model
{
    protected $fillable = [
        'properti_id', 'anak_kos_id', 'pengirim_id', 'isi', 'dibaca_pada',
    ];

    protected function casts(): array
    {
        return [
            'dibaca_pada' => 'datetime',
        ];
    }

    public function properti(): BelongsTo
    {
        return $this->belongsTo(Properti::class);
    }

    public function anakKos(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anak_kos_id');
    }

    public function pengirim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }

    /**
     * Pesan dalam satu percakapan (satu properti + satu penyewa).
     */
    public function scopeAntara(Builder $query, int $propertiId, int $anakKosId): Builder
    {
        return $query->where('properti_id', $propertiId)
            ->where('anak_kos_id', $anakKosId)
            ->orderBy('created_at')
            ->orderBy('id');
    }

    /**
     * Beri tahu pemilik lewat chat bahwa anak kos sudah mengajukan pembayaran.
     */
    public static function notifikasiPembayaranDiajukan(Pembayaran $pembayaran): self
    {
        $tagihan = $pembayaran->tagihan;

        return self::create([
            'properti_id' => $tagihan->penyewaan->properti_id,
            'anak_kos_id' => $pembayaran->anak_kos_id,
            'pengirim_id' => $pembayaran->anak_kos_id,
            'isi' => 'Saya sudah membayar tagihan '.$tagihan->periode
                .' di '.$tagihan->penyewaan->properti->nama
                .' sebesar Rp'.number_format((float) $pembayaran->jumlah, 0, ',', '.')
                .' via '.$pembayaran->labelMetode().'. Mohon diverifikasi. Terima kasih.',
        ]);
    }

    /**
     * Beri tahu teman + utama bahwa anggota patungan baru ditambahkan.
     * Dua thread (per anak_kos_id) agar terbaca teman, utama, dan pemilik.
     *
     * @return array{ke_teman: self, ke_utama: self}
     */
    public static function notifikasiAnggotaDitambah(
        int $propertiId,
        int $utamaId,
        string $utamaNama,
        int $temanId,
        string $temanNama,
        string $kamarNama,
        string $propertiNama,
    ): array {
        $keTeman = self::create([
            'properti_id' => $propertiId,
            'anak_kos_id' => $temanId,
            'pengirim_id' => $utamaId,
            'isi' => 'Halo '.$temanNama.', kamu ditambahkan oleh '.$utamaNama
                .' sebagai teman sekamar '.$kamarNama.' di '.$propertiNama
                .' (patungan 50/50). Porsimu 50% tiap tagihan — pantau di Sewa Saya & tab Tagihan.',
        ]);

        $keUtama = self::create([
            'properti_id' => $propertiId,
            'anak_kos_id' => $utamaId,
            'pengirim_id' => $temanId,
            'isi' => 'Halo '.$utamaNama.', '.$temanNama.' sudah bergabung di kamar '
                .$kamarNama.' (patungan 50/50). KTP-nya sudah tersimpan dan bisa dicek pemilik.',
        ]);

        return ['ke_teman' => $keTeman, 'ke_utama' => $keUtama];
    }

    /**
     * Beri tahu leaver + stayer bahwa satu penghuni keluar patungan.
     *
     * @param  array<int>  $stayerIds
     * @return array<int, self>
     */
    public static function notifikasiAnggotaKeluar(
        int $propertiId,
        int $leaverId,
        string $leaverNama,
        array $stayerIds,
        string $kamarNama,
    ): array {
        $hasil = [];

        $hasil[] = self::create([
            'properti_id' => $propertiId,
            'anak_kos_id' => $leaverId,
            'pengirim_id' => $leaverId,
            'isi' => 'Kamu sudah keluar dari kamar '.$kamarNama
                .' (patungan). Porsi tagihan berikutnya menjadi tanggung jawab penghuni yang stay.',
        ]);

        foreach (array_values(array_unique($stayerIds)) as $stayerId) {
            if ($stayerId === $leaverId) {
                continue;
            }

            $hasil[] = self::create([
                'properti_id' => $propertiId,
                'anak_kos_id' => $stayerId,
                'pengirim_id' => $leaverId,
                'isi' => $leaverNama.' sudah keluar dari kamar '.$kamarNama
                    .'. Mulai tagihan berikutnya porsimu 100%. Kamar tetap terisi.',
            ]);
        }

        return $hasil;
    }

    /**
     * Pengingat tagihan H-3 / H-1 / H0 / telat-harian ke satu penghuni.
     * Dibuat oleh sistem (pengirim = pemilik properti) agar masuk
     * menu Pesan + badge belum dibaca anak kos.
     */
    public static function notifikasiTagihan(
        int $propertiId,
        int $anakKosId,
        int $pengirimId,
        string $isi,
    ): self {
        return self::create([
            'properti_id' => $propertiId,
            'anak_kos_id' => $anakKosId,
            'pengirim_id' => $pengirimId,
            'isi' => $isi,
        ]);
    }

    /**
     * Balas ke anak kos bahwa pembayarannya telah diverifikasi.
     * Verifikator bisa pemilik, admin, atau super admin.
     */
    public static function notifikasiPembayaranDiverifikasi(Pembayaran $pembayaran, int $verifikatorId): self
    {
        $isi = 'Pembayaran '.$pembayaran->tagihan->periode
            .' sebesar Rp'.number_format((float) $pembayaran->jumlah, 0, ',', '.')
            .' telah saya verifikasi. Terima kasih.';

        if ($pembayaran->nomor_kwitansi) {
            $isi .= ' Kwitansi '.$pembayaran->nomor_kwitansi.' tersedia dan bisa diunduh di dashboard (tab Pembayaran Saya).';
        }

        return self::create([
            'properti_id' => $pembayaran->tagihan->penyewaan->properti_id,
            'anak_kos_id' => $pembayaran->anak_kos_id,
            'pengirim_id' => $verifikatorId,
            'isi' => $isi,
        ]);
    }
}
