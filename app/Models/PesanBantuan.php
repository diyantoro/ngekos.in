<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesanBantuan extends Model
{
    protected $fillable = [
        'user_id', 'nama', 'email', 'subjek', 'pesan', 'status', 'balasan', 'dibalas_oleh', 'dibalas_at', 'dibaca_pada',
    ];

    protected function casts(): array
    {
        return [
            'dibalas_at' => 'datetime',
            'dibaca_pada' => 'datetime',
        ];
    }

    public function scopeBaru($query)
    {
        return $query->where('status', 'baru');
    }

    public static function jumlahBaru(): int
    {
        return (int) static::baru()->count();
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
