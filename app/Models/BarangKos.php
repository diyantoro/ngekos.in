<?php

namespace App\Models;

use App\Enums\BarangKategori;
use App\Enums\BarangKondisi;
use App\Enums\BarangStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BarangKos extends Model
{
    use HasFactory;

    public const BATAS_BARANG_PER_AKUN = 2;

    protected $table = 'barang_kos';

    protected $fillable = [
        'user_id',
        'properti_id',
        'nama',
        'kategori',
        'kondisi',
        'harga',
        'bisa_nego',
        'deskripsi',
        'minus',
        'foto',
        'foto_tambahan',
        'kota',
        'lokasi_detail',
        'no_wa',
        'status',
        'dilihat',
        'tayang_sampai',
        'tayang_hari',
    ];

    protected function casts(): array
    {
        return [
            'kategori' => BarangKategori::class,
            'kondisi' => BarangKondisi::class,
            'status' => BarangStatus::class,
            'bisa_nego' => 'boolean',
            'harga' => 'integer',
            'dilihat' => 'integer',
            'foto_tambahan' => 'array',
            'tayang_sampai' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function properti(): BelongsTo
    {
        return $this->belongsTo(Properti::class);
    }

    public function pesans(): HasMany
    {
        return $this->hasMany(BarangPesan::class, 'barang_kos_id');
    }

    /**
     * Akun/toko tempat barang ini ditampilkan. Satu barang bisa tampil di
     * beberapa akun sekaligus sesuai kurasi superadmin.
     */
    public function akunTampil(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'barang_kos_akun', 'barang_id', 'user_id')
            ->withTimestamps();
    }

    protected static function booted(): void
    {
        // Barang baru otomatis tampil di toko pemiliknya. Superadmin bisa
        // menambah/mengurangi akun lewat halaman Kelola Pasar Kos.
        static::created(function (self $barang) {
            if ($barang->user_id) {
                $barang->akunTampil()->syncWithoutDetaching([$barang->user_id]);
            }
        });
    }

    public function iklanPasarKos(): HasOne
    {
        return $this->hasOne(IklanPasarKos::class, 'barang_kos_id');
    }

    /**
     * Barang yang belum lewat masa tayang. Masa tayang dihitung per hari,
     * jadi barang masih tampil sampai akhir hari tayang_sampai.
     */
    public function scopeBelumKedaluwarsa(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('tayang_sampai')
                ->orWhere('tayang_sampai', '>=', now()->startOfDay());
        });
    }

    public function scopeTersedia(Builder $query): Builder
    {
        return $query->where('status', BarangStatus::Tersedia->value)
            ->belumKedaluwarsa();
    }

    /**
     * Barang yang sudah di-assign ke minimal satu akun/toko. Barang tanpa
     * akun dianggap disembunyikan dari katalog publik.
     */
    public function scopeTampilDiKatalog(Builder $query): Builder
    {
        return $query->whereHas('akunTampil');
    }

    /**
     * Barang sudah lewat masa tayang (tidak tampil di katalog publik).
     */
    public function sudahKedaluwarsa(): bool
    {
        return $this->tayang_sampai !== null
            && $this->tayang_sampai->copy()->endOfDay()->lessThan(now());
    }

    public function scopeBelumTerjual(Builder $query): Builder
    {
        return $query->where('status', '!=', BarangStatus::Terjual->value);
    }

    public static function jumlahBelumTerjual(int $userId, ?int $kecualiId = null): int
    {
        return static::query()
            ->where('user_id', $userId)
            ->belumTerjual()
            ->when($kecualiId, fn (Builder $query) => $query->where('id', '!=', $kecualiId))
            ->count();
    }

    public function scopeKota(Builder $query, ?string $kota): Builder
    {
        if (empty($kota)) {
            return $query;
        }

        return $query->where('kota', $kota);
    }

    public function scopeKategori(Builder $query, ?string $kategori): Builder
    {
        if (empty($kategori)) {
            return $query;
        }

        return $query->where('kategori', $kategori);
    }

    public function fotoUrl(): string
    {
        return $this->urlFoto($this->foto);
    }

    /**
     * Ubah path foto tersimpan menjadi URL yang bisa dibuka browser.
     * Mendukung URL penuh, file di folder public (gambar demo), dan
     * hasil upload di disk public.
     */
    private function urlFoto(?string $path): string
    {
        if (empty($path)) {
            return asset('images/default-properti.jpg');
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return asset('storage/'.ltrim($path, '/'));
    }

    /**
     * @return array<int, string>
     */
    public function semuaFoto(): array
    {
        $hasil = [];

        if (! empty($this->foto)) {
            $hasil[] = $this->urlFoto($this->foto);
        }

        if (is_array($this->foto_tambahan)) {
            foreach ($this->foto_tambahan as $path) {
                if (! empty($path)) {
                    $hasil[] = $this->urlFoto($path);
                }
            }
        }

        if (empty($hasil)) {
            $hasil[] = asset('images/default-properti.jpg');
        }

        return $hasil;
    }

    public function formattedHarga(): string
    {
        return 'Rp '.number_format($this->harga, 0, ',', '.');
    }

    public function waLink(?string $pesan = null): string
    {
        $raw = $this->no_wa ?: ($this->user?->no_hp ?? '');
        $digits = preg_replace('/\D+/', '', (string) $raw);

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        }

        if (empty($digits)) {
            return '#';
        }

        if ($pesan === null) {
            $namaPenjual = $this->user?->nama ?? 'Kak';
            $pesan = "Halo {$namaPenjual}, saya tertarik dengan barang kos \"{$this->nama}\" di Ngekos.in ({$this->formattedHarga()}). Apakah barang ini masih tersedia?";
        }

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($pesan);
    }

    public function isMilik(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return (int) $this->user_id === (int) $user->id || $user->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Cek apakah iklan masih aktif (belum lewat tayang_sampai).
     */
    public function isAktif(): bool
    {
        if ($this->status !== BarangStatus::Tersedia) {
            return false;
        }

        return ! $this->sudahKedaluwarsa();
    }

    /**
     * Scope untuk iklan yang masih aktif (status tersedia dan belum lewat masa tayang).
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->tersedia();
    }
}
