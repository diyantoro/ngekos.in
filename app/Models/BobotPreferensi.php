<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BobotPreferensi extends Model
{
    protected $table = 'bobot_preferensi';

    protected $fillable = [
        'profil_id',
        'kriteria_id',
        'bobot',
    ];

    protected function casts(): array
    {
        return [
            'bobot' => 'decimal:4',
        ];
    }

    public function profil(): BelongsTo
    {
        return $this->belongsTo(ProfilPreferensi::class, 'profil_id');
    }

    public function kriteria(): BelongsTo
    {
        return $this->belongsTo(Kriteria::class, 'kriteria_id');
    }
}
