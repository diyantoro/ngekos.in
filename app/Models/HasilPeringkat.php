<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HasilPeringkat extends Model
{
    protected $table = 'hasil_peringkat';

    protected $fillable = [
        'sesi_id',
        'kamar_id',
        'skor_preferensi',
        'peringkat',
        'detail_skor',
    ];

    protected function casts(): array
    {
        return [
            'skor_preferensi' => 'decimal:5',
            'detail_skor' => 'array',
        ];
    }

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(SesiPencarian::class, 'sesi_id');
    }

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class, 'kamar_id');
    }
}
