<?php

namespace App\Services;

use App\Enums\KamarStatus;
use App\Models\Properti;
use App\Support\DaftarKampus;
use App\Support\DaftarKota;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Rekomendasi kos berbasis database untuk chatbot.
 *
 * Memahami pertanyaan seperti:
 * - "ada kos di bawah 1,5 juta di Bandung?"
 * - "kos di Palembang dekat kampus UNSRI"
 * - "kalau budget 700rb?" (melanjutkan pertanyaan sebelumnya)
 * - "kos di kota mana saja?" / "dekat kampus mana?"
 *
 * Sumber jawaban selalu data properti + kamar yang tersedia di database,
 * bukan tebakan. Bila area kampus tidak tercantum di alamat kos, chatbot
 * mengatakan apa adanya lalu menampilkan kos di kota yang sama.
 */
class RekomendasiKosService
{
    private const KATA_KOS = ['kos', 'kost', 'kamar', 'sewa', 'kontrakan', 'hunian'];

    private const KATA_REKOMENDASI = [
        'rekomendasi', 'saran', 'sarankan', 'saranin', 'carikan', 'cariin',
        'butuh', 'mau', 'adakah', 'ada', 'cocok', 'murah', 'budget', 'bujet',
        'di bawah', 'dibawah', 'maksimal', 'maks', 'kisaran', 'harga', 'sewa',
        'tersedia', 'dekat', 'sekitar', 'kampus', 'universitas', 'kota',
        'daerah', 'wilayah', 'area', 'lokasi', 'peta',
    ];

    private const STOPWORDS_LANDMARK = [
        'mana', 'apa', 'saja', 'aja', 'itu', 'ini', 'yang', 'di', 'ke', 'dari',
        'dekat', 'didekat', 'sekitar', 'disekitar', 'area', 'kawasan', 'wilayah',
        'kampus', 'universitas', 'univ', 'politeknik', 'poltek', 'dan', 'atau',
        'budget', 'bujet', 'harga', 'murah', 'mahal', 'ada', 'kos', 'kost',
        'kamar', 'tersedia', 'tidak', 'kurang', 'sampai', 'maks', 'maksimal',
        'rp', 'juta', 'jt', 'ribu', 'rb', 'orang', 'putra', 'putri', 'campur',
        'juga', 'ya', 'dong', 'nih', 'sih', 'kak', 'min', 'bang', 'bro', 'pak',
        'bu', 'mas', 'mbak', 'cari', 'carikan', 'rekomendasi', 'saran',
        'berapa', 'harganya', 'biaya', 'tarif', 'perbulan', 'per', 'bulan',
    ];

    /**
     * Apakah pertanyaan ini meminta rekomendasi/ringkasan kos (bukan panduan cara).
     *
     * @param  array<string, mixed>  $konteks  Kriteria pencarian sebelumnya (untuk pertanyaan lanjutan).
     */
    public function cocok(string $pertanyaan, array $konteks = []): bool
    {
        $teks = $this->normalisasi($pertanyaan);

        if ($teks === '') {
            return false;
        }

        // Jangan bajak pertanyaan panduan seperti "cara cari kos".
        if ($this->pertanyaanPanduan($teks)) {
            return false;
        }

        $adaKos = Str::contains($teks, self::KATA_KOS);
        $adaNiat = Str::contains($teks, self::KATA_REKOMENDASI);
        $adaAngka = preg_match('/\d/', $teks) === 1;
        $kampus = DaftarKampus::cocok($teks);
        $kota = $this->parseKota($pertanyaan);
        $budget = $this->parseBudget($pertanyaan);
        $tipe = $this->parseTipe($pertanyaan);

        if ($adaKos && ($adaNiat || $adaAngka || $kota !== null || $kampus !== null)) {
            return true;
        }

        // Pertanyaan ringkasan yang tidak selalu menyebut kata "kos".
        if (preg_match('/(?:kota|daerah|wilayah)\s+(?:apa|mana)|kos\s+di\s*mana/u', $teks) === 1) {
            return true;
        }

        if ($kampus !== null) {
            return true;
        }

        if (Str::contains($teks, ['kampus', 'universitas']) && $this->bertanya($teks)) {
            return true;
        }

        // "budget 700rb", "700.000", "di bawah 1 juta" tanpa kata kos.
        if ($budget !== null && ($adaNiat || $adaAngka) && mb_strlen($teks) <= 60) {
            return true;
        }

        // Pesan singkat berisi nama kota, mis. "Palembang" / "ada di Bandung?".
        if ($kota !== null && count(preg_split('/\s+/', $teks) ?: []) <= 4) {
            return true;
        }

        // Pertanyaan lanjutan: "budget 700000", "kalau di Bandung?", "yang putri".
        if ($konteks !== [] && ($budget !== null || $kota !== null || $kampus !== null || $tipe !== null)) {
            return true;
        }

        return false;
    }

