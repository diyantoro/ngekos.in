<?php

namespace App\Support;

use Illuminate\Support\Collection;

class NormalisasiKota
{
    /** Sub-daerah → kota induk (kunci & nilai sudah ternormalisasi). Tambahkan sesuai kebutuhan data. */
    public static array $alias = [
        'janti' => 'bantul',
        'palagan' => 'sleman',
    ];

    /**
     * Menyeragamkan nama kota: huruf kecil, spasi rapi, awalan
     * kota/kabupaten/kotamadya dihilangkan, tanda baca tepi dibersihkan.
     */
    public static function normalize(?string $kota): string
    {
        $kota = trim((string) $kota);
        $kota = mb_strtolower($kota, 'UTF-8');
        $kota = preg_replace('/\s+/', ' ', $kota) ?? $kota;
        $kota = preg_replace('/^(kota|kotamadya|kabupaten|kab\.|kab|city)\s+/', '', $kota) ?? $kota;

        return trim($kota, " \t\n\r\0\x0B.,-");
    }

    /**
     * Mengelompokkan daftar nama kota menjadi dua tingkat: kota induk → sub-daerah.
     * Sub-daerah dicari lewat alias atau aturan prefix terpanjang
     * ("bekasi selatan" berawalan "bekasi" → jadi sub-daerah "bekasi").
     *
     * @return Collection<int, array{nama: string, jumlah: int, daerah: array<int, array{nama: string, jumlah: int}>}>
     */
    public static function agregasi(iterable $kotaList): Collection
    {
        $counts = [];
        $spellings = [];

        foreach ($kotaList as $raw) {
            $raw = trim((string) $raw);
            if ($raw === '') {
                continue;
            }
            $n = self::normalize($raw);
            if ($n === '') {
                continue;
            }
            $counts[$n] = ($counts[$n] ?? 0) + 1;
            $spellings[$n][$raw] = ($spellings[$n][$raw] ?? 0) + 1;
        }

        if ($counts === []) {
            return collect();
        }

        $namaTampil = [];
        foreach ($spellings as $n => $variants) {
            arsort($variants);
            $namaTampil[$n] = array_key_first($variants);
        }

        $names = array_keys($counts);

        // Induk langsung: alias lebih dulu, sisanya pakai prefix terpanjang.
        $indukLangsung = [];
        foreach ($names as $n) {
            $indukLangsung[$n] = self::$alias[$n] ?? self::cariIndukPrefix($n, $names);
        }

        // Resolusi transiitif: cucu diangkat langsung ke induk paling atas (menjaga 2 tingkat).
        $induk = [];
        $anakDari = [];
        $resolve = null;
        $resolve = function (string $n) use (&$resolve, $indukLangsung, &$induk, &$anakDari): void {
            if (array_key_exists($n, $induk)) {
                return;
            }
            $p = $indukLangsung[$n] ?? null;
            if ($p === null || $p === $n) {
                $induk[$n] = null;

                return;
            }
            $resolve($p);
            $indukAtas = $induk[$p];
            if ($indukAtas === null) {
                $induk[$n] = $p;
                $anakDari[$p][$n] = true;
            } else {
                $induk[$n] = $indukAtas;
                $anakDari[$indukAtas][$n] = true;
            }
        };
        foreach ($names as $n) {
            $resolve($n);
        }

        // Kota induk dari alias yang belum tercatat datanya (mis. hanya ada "janti" tanpa "bantul").
        $semuaNama = $names;
        foreach ($induk as $n => $p) {
            if ($p !== null && ! in_array($p, $semuaNama, true)) {
                $semuaNama[] = $p;
                $counts[$p] = $counts[$p] ?? 0;
                $namaTampil[$p] = $namaTampil[$p] ?? ucwords($p);
            }
        }
        $semuaNama = array_values(array_unique($semuaNama));

        $hasil = [];
        foreach ($semuaNama as $n) {
            if (($induk[$n] ?? null) !== null) {
                continue;
            }
            $daerah = collect($anakDari[$n] ?? [])
                ->keys()
                ->map(fn ($d) => ['nama' => $namaTampil[$d], 'jumlah' => $counts[$d]])
                ->sortByDesc('jumlah')
                ->values()
                ->all();
            $total = $counts[$n] + collect($daerah)->sum('jumlah');
            $hasil[] = [
                'nama' => $namaTampil[$n],
                'jumlah' => $total,
                'daerah' => $daerah,
            ];
        }

        return collect($hasil)->sortByDesc('jumlah')->values();
    }

    /** Sub-daerah terpanjang yang menjadi awalan $n (jika ada). */
    private static function cariIndukPrefix(string $n, array $names): ?string
    {
        $best = null;
        foreach ($names as $p) {
            if ($p === $n) {
                continue;
            }
            if (str_starts_with($n, $p.' ')) {
                if ($best === null || strlen($p) > strlen($best)) {
                    $best = $p;
                }
            }
        }

        return $best;
    }
}