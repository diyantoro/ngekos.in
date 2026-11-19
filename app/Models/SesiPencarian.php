<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SesiPencarian extends Model
{
    protected $table = 'sesi_pencarian';

    protected $fillable = [
        'user_id',
        'profil_id',
        'filter',
        'dibuat_pada',
    ];

    protected function casts(): array
    {
        return [
            'filter' => 'array',
            'dibuat_pada' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function profil(): BelongsTo
    {
        return $this->belongsTo(ProfilPreferensi::class, 'profil_id');
    }

    public function hasilPeringkat(): HasMany
    {
        return $this->hasMany(HasilPeringkat::class, 'sesi_id')->orderBy('peringkat');
    }
}
