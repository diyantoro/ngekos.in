<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TitikAcuan extends Model
{
    protected $table = 'titik_acuan';

    protected $fillable = [
        'nama',
        'latitude',
        'longitude',
        'kategori',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function profilPreferensi(): HasMany
    {
        return $this->hasMany(ProfilPreferensi::class, 'titik_acuan_id');
    }
}
