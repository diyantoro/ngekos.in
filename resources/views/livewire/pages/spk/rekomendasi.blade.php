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

    // Preset prioritas sederhana untuk pengguna awam.
    public string $presetAktif = 'seimbang';
    public int $batasTampil = 10;

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

        $this->batasTampil = 10;
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

    /**
     * Terapkan preset prioritas agar pengguna tidak perlu mengatur 5 slider.
     */
    public function terapkanPreset(string $preset): void
    {
        $peta = [
            'hemat' => ['C1' => 40, 'C2' => 15, 'C3' => 30, 'C4' => 10, 'C5' => 5],
            'seimbang' => ['C1' => 25, 'C2' => 20, 'C3' => 25, 'C4' => 15, 'C5' => 15],
            'lengkap' => ['C1' => 15, 'C2' => 15, 'C3' => 15, 'C4' => 30, 'C5' => 25],
        ];

        if (! isset($peta[$preset])) {
            return;
        }

        $this->bobotC1 = (float) $peta[$preset]['C1'];
        $this->bobotC2 = (float) $peta[$preset]['C2'];
        $this->bobotC3 = (float) $peta[$preset]['C3'];
        $this->bobotC4 = (float) $peta[$preset]['C4'];
        $this->bobotC5 = (float) $peta[$preset]['C5'];

        // Set terakhir agar tidak tertimpa hook updated().
        $this->presetAktif = $preset;
    }

    /**
     * Jika slider diubah manual, tandai sebagai pengaturan sendiri.
     */
    public function updated(string $name): void
    {
        if (in_array($name, ['bobotC1', 'bobotC2', 'bobotC3', 'bobotC4', 'bobotC5'], true)) {
            $this->presetAktif = 'custom';
        }
    }

    public function tambahBatas(): void
    {
        $this->batasTampil += 10;
    }

    public function resetFilter(): void
    {
        $this->budgetMaks = null;
        $this->tipeHunian = '';
        $this->fasilitasWajib = [];
        $this->hasilPeringkat = null;
        $this->sensitivitas = null;
        $this->totalKamarDitemukan = null;
        $this->pesan = null;
        $this->batasTampil = 10;
        $this->terapkanPreset('seimbang');

        if ($titik = TitikAcuan::first()) {
            $this->titikAcuanId = $titik->id;
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
                <span>Ditenagai AI + peringkat otomatis</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Temukan Kos yang Paling Cocok</h1>
            <p class="mt-2 text-sm sm:text-base text-teal-100">
                Beri tahu lokasi kampus, budget, dan prioritas Anda. Sistem akan mengurutkan kos
                berdasarkan harga, kewajaran harga menurut AI, jarak, fasilitas, dan luas kamar.
            </p>
        </div>
        <div class="absolute right-0 top-0 -mt-10 -mr-10 h-64 w-64 rounded-full bg-teal-500/10 blur-3xl"></div>
    </div>

    @php
        $penggunaAktif = auth()->user();
        $peranPanduan = 'pencari';

        if ($penggunaAktif?->hasRole('pemilik')) {
            $peranPanduan = 'pemilik';
        } elseif ($penggunaAktif?->hasRole('super_admin') || $penggunaAktif?->hasRole('admin')) {
            $peranPanduan = 'pengelola';
        }
    @endphp

    <!-- Panduan singkat sesuai peran pengguna -->
    @if ($penggunaAktif && $peranPanduan === 'pencari')
        <div class="rounded-2xl bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-gray-700 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-4">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-700 dark:bg-teal-500/10 dark:text-teal-300">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-gray-900 dark:text-gray-100">Anda masuk sebagai Anak Kos</p>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                    Ikuti 3 langkah di bawah: tentukan lokasi &amp; budget, pilih prioritas, lalu lihat rekomendasi.
                    Karena sudah masuk, Anda juga bisa menyimpan profil preferensi agar tidak perlu mengatur ulang nanti.
                </p>
            </div>
        </div>
    @elseif ($peranPanduan === 'pemilik')
        <div class="rounded-2xl bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-gray-700 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-4">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-gray-900 dark:text-gray-100">Anda masuk sebagai Pemilik Kos</p>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                    Halaman ini untuk mencari kos. Sebagai pemilik, atur harga kamar Anda lewat menu
                    <strong>Kelola Kos &rarr; Kamar</strong> dan gunakan tombol <strong>Hitung Saran Harga</strong>
                    agar harga sewa sesuai pasar. Data kamar Anda juga menjadi bahan peringkat SPK bagi pencari kos.
                </p>
            </div>
            <a href="{{ route('pemilik.properti') }}" wire:navigate
                class="shrink-0 inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-700 transition">
                Kelola Kos Saya
            </a>
        </div>
    @elseif ($peranPanduan === 'pengelola')
        <div class="rounded-2xl bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-gray-700 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-4">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.063-.374-.313-.686-.645-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.213-1.281z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-gray-900 dark:text-gray-100">Anda masuk sebagai Pengelola Sistem</p>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                    Halaman ini menampilkan hasil SPK yang dipakai pencari kos. Tugas Anda menjaga kualitasnya:
                    lengkapi <strong>Data Latih</strong>, latih model di <strong>Model AI</strong>, lalu aktifkan versinya.
                </p>
            </div>
            <div class="flex shrink-0 gap-2">
                <a href="{{ route('admin.data-latih') }}" wire:navigate
                    class="inline-flex items-center justify-center rounded-xl border border-teal-200 dark:border-teal-700 px-3 py-2 text-xs font-bold text-teal-700 dark:text-teal-300 hover:bg-teal-50 dark:hover:bg-teal-500/10 transition">
                    Data Latih
                </a>
                <a href="{{ route('admin.model-ai') }}" wire:navigate
                    class="inline-flex items-center justify-center rounded-xl bg-teal-600 px-3 py-2 text-xs font-bold text-white hover:bg-teal-700 transition">
                    Model AI
                </a>
            </div>
        </div>
    @endif

    @if ($pesan)
        <div class="flex items-center justify-between gap-3 rounded-xl bg-teal-50 dark:bg-teal-950/30 border border-teal-200 dark:border-teal-800 px-4 py-3 text-sm text-teal-800 dark:text-teal-200">
            <span>{{ $pesan }}</span>
            <button wire:click="$set('pesan', null)" class="text-teal-600 font-bold">&times;</button>
        </div>
    @endif

    <!-- Ringkasan filter aktif -->
    <div class="flex flex-wrap items-center gap-2 text-xs">
        @php $titikAktif = $daftarTitik->firstWhere('id', $titikAcuanId); @endphp
        <span class="inline-flex items-center gap-1 rounded-full bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-gray-700 px-3 py-1 text-gray-600 dark:text-gray-300">
            📍 {{ $titikAktif?->nama ?? 'Pilih kampus' }}
        </span>
        @if ($budgetMaks)
            <span class="inline-flex items-center gap-1 rounded-full bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-gray-700 px-3 py-1 text-gray-600 dark:text-gray-300">
                Budget ≤ Rp{{ number_format($budgetMaks, 0, ',', '.') }}
            </span>
        @endif
        @if ($tipeHunian !== '')
            <span class="inline-flex items-center gap-1 rounded-full bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-gray-700 px-3 py-1 text-gray-600 dark:text-gray-300">
                Tipe {{ ucfirst($tipeHunian) }}
            </span>
        @endif
        @foreach ($fasilitasWajib as $f)
            <span class="inline-flex items-center gap-1 rounded-full bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-gray-700 px-3 py-1 text-gray-600 dark:text-gray-300">
                {{ $f }}
            </span>
        @endforeach
        <button wire:click="resetFilter"
            class="ml-auto inline-flex items-center gap-1 font-semibold text-gray-500 hover:text-rose-600 dark:text-gray-400 dark:hover:text-rose-400">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
            Reset filter
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Kolom Kiri: Form Preferensi & Slider Bobot -->
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-6 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">Langkah 1 — Lokasi &amp; Budget</h2>
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
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Kampus / lokasi acuan</label>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-1">Jarak setiap kos dihitung dari titik ini.</p>
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

                <!-- Prioritas: preset sederhana + pengaturan lanjutan -->
                <div class="pt-4 border-t border-gray-100 dark:border-gray-700 space-y-4">
                    <div>
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-teal-800 dark:text-teal-300">Langkah 2 — Prioritas Anda</h3>
                        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                            Pilih yang paling penting. Kami sudah menyiapkan kombinasi bobot yang seimbang.
                        </p>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        @foreach ([
                            'hemat' => ['Hemat', 'Murah & dekat'],
                            'seimbang' => ['Seimbang', 'Semua aspek'],
                            'lengkap' => ['Lengkap', 'Fasilitas & luas'],
                        ] as $kunci => $label)
                            <button type="button" wire:click="terapkanPreset('{{ $kunci }}')"
                                @class([
                                    'rounded-xl border-2 px-2 py-2 text-center transition',
                                    'border-teal-500 bg-teal-50 dark:bg-teal-500/10' => $presetAktif === $kunci,
                                    'border-gray-200 dark:border-gray-600 hover:border-teal-300' => $presetAktif !== $kunci,
                                ])>
                                <span class="block text-xs font-bold text-gray-800 dark:text-gray-100">{{ $label[0] }}</span>
                                <span class="mt-0.5 block text-[10px] text-gray-500 dark:text-gray-400">{{ $label[1] }}</span>
                            </button>
                        @endforeach
                    </div>

                    <details class="rounded-xl bg-gray-50 dark:bg-gray-700/40 p-3">
                        <summary class="cursor-pointer text-xs font-semibold text-gray-700 dark:text-gray-200">
                            Atur bobot sendiri (opsional)
                        </summary>
                        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                            Geser untuk menyesuaikan. Persentase dihitung otomatis.
                        </p>

                        @php $persen = $this->bobotPersen; @endphp

                        <div class="mt-3 space-y-3">
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Harga sewa <span class="text-gray-400">(makin murah makin baik)</span></span>
                                    <span class="font-bold text-teal-700 dark:text-teal-400">{{ $persen['C1'] }}%</span>
                                </div>
                                <input type="range" min="1" max="50" wire:model.live="bobotC1" class="w-full accent-teal-600" aria-label="Bobot harga sewa" />
                            </div>

                            <div class="space-y-1">
                                <div class="flex justify-between text-xs">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Kewajaran harga AI <span class="text-gray-400">(tidak kemahalan)</span></span>
                                    <span class="font-bold text-teal-700 dark:text-teal-400">{{ $persen['C2'] }}%</span>
                                </div>
                                <input type="range" min="1" max="50" wire:model.live="bobotC2" class="w-full accent-teal-600" aria-label="Bobot kewajaran harga" />
                            </div>

                            <div class="space-y-1">
                                <div class="flex justify-between text-xs">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Jarak ke kampus <span class="text-gray-400">(makin dekat makin baik)</span></span>
                                    <span class="font-bold text-teal-700 dark:text-teal-400">{{ $persen['C3'] }}%</span>
                                </div>
                                <input type="range" min="1" max="50" wire:model.live="bobotC3" class="w-full accent-teal-600" aria-label="Bobot jarak ke kampus" />
                            </div>

                            <div class="space-y-1">
                                <div class="flex justify-between text-xs">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Kelengkapan fasilitas <span class="text-gray-400">(makin lengkap makin baik)</span></span>
                                    <span class="font-bold text-teal-700 dark:text-teal-400">{{ $persen['C4'] }}%</span>
                                </div>
                                <input type="range" min="1" max="50" wire:model.live="bobotC4" class="w-full accent-teal-600" aria-label="Bobot fasilitas" />
                            </div>

                            <div class="space-y-1">
                                <div class="flex justify-between text-xs">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Luas kamar <span class="text-gray-400">(makin luas makin baik)</span></span>
                                    <span class="font-bold text-teal-700 dark:text-teal-400">{{ $persen['C5'] }}%</span>
                                </div>
                                <input type="range" min="1" max="50" wire:model.live="bobotC5" class="w-full accent-teal-600" aria-label="Bobot luas kamar" />
                            </div>
                        </div>
                    </details>
                </div>

                <!-- Tombol Aksi Hitung -->
                <div class="pt-2">
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-teal-800 dark:text-teal-300 mb-2">Langkah 3 — Lihat Rekomendasi</h3>
                    <button wire:click="cariDanPeringkatkan" wire:loading.attr="disabled"
                        class="w-full py-3 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm shadow-md transition disabled:opacity-50 flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="cariDanPeringkatkan">Cari Kos Terbaik</span>
                        <span wire:loading wire:target="cariDanPeringkatkan">Mencari kos terbaik...</span>
                    </button>
                </div>

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
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Belum ada rekomendasi</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                        Isi <strong>Langkah 1</strong> (lokasi &amp; budget), pilih <strong>Langkah 2</strong> (prioritas),
                        lalu tekan tombol <strong>Cari Kos Terbaik</strong>. Hasil akan muncul di sini.
                    </p>
                </div>
            @elseif (empty($hasilPeringkat))
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-12 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada kamar yang memenuhi filter pencarian Anda. Silakan longgarkan batas budget atau fasilitas wajib.</p>
                </div>
            @else
                <!-- Tingkat keyakinan rekomendasi -->
                @if ($sensitivitas)
                    <details class="bg-amber-50/70 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/40 rounded-2xl p-4">
                        <summary class="flex cursor-pointer items-center gap-2 text-xs font-bold text-amber-900 dark:text-amber-200">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-amber-500 text-white text-[10px] font-bold">i</span>
                            Seberapa yakin rekomendasi ini?
                        </summary>
                        <p class="mt-2 text-[11px] text-amber-800 dark:text-amber-300">
                            Kami mencoba mengubah prioritas sedikit (±25%). Jika peringkat tetap,
                            artinya rekomendasi cenderung stabil meski selera Anda berubah.
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 text-[11px] pt-2">
                            @foreach ($sensitivitas as $kode => $s)
                                <div class="flex items-center gap-1.5 text-gray-700 dark:text-gray-300">
                                    <span class="inline-block h-2 w-2 rounded-full {{ $s['stabil'] ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                    <span><strong>{{ $s['kriteria'] }}:</strong> {{ $s['stabil'] ? 'stabil' : 'bisa berubah bila prioritas diubah' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </details>
                @endif

                @php
                    $totalHasil = count($hasilPeringkat);
                    $ditampilkan = array_slice($hasilPeringkat, 0, $batasTampil);
                @endphp

                <div class="flex items-end justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Rekomendasi untuk Anda</h3>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">
                            Menampilkan {{ count($ditampilkan) }} dari {{ $totalHasil }} kamar yang cocok.
                        </p>
                    </div>
                </div>

                <!-- Daftar Kartu Peringkat -->
                <div class="space-y-4">
                    @foreach ($ditampilkan as $item)
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
                                        Kecocokan: <span class="font-bold text-gray-900 dark:text-gray-100">{{ round($item['skor'] * 100) }}%</span>
                                    </div>
                                    <div class="mt-1 ml-auto h-1.5 w-24 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                        <div class="h-full rounded-full bg-teal-500" style="width: {{ max(0, min(100, (int) round($item['skor'] * 100))) }}%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Penjelasan harga wajar menurut AI -->
                            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-gray-100 dark:border-gray-700 text-xs">
                                <span class="text-gray-500">Harga wajar menurut AI:</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">Rp{{ number_format($item['harga_prediksi'], 0, ',', '.') }}</span>

                                @php
                                    $lbl = $item['label_kewajaran'];
                                    $badgeCls = match($lbl) {
                                        'di_bawah_pasar' => 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800',
                                        'di_atas_pasar' => 'bg-rose-100 text-rose-800 border-rose-300 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800',
                                        default => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800',
                                    };
                                    $badgeTxt = match($lbl) {
                                        'di_bawah_pasar' => 'Lebih murah dari pasar',
                                        'di_atas_pasar' => 'Lebih mahal dari pasar',
                                        default => 'Wajar pasar',
                                    };

                                    $selisih = (float) $item['selisih_persen'];
                                    $penjelasanHarga = $selisih < 0
                                        ? 'Sekitar '.number_format(abs($selisih), 1, ',', '.').'% lebih murah dari harga pasar.'
                                        : ($selisih > 0
                                            ? 'Sekitar '.number_format($selisih, 1, ',', '.').'% lebih mahal dari harga pasar.'
                                            : 'Sesuai harga pasar.');
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full font-bold text-[10px] border {{ $badgeCls }}">
                                    {{ $badgeTxt }} ({{ $item['selisih_persen'] > 0 ? '+' : '' }}{{ $item['selisih_persen'] }}%)
                                </span>
                                <p class="w-full text-[10px] text-gray-500 dark:text-gray-400">{{ $penjelasanHarga }}</p>
                            </div>

                            <!-- Alasan peringkat -->
                            <div class="rounded-xl bg-gray-50 dark:bg-gray-750 p-3 text-xs text-gray-600 dark:text-gray-300">
                                <strong>Kenapa cocok?</strong> {{ $item['alasan'] }}
                            </div>

                            <!-- Skor relatif tiap kriteria (0-100) -->
                            <div class="grid grid-cols-5 gap-2 pt-1 text-center text-[10px]" title="Skor 0-100: makin tinggi makin baik untuk kriteria tersebut">
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

                    @if ($totalHasil > $batasTampil)
                        <div class="text-center">
                            <button wire:click="tambahBatas"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-teal-200 dark:border-teal-700 bg-white dark:bg-gray-800 px-4 py-2 text-xs font-bold text-teal-700 dark:text-teal-300 hover:bg-teal-50 dark:hover:bg-teal-500/10 transition">
                                Muat lebih banyak ({{ $totalHasil - $batasTampil }} lagi)
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Cara kerja singkat -->
    <details class="rounded-2xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-5">
        <summary class="cursor-pointer text-sm font-bold text-gray-900 dark:text-gray-100">
            Bagaimana rekomendasi ini dihitung?
        </summary>
        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-gray-600 dark:text-gray-300">
            <div><strong>Harga sewa</strong> — kos dengan harga lebih rendah mendapat nilai lebih tinggi.</div>
            <div><strong>Kewajaran harga AI</strong> — membandingkan harga kamar dengan estimasi harga pasar.</div>
            <div><strong>Jarak ke kampus</strong> — semakin dekat ke titik acuan, semakin tinggi nilainya.</div>
            <div><strong>Kelengkapan fasilitas</strong> — semakin banyak fasilitas, semakin tinggi nilainya.</div>
            <div><strong>Luas kamar</strong> — kamar yang lebih luas mendapat nilai lebih tinggi.</div>
            <div><strong>Kecocokan</strong> — gabungan semua kriteria sesuai prioritas yang Anda pilih (0–100%).</div>
        </div>
    </details>
</div>
