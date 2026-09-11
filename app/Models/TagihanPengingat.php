<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TagihanPengingat extends Model
{
    protected $table = 'tagihan_pengingat';

    protected $fillable = [
        'tagihan_id', 'jenis', 'tanggal_kirim', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_kirim' => 'date',
            'meta' => 'array',
        ];
    }

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }
}
