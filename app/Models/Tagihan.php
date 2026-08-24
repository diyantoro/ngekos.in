<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tagihan extends Model
{
    protected $fillable = [
        'penyewaan_id', 'periode', 'jumlah', 'denda', 'jatuh_tempo', 'status',
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

    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }
}
