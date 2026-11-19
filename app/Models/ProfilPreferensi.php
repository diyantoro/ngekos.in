<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfilPreferensi extends Model
{
    protected $table = 'profil_preferensi';

    protected $fillable = [
        'user_id',
        'titik_acuan_id',
        'nama_profil',
        'metode',
        'budget_maks',
    ];

    protected function casts(): array
    {
        return [
            'budget_maks' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function titikAcuan(): BelongsTo
    {
        return $this->belongsTo(TitikAcuan::class, 'titik_acuan_id');
    }

    public function bobots(): HasMany
    {
        return $this->hasMany(BobotPreferensi::class, 'profil_id');
    }

    public function sesiPencarian(): HasMany
    {
        return $this->hasMany(SesiPencarian::class, 'profil_id');
    }
}