    /**
     * Cari rekomendasi dan susun balasan chatbot.
     *
     * @param  array<string, mixed>  $konteks  Kriteria pencarian sebelumnya.
     * @return array{
     *     teks: string,
     *     tautan: array<int, array{label: string, url: string}>,
     *     konteks: array<string, mixed>
     * }
     */
    public function cari(string $pertanyaan, array $konteks = []): array
    {
        $baru = $this->parse($pertanyaan);
        $kriteria = $this->gabungKonteks($konteks, $baru);
        $teks = $this->normalisasi($pertanyaan);

        if ($this->tanyaKampus($teks, $baru)) {
            return $this->ringkasanKampus($baru['kota'] ?? $kriteria['kota'] ?? null);
        }

        if ($this->tanyaKota($teks, $baru)) {
            return $this->ringkasanKota();
        }

        if ($this->tanyaArea($teks, $baru, $kriteria['kota'] ?? null)) {
            return $this->ringkasanArea((string) $kriteria['kota']);
        }

        if (! $this->adaKriteria($kriteria)) {
            return [
                'teks' => 'Boleh sebutkan lokasi atau budget-mu? Contoh: "kos di Bandung dekat kampus ITB" atau "kos putri di bawah 1,5 juta".',
                'tautan' => [
                    ['label' => 'Lihat semua kos', 'url' => route('kos.index')],
                ],
                'konteks' => [],
            ];
        }

        $lokasi = $this->tentukanLokasi($kriteria);
        $hasil = $this->jalankanQuery($kriteria, $lokasi);
        $fallback = false;

        // Area kampus/landmark tidak selalu dicantumkan di alamat kos.
        // Bila hasilnya kosong, tampilkan kos di kota yang sama dan katakan
        // terus terang bahwa alamatnya tidak menyebut area tersebut.
        $adaArea = collect($lokasi)->contains(fn (array $l) => $l['area'] !== []);

        if ($hasil->isEmpty() && $adaArea && ($kriteria['kota'] !== null || $kriteria['kampus'] !== null)) {
            $fallback = true;
            $longgar = array_map(function (array $loc): array {
                $loc['area'] = [];

                return $loc;
            }, $lokasi);

            $hasil = $this->jalankanQuery($kriteria, $longgar);

            if ($hasil->isNotEmpty()) {
                $lokasi = $longgar;
            }
        }

        $hasil = $hasil
            ->unique('id')
            ->sortBy(fn (Properti $p) => (float) $p->harga_termurah)
            ->take(5)
            ->values();

        if ($hasil->isEmpty()) {
            return $this->balasanKosong($kriteria);
        }

        return $this->balasanHasil($kriteria, $hasil, $fallback, $lokasi);
    }

    /**
     * @return array{budget: float|null, kota: string|null, tipe: string|null, kampus: string|null, landmark: string|null}
     */
    private function parse(string $pertanyaan): array
    {
        $kampus = DaftarKampus::cocok($pertanyaan);

        return [
            'budget' => $this->parseBudget($pertanyaan),
            'kota' => $this->parseKota($pertanyaan),
            'tipe' => $this->parseTipe($pertanyaan),
            'kampus' => $kampus['nama'] ?? null,
            'landmark' => $kampus === null ? $this->parseLandmark($pertanyaan) : null,
        ];
    }

    /**
     * Gabungkan kriteria baru dengan konteks percakapan sebelumnya.
     *
     * @param  array<string, mixed>  $lama
     * @param  array<string, mixed>  $baru
     * @return array<string, mixed>
     */
    private function gabungKonteks(array $lama, array $baru): array
    {
        $hasil = [
            'budget' => $lama['budget'] ?? null,
            'kota' => $lama['kota'] ?? null,
            'tipe' => $lama['tipe'] ?? null,
            'kampus' => $lama['kampus'] ?? null,
            'landmark' => $lama['landmark'] ?? null,
        ];

        if (($baru['kampus'] ?? null) !== null) {
            $hasil['kampus'] = $baru['kampus'];
            $hasil['landmark'] = null;

            // Kampus baru menentukan lokasinya sendiri, kecuali user juga
            // menyebut kota di pesan yang sama.
            if (($baru['kota'] ?? null) === null) {
                $hasil['kota'] = null;
            }
        }

        if (($baru['landmark'] ?? null) !== null) {
            $hasil['landmark'] = $baru['landmark'];
            $hasil['kampus'] = null;
        }

        if (($baru['kota'] ?? null) !== null) {
            $hasil['kota'] = $baru['kota'];

            $kampus = DaftarKampus::cariNama($hasil['kampus']);

            if ($kampus !== null) {
                $adaDiKota = collect($kampus['lokasi'])
                    ->contains(fn (array $l) => strcasecmp($l['kota'], (string) $hasil['kota']) === 0);

                if (! $adaDiKota) {
                    $hasil['kampus'] = null;
                    $hasil['landmark'] = null;
                }
            }
        }

        if (($baru['budget'] ?? null) !== null) {
            $hasil['budget'] = $baru['budget'];
        }

        if (($baru['tipe'] ?? null) !== null) {
            $hasil['tipe'] = $baru['tipe'];
        }

        return $hasil;
    }

    /**
     * @param  array<string, mixed>  $kriteria
     */
    private function adaKriteria(array $kriteria): bool
    {
        return $kriteria['budget'] !== null
            || $kriteria['kota'] !== null
            || $kriteria['kampus'] !== null
            || $kriteria['landmark'] !== null
            || $kriteria['tipe'] !== null;
    }

