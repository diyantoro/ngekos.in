<?php

namespace App\Models;

use App\Models\BarangKos;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model untuk iklan pasar kos (iklan premium).
 * 
 * Setiap akun gratis maksimal 2 iklan. 
 * Untuk menambah batas, perlu berlangganan (belum diimplement).
 * Tayang maksimal 7 hari sesuai pengaturan penjual.
 */
class IklanPasarKos extends Model
{
    use HasFactory;

    protected $table = 'iklan_pasar_kos';

    protected $fillable = [
        'user_id',
        'barang_kos_id',
        'judul',
        'deskripsi',
        'gambar',
        'link',
        'gradient',
        'posisi',
        'akun_jumlah',
        'urutan',
        'tayang_dimulai',
        'tayang_sampai',
        'durasi_hari',
        'biaya',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tayang_dimulai' => 'datetime',
            'tayang_sampai' => 'datetime',
            'biaya' => 'integer',
            'durasi_hari' => 'integer',
            'status' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function barangKos(): BelongsTo
    {
        return $this->belongsTo(BarangKos::class, 'barang_kos_id');
    }

    /**
     * Cek apakah iklan masih aktif (belum lewat tayang_sampai).
     */
    public function isAktif(): bool
    {
        if (! $this->status) {
            return false;
        }

        if ($this->tayang_sampai === null) {
            return true;
        }

        return \Illuminate\Support\Carbon::now()->lessThanOrEqualTo($this->tayang_sampai);
    }

    /**
     * Scope untuk iklan aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('status', true)
            ->where(function ($q) {
                $q->whereNull('tayang_sampai')
                    ->orWhere('tayang_sampai', '>=', \Illuminate\Support\Carbon::now());
            });
    }

    /**
     * Scope untuk iklan banner (posisi banner_atas).
     * Mengambil banner yang sedang tayang dan aktif.
     */
    public function scopeBanner($query)
    {
        return $query->where('posisi', 'banner_atas')
            ->where('status', true)
            ->where(function ($q) {
                $q->whereNull('tayang_sampai')
                    ->orWhere('tayang_sampai', '>=', \Illuminate\Support\Carbon::now());
            })
            ->orderBy('urutan', 'asc')
            ->orderBy('tayang_sampai', 'desc');
    }

    /**
     * Scope untuk iklan sisipan di katalog.
     * Hanya mengambil iklan dengan posisi katalog_sisipan yang aktif.
     */
    public function scopeKatalogSisipan($query)
    {
        return $query->where('posisi', 'katalog_sisipan')
            ->where('status', true)
            ->where(function ($q) {
                $q->whereNull('tayang_sampai')
                    ->orWhere('tayang_sampai', '>=', \Illuminate\Support\Carbon::now());
            })
            ->orderBy('urutan', 'asc')
            ->orderBy('tayang_sampai', 'desc');
    }

    /**
     * Hitung sisa hari iklan.
     */
    public function sisaHari(): ?int
    {
        if ($this->tayang_sampai === null) {
            return null;
        }

        $now = \Illuminate\Support\Carbon::now();
        $end = $this->tayang_sampai;

        if ($now->greaterThan($end)) {
            return 0;
        }

        return $now->diffInDays($end, false);
    }

    /**
     * Batas akun gratis per user.
     */
    public static function batasAkunGratis(): int
    {
        return 2;
    }

    /**
     * Ambil batas iklan berdasarkan role user.
     */
    public static function batasIklanPerUser(?User $user): int
    {
        if (! $user) {
            return 0;
        }

        // Super admin punya batas tak terbatas
        if ($user->hasRole('super_admin')) {
            return PHP_INT_MAX;
        }

        // Admin juga punya batas tak terbatas (untuk manajemen)
        if ($user->hasRole('admin')) {
            return PHP_INT_MAX;
        }

        // Anak kos dan pemilik sama - batas gratis 2
        return self::batasAkunGratis();
    }

    /**
     * Hitung jumlah iklan aktif user saat ini.
     */
    public static function jumlahIklanAktifUser(User $user): int
    {
        return static::aktif()
            ->where('user_id', $user->id)
            ->count();
    }

    /**
     * Cek apakah user bisa membuat iklan baru.
     */
    public static function bisaBuatIklan(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return self::jumlahIklanAktifUser($user) < self::batasIklanPerUser($user);
    }
}
