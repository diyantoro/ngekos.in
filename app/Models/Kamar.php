<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kamar extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'properti_id', 'nama', 'kapasitas', 'harga_sewa_bulanan', 'harga_sewa_harian', 'jenis_harga', 'harga_asli', 'status', 'foto',
    ];

    protected function casts(): array
    {
        return [
            'harga_sewa_bulanan' => 'decimal:2',
            'harga_sewa_harian' => 'decimal:2',
            'harga_asli' => 'decimal:2',
        ];
    }

    public function properti(): BelongsTo
    {
        return $this->belongsTo(Properti::class);
    }
}
