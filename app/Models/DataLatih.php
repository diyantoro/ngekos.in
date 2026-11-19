<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataLatih extends Model
{
    protected $table = 'data_latih';

    protected $fillable = [
        'kamar_id',
        'sumber',
        'luas_m2',
        'jarak_km',
        'kawasan',
        'tipe_penghuni',
        'fasilitas',
        'harga_aktual',
        'ditandai_outlier',
    ];

    protected function casts(): array
    {
        return [
            'luas_m2' => 'decimal:2',
            'jarak_km' => 'decimal:3',
            'harga_aktual' => 'decimal:2',
            'ditandai_outlier' => 'boolean',
            'fasilitas' => 'array',
        ];
    }

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class, 'kamar_id');
    }
}
