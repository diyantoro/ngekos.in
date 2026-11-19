<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kriteria extends Model
{
    protected $table = 'kriteria';

    protected $fillable = [
        'kode',
        'nama',
        'tipe',
        'bobot_default',
    ];

    protected function casts(): array
    {
        return [
            'bobot_default' => 'decimal:4',
        ];
    }

    public function bobotPreferensi(): HasMany
    {
        return $this->hasMany(BobotPreferensi::class, 'kriteria_id');
    }
}
