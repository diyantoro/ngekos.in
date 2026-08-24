<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesanBantuan extends Model
{
    protected $fillable = [
        'user_id', 'nama', 'email', 'subjek', 'pesan', 'status', 'balasan', 'dibalas_oleh', 'dibalas_at',
    ];

    protected function casts(): array
    {
        return [
            'dibalas_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pembalas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibalas_oleh');
    }
}
