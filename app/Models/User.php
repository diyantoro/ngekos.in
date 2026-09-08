<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['nama', 'email', 'no_hp', 'avatar', 'password', 'dinonaktifkan_pada', 'preferensi_notifikasi'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    private ?int $pesanBelumDibacaCache = null;

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
            'preferensi_notifikasi' => 'array',
        ];
    }

    /**
     * Daftar kunci preferensi notifikasi yang dikenal aplikasi.
     *
     * @return array<string, string>
     */
    public static function daftarNotifikasi(): array
    {
        return [
            'tagihan_baru' => 'Email saat tagihan bulanan dibuat',
            'pembayaran_diverifikasi' => 'Email saat pembayaran diverifikasi',
            'chat_baru' => 'Pemberitahuan pesan chat baru',
            'bantuan_balasan' => 'Notifikasi push saat admin membalas pesan bantuan',
        ];
    }

    /**
     * Preferensi notifikasi user untuk kunci tertentu (default: aktif).
     */
    public function notif(string $kunci): bool
    {
        return (bool) ($this->preferensi_notifikasi[$kunci] ?? true);
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
     * Properti yang ditandai (favorit) user ini.
     */
    public function favorits(): BelongsToMany
    {
        return $this->belongsToMany(Properti::class, 'properti_favorits', 'user_id', 'properti_id')->withTimestamps();
    }

    /**
     * Ulasan yang ditulis user ini.
     */
    public function ulasans(): HasMany
    {
        return $this->hasMany(Ulasan::class);
    }

    /**
     * Token perangkat (FCM) untuk notifikasi push.
     */
    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    /**
     * Jumlah pesan chat yang belum dibaca user ini
     * (sebagai penyewa atau sebagai pemilik kos).
     */
    public function pesanBelumDibaca(): int
    {
        return $this->pesanBelumDibacaCache ??= ChatPesan::query()
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

    /**
     * URL avatar user (fallback ke inisial nama).
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? asset('storage/'.$this->avatar) : null;
    }

    /**
     * Inisial dari nama user (untuk avatar placeholder).
     */
    public function getInisialAttribute(): string
    {
        $parts = explode(' ', trim($this->nama));
        if (count($parts) >= 2) {
            return strtoupper(mb_substr($parts[0], 0, 1).mb_substr(end($parts), 0, 1));
        }

        return strtoupper(mb_substr($this->nama, 0, 2));
    }
}