    /**
     * Tentukan kota + area yang akan dicari.
     *
     * @param  array<string, mixed>  $kriteria
     * @return array<int, array{kota: string|null, area: array<int, string>, label: string, kampus: string|null}>
     */
    private function tentukanLokasi(array $kriteria): array
    {
        $kampus = DaftarKampus::cariNama($kriteria['kampus'] ?? null);

        if ($kampus !== null) {
            $lokasi = $kampus['lokasi'];

            // Bila user menyebut kota, pakai lokasi kampus yang ada di kota itu.
            if ($kriteria['kota'] !== null) {
                $sesuaiKota = array_values(array_filter(
                    $lokasi,
                    fn (array $l) => strcasecmp($l['kota'], (string) $kriteria['kota']) === 0
                ));

                if ($sesuaiKota !== []) {
                    $lokasi = $sesuaiKota;
                }
            }

            return array_map(fn (array $l) => [
                'kota' => $l['kota'],
                'area' => $l['area'],
                'label' => $kampus['nama'].' — '.ucwords(implode(', ', $l['area'])).', '.$l['kota'],
                'kampus' => $kampus['nama'],
            ], $lokasi);
        }

        if ($kriteria['kota'] !== null) {
            return [[
                'kota' => $kriteria['kota'],
                'area' => $kriteria['landmark'] !== null ? [$kriteria['landmark']] : [],
                'label' => $kriteria['landmark'] !== null
                    ? 'area '.ucwords((string) $kriteria['landmark']).', '.$kriteria['kota']
                    : $kriteria['kota'],
                'kampus' => null,
            ]];
        }

        if ($kriteria['landmark'] !== null) {
            return [[
                'kota' => null,
                'area' => [$kriteria['landmark']],
                'label' => 'area '.ucwords((string) $kriteria['landmark']),
                'kampus' => null,
            ]];
        }

        return [['kota' => null, 'area' => [], 'label' => 'semua kota', 'kampus' => null]];
    }

    /**
     * @param  array<string, mixed>  $kriteria
     * @param  array<int, array{kota: string|null, area: array<int, string>, label: string, kampus: string|null}>  $lokasi
     * @return Collection<int, Properti>
     */
    private function jalankanQuery(array $kriteria, array $lokasi): Collection
    {
        $hasil = collect();

        foreach ($lokasi as $loc) {
            foreach ($this->querySatuLokasi($kriteria, $loc) as $properti) {
                $hasil->push($properti);
            }
        }

        return $hasil;
    }

    /**
     * @param  array<string, mixed>  $kriteria
     * @param  array{kota: string|null, area: array<int, string>, label: string, kampus: string|null}  $lokasi
     * @return Collection<int, Properti>
     */
    private function querySatuLokasi(array $kriteria, array $lokasi): Collection
    {
        $tersedia = function (Builder $q) use ($kriteria): void {
            $q->where('status', KamarStatus::Tersedia->value)
                ->where('harga_sewa_bulanan', '>', 0);

            if ($kriteria['budget'] !== null) {
                $q->where('harga_sewa_bulanan', '<=', $kriteria['budget']);
            }
        };

        return Properti::query()
            ->where('status', 'aktif')
            ->when($kriteria['tipe'], fn (Builder $q, string $tipe) => $q->where('tipe_hunian', $tipe))
            ->when($lokasi['kota'], fn (Builder $q, string $kota) => $this->filterKota($q, $kota))
            ->when($lokasi['area'] !== [], function (Builder $q) use ($lokasi): void {
                $q->where(function (Builder $qq) use ($lokasi): void {
                    foreach ($lokasi['area'] as $area) {
                        $pola = '%'.$area.'%';
                        $qq->orWhere('alamat', 'like', $pola)
                            ->orWhere('nama', 'like', $pola);
                    }
                });
            })
            ->whereHas('kamars', $tersedia)
            ->withMin(['kamars as harga_termurah' => $tersedia], 'harga_sewa_bulanan')
            ->withCount(['kamars as kamar_tersedia' => $tersedia])
            ->orderBy('harga_termurah')
            ->limit(8)
            ->get(['id', 'nama', 'kota', 'alamat', 'tipe_hunian']);
    }

    private function filterKota(Builder $query, string $kota): void
    {
        $query->where(function (Builder $q) use ($kota): void {
            $q->where('kota_kanonik', $kota)
                ->orWhere(fn (Builder $qq) => $qq
                    ->whereNull('kota_kanonik')
                    ->where('kota', 'like', '%'.$kota.'%'));

            // "Jakarta" mencakup Jakarta Pusat/Utara/Barat/Selatan/Timur.
            if (strcasecmp($kota, 'Jakarta') === 0) {
                $q->orWhere('kota_kanonik', 'like', 'Jakarta %');
            }
        });
    }

