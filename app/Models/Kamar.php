<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kamar extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'properti_id', 'nama', 'kapasitas', 'harga_sewa_bulanan', 'harga_sewa_mingguan', 'harga_sewa_harian', 'jenis_harga', 'harga_asli', 'status', 'foto',
    ];

    protected function casts(): array
    {
        return [
            'harga_sewa_bulanan' => 'decimal:2',
            'harga_sewa_mingguan' => 'decimal:2',
            'harga_sewa_harian' => 'decimal:2',
            'harga_asli' => 'decimal:2',
        ];
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
}
