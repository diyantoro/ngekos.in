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
}