    /**
     * @param  array<string, mixed>  $kriteria
     * @param  Collection<int, Properti>  $hasil
     * @param  array<int, array{kota: string|null, area: array<int, string>, label: string, kampus: string|null}>  $lokasi
     * @return array{teks: string, tautan: array<int, array{label: string, url: string}>, konteks: array<string, mixed>}
     */
    private function balasanHasil(array $kriteria, Collection $hasil, bool $fallback, array $lokasi): array
    {
        $kampus = DaftarKampus::cariNama($kriteria['kampus'] ?? null);
        $jumlah = $hasil->count();
        $kalimat = [];

        if ($fallback && ($kampus !== null || $kriteria['landmark'] !== null)) {
            if ($kampus !== null) {
                $area = collect($kampus['lokasi'])
                    ->when($kriteria['kota'] !== null, fn (Collection $c) => $c->filter(
                        fn (array $l) => strcasecmp($l['kota'], (string) $kriteria['kota']) === 0
                    ))
                    ->flatMap(fn (array $l) => $l['area'])
                    ->map(fn (string $a) => ucwords($a))
                    ->unique()
                    ->implode(', ');
            } else {
                $area = ucwords((string) $kriteria['landmark']);
            }

            if ($area === '') {
                $area = $kampus['nama'] ?? ucwords((string) $kriteria['landmark']);
            }

            $kalimat[] = 'Belum ada kos yang cocok dengan kriteria itu di area '.$area
                .($kampus !== null ? ' (sekitar Kampus '.$kampus['nama'].')' : '').'.';
        }

        $pembuka = 'Ini '.$jumlah.' kos yang masih tersedia';

        if ($fallback) {
            $kotaLabel = collect($lokasi)->pluck('kota')->filter()->unique()->implode(' & ');

            if ($kotaLabel !== '') {
                $pembuka .= ' di '.$kotaLabel;
            }
        } elseif ($kampus !== null) {
            $pembuka .= ' di sekitar Kampus '.$kampus['nama'];
        } elseif ($kriteria['landmark'] !== null) {
            $pembuka .= ' di area '.ucwords((string) $kriteria['landmark']);
        } elseif ($kriteria['kota'] !== null) {
            $pembuka .= ' di '.$kriteria['kota'];
        }

        if ($kriteria['tipe'] !== null) {
            $pembuka .= ' untuk kos '.$kriteria['tipe'];
        }

        if ($kriteria['budget'] !== null) {
            $pembuka .= ' dengan harga sampai Rp'.number_format((float) $kriteria['budget'], 0, ',', '.');
        }

        $kalimat[] = $pembuka.':';

        foreach ($hasil->values() as $i => $properti) {
            $baris = ($i + 1).'. '.$properti->nama.' — '.($properti->kota ?: 'lokasi belum diisi');
            $detail = [];

            if (filled($properti->alamat)) {
                $detail[] = Str::limit(trim((string) $properti->alamat), 70);
            }

            $detail[] = 'Mulai Rp'.number_format((float) $properti->harga_termurah, 0, ',', '.').'/bulan';
            $detail[] = (int) $properti->kamar_tersedia.' kamar tersedia';

            $kalimat[] = $baris;
            $kalimat[] = '   '.implode(' · ', $detail);
        }

        $kalimat[] = $fallback
            ? 'Coba sebutkan area lain di sekitar kampus, atau ketuk kos di bawah untuk melihat detail dan peta lokasinya.'
            : 'Ketuk salah satu kos di bawah untuk melihat detail, fasilitas, dan peta lokasinya.';

        /** @var array<int, array{label: string, url: string}> $tautan */
        $tautan = $hasil->map(fn (Properti $p) => [
            'label' => $p->nama,
            'url' => route('kos.detail', $p),
        ])->values()->all();

        $tautan[] = [
            'label' => $this->labelKatalog($kriteria),
            'url' => $this->urlKatalog($kriteria, $fallback),
        ];

        return [
            'teks' => implode("\n", $kalimat),
            'tautan' => $tautan,
            'konteks' => $this->konteksBersih($kriteria),
        ];
    }

    /**
     * @param  array<string, mixed>  $kriteria
     * @return array{teks: string, tautan: array<int, array{label: string, url: string}>, konteks: array<string, mixed>}
     */
    private function balasanKosong(array $kriteria): array
    {
        $filter = '';

        if ($kriteria['kampus'] !== null) {
            $filter .= ' di sekitar Kampus '.$kriteria['kampus'];
        } elseif ($kriteria['kota'] !== null) {
            $filter .= ' di '.$kriteria['kota'];
        } elseif ($kriteria['landmark'] !== null) {
            $filter .= ' di area '.ucwords((string) $kriteria['landmark']);
        }

        if ($kriteria['budget'] !== null) {
            $filter .= ' dengan budget sampai Rp'.number_format((float) $kriteria['budget'], 0, ',', '.');
        }

        if ($kriteria['tipe'] !== null) {
            $filter .= ' untuk kos '.$kriteria['tipe'];
        }

        return [
            'teks' => 'Belum ada kos tersedia'.$filter.'. Coba naikkan budget, ganti area/kampus, atau lihat semua kos yang tersedia.',
            'tautan' => [
                ['label' => 'Lihat semua kos', 'url' => route('kos.index')],
            ],
            'konteks' => $this->konteksBersih($kriteria),
        ];
    }

