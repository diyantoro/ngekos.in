<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['nama', 'email', 'no_hp', 'password', 'dinonaktifkan_pada'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'dinonaktifkan_pada' => 'datetime',
        ];
    }

    /**
     * Akun masih aktif (tidak dinonaktifkan super admin).
     */
    public function aktif(): bool
    {
        return $this->dinonaktifkan_pada === null;
    }

    /**
     * Properti yang dimiliki user ini (role: pemilik).
     */
    public function propertis(): HasMany
    {
        return $this->hasMany(Properti::class, 'pemilik_id');
    }

    /**
     * Booking yang diajukan user ini (role: anak kos).
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'anak_kos_id');
    }

    /**
     * Jumlah pesan chat yang belum dibaca user ini
     * (sebagai penyewa atau sebagai pemilik kos).
     */
    public function pesanBelumDibaca(): int
    {
        return ChatPesan::query()
            ->where('pengirim_id', '!=', $this->id)
            ->whereNull('dibaca_pada')
            ->where(function ($q) {
                $q->where('anak_kos_id', $this->id)
                    ->orWhereHas('properti', fn ($p) => $p->where('pemilik_id', $this->id));
            })
            ->count();
    }

    /**
     * Merupakan super admin atau tidak.
     */
    public function getIsSuperAdminAttribute(): bool
    {
        return $this->hasRole('super_admin');
    }
}
