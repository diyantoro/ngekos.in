<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertiFoto extends Model
{
    protected $fillable = [
        'properti_id', 'path', 'urutan', 'is_cover',
    ];

    protected function casts(): array
    {
        return [
            'is_cover' => 'boolean',
        ];
    }

    public function properti(): BelongsTo
    {
        return $this->belongsTo(Properti::class);
    }

    public function url(): ?string
    {
        return $this->path ? '/storage/'.$this->path : null;
    }
}
