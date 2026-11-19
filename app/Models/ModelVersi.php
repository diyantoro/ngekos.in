<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModelVersi extends Model
{
    protected $table = 'model_versi';

    protected $fillable = [
        'versi',
        'algoritma',
        'hyperparameter',
        'mae',
        'rmse',
        'r2',
        'mape',
        'artifact_path',
        'status',
        'dilatih_pada',
    ];

    protected function casts(): array
    {
        return [
            'hyperparameter' => 'array',
            'mae' => 'decimal:2',
            'rmse' => 'decimal:2',
            'r2' => 'decimal:4',
            'mape' => 'decimal:2',
            'dilatih_pada' => 'datetime',
        ];
    }

    public function prediksiHarga(): HasMany
    {
        return $this->hasMany(PrediksiHarga::class, 'model_versi_id');
    }

    public static function modelAktif(): ?self
    {
        return self::where('status', 'aktif')->latest('dilatih_pada')->first();
    }
}
