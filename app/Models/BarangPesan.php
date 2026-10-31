<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangPesan extends Model
{
    protected $fillable = [
        'barang_kos_id',
        'penjual_id',
        'pembeli_id',
        'pengirim_id',
        'pesan',
        'dibaca_pada',
    ];

    protected function casts(): array
    {
        return [
            'dibaca_pada' => 'datetime',
        ];
    }

    public function barangKos(): BelongsTo
    {
        return $this->belongsTo(BarangKos::class, 'barang_kos_id');
    }

    public function penjual(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penjual_id');
    }

    public function pembeli(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembeli_id');
    }

    public function pengirim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }

    /**
     * Scope percakapan untuk satu barang kos dan satu calon pembeli.
     */
    public function scopePercakapan(Builder $query, int $barangKosId, int $pembeliId): Builder
    {
        return $query->where('barang_kos_id', $barangKosId)
            ->where('pembeli_id', $pembeliId)
            ->orderBy('created_at')
            ->orderBy('id');
    }

    /**
     * Scope seluruh pesan yang melibatkan user (sebagai penjual atau pembeli).
     */
    public function scopeUntukUser(Builder $query, int $userId): Builder
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('penjual_id', $userId)
                ->orWhere('pembeli_id', $userId);
        });
    }

    /**
     * Mendapatkan lawan bicara berdasarkan user yang sedang aktif.
     */
    public function lawan(?User $user): ?User
    {
        if (! $user) {
            return $this->penjual;
        }

        return (int) $user->id === (int) $this->penjual_id
            ? $this->pembeli
            : $this->penjual;
    }

    public function isPengirim(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return (int) $this->pengirim_id === (int) $user->id;
    }
}
