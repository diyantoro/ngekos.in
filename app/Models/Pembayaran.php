<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    protected $fillable = [
        'tagihan_id', 'anak_kos_id', 'metode', 'jumlah', 'bukti', 'status', 'diverifikasi_oleh', 'verified_at',
        'nomor_kwitansi', 'file_kwitansi',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'verified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Badge Tagihan (pembayaran menunggu) di sidebar harus langsung
        // hilang/muncul seperti badge Pesan — lupakan cache pemiliknya
        // setiap ada pembayaran masuk / diverifikasi / ditolak / dihapus.
        $lupakanBadge = function (Pembayaran $pembayaran): void {
            try {
                $pembayaran->loadMissing('tagihan.penyewaan.properti:id,pemilik_id');
                $pemilikId = (int) ($pembayaran->tagihan?->penyewaan?->properti?->pemilik_id ?? 0);
                if ($pemilikId > 0) {
                    User::forgetPembayaranMenungguUntukPemilik($pemilikId);
                }
            } catch (\Throwable) {
                // Abaikan: badge cukup mengandalkan TTL 30 detik.
            }
        };

        static::created($lupakanBadge);
        static::updated($lupakanBadge);
        static::deleted($lupakanBadge);
    }

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }

    public function anakKos(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anak_kos_id');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function labelMetode(): string
    {
        return match ($this->metode) {
            'cash' => 'Tunai (Cash)',
            default => 'Transfer',
        };
    }
}
