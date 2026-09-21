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
            'pengingat_tagihan' => 'Pengingat H-3, H-1 & jatuh tempo tagihan',
            'tagihan_telat' => 'Peringatan tagihan terlambat + denda harian',
            'pembayaran_diverifikasi' => 'Email saat pembayaran diverifikasi',
            'chat_baru' => 'Pemberitahuan pesan chat baru',
            'bantuan_balasan' => 'Notifikasi push saat admin membalas pesan bantuan',
            'bantuan_baru' => 'Push saat ada pesan bantuan baru (admin)',
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

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): ?Subscription
    {
        $subscription = $this->relationLoaded('subscriptions')
            ? $this->subscriptions->sortByDesc('id')->first()
            : $this->subscriptions()->latest('id')->first();

        return $subscription && $subscription->isActive() ? $subscription : null;
    }

    /**
     * Jumlah pesan chat yang belum dibaca user ini
     * (sebagai penyewa atau sebagai pemilik kos).
     * Dicabang per role agar tanpa OR + subquery properti yang berat;
     * role lain (admin/super_admin) tidak memakai chat sehingga langsung 0.
     */
    public function pesanBelumDibaca(): int
    {
        if ($this->hasRole('anak_kos')) {
            return ChatPesan::query()
                ->where('anak_kos_id', $this->id)
                ->where('pengirim_id', '!=', $this->id)
                ->whereNull('dibaca_pada')
                ->count();
        }

        if ($this->hasRole('pemilik')) {
            $ids = cache()->remember("pemilik.properti-ids.{$this->id}", 300, fn () => Properti::where('pemilik_id', $this->id)->pluck('id')->all());

            if ($ids === []) {
                return 0;
            }

            return ChatPesan::query()
                ->whereIn('properti_id', $ids)
                ->where('pengirim_id', '!=', $this->id)
                ->whereNull('dibaca_pada')
                ->count();
        }

        return 0;
    }

    public function bantuanMasukBelumDibaca(): int
    {
        if (! $this->hasAnyRole(['super_admin', 'admin'])) {
            return 0;
        }

        return cache()->remember('bantuan_masuk_count', 60, fn () => PesanBantuan::jumlahBaru());
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
