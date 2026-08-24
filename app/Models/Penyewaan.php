<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penyewaan extends Model
{
    protected $fillable = [
        'anak_kos_id', 'kamar_id', 'properti_id', 'tanggal_masuk', 'tanggal_keluar', 'status', 'permintaan_keluar_pada',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_masuk' => 'date',
            'tanggal_keluar' => 'date',
            'permintaan_keluar_pada' => 'datetime',
        ];
    }

    public function anakKos(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anak_kos_id');
    }

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class);
    }

    public function properti(): BelongsTo
    {
        return $this->belongsTo(Properti::class);
    }

    public function tagihans(): HasMany
    {
        return $this->hasMany(Tagihan::class);
    }
}
