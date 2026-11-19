<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrediksiHarga extends Model
{
    protected $table = 'prediksi_harga';

    protected $fillable = [
        'kamar_id',
        'model_versi_id',
        'harga_prediksi',
        'selisih_persen',
        'label_kewajaran',
    ];

    protected function casts(): array
    {
        return [
            'harga_prediksi' => 'decimal:2',
            'selisih_persen' => 'decimal:2',
        ];
    }

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class, 'kamar_id');
    }

    public function modelVersi(): BelongsTo
    {
        return $this->belongsTo(ModelVersi::class, 'model_versi_id');
    }

    /**
     * Teks badge dan warna untuk antarmuka.
     */
    public function getLabelBadgeAttribute(): array
    {
        return match ($this->label_kewajaran) {
            'di_bawah_pasar' => [
                'text' => 'Di Bawah Pasar (Murah)',
                'class' => 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800',
            ],
            'di_atas_pasar' => [
                'text' => 'Di Atas Pasar (Mahal)',
                'class' => 'bg-rose-100 text-rose-800 border-rose-300 dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-800',
            ],
            default => [
                'text' => 'Wajar',
                'class' => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800',
            ],
        };
    }
}
