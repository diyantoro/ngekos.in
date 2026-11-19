<?php

namespace App\Models;

use App\Enums\KamarStatus;
use App\Support\FacilityHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kamar extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'properti_id', 'nama', 'kapasitas', 'ukuran', 'termasuk_listrik', 'harga_sewa_bulanan',
        'harga_sewa_mingguan', 'harga_sewa_harian', 'harga_asli', 'deposit', 'fasilitas',
        'peraturan', 'status', 'foto',
    ];

    protected function casts(): array
    {
        return [
            'harga_sewa_bulanan' => 'decimal:2',
            'harga_sewa_mingguan' => 'decimal:2',
            'harga_sewa_harian' => 'decimal:2',
            'harga_asli' => 'decimal:2',
            'deposit' => 'decimal:2',
            'termasuk_listrik' => 'boolean',
            'status' => KamarStatus::class,
        ];
    }

    /** @return array<int,string> */
    public function daftarFasilitas(): array
    {
        if (blank($this->fasilitas)) {
            return [];
        }

        return FacilityHelper::normalizeList(
            FacilityHelper::fromString((string) $this->fasilitas)
        );
    }

    public function properti(): BelongsTo
    {
        return $this->belongsTo(Properti::class);
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(KamarFoto::class)->orderBy('urutan')->orderBy('id');
    }

    private ?array $galeriCache = null;

    /**
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
     * Harga untuk satu periode ('bulanan'|'mingguan'|'harian').
     * Null bila pemilik tidak menetapkan harga periode itu.
     */
    public function hargaUntuk(string $periode): ?float
    {
        $nilai = match ($periode) {
            'mingguan' => $this->harga_sewa_mingguan,
            'harian' => $this->harga_sewa_harian,
            default => $this->harga_sewa_bulanan,
        };

        return $nilai !== null ? (float) $nilai : null;
    }

    /**
     * Daftar periode yang bisa dipilih pencari kos
     * (hanya yang harganya diisi pemilik).
     *
     * @return array<int,string>
     */
    public function periodeTersedia(): array
    {
        $daftar = [];

        foreach (['bulanan', 'mingguan', 'harian'] as $periode) {
            if ($this->hargaUntuk($periode) !== null && $this->hargaUntuk($periode) > 0) {
                $daftar[] = $periode;
            }
        }

        return $daftar;
    }

    public function prediksiHarga(): HasMany
    {
        return $this->hasMany(PrediksiHarga::class, 'kamar_id');
    }

    public function prediksiHargaTerbaru(): HasOne
    {
        return $this->hasOne(PrediksiHarga::class, 'kamar_id')->latestOfMany();
    }

    public function dataLatih(): HasMany
    {
        return $this->hasMany(DataLatih::class, 'kamar_id');
    }

    public function hasilPeringkat(): HasMany
    {
        return $this->hasMany(HasilPeringkat::class, 'kamar_id');
    }

    /**
     * Hitung luas kamar dalam m2 dari kolom 'ukuran' (mis. '3x4', '3 x 3.5 m', dll).
     * Jika tidak dapat diparsing, default ke 12 m2 (ukuran kos standar 3x4).
     */
    public function hitungLuasM2(): float
    {
        $teks = strtolower(trim((string) $this->ukuran));
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*[xX*×]\s*(\d+(?:[.,]\d+)?)/', $teks, $matches)) {
            $p = (float) str_replace(',', '.', $matches[1]);
            $l = (float) str_replace(',', '.', $matches[2]);
            $hasil = $p * $l;
            if ($hasil > 0 && $hasil < 100) {
                return round($hasil, 2);
            }
        }

        if (preg_match('/(\d+(?:[.,]\d+)?)/', $teks, $matches)) {
            $angka = (float) str_replace(',', '.', $matches[1]);
            if ($angka >= 4 && $angka <= 60) {
                return round($angka, 2);
            }
        }

        return 12.0;
    }
}
