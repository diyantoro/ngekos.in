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
