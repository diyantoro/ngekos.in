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
