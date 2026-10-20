<?php

namespace App\Services;

use App\Enums\KamarStatus;
use App\Models\Properti;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Rekomendasi kos berbasis database untuk chatbot.
 *
 * Memahami pertanyaan seperti "ada kos di bawah 1,5 juta di Bandung?"
 * lalu mencari properti aktif yang masih punya kamar tersedia.
 */
class RekomendasiKosService
{
    private const KATA_KOS = ['kos', 'kost', 'kamar', 'sewa', 'kontrakan', 'hunian'];

    private const KATA_REKOMENDASI = [
        'rekomendasi', 'saran', 'sarankan', 'saranin', 'carikan', 'cariin',
        'butuh', 'mau', 'adakah', 'cocok', 'murah', 'budget', 'bujet',
        'di bawah', 'dibawah', 'maksimal', 'maks', 'kisaran', 'harga',
    ];

    /**
     * Apakah pertanyaan ini meminta rekomendasi kos (bukan panduan cara).
     */
    public function cocok(string $pertanyaan): bool
    {
        $teks = $this->normalisasi($pertanyaan);

        if ($teks === '') {
            return false;
        }

        // Jangan bajak pertanyaan panduan seperti "cara cari kos".
        if (
            str_starts_with($teks, 'cara ')
            || str_contains($teks, 'cara cari')
            || str_contains($teks, 'cara daftar')
            || str_contains($teks, 'cara bayar')
            || str_contains($teks, 'cara pesan')
            || str_contains($teks, 'cara sewa')
        ) {
            return false;
        }

        $adaKos = Str::contains($teks, self::KATA_KOS);
        $adaNiat = Str::contains($teks, self::KATA_REKOMENDASI);
        $adaAngka = preg_match('/\d/', $teks) === 1;

        return $adaKos && ($adaNiat || $adaAngka);
    }

    /**
     * Cari rekomendasi dan susun balasan chatbot.
     *
     * @return array{teks: string, tautan: array<int, array{label: string, url: string}>}
     */
    public function cari(string $pertanyaan): array
    {
        $budget = $this->parseBudget($pertanyaan);
        $kota = $this->parseKota($pertanyaan);
        $tipe = $this->parseTipe($pertanyaan);

        if ($budget === null) {
            return [
                'teks' => 'Boleh sebutkan budget per bulan? Contoh: "kos di bawah 1 juta di Bandung" atau "kos putri 1,5 juta".',
                'tautan' => [
                    ['label' => 'Lihat semua kos', 'url' => route('kos.index')],
                ],
            ];
        }

        $hasil = $this->query($budget, $kota, $tipe);

        if ($hasil->isEmpty()) {
            $filter = ' dengan budget Rp'.number_format($budget, 0, ',', '.');
            $filter .= $kota ? ' di '.$kota : '';
            $filter .= $tipe ? ' ('.$tipe.')' : '';

            return [
                'teks' => 'Belum ada kos tersedia'.$filter.'. Coba naikkan budget sedikit atau lihat semua kos yang tersedia.',
                'tautan' => [
                    ['label' => 'Lihat semua kos', 'url' => route('kos.index')],
                ],
            ];
        }

        $baris = [
            'Ini rekomendasi kos dengan budget sampai Rp'.number_format($budget, 0, ',', '.')
                .($kota ? ' di '.$kota : '')
                .($tipe ? ' ('.$tipe.')' : '').':',
        ];

        $tautan = [];

        foreach ($hasil->values() as $i => $properti) {
            $harga = (float) ($properti->harga_termurah ?? 0);
            $tersedia = (int) ($properti->kamar_tersedia ?? 0);

            $baris[] = ($i + 1).'. '.$properti->nama.' — '.$properti->kota;
            $baris[] = '   Mulai Rp'.number_format($harga, 0, ',', '.').'/bulan · '.$tersedia.' kamar tersedia';

            $tautan[] = [
                'label' => $properti->nama,
                'url' => route('kos.detail', $properti),
            ];
        }

        $baris[] = 'Ketuk salah satu kos di bawah untuk melihat detail dan fasilitasnya.';

        return [
            'teks' => implode("\n", $baris),
            'tautan' => $tautan,
        ];
    }

