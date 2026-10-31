<?php

namespace App\Models;

use App\Enums\BarangKategori;
use App\Enums\BarangKondisi;
use App\Enums\BarangStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BarangKos extends Model
{
    use HasFactory;

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

    public function scopeTersedia(Builder $query): Builder
    {
        return $query->where('status', BarangStatus::Tersedia->value);
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
        if (! empty($this->foto)) {
            return str_starts_with($this->foto, 'http') ? $this->foto : asset('storage/'.$this->foto);
        }

        return asset('images/default-properti.jpg');
    }

    /**
     * @return array<int, string>
     */
    public function semuaFoto(): array
    {
        $hasil = [];
        if (! empty($this->foto)) {
            $hasil[] = str_starts_with($this->foto, 'http') ? $this->foto : asset('storage/'.$this->foto);
        }

        if (is_array($this->foto_tambahan)) {
            foreach ($this->foto_tambahan as $path) {
                if (! empty($path)) {
                    $hasil[] = str_starts_with($path, 'http') ? $path : asset('storage/'.$path);
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
}
