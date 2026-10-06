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
            'langganan_baru' => 'Push saat ada pembayaran langganan baru (admin)',
            'langganan_hampir_berakhir' => 'Pengingat H-3 paket langganan akan berakhir',
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
        // Jangan hanya cek baris terbaru: pemilik bisa punya trial Free
        // aktif + PRO terjadwal / sebaliknya. Cari yang benar-benar
        // aktif, dahulukan id terbaru.
        $list = $this->relationLoaded('subscriptions')
            ? $this->subscriptions->sortByDesc('id')
            : $this->subscriptions()->latest('id')->get();

        return $list->first(fn (Subscription $s) => $s->isActive());
    }

    public function dashboardRoute(): string
    {
        $role = $this->relationLoaded('roles')
            ? $this->roles->pluck('name')->first()
            : $this->getRoleNames()->first();

        return match ($role) {
            'super_admin' => 'dashboard.super-admin',
            'pemilik' => 'dashboard.pemilik',
            'admin' => 'dashboard.admin',
            default => 'dashboard.anak-kos',
        };
    }

    protected static array $memoPesan = [];

    protected static array $memoBantuan = [];

    public function pesanBelumDibaca(): int
    {
        if (! $this->hasAnyRole(['anak_kos', 'pemilik'])) {
            return 0;
        }

        $id = $this->getKey();

        if (array_key_exists($id, self::$memoPesan)) {
            return self::$memoPesan[$id];
        }

        return self::$memoPesan[$id] = cache()->remember(
            "chat.belum-dibaca.{$id}",
            30,
            fn () => $this->hitungPesanBelumDibaca()
        );
    }

    public function forgetPesanBelumDibacaCache(): void
    {
        unset(self::$memoPesan[$this->getKey()]);
        cache()->forget("chat.belum-dibaca.{$this->getKey()}");
    }

    private function hitungPesanBelumDibaca(): int
    {
        if ($this->hasRole('anak_kos')) {
            return ChatPesan::query()
                ->where('anak_kos_id', $this->id)
                ->where('pengirim_id', '!=', $this->id)
                ->whereNull('dibaca_pada')
                ->count();
        }

        $ids = Properti::where('pemilik_id', $this->id)->pluck('id')->all();

        if ($ids === []) {
            return 0;
        }

        return ChatPesan::query()
            ->whereIn('properti_id', $ids)
            ->where('pengirim_id', '!=', $this->id)
            ->whereNull('dibaca_pada')
            ->count();
    }

    public function bantuanMasukBelumDibaca(): int
    {
        if (! $this->hasAnyRole(['super_admin', 'admin'])) {
            return 0;
        }

        $id = $this->getKey();

        if (array_key_exists($id, self::$memoBantuan)) {
            return self::$memoBantuan[$id];
        }

        return self::$memoBantuan[$id] = cache()->remember('bantuan_masuk_count', 60, fn () => PesanBantuan::jumlahBaru());
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
