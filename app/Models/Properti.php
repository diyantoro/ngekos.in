<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Properti extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pemilik_id', 'nama', 'kota', 'alamat', 'latitude', 'longitude', 'deskripsi', 'fasilitas', 'aturan', 'denda_per_hari',
        'harga', 'harga_mingguan', 'harga_harian', 'jenis_harga', 'harga_asli', 'status', 'foto',
    ];

    protected function casts(): array
    {
        return [
            'denda_per_hari' => 'decimal:2',
            'harga' => 'decimal:2',
            'harga_mingguan' => 'decimal:2',
            'harga_harian' => 'decimal:2',
            'harga_asli' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    protected static function booted(): void
    {
        $lupakanCache = function (): void {
            foreach (['katalog.markers', 'katalog.daftarKota', 'beranda.markers', 'beranda.daftarKota', 'beranda.kotaStatistik', 'beranda.totalProperti', 'beranda.totalKamar'] as $kunci) {
                try {
                    cache()->forget($kunci);
                } catch (\Throwable $e) {
                }
            }
        };

        static::saved($lupakanCache);
        static::deleted($lupakanCache);
    }

    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }

    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'admin_properti', 'properti_id', 'admin_id');
    }

    public function kamars(): HasMany
    {
        return $this->hasMany(Kamar::class);
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(PropertiFoto::class)->orderBy('urutan')->orderBy('id');
    }

    private ?array $galeriCache = null;

    /**
     * Daftar URL galeri (cover dulu). Fallback ke kolom foto lama bila galeri kosong.
     * Hasil di-memoize per instance agar pemanggilan berulang di satu render gratis.
     *
     * @return array<int,string>
     */
    public function galeriUrls(): array
    {
        if ($this->galeriCache !== null) {
            return $this->galeriCache;
        }

        $dariGaleri = $this->relationLoaded('fotos')
            ? $this->fotos->sortBy([['urutan', 'asc'], ['id', 'asc']])->pluck('path')->all()
            : $this->fotos()->orderBy('urutan')->orderBy('id')->pluck('path')->all();

        $urls = collect($dariGaleri)
            ->filter()
            ->map(fn ($p) => '/storage/'.$p)
            ->values()
            ->all();

        if ($urls === [] && $this->foto) {
            $urls[] = '/storage/'.$this->foto;
        }

        return $this->galeriCache = $urls;
    }

    public function fotoCover(): ?string
    {
        $galeri = $this->galeriUrls();

        return $galeri[0] ?? null;
    }

    /**
     * Harga acuan properti untuk satu periode.
     */
    public function hargaUntuk(string $periode): ?float
    {
        $nilai = match ($periode) {
            'mingguan' => $this->harga_mingguan,
            'harian' => $this->harga_harian,
            default => $this->harga,
        };

        return $nilai !== null ? (float) $nilai : null;
    }

    /**
     * @return array<int,string>
     */
    public function periodeTersedia(): array
    {
        $daftar = [];

        foreach (['bulanan', 'mingguan', 'harian'] as $periode) {
            $harga = $this->hargaUntuk($periode);

            if ($harga !== null && $harga > 0) {
                $daftar[] = $periode;
            }
        }

        return $daftar;
    }

    public function peminat(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'properti_favorits', 'properti_id', 'user_id')->withTimestamps();
    }

    public function ulasans(): HasMany
    {
        return $this->hasMany(Ulasan::class);
    }
}