    /**
     * "Kos ada di kota mana saja?" — dijawab dari data aktif.
     *
     * @return array{teks: string, tautan: array<int, array{label: string, url: string}>, konteks: array<string, mixed>}
     */
    private function ringkasanKota(): array
    {
        $tersedia = fn (Builder $q) => $q
            ->where('status', KamarStatus::Tersedia->value)
            ->where('harga_sewa_bulanan', '>', 0);

        $properti = Properti::query()
            ->where('status', 'aktif')
            ->whereHas('kamars', $tersedia)
            ->withMin(['kamars as harga_termurah' => $tersedia], 'harga_sewa_bulanan')
            ->get(['id', 'kota', 'kota_kanonik']);

        if ($properti->isEmpty()) {
            return [
                'teks' => 'Belum ada kos yang tersedia saat ini. Silakan cek kembali nanti ya.',
                'tautan' => [['label' => 'Lihat katalog', 'url' => route('kos.index')]],
                'konteks' => [],
            ];
        }

        $grup = $properti
            ->groupBy(fn (Properti $p) => $p->kota_kanonik ?: ($p->kota ?: 'Lainnya'))
            ->map(fn (Collection $items, string $kota) => [
                'kota' => $kota,
                'jumlah' => $items->count(),
                'min' => (float) $items->min('harga_termurah'),
            ])
            ->sortByDesc('jumlah')
            ->take(8)
            ->values();

        $baris = [
            'Saat ini ada kos tersedia di '.$properti
                ->map(fn (Properti $p) => $p->kota_kanonik ?: $p->kota)
                ->filter()
                ->unique()
                ->count().' kota. Yang paling banyak:',
        ];

        foreach ($grup as $item) {
            $baris[] = '• '.$item['kota'].' — '.$item['jumlah'].' kos, mulai Rp'
                .number_format($item['min'], 0, ',', '.');
        }

        $baris[] = 'Sebutkan kota, area, atau kampusnya, misalnya "kos dekat kampus UNSRI budget 700rb".';

        $tautan = $grup->map(fn (array $item) => [
            'label' => $item['kota'].' ('.$item['jumlah'].')',
            'url' => route('kos.index', ['kota' => $item['kota']]),
        ])->values()->all();

        $tautan[] = ['label' => 'Lihat semua kos', 'url' => route('kos.index')];

        return [
            'teks' => implode("\n", $baris),
            'tautan' => $tautan,
            'konteks' => [],
        ];
    }

    /**
     * "Kos dekat kampus mana?" — daftar kampus yang area alamatnya punya kos.
     *
     * @return array{teks: string, tautan: array<int, array{label: string, url: string}>, konteks: array<string, mixed>}
     */
    private function ringkasanKampus(?string $kota): array
    {
        $tersedia = fn (Builder $q) => $q
            ->where('status', KamarStatus::Tersedia->value)
            ->where('harga_sewa_bulanan', '>', 0);

        $properti = Properti::query()
            ->where('status', 'aktif')
            ->whereHas('kamars', $tersedia)
            ->withMin(['kamars as harga_termurah' => $tersedia], 'harga_sewa_bulanan')
            ->get(['id', 'nama', 'kota', 'kota_kanonik', 'alamat']);

        if ($properti->isEmpty()) {
            return [
                'teks' => 'Belum ada kos yang tersedia saat ini. Silakan cek kembali nanti ya.',
                'tautan' => [['label' => 'Lihat katalog', 'url' => route('kos.index')]],
                'konteks' => [],
            ];
        }

        $kandidat = [];

        foreach (DaftarKampus::DAFTAR as $kampus) {
            $lokasi = collect($kampus['lokasi'])
                ->when($kota !== null, fn (Collection $c) => $c->filter(
                    fn (array $l) => strcasecmp($l['kota'], $kota) === 0
                ))
                ->values();

            if ($lokasi->isEmpty()) {
                continue;
            }

            $perLokasi = [];
            $idCocok = [];

            foreach ($lokasi as $l) {
                $cocok = $properti->filter(fn (Properti $p) => $this->cocokLokasi($p, $l));

                if ($cocok->isEmpty()) {
                    continue;
                }

                $perLokasi[] = ['lokasi' => $l, 'cocok' => $cocok];

                foreach ($cocok as $p) {
                    $idCocok[$p->id] = true;
                }
            }

            if ($perLokasi === []) {
                continue;
            }

            // Lokasi dengan kos terbanyak dipakai untuk tautan katalog.
            usort($perLokasi, fn (array $a, array $b) => $b['cocok']->count() <=> $a['cocok']->count());

            $kandidat[] = [
                'kampus' => $kampus,
                'jumlah' => count($idCocok),
                'min' => (float) collect($perLokasi)->min(
                    fn (array $item) => (float) $item['cocok']->min('harga_termurah')
                ),
                'kota' => collect($perLokasi)->pluck('lokasi.kota')->unique()->implode(' & '),
                'lokasi' => $perLokasi[0]['lokasi'],
                'cocok' => $perLokasi[0]['cocok'],
            ];
        }

        // Kampus dengan kos terbanyak tampil lebih dulu.
        usort($kandidat, fn (array $a, array $b) => $b['jumlah'] <=> $a['jumlah']);
        $kandidat = array_slice($kandidat, 0, 6);

        $baris = [];
        $tautan = [];

        foreach ($kandidat as $item) {
            $baris[] = '• '.$item['kampus']['nama'].' — '.$item['kota'].' ('.$item['jumlah'].' kos, mulai Rp'
                .number_format($item['min'], 0, ',', '.').')';

            $params = ['kota' => $item['lokasi']['kota']];
            $area = $this->areaPertamaCocok($item['cocok'], $item['lokasi']);

            if ($area !== null) {
                $params['cari'] = $area;
            }

            $tautan[] = [
                'label' => $this->namaSingkatKampus($item['kampus']['nama']).' — '
                    .$item['lokasi']['kota'].' ('.$item['cocok']->count().')',
                'url' => route('kos.index', $params),
            ];
        }

        if ($baris === []) {
            $tempat = $kota !== null ? ' di '.$kota : '';

            return [
                'teks' => 'Belum ada kos yang alamatnya menyebut area kampus'.$tempat.'. Sebutkan nama kampus atau kota lain, misalnya "kos dekat kampus UNSRI di Palembang".',
                'tautan' => [['label' => 'Lihat semua kos', 'url' => route('kos.index')]],
                'konteks' => $kota !== null ? ['kota' => $kota] : [],
            ];
        }

        array_unshift(
            $baris,
            'Kos yang tersedia di sekitar kampus berikut (berdasarkan area yang tercantum di alamat kos)'.($kota !== null ? ' di '.$kota : '').':'
        );
        $baris[] = 'Sebutkan kampus + budget-mu, misalnya "kos dekat kampus UNSRI budget 700rb".';

        return [
            'teks' => implode("\n", $baris),
            'tautan' => $tautan,
            'konteks' => $kota !== null ? ['kota' => $kota] : [],
        ];
    }

