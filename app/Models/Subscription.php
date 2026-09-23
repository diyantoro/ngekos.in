<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'user_id', 'plan', 'status', 'is_trial', 'starts_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_trial' => 'boolean',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        // Belum mulai (jadwal masa depan) belum dihitung aktif.
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        return $this->expires_at === null || $this->expires_at->gte(now());
    }
}
