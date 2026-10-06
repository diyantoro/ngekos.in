<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Context;

class Subscription extends Model
{
    /**
     * Kunci cache request-scoped berisi seluruh baris Subscription milik
     * satu user. Satu query ini menggantikan puluhan query `subscriptions`
     * yang berulang di SubscriptionService pada satu render halaman.
     */
    public const MEMO_KEY = 'ngekos.subscription.langganan';

    protected $fillable = [
        'user_id', 'plan', 'status', 'is_trial', 'starts_at', 'expires_at',
    ];

    protected static function booted(): void
    {
        // Setiap perubahan baris langsung membuang memo + cache sidebar
        // agar UI tidak menampilkan FREE basi setelah upgrade ke PRO.
        static::saved(function (Subscription $s) {
            self::flushMemo();
            Cache::forget("navigasi.paket.v2.{$s->user_id}");
        });
        static::deleted(function (Subscription $s) {
            self::flushMemo();
            Cache::forget("navigasi.paket.v2.{$s->user_id}");
        });
    }

    /**
     * Buang seluruh memo langganan yang disimpan di scope request ini.
     */
    public static function flushMemo(): void
    {
        if (Context::has(self::MEMO_KEY)) {
            Context::forget(self::MEMO_KEY);
        }
    }

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
