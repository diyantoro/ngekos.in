<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'anak_kos_id', 'kamar_id', 'tanggal_booking', 'tanggal_masuk', 'durasi_bulan', 'durasi_hari', 'status', 'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_booking' => 'date',
            'tanggal_masuk' => 'date',
        ];
    }

    public function anakKos(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anak_kos_id');
    }

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class);
    }
}
