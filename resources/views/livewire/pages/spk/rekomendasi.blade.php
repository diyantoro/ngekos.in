<?php

use App\Models\BobotPreferensi;
use App\Models\HasilPeringkat;
use App\Models\Kamar;
use App\Models\Kriteria;
use App\Models\ProfilPreferensi;
use App\Models\SesiPencarian;
use App\Models\TitikAcuan;
use App\Services\SpkTopsisService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.publik')] class extends Component
{
    public ?int $titikAcuanId = null;
    public ?float $budgetMaks = null;
    public string $tipeHunian = '';
    public array $fasilitasWajib = [];

    // Bobot slider C1 - C5 (mentah 1 - 10)
    public float $bobotC1 = 25; // Harga
    public float $bobotC2 = 20; // Kewajaran Harga (JST)
    public float $bobotC3 = 25; // Jarak
    public float $bobotC4 = 15; // Fasilitas
    public float $bobotC5 = 15; // Luas

    // Profil preferensi
    public string $namaProfil = '';
    public ?int $profilTerpilihId = null;

    public ?array $hasilPeringkat = null;
    public ?array $sensitivitas = null;
    public ?int $totalKamarDitemukan = null;
    public ?string $pesan = null;

    public function mount(): void
    {
        $titikPertama = TitikAcuan::first();
        if ($titikPertama) {
            $this->titikAcuanId = $titikPertama->id;
        }

        // Jika user login punya profil, muat profil pertama
        if (auth()->check()) {
            $profil = ProfilPreferensi::with('bobots')->where('user_id', auth()->id())->first();
            if ($profil) {
                $this->pilihProfil($profil->id);
            }
        }
    }

    public function getBobotPersenProperty(): array
    {
        $total = $this->bobotC1 + $this->bobotC2 + $this->bobotC3 + $this->bobotC4 + $this->bobotC5;
        $total = max(1.0, $total);

        return [
            'C1' => round(($this->bobotC1 / $total) * 100, 1),
            'C2' => round(($this->bobotC2 / $total) * 100, 1),
            'C3' => round(($this->bobotC3 / $total) * 100, 1),
            'C4' => round(($this->bobotC4 / $total) * 100, 1),
            'C5' => round(($this->bobotC5 / $total) * 100, 1),
        ];
    }

    public function cariDanPeringkatkan(): void
    {
        $this->validate([
            'titikAcuanId' => 'required|exists:titik_acuan,id',
            'budgetMaks' => 'nullable|numeric|min:0',
        ]);

        $titik = TitikAcuan::findOrFail($this->titikAcuanId);

        // Filter kamar aktif & tersedia
        $query = Kamar::query()
            ->with(['properti', 'fotos'])
            ->where('status', 'tersedia')
            ->whereNotNull('harga_sewa_bulanan')
            ->where('harga_sewa_bulanan', '>', 0)
            ->whereHas('properti', function ($q) {
                $q->where('status', 'aktif');
                if ($this->tipeHunian !== '') {
                    $q->where('tipe_hunian', $this->tipeHunian);
                }
            });

        if ($this->budgetMaks && $this->budgetMaks > 0) {
            $query->where('harga_sewa_bulanan', '<=', $this->budgetMaks);
        }

        $kamars = $query->take(150)->get();

        // Filter fasilitas wajib di memori jika ada
        if (! empty($this->fasilitasWajib)) {
            $kamars = $kamars->filter(function ($kamar) {
                $facs = collect($kamar->daftarFasilitas())->map(fn ($f) => strtolower($f))->all();
                $propFacs = array_map('trim', explode(',', strtolower((string) $kamar->properti?->fasilitas)));
                $gabungan = array_merge($facs, $propFacs);

                foreach ($this->fasilitasWajib as $wajib) {
                    $ketemu = false;
                    foreach ($gabungan as $g) {
                        if (str_contains($g, strtolower($wajib))) {
                            $ketemu = true;
                            break;
                        }
                    }
                    if (! $ketemu) {
                        return false;
                    }
                }
                return true;
            })->values();
        }

        $this->totalKamarDitemukan = $kamars->count();

        if ($kamars->isEmpty()) {
            $this->hasilPeringkat = [];
            $this->sensitivitas = [];
            return;
        }

        // Format bobot
        $bobotInput = [
            'C1' => $this->bobotC1,
            'C2' => $this->bobotC2,
            'C3' => $this->bobotC3,
            'C4' => $this->bobotC4,
            'C5' => $this->bobotC5,
        ];

        // Eksekusi SPK TOPSIS
        $service = app(SpkTopsisService::class);
        $topsisRes = $service->rank($kamars, $titik, $bobotInput);

        $this->hasilPeringkat = $topsisRes['hasil'];
        $this->sensitivitas = $topsisRes['sensitivitas'];

        // Rekam sesi pencarian (FR-10)
        $sesi = SesiPencarian::create([
            'user_id' => auth()->id(),
            'profil_id' => $this->profilTerpilihId,
            'filter' => [
                'titik_acuan_id' => $this->titikAcuanId,
                'titik_nama' => $titik->nama,
                'budget_maks' => $this->budgetMaks,
                'tipe_hunian' => $this->tipeHunian,
                'bobot' => $topsisRes['bobot'],
            ],
            'dibuat_pada' => now(),
        ]);

        // Simpan top 30 hasil peringkat
        foreach (array_slice($this->hasilPeringkat, 0, 30) as $row) {
            HasilPeringkat::create([
                'sesi_id' => $sesi->id,
                'kamar_id' => $row['kamar_id'],
                'skor_preferensi' => $row['skor'],
                'peringkat' => $row['peringkat'],
                'detail_skor' => $row['detail_skor'],
            ]);
        }
    }

    public function simpanProfil(): void
    {
        if (! auth()->check()) {
            $this->pesan = 'Silakan masuk (login) terlebih dahulu untuk menyimpan profil preferensi.';
            return;
        }

        $this->validate([
            'namaProfil' => 'required|string|max:100',
            'titikAcuanId' => 'required|exists:titik_acuan,id',
        ]);

        $profil = ProfilPreferensi::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'nama_profil' => $this->namaProfil,
            ],
            [
                'titik_acuan_id' => $this->titikAcuanId,
                'metode' => 'topsis',
                'budget_maks' => $this->budgetMaks,
            ]
        );

        $kriterias = Kriteria::all()->keyBy('kode');
        $persen = $this->bobotPersen;

        foreach (['C1', 'C2', 'C3', 'C4', 'C5'] as $kode) {
            if ($kriterias->has($kode)) {
                BobotPreferensi::updateOrCreate(
                    [
                        'profil_id' => $profil->id,
                        'kriteria_id' => $kriterias[$kode]->id,
                    ],
                    [
                        'bobot' => round($persen[$kode] / 100, 4),
                    ]
                );
            }
        }

        $this->profilTerpilihId = $profil->id;
        $this->pesan = "Profil preferensi '{$this->namaProfil}' berhasil disimpan!";
    }

    public function pilihProfil(int $id): void
    {
        $profil = ProfilPreferensi::with('bobots.kriteria')->where('id', $id)->first();
        if (! $profil) {
            return;
        }

        $this->profilTerpilihId = $profil->id;
        $this->namaProfil = $profil->nama_profil;
        $this->titikAcuanId = $profil->titik_acuan_id;
        $this->budgetMaks = $profil->budget_maks ? (float) $profil->budget_maks : null;

        foreach ($profil->bobots as $b) {
            $kode = $b->kriteria?->kode;
            $val = (float) $b->bobot * 100;
            if ($kode === 'C1') $this->bobotC1 = $val;
            if ($kode === 'C2') $this->bobotC2 = $val;
            if ($kode === 'C3') $this->bobotC3 = $val;
            if ($kode === 'C4') $this->bobotC4 = $val;
            if ($kode === 'C5') $this->bobotC5 = $val;
        }
    }

    public function with(): array
    {
        return [
            'daftarTitik' => TitikAcuan::orderBy('nama')->get(),
            'profilSaya' => auth()->check()
                ? ProfilPreferensi::where('user_id', auth()->id())->orderBy('nama_profil')->get()
                : collect(),
        ];
    }
};
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <!-- Header -->
    <div class="bg-gradient-to-r from-teal-700 via-teal-800 to-teal-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10 max-w-3xl">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/10 backdrop-blur-md px-3 py-1 text-xs font-semibold text-teal-200 border border-white/20 mb-3">
                <span>Sistem Pendukung Keputusan (TOPSIS) + AI JST</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Rekomendasi Cerdas & Pemeringkatan Kos</h1>
            <p class="mt-2 text-sm sm:text-base text-teal-100">
                Temukan kos terbaik berdasarkan kalkulasi multi-kriteria: harga sewa, estimasi harga wajar pasar oleh AI, jarak akurat ke kampus, kelengkapan fasilitas, dan luas kamar.
            </p>
        </div>
        <div class="absolute right-0 top-0 -mt-10 -mr-10 h-64 w-64 rounded-full bg-teal-500/10 blur-3xl"></div>
    </div>

    @if ($pesan)
        <div class="flex items-center justify-between gap-3 rounded-xl bg-teal-50 dark:bg-teal-950/30 border border-teal-200 dark:border-teal-800 px-4 py-3 text-sm text-teal-800 dark:text-teal-200">
            <span>{{ $pesan }}</span>
            <button wire:click="$set('pesan', null)" class="text-teal-600 font-bold">&times;</button>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Kolom Kiri: Form Preferensi & Slider Bobot -->
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-6 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">1. Preferensi Pencari Kos</h2>
                    @if (auth()->check() && $profilSaya->isNotEmpty())
                        <div class="flex items-center gap-2">
                            <label class="text-[11px] text-gray-400">Profil:</label>
                            <select wire:change="pilihProfil($event.target.value)" class="text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 py-1 px-2">
                                <option value="">Pilih Profil</option>
                                @foreach ($profilSaya as $p)
                                    <option value="{{ $p->id }}" @selected($profilTerpilihId === $p->id)>{{ $p->nama_profil }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <!-- Titik Acuan (Kampus) -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Titik Acuan Kampus / Landmark</label>
                    <select wire:model="titikAcuanId" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm shadow-xs focus:ring-teal-500 focus:border-teal-500">
                        @foreach ($daftarTitik as $t)
                            <option value="{{ $t->id }}">{{ $t->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Budget Maksimal & Tipe Hunian -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Batas Budget / Bulan (Rp)</label>
                        <input type="number" wire:model="budgetMaks" placeholder="Misal: 1500000"
                            class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm shadow-xs focus:ring-teal-500 focus:border-teal-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Tipe Penghuni</label>
                        <select wire:model="tipeHunian" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm shadow-xs focus:ring-teal-500 focus:border-teal-500">
                            <option value="">Semua Tipe</option>
                            <option value="putra">Putra Saja</option>
                            <option value="putri">Putri Saja</option>
                            <option value="campur">Campur</option>
                            <option value="bebas">Bebas</option>
                        </select>
                    </div>
                </div>

                <!-- Fasilitas Wajib -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Fasilitas Wajib (Filter Awal)</label>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        @foreach (['AC', 'Kamar Mandi Dalam', 'WiFi', 'Kasur', 'Water Heater', 'Parkir Mobil'] as $f)
                            <label class="inline-flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 cursor-pointer">
                                <input type="checkbox" wire:model="fasilitasWajib" value="{{ $f }}" class="rounded text-teal-600 focus:ring-teal-500" />
                                <span class="text-gray-700 dark:text-gray-200">{{ $f }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Pembobotan 5 Kriteria TOPSIS -->
                <div class="pt-4 border-t border-gray-100 dark:border-gray-700 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-teal-800 dark:text-teal-300">2. Bobot Kriteria TOPSIS</h3>
                        <span class="text-[11px] font-semibold text-gray-400">Total Bobot: 100%</span>
                    </div>

                    @php $persen = $this->bobotPersen; @endphp

                    <!-- C1: Harga Sewa -->
                    <div class="space-y-1">
                        <div class="flex justify-between text-xs">
                            <span class="font-medium text-gray-700 dark:text-gray-300">C1: Harga Sewa (Cost)</span>
                            <span class="font-bold text-teal-700 dark:text-teal-400">{{ $persen['C1'] }}%</span>
                        </div>
                        <input type="range" min="1" max="50" wire:model.live="bobotC1" class="w-full accent-teal-600" />
                    </div>

                    <!-- C2: Kewajaran Harga (JST) -->
                    <div class="space-y-1">
                        <div class="flex justify-between text-xs">
                            <span class="font-medium text-gray-700 dark:text-gray-300">C2: Kewajaran Harga AI JST (Benefit)</span>
                            <span class="font-bold text-teal-700 dark:text-teal-400">{{ $persen['C2'] }}%</span>
                        </div>
                        <input type="range" min="1" max="50" wire:model.live="bobotC2" class="w-full accent-teal-600" />
                    </div>

                    <!-- C3: Jarak Kampus -->
                    <div class="space-y-1">
                        <div class="flex justify-between text-xs">
                            <span class="font-medium text-gray-700 dark:text-gray-300">C3: Jarak ke Kampus (Cost)</span>
                            <span class="font-bold text-teal-700 dark:text-teal-400">{{ $persen['C3'] }}%</span>
                        </div>
                        <input type="range" min="1" max="50" wire:model.live="bobotC3" class="w-full accent-teal-600" />
                    </div>

                    <!-- C4: Kelengkapan Fasilitas -->
                    <div class="space-y-1">
                        <div class="flex justify-between text-xs">
                            <span class="font-medium text-gray-700 dark:text-gray-300">C4: Kelengkapan Fasilitas (Benefit)</span>
                            <span class="font-bold text-teal-700 dark:text-teal-400">{{ $persen['C4'] }}%</span>
                        </div>
                        <input type="range" min="1" max="50" wire:model.live="bobotC4" class="w-full accent-teal-600" />
                    </div>

                    <!-- C5: Luas Kamar -->
                    <div class="space-y-1">
                        <div class="flex justify-between text-xs">
                            <span class="font-medium text-gray-700 dark:text-gray-300">C5: Luas Kamar (Benefit)</span>
                            <span class="font-bold text-teal-700 dark:text-teal-400">{{ $persen['C5'] }}%</span>
                        </div>
                        <input type="range" min="1" max="50" wire:model.live="bobotC5" class="w-full accent-teal-600" />
                    </div>
                </div>

                <!-- Tombol Aksi Hitung -->
                <button wire:click="cariDanPeringkatkan" wire:loading.attr="disabled"
                    class="w-full py-3 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm shadow-md transition disabled:opacity-50 flex items-center justify-center gap-2">
                    <span wire:loading.remove wire:target="cariDanPeringkatkan">Hitung & Tampilkan Peringkat</span>
                    <span wire:loading wire:target="cariDanPeringkatkan">Menjalankan Algoritma TOPSIS...</span>
                </button>

                <!-- Simpan Profil (FR-06) -->
                @auth
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center gap-2">
                        <input type="text" wire:model="namaProfil" placeholder="Nama Profil (mis. Murah Dekat)"
                            class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-xs py-1.5 px-3" />
                        <button type="button" wire:click="simpanProfil"
                            class="rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold text-xs py-1.5 px-3 transition">
                            Simpan Profil
                        </button>
                    </div>
                @endauth
            </div>
        </div>

        <!-- Kolom Kanan: Hasil Peringkat & Visualisasi -->
        <div class="lg:col-span-7 space-y-6">
            @if ($hasilPeringkat === null)
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-12 text-center space-y-3">
                    <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Siap Menghitung Rekomendasi</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                        Pilih titik acuan kampus dan atur bobot kriteria di panel sebelah kiri, lalu klik tombol <strong>Hitung & Tampilkan Peringkat</strong>.
                    </p>
                </div>
            @elseif (empty($hasilPeringkat))
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-12 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada kamar yang memenuhi filter pencarian Anda. Silakan longgarkan batas budget atau fasilitas wajib.</p>
                </div>
            @else
                <!-- Ringkasan Hasil & Uji Sensitivitas (FR-07) -->
                @if ($sensitivitas)
                    <div class="bg-amber-50/70 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/40 rounded-2xl p-4 space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-amber-500 text-white text-[10px] font-bold">i</span>
                            <h4 class="text-xs font-bold text-amber-900 dark:text-amber-200">Uji Sensitivitas Bobot (FR-07)</h4>
                        </div>
                        <p class="text-[11px] text-amber-800 dark:text-amber-300">
                            Sistem menguji kestabilan peringkat ketika bobot kriteria divariasikan ±20%:
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 text-[11px] pt-1">
                            @foreach ($sensitivitas as $kode => $s)
                                <div class="flex items-center gap-1.5 text-gray-700 dark:text-gray-300">
                                    <span class="inline-block h-2 w-2 rounded-full {{ $s['stabil'] ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                    <span><strong>{{ $s['kriteria'] }}:</strong> {{ $s['stabil'] ? 'Stabil' : 'Sensitif terhadap perubahan bobot' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                        Hasil Pemeringkatan TOPSIS ({{ count($hasilPeringkat) }} Kamar)
                    </h3>
                </div>

                <!-- Daftar Kartu Peringkat (FR-04, FR-05) -->
                <div class="space-y-4">
                    @foreach ($hasilPeringkat as $item)
                        @php
                            $isTop3 = $item['peringkat'] <= 3;
                        @endphp
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-5 space-y-3 transition hover:shadow-md">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl font-black text-sm {{ $isTop3 ? 'bg-amber-400 text-amber-950 shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                        #{{ $item['peringkat'] }}
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                            {{ $item['properti_nama'] }} — Kamar {{ $item['kamar_nama'] }}
                                        </h4>
                                        <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            <span>{{ $item['kota'] ?? 'Bandung' }}</span>
                                            <span>&bull;</span>
                                            <span>{{ round($item['jarak_km'], 2) }} km ke kampus</span>
                                            <span>&bull;</span>
                                            <span>{{ $item['nilai_kriteria']['C5'] }} m²</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <div class="text-base font-black text-teal-700 dark:text-teal-400">
                                        Rp{{ number_format($item['harga_bulanan'], 0, ',', '.') }}<span class="text-[10px] font-normal text-gray-400">/bln</span>
                                    </div>
                                    <div class="text-[11px] font-semibold text-gray-500 dark:text-gray-400">
                                        Skor TOPSIS: <span class="font-bold text-gray-900 dark:text-gray-100">{{ number_format($item['skor'], 4) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Label Kewajaran Harga AI JST (C2) -->
                            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-gray-100 dark:border-gray-700 text-xs">
                                <span class="text-gray-500">Estimasi AI:</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">Rp{{ number_format($item['harga_prediksi'], 0, ',', '.') }}</span>

                                @php
                                    $lbl = $item['label_kewajaran'];
                                    $badgeCls = match($lbl) {
                                        'di_bawah_pasar' => 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800',
                                        'di_atas_pasar' => 'bg-rose-100 text-rose-800 border-rose-300 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800',
                                        default => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800',
                                    };
                                    $badgeTxt = match($lbl) {
                                        'di_bawah_pasar' => 'Di Bawah Pasar (Sangat Murah)',
                                        'di_atas_pasar' => 'Di Atas Pasar',
                                        default => 'Wajar Pasar',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full font-bold text-[10px] border {{ $badgeCls }}">
                                    {{ $badgeTxt }} ({{ $item['selisih_persen'] > 0 ? '+' : '' }}{{ $item['selisih_persen'] }}%)
                                </span>
                            </div>

                            <!-- Alasan Peringkat (FR-05) -->
                            <div class="rounded-xl bg-gray-50 dark:bg-gray-750 p-3 text-xs text-gray-600 dark:text-gray-300">
                                <strong>Alasan:</strong> {{ $item['alasan'] }}
                            </div>

                            <!-- Indikator Skor Radar / 5 Kriteria (FR-05) -->
                            <div class="grid grid-cols-5 gap-2 pt-1 text-center text-[10px]">
                                <div class="p-1.5 rounded-lg bg-gray-50 dark:bg-gray-700">
                                    <div class="text-gray-400 font-semibold">Harga</div>
                                    <div class="font-bold text-teal-700 dark:text-teal-400 text-xs">{{ $item['detail_skor']['C1'] }}</div>
                                </div>
                                <div class="p-1.5 rounded-lg bg-gray-50 dark:bg-gray-700">
                                    <div class="text-gray-400 font-semibold">Kewajaran</div>
                                    <div class="font-bold text-teal-700 dark:text-teal-400 text-xs">{{ $item['detail_skor']['C2'] }}</div>
                                </div>
                                <div class="p-1.5 rounded-lg bg-gray-50 dark:bg-gray-700">
                                    <div class="text-gray-400 font-semibold">Jarak</div>
                                    <div class="font-bold text-teal-700 dark:text-teal-400 text-xs">{{ $item['detail_skor']['C3'] }}</div>
                                </div>
                                <div class="p-1.5 rounded-lg bg-gray-50 dark:bg-gray-700">
                                    <div class="text-gray-400 font-semibold">Fasilitas</div>
                                    <div class="font-bold text-teal-700 dark:text-teal-400 text-xs">{{ $item['detail_skor']['C4'] }}</div>
                                </div>
                                <div class="p-1.5 rounded-lg bg-gray-50 dark:bg-gray-700">
                                    <div class="text-gray-400 font-semibold">Luas</div>
                                    <div class="font-bold text-teal-700 dark:text-teal-400 text-xs">{{ $item['detail_skor']['C5'] }}</div>
                                </div>
                            </div>

                            <div class="pt-2 flex justify-end">
                                <a href="{{ route('kos.detail', $item['properti_id'] ?? 1) }}" wire:navigate
                                    class="inline-flex items-center gap-1.5 text-xs font-bold text-teal-600 dark:text-teal-400 hover:text-teal-800 dark:hover:text-teal-300">
                                    Lihat Detail Kos & Kamar &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
