<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penyewaan extends Model
{
    protected $fillable = [
        'anak_kos_id', 'kamar_id', 'properti_id', 'tanggal_masuk', 'tanggal_keluar', 'status', 'permintaan_keluar_pada',
        'ktp_path', 'mode_hunian',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_masuk' => 'date',
            'tanggal_keluar' => 'date',
            'permintaan_keluar_pada' => 'datetime',
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

    public function properti(): BelongsTo
    {
        return $this->belongsTo(Properti::class);
    }

    public function tagihans(): HasMany
    {
        return $this->hasMany(Tagihan::class);
    }

    public function anggotas(): HasMany
    {
        return $this->hasMany(PenyewaanAnggota::class);
    }

    public function anggotaAktif(): HasMany
    {
        return $this->hasMany(PenyewaanAnggota::class)->where('status', 'aktif');
    }

    /**
     * Seluruh penghuni aktif: penyewa utama + anggota patungan yang masih aktif.
     * Dipakai untuk otorisasi bayar & hitung porsi patungan.
     *
     * @return array<int>
     */
    public function idPenghuniAktif(): array
    {
        $ids = [$this->anak_kos_id];

        $tambahan = $this->relationLoaded('anggotas')
            ? $this->anggotas->where('status', 'aktif')->pluck('user_id')->all()
            : $this->anggotas()->where('status', 'aktif')->pluck('user_id')->all();

        return array_values(array_unique(array_merge($ids, $tambahan)));
    }

    public function isPatungan(): bool
    {
        return $this->mode_hunian === 'patungan';
    }
}