    /**
     * Apakah alamat properti menyebut kota + area kampus tertentu.
     *
     * @param  array{kota: string, area: array<int, string>}  $lokasi
     */
    private function cocokLokasi(Properti $properti, array $lokasi): bool
    {
        $kotaProperti = (string) ($properti->kota_kanonik ?: $properti->kota);

        if (strcasecmp($kotaProperti, $lokasi['kota']) !== 0) {
            return false;
        }

        if ($lokasi['area'] === []) {
            return true;
        }

        return Str::contains(mb_strtolower((string) $properti->alamat), $lokasi['area']);
    }

    /**
     * Area pertama pada daftar yang benar-benar muncul di alamat kos.
     *
     * @param  Collection<int, Properti>  $properti
     * @param  array{kota: string, area: array<int, string>}  $lokasi
     */
    private function areaPertamaCocok(Collection $properti, array $lokasi): ?string
    {
        foreach ($lokasi['area'] as $area) {
            $ada = $properti->contains(
                fn (Properti $p) => Str::contains(mb_strtolower((string) $p->alamat), $area)
            );

            if ($ada) {
                return $area;
            }
        }

        return null;
    }

    /**
     * "Kos di Bandung dekat mana?" — ringkasan area dari kolom alamat.
     *
     * @return array{teks: string, tautan: array<int, array{label: string, url: string}>, konteks: array<string, mixed>}
     */
    private function ringkasanArea(string $kota): array
    {
        $tersedia = fn (Builder $q) => $q
            ->where('status', KamarStatus::Tersedia->value)
            ->where('harga_sewa_bulanan', '>', 0);

        $properti = Properti::query()
            ->where('status', 'aktif')
            ->where(fn (Builder $q) => $this->filterKota($q, $kota))
            ->whereHas('kamars', $tersedia)
            ->withMin(['kamars as harga_termurah' => $tersedia], 'harga_sewa_bulanan')
            ->get(['id', 'kota', 'kota_kanonik', 'alamat']);

        if ($properti->isEmpty()) {
            return [
                'teks' => 'Belum ada kos yang tersedia di '.$kota.'. Coba kota lain atau lihat semua kos.',
                'tautan' => [['label' => 'Lihat semua kos', 'url' => route('kos.index')]],
                'konteks' => ['kota' => $kota],
            ];
        }

        $grup = $properti
            ->map(function (Properti $p): array {
                $alamat = trim((string) $p->alamat);
                $area = $alamat !== '' ? trim(explode(',', $alamat)[0]) : 'Lokasi belum diisi';

                return ['area' => $area, 'harga' => (float) $p->harga_termurah];
            })
            ->groupBy('area')
            ->map(fn (Collection $items, string $area) => [
                'area' => $area,
                'jumlah' => $items->count(),
                'min' => (float) $items->min('harga'),
            ])
            ->sortByDesc('jumlah')
            ->take(8)
            ->values();

        $baris = ['Kos di '.$kota.' tersedia di area berikut (berdasarkan alamat kos):'];

        foreach ($grup as $item) {
            $baris[] = '• '.$item['area'].' — '.$item['jumlah'].' kos, mulai Rp'
                .number_format($item['min'], 0, ',', '.');
        }

        $baris[] = 'Sebutkan area atau kampus yang kamu incar, misalnya "kos di '.$grup->first()['area'].' budget 1 juta".';

        $tautan = $grup->map(fn (array $item) => [
            'label' => $item['area'].' ('.$item['jumlah'].')',
            'url' => route('kos.index', ['kota' => $kota, 'cari' => $item['area']]),
        ])->values()->all();

        $tautan[] = ['label' => 'Lihat semua kos di '.$kota, 'url' => route('kos.index', ['kota' => $kota])];

        return [
            'teks' => implode("\n", $baris),
            'tautan' => $tautan,
            'konteks' => ['kota' => $kota],
        ];
    }

    /**
     * @param  array<string, mixed>  $kriteria
     */
    private function labelKatalog(array $kriteria): string
    {
        if ($kriteria['kota'] !== null) {
            return 'Lihat semua kos di '.$kriteria['kota'];
        }

        if ($kriteria['kampus'] !== null) {
            return 'Lihat semua kos di sekitar kampus';
        }

        return 'Lihat semua hasil';
    }

    /**
     * @param  array<string, mixed>  $kriteria
     */
    private function urlKatalog(array $kriteria, bool $fallback = false): string
    {
        $params = [];

        if ($kriteria['kota'] !== null) {
            $params['kota'] = $kriteria['kota'];
        }

        if ($kriteria['budget'] !== null) {
            $params['hargaMax'] = (int) $kriteria['budget'];
        }

        // Saat fallback, jangan bawa filter area karena justru mengosongkan hasil.
        if (! $fallback) {
            if ($kriteria['landmark'] !== null) {
                $params['cari'] = $kriteria['landmark'];
            } elseif ($kriteria['kampus'] !== null) {
                $kampus = DaftarKampus::cariNama($kriteria['kampus']);

                if ($kampus !== null && $kampus['lokasi'][0]['area'] !== []) {
                    $params['cari'] = $kampus['lokasi'][0]['area'][0];
                }
            }
        }

        return route('kos.index', $params);
    }

