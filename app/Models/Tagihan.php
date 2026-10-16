<?php

namespace App\Models;

use App\Enums\TagihanStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tagihan extends Model
{
    protected $fillable = [
        'penyewaan_id', 'periode', 'jumlah', 'jenis', 'denda', 'jatuh_tempo', 'status',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'denda' => 'decimal:2',
            'jatuh_tempo' => 'date',
        ];
    }

    public function penyewaan(): BelongsTo
    {
        return $this->belongsTo(Penyewaan::class);
    }

    /**
     * Tagihan yang belum lunas (belum dibayar).
     *
     * Enum TagihanStatus hanya berisi belum_bayar|lunas|batal dan kolom
     * NOT NULL, jadi ini setara whereNotIn(['lunas','batal']) tetapi
     * ramah index: memakai kesetaraan status agar index (status, ...)
     * terpakai (negasi NOT IN memaksa full-scan).
     */
    public function scopeBelumLunas(Builder $query): void
    {
        $query->where($query->getModel()->getTable().'.status', TagihanStatus::BelumBayar->value);
    }

    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }
}
