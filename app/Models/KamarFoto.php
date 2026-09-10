<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KamarFoto extends Model
{
    protected $fillable = [
        'kamar_id', 'path', 'urutan', 'is_cover',
    ];

    protected function casts(): array
    {
        return [
            'is_cover' => 'boolean',
        ];
    }

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class);
    }

    public function url(): ?string
    {
        return $this->path ? '/storage/'.$this->path : null;
    }
}