    /**
     * @param  array<string, mixed>  $kriteria
     * @return array<string, mixed>
     */
    private function konteksBersih(array $kriteria): array
    {
        return array_filter([
            'budget' => $kriteria['budget'] ?? null,
            'kota' => $kriteria['kota'] ?? null,
            'tipe' => $kriteria['tipe'] ?? null,
            'kampus' => $kriteria['kampus'] ?? null,
            'landmark' => $kriteria['landmark'] ?? null,
        ], fn ($nilai) => $nilai !== null);
    }

    private function namaSingkatKampus(string $nama): string
    {
        if (preg_match('/\(([^)]+)\)\s*$/', $nama, $m) === 1) {
            return $m[1];
        }

        return Str::limit($nama, 24, '');
    }

    /**
     * @param  array<string, mixed>  $kriteria
     */
    private function tanyaKampus(string $teks, array $kriteria): bool
    {
        if ($kriteria['kampus'] !== null) {
            return false;
        }

        if (! Str::contains($teks, ['kampus', 'universitas'])) {
            return false;
        }

        return Str::contains($teks, ['mana', 'apa', 'saja']);
    }

    /**
     * @param  array<string, mixed>  $kriteria
     */
    private function tanyaKota(string $teks, array $kriteria): bool
    {
        if ($kriteria['kota'] !== null || $kriteria['kampus'] !== null || $kriteria['landmark'] !== null) {
            return false;
        }

        return preg_match('/(?:kota|daerah|wilayah)\s+(?:apa|mana)|kos\s+di\s*mana/u', $teks) === 1;
    }

    /**
     * @param  array<string, mixed>  $baru
     */
    private function tanyaArea(string $teks, array $baru, ?string $kota): bool
    {
        if ($kota === null || $baru['kampus'] !== null || $baru['landmark'] !== null) {
            return false;
        }

        return preg_match('/(?:dekat|daerah|area|wilayah|lokasi|sekitar)\s+(?:apa|mana)|di\s+(?:daerah|area|wilayah)\s+mana/u', $teks) === 1;
    }

    private function bertanya(string $teks): bool
    {
        return Str::contains($teks, [
            'mana', 'apa', 'ada', 'tersedia', 'dekat', 'sekitar', 'rekomendasi',
            'saran', 'cari', 'carikan', 'murah', 'harga', 'budget', 'kota',
            'daerah', 'area', 'kampus', 'universitas', 'saja',
        ]);
    }

    private function pertanyaanPanduan(string $teks): bool
    {
        if (str_starts_with($teks, 'cara ') || str_starts_with($teks, 'gimana cara') || str_starts_with($teks, 'bagaimana cara')) {
            return true;
        }

        return Str::contains($teks, [
            'cara cari', 'cara daftar', 'cara bayar', 'cara pesan', 'cara sewa',
        ]);
    }

    private function parseBudget(string $pertanyaan): ?float
    {
        $teks = mb_strtolower($pertanyaan, 'UTF-8');
        $teks = str_replace(["\u{00A0}", 'rp.', 'rp'], ' ', $teks);
        $teks = (string) preg_replace('/\s+/', ' ', $teks);

        $kandidat = [];

        // "1,5 juta", "2jt", "700 juta".
        if (preg_match_all('/(\d+(?:[.,]\d+)?)\s*(?:juta|jt)(?![a-z])/u', $teks, $m) > 0) {
            foreach ($m[1] as $angka) {
                $kandidat[] = $this->keAngka($angka) * 1_000_000;
            }
        }

        // "700 ribu", "850rb", "700rebu".
        if (preg_match_all('/(\d+(?:[.,]\d+)?)\s*(?:ribu|ribuan|rebu|rb)(?![a-z])/u', $teks, $m) > 0) {
            foreach ($m[1] as $angka) {
                $kandidat[] = $this->keAngka($angka) * 1_000;
            }
        }

        // "700k" (bukan "700km" / "700kali").
        if (preg_match_all('/(\d+(?:[.,]\d+)?)\s*k(?![a-z])/u', $teks, $m) > 0) {
            foreach ($m[1] as $angka) {
                $kandidat[] = $this->keAngka($angka) * 1_000;
            }
        }

        // Angka rupiah penuh: "700000", "1.500.000", "Rp 700.000".
        if (preg_match_all('/(?<![\d.,])(\d{1,3}(?:[.,]\d{3})+|\d{4,})(?![\d.,])/u', $teks, $m) > 0) {
            foreach ($m[1] as $angka) {
                $nilai = $this->keAngka($angka);

                if ($nilai >= 100_000 && $nilai <= 50_000_000) {
                    $kandidat[] = $nilai;
                }
            }
        }

        // "budget 700" / "maks 900" yang lazim berarti 700rb / 900rb.
        if (preg_match_all('/(?:budget|bujet|maks(?:imal)?|max|di\s?bawah|dibawah|sampai|kurang\s+dari|harga)\s*(?:rp\s*)?(\d{3})(?![\d.,])/u', $teks, $m) > 0) {
            foreach ($m[1] as $angka) {
                $kandidat[] = (float) $angka * 1_000;
            }
        }

        $kandidat = array_values(array_filter(
            $kandidat,
            fn (float $nilai) => $nilai >= 100_000 && $nilai <= 50_000_000
        ));

        return $kandidat === [] ? null : (float) max($kandidat);
    }

