<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Properti extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pemilik_id', 'nama', 'kota', 'alamat', 'latitude', 'longitude', 'deskripsi', 'fasilitas', 'aturan', 'denda_per_hari',
        'harga', 'jenis_harga', 'status', 'foto',
    ];

    protected function casts(): array
    {
        return [
            'denda_per_hari' => 'decimal:2',
            'harga' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }

    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'admin_properti', 'properti_id', 'admin_id');
    }

    public function kamars(): HasMany
    {
        return $this->hasMany(Kamar::class);
    }

    public function peminat(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'properti_favorits', 'properti_id', 'user_id')->withTimestamps();
    }

    public function ulasans(): HasMany
    {
        return $this->hasMany(Ulasan::class);
    }
}