    /**
     * @return Collection<int, Properti>
     */
    private function query(float $budget, ?string $kota, ?string $tipe): Collection
    {
        $tersedia = fn ($q) => $q
            ->where('status', KamarStatus::Tersedia->value)
            ->where('harga_sewa_bulanan', '>', 0)
            ->where('harga_sewa_bulanan', '<=', $budget);

        return Properti::query()
            ->where('status', 'aktif')
            ->when($kota, fn ($q) => $q->where('kota', 'like', '%'.$kota.'%'))
            ->when($tipe, fn ($q) => $q->where('tipe_hunian', $tipe))
            ->whereHas('kamars', $tersedia)
            ->withMin(['kamars as harga_termurah' => $tersedia], 'harga_sewa_bulanan')
            ->withCount(['kamars as kamar_tersedia' => $tersedia])
            ->orderBy('harga_termurah')
            ->limit(5)
            ->get(['id', 'nama', 'kota', 'tipe_hunian']);
    }

    private function parseBudget(string $pertanyaan): ?float
    {
        $teks = mb_strtolower($pertanyaan);
        $teks = str_replace(['rp', 'Rp', 'RP'], ' ', $teks);

        $kandidat = [];

        // Rentang: "1 - 1,5 juta" / "1 sampai 2 juta".
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*(?:-|sampai|hingga|sd)\s*(\d+(?:[.,]\d+)?)\s*(juta|jt)\b/', $teks, $m)) {
            $kandidat[] = ((float) str_replace(',', '.', $m[2])) * 1_000_000;
        }

        // Juta / jt.
        if (preg_match_all('/(\d+(?:[.,]\d+)?)\s*(?:juta|jt)\b/', $teks, $m)) {
            foreach ($m[1] as $angka) {
                $kandidat[] = ((float) str_replace(',', '.', $angka)) * 1_000_000;
            }
        }

        // Ribu / rb / k.
        if (preg_match_all('/(\d+(?:[.,]\d+)?)\s*(?:ribu|rb|k)\b/', $teks, $m)) {
            foreach ($m[1] as $angka) {
                $kandidat[] = ((float) str_replace(',', '.', $angka)) * 1_000;
            }
        }

        // Angka rupiah penuh, mis. "budget 1500000".
        if (preg_match_all('/(?:budget|bujet|harga|maks(?:imal)?|di\s?bawah|dibawah)\s*(\d{4,})/', $teks, $m)) {
            foreach ($m[1] as $angka) {
                $kandidat[] = (float) $angka;
            }
        }

        $kandidat = array_values(array_filter($kandidat, fn ($n) => $n >= 100_000));

        return $kandidat === [] ? null : (float) max($kandidat);
    }

    private function parseKota(string $pertanyaan): ?string
    {
        $teks = mb_strtolower($pertanyaan);

        $kota = Properti::query()
            ->where('status', 'aktif')
            ->whereNotNull('kota')
            ->distinct()
            ->pluck('kota')
            ->filter()
            ->first(fn ($nama) => $nama && str_contains($teks, mb_strtolower((string) $nama)));

        if ($kota) {
            return (string) $kota;
        }

        // Alias umum yang sering dipakai pencari kos.
        $alias = [
            'jogja' => 'Yogyakarta',
            'yogya' => 'Yogyakarta',
            'jakarta' => 'Jakarta',
            'bandung' => 'Bandung',
            'surabaya' => 'Surabaya',
            'semarang' => 'Semarang',
            'malang' => 'Malang',
            'solo' => 'Surakarta',
            'bali' => 'Denpasar',
        ];

        foreach ($alias as $kunci => $nama) {
            if (str_contains($teks, $kunci)) {
                return $nama;
            }
        }

        return null;
    }

    private function parseTipe(string $pertanyaan): ?string
    {
        $teks = mb_strtolower($pertanyaan);

        return match (true) {
            str_contains($teks, 'putri'), str_contains($teks, 'perempuan'), str_contains($teks, 'wanita') => 'putri',
            str_contains($teks, 'putra'), str_contains($teks, 'laki'), str_contains($teks, 'pria') => 'putra',
            str_contains($teks, 'campur') => 'campur',
            default => null,
        };
    }

    private function normalisasi(string $teks): string
    {
        $teks = mb_strtolower($teks, 'UTF-8');
        $teks = (string) preg_replace('/[^\p{L}\p{N}\s,.\-]/u', ' ', $teks);
        $teks = (string) preg_replace('/\s+/', ' ', $teks);

        return trim($teks);
    }
}