    private function keAngka(string $nilai): float
    {
        $nilai = trim($nilai);

        // Pemisah ribuan gaya Indonesia: 1.500.000
        if (preg_match('/^\d{1,3}(?:\.\d{3})+$/', $nilai) === 1) {
            return (float) str_replace('.', '', $nilai);
        }

        // Pemisah ribuan gaya Inggris: 1,500,000
        if (preg_match('/^\d{1,3}(?:,\d{3})+$/', $nilai) === 1) {
            return (float) str_replace(',', '', $nilai);
        }

        return (float) str_replace(',', '.', $nilai);
    }

    private function parseKota(string $pertanyaan): ?string
    {
        $teks = $this->normalisasi($pertanyaan);

        if ($teks === '') {
            return null;
        }

        // Area yang lebih dikenal daripada nama kota administratifnya.
        $aliasArea = [
            'jatinangor' => 'Sumedang',
            'bsd' => 'Tangerang Selatan',
            'serpong' => 'Tangerang Selatan',
            'jogja' => 'Yogyakarta',
            'yogya' => 'Yogyakarta',
            'solo' => 'Surakarta',
            'bali' => 'Denpasar',
        ];

        foreach ($aliasArea as $kunci => $nama) {
            if (preg_match('/(?<![\p{L}\p{N}])'.preg_quote($kunci, '/').'(?![\p{L}\p{N}])/u', $teks) === 1) {
                return $nama;
            }
        }

        $daftar = collect(DaftarKota::semua())
            ->merge(Properti::query()->where('status', 'aktif')->pluck('kota_kanonik'))
            ->merge(Properti::query()->where('status', 'aktif')->pluck('kota'))
            ->filter()
            ->map(fn ($kota) => trim((string) $kota))
            ->filter()
            ->unique()
            ->sortByDesc(fn (string $kota) => mb_strlen($kota))
            ->values();

        foreach ($daftar as $nama) {
            $kunci = $this->normalisasi($nama);

            if ($kunci === '') {
                continue;
            }

            if (preg_match('/(?<![\p{L}\p{N}])'.preg_quote($kunci, '/').'(?![\p{L}\p{N}])/u', $teks) === 1) {
                return DaftarKota::kanonik($nama) ?? $nama;
            }
        }

        return null;
    }

    private function parseLandmark(string $pertanyaan): ?string
    {
        $teks = $this->normalisasi($pertanyaan);

        $pola = [
            '/(?:dekat|didekat|disekitar|sekitar|area|kawasan|wilayah|kelurahan|kecamatan)\s+(?:dengan\s+)?(?:kampus|universitas|univ|politeknik|poltek)?\s*([\p{L}\p{N}]+(?:\s+[\p{L}\p{N}]+){0,3})/u',
            '/(?:kampus|universitas|univ|politeknik|poltek)\s+([\p{L}\p{N}]+(?:\s+[\p{L}\p{N}]+){0,3})/u',
        ];

        foreach ($pola as $polaItem) {
            if (preg_match($polaItem, $teks, $m) !== 1) {
                continue;
            }

            $kandidat = $this->bersihkanLandmark($m[1]);

            if ($kandidat === null) {
                continue;
            }

            $kota = $this->parseKota($pertanyaan);

            if ($kota !== null && strcasecmp($kandidat, $kota) === 0) {
                return null;
            }

            return $kandidat;
        }

        return null;
    }

    private function bersihkanLandmark(string $kandidat): ?string
    {
        $kata = preg_split('/\s+/', trim($kandidat)) ?: [];
        $kata = array_values(array_filter(
            $kata,
            fn (string $k) => $k !== '' && ! in_array($k, self::STOPWORDS_LANDMARK, true)
        ));
        $kata = array_slice($kata, 0, 3);
        $hasil = trim(implode(' ', $kata));

        if ($hasil === '' || mb_strlen($hasil) < 3) {
            return null;
        }

        if (in_array($hasil, ['mana', 'apa', 'saja', 'kota', 'daerah', 'area', 'kampus', 'universitas'], true)) {
            return null;
        }

        return $hasil;
    }

    private function parseTipe(string $pertanyaan): ?string
    {
        $teks = mb_strtolower($pertanyaan, 'UTF-8');

        return match (true) {
            str_contains($teks, 'putri'),
            str_contains($teks, 'perempuan'),
            str_contains($teks, 'wanita'),
            str_contains($teks, 'cewek') => 'putri',
            str_contains($teks, 'putra'),
            str_contains($teks, 'laki'),
            str_contains($teks, 'pria'),
            str_contains($teks, 'cowok') => 'putra',
            str_contains($teks, 'campur') => 'campur',
            str_contains($teks, 'bebas') => 'bebas',
            default => null,
        };
    }

    private function normalisasi(string $teks): string
    {
        $teks = mb_strtolower($teks, 'UTF-8');
        $teks = (string) preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $teks);
        $teks = (string) preg_replace('/\s+/', ' ', $teks);

        return trim($teks);
    }
}
