<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pengeluaran operasional sebuah properti kos.
 * Kategori mengikuti daftar tetap: listrik, air, internet, maintenance,
 * kebersihan, gaji, renovasi, lainnya.
 */
class Pengeluaran extends Model
{
    protected $fillable = [
        'properti_id', 'kategori', 'keterangan', 'jumlah', 'tanggal', 'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'tanggal' => 'date',
        ];
    }

    public const KATEGORI = [
        'listrik', 'air', 'internet', 'maintenance', 'kebersihan', 'gaji', 'renovasi', 'lainnya',
    ];

    public function properti(): BelongsTo
    {
        return $this->belongsTo(Properti::class);
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
}