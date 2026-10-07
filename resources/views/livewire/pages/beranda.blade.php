<?php

use App\Models\Kamar;
use App\Models\Pengaturan;
use App\Models\Properti;
use App\Support\Koordinat;
use App\Support\NormalisasiKota;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.publik')] class extends Component
{
    public string $cari = '';

    public function mount(): void
    {
        if (auth()->check()) {
            $this->redirect(route('dashboard'), navigate: true);
        }
    }

    public function with(): array
    {
        $query = Properti::query()
            ->where('status', 'aktif')
            ->select(['id', 'nama', 'kota', 'alamat', 'foto', 'fasilitas', 'tipe_hunian', 'harga', 'harga_mingguan', 'harga_harian', 'harga_asli', 'status', 'created_at'])
            ->with(['fotos:id,properti_id,path,urutan'])
            ->withCount(['kamars as kamar_tersedia' => fn ($q) => $q->where('status', 'tersedia'), 'ulasans as total_ulasan'])
            ->withAvg('ulasans as rating_ulasan', 'rating')
            ->withMin(['kamars as harga_termurah' => fn ($q) => $q->where('status', 'tersedia')->where('harga_sewa_bulanan', '>', 0)], 'harga_sewa_bulanan');

        if ($this->cari) {
            $query->where(fn ($q) => $q
                ->where('nama', 'like', "%{$this->cari}%")
                ->orWhere('kota', 'like', "%{$this->cari}%")
                ->orWhere('alamat', 'like', "%{$this->cari}%"));
        }

        $propertiList = $query
            ->latest('created_at')
            ->take(12)
            ->get();

        $promoIds = Pengaturan::landingPromoIds();
        $propertiPromo = collect();

        if ($promoIds !== []) {
            $propertiPromo = Properti::query()
                ->where('status', 'aktif')
                ->select(['id', 'nama', 'kota', 'alamat', 'foto', 'fasilitas', 'tipe_hunian', 'harga', 'harga_mingguan', 'harga_harian', 'harga_asli', 'status', 'created_at'])
                ->with(['fotos:id,properti_id,path,urutan'])
                ->withCount(['kamars as kamar_tersedia' => fn ($q) => $q->where('status', 'tersedia'), 'ulasans as total_ulasan'])
                ->withAvg('ulasans as rating_ulasan', 'rating')
                ->withMin(['kamars as harga_termurah' => fn ($q) => $q->where('status', 'tersedia')->where('harga_sewa_bulanan', '>', 0)], 'harga_sewa_bulanan')
                ->whereIn('id', $promoIds)
                ->orderByRaw('FIELD(id, '.implode(',', array_map('intval', $promoIds)).')')
                ->take(8)
                ->get();
        }

        if ($propertiPromo->isEmpty()) {
            $propertiPromo = (clone $query)
                ->whereNotNull('harga_asli')
                ->whereColumn('harga_asli', '>', 'harga')
                ->latest('created_at')
                ->take(8)
                ->get();
        }

        $heroId = Pengaturan::landingHeroId();
        $heroKos = $heroId ? Properti::query()
            ->where('status', 'aktif')
            ->select(['id', 'nama', 'kota', 'alamat', 'foto', 'fasilitas', 'tipe_hunian', 'harga', 'harga_mingguan', 'harga_harian', 'harga_asli', 'status', 'created_at'])
            ->with(['fotos:id,properti_id,path,urutan'])
            ->withCount(['kamars as kamar_tersedia' => fn ($q) => $q->where('status', 'tersedia'), 'ulasans as total_ulasan'])
            ->withAvg('ulasans as rating_ulasan', 'rating')
            ->withMin(['kamars as harga_termurah' => fn ($q) => $q->where('status', 'tersedia')->where('harga_sewa_bulanan', '>', 0)], 'harga_sewa_bulanan')
            ->find($heroId) : null;

        if (! $heroKos) {
            $heroKos = $propertiList->first();
        }

        $bannerKustom = Pengaturan::landingBanners(true);

        return [
            'totalProperti' => cache()->remember('beranda.totalProperti', 300, fn () => Properti::where('status', 'aktif')->count()),
            'totalKamar' => cache()->remember('beranda.totalKamar', 300, fn () => Kamar::where('status', 'tersedia')->count()),
            'propertiList' => $propertiList,
            'propertiPromo' => $propertiPromo,
            'heroKos' => $heroKos,
            'bannerAds' => $bannerKustom === [] && ! Pengaturan::adaKustomLandingBanners() ? [] : $bannerKustom,
            'kotaRekomendasi' => cache()->remember('beranda.kotaRekomendasi', 3600, fn () => Properti::where('status', 'aktif')
                ->whereNotNull('kota')
                ->where('kota', '!=', '')
                ->groupBy('kota')
                ->selectRaw('kota, COUNT(*) as total')
                ->orderByDesc('total')
                ->limit(1)
                ->value('kota')),
            'akhirPromo' => mktime(23, 59, 59, (int) date('n'), (int) date('t'), (int) date('Y')),
            'daftarKota' => collect(cache()->remember('beranda.daftarKota', 3600, fn () => Properti::where('status', 'aktif')
                ->whereNotNull('kota')
                ->distinct()
                ->orderBy('kota')
                ->pluck('kota')
                ->all())),
            'kotaStatistik' => collect(cache()->remember('beranda.kotaStatistik', 3600, fn () => NormalisasiKota::agregasi(Properti::where('status', 'aktif')
                ->whereNotNull('kota')
                ->where('kota', '!=', '')
                ->pluck('kota')
                ->all())->all())),
            'markers' => collect(cache()->remember('beranda.markers', 3600, fn () => Properti::where('status', 'aktif')
                ->get(['id', 'nama', 'kota', 'alamat', 'latitude', 'longitude'])
                ->map(function ($p) {
                    $titik = Koordinat::titik($p->kota, $p->latitude, $p->longitude);

                    return $titik ? [
                        'id' => $p->id,
                        'nama' => $p->nama,
                        'kota' => $p->kota,
                        'alamat' => $p->alamat,
                        'lat' => $titik[0],
                        'lng' => $titik[1],
                    ] : null;
                })
                ->filter()
                ->values()
                ->all())),
        ];
    }
}; ?>

<div class="bg-paper dark:bg-gray-950">
    <!-- Banner iklan partner (auto-slide, bisa diatur superadmin) -->
    @if ($bannerAds !== [] || ! \App\Models\Pengaturan::adaKustomLandingBanners())
        <section class="relative overflow-hidden">
            <x-promo-ads variant="hero" :ads="$bannerAds" />
        </section>
    @endif

    <!-- Hero pencarian -->
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 dark:from-gray-950 dark:via-brand-950 dark:to-brand-900">
        <style>
            @keyframes beranda-float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
            @keyframes beranda-float2 { 0%,100% { transform: translateY(0) rotate(-2deg); } 50% { transform: translateY(-8px) rotate(2deg); } }
            @keyframes beranda-pulse-dot { 0%,100% { opacity: 1; transform: scale(1); } 50% { opacity: .5; transform: scale(.8); } }
            .beranda-float { animation: beranda-float 5s ease-in-out infinite; }
            .beranda-float2 { animation: beranda-float2 6s ease-in-out infinite; }
            .beranda-pulse-dot { animation: beranda-pulse-dot 1.8s ease-in-out infinite; }
        </style>
        <!-- Dekorasi latar -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -right-24 -top-28 h-80 w-80 rounded-full bg-emerald-400/20 blur-3xl"></div>
            <div class="absolute right-1/3 -bottom-32 h-64 w-64 rounded-full bg-amber-400/20 blur-3xl"></div>
            <div class="absolute -left-20 top-1/3 h-56 w-56 rounded-full bg-teal-300/10 blur-3xl"></div>
            <div class="absolute inset-0 opacity-[0.07]" style="background-image:url('data:image/svg+xml,%3Csvg width=%2260%22 height=%2260%22 viewBox=%220 0 60 60%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg fill=%22none%22 fill-rule=%22evenodd%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%221%22%3E%3Cpath d=%22M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z%22/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 pt-8 pb-8 sm:pt-16 sm:pb-14 grid lg:grid-cols-[1.1fr_.9fr] gap-8 lg:gap-10 items-center">
            <!-- Kolom teks -->
            <div class="min-w-0">
                <p class="inline-flex max-w-full items-center gap-2 rounded-full bg-white/10 border border-white/15 backdrop-blur px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-amber-300">
                    <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-400 beranda-pulse-dot"></span>
                    <span class="truncate">Ngekos.in #LebihDariHunian</span>
                </p>
                <h1 class="mt-3 sm:mt-4 text-[28px] sm:text-5xl font-extrabold tracking-tight text-white leading-[1.15] sm:leading-[1.1]">
                    Cari <span class="text-amber-300">Kos Impianmu</span>,<br class="hidden sm:block">
                    Semudah Rebahan.
                </h1>
                <p class="mt-2.5 sm:mt-3 text-sm sm:text-lg text-brand-100 max-w-xl leading-relaxed">
                    Foto asli, harga transparan, chat langsung pemilik. Saat ini ada
                    <span class="font-bold text-white whitespace-nowrap">{{ number_format($totalKamar, 0, ',', '.') }} kamar</span> di
                    <span class="font-bold text-white whitespace-nowrap">{{ number_format($totalProperti, 0, ',', '.') }} kos aktif</span> menunggumu.
                </p>

                <form action="{{ route('kos.index') }}" method="GET"
                      class="mt-5 sm:mt-6 max-w-2xl bg-white dark:bg-gray-800 rounded-2xl p-1.5 sm:p-2 flex items-center gap-1.5 sm:gap-2 shadow-2xl shadow-black/25 ring-1 ring-white/20">
                    <div class="flex-1 min-w-0 flex items-center gap-2 px-2 sm:px-3">
                        <span class="hidden min-[360px]:flex h-8 w-8 sm:h-9 sm:w-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 dark:bg-brand-500/10">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5 text-brand-700 dark:text-brand-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                        </span>
                        <input type="search" name="cari" value="{{ request('cari', $cari) }}" placeholder="Mau ngekos di mana?"
                            autocomplete="off" enterkeyhint="search"
                            class="w-full min-w-0 border-0 bg-transparent text-base text-slate-900 dark:text-gray-100 placeholder-slate-400 focus:ring-0 focus:outline-none py-3">
                    </div>
                    <button type="submit" class="btn-accent shrink-0 !px-4 sm:!px-6 !py-3 !rounded-xl !text-sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        <span class="hidden sm:inline">Cari Kos</span><span class="sm:hidden">Cari</span>
                    </button>
                </form>

                <div class="mt-3.5 sm:mt-4 flex items-center gap-2 overflow-x-auto scrollbar-hide pb-1 -mx-4 px-4 scroll-px-4 sm:mx-0 sm:px-0">
                    <span class="shrink-0 text-xs font-semibold text-brand-100">Populer:</span>
                    @foreach ($daftarKota->take(6) as $kota)
                        <a href="{{ route('kos.index', ['kota' => $kota]) }}" wire:navigate
                           class="shrink-0 inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/5 backdrop-blur px-3 py-1.5 text-xs font-medium text-white hover:bg-amber-400 hover:text-brand-950 hover:border-amber-400 transition">
                            {{ $kota }}
                        </a>
                    @endforeach
                </div>

                <!-- Statistik mini -->
                <div class="mt-5 sm:mt-6 grid grid-cols-3 max-w-xl gap-2 sm:gap-3">
                    <div class="min-w-0 rounded-2xl bg-white/10 border border-white/15 backdrop-blur px-2.5 py-2.5 sm:px-4 sm:py-3">
                        <p class="text-lg sm:text-2xl font-extrabold text-white tabular-nums truncate">{{ number_format($totalKamar, 0, ',', '.') }}+</p>
                        <p class="text-[11px] sm:text-xs text-brand-100 font-medium leading-tight">Kamar Tersedia</p>
                    </div>
                    <div class="min-w-0 rounded-2xl bg-white/10 border border-white/15 backdrop-blur px-2.5 py-2.5 sm:px-4 sm:py-3">
                        <p class="text-lg sm:text-2xl font-extrabold text-white tabular-nums truncate">{{ number_format($totalProperti, 0, ',', '.') }}+</p>
                        <p class="text-[11px] sm:text-xs text-brand-100 font-medium leading-tight">Kos Aktif</p>
                    </div>
                    <div class="min-w-0 rounded-2xl bg-white/10 border border-white/15 backdrop-blur px-2.5 py-2.5 sm:px-4 sm:py-3">
                        <p class="text-lg sm:text-2xl font-extrabold text-white tabular-nums truncate">{{ $daftarKota->count() }}+</p>
                        <p class="text-[11px] sm:text-xs text-brand-100 font-medium leading-tight">Kota di Indonesia</p>
                    </div>
                </div>
            </div>

            <!-- Kolom visual (desktop) -->
            <div class="hidden lg:block relative">
                <div class="relative mx-auto w-full max-w-sm">
                    <div class="beranda-float relative rounded-3xl overflow-hidden shadow-2xl shadow-black/40 ring-1 ring-white/20 rotate-2">
                        @if ($heroKos ?? $propertiList->first())
                            @php $heroKos = $heroKos ?? $propertiList->first(); @endphp
                            <img src="{{ $heroKos->fotoCover() ?? asset('favicon.svg') }}" alt="{{ $heroKos->nama }}" class="h-80 w-full object-cover">
                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent p-5 pt-14">
                                <p class="text-white font-bold truncate">{{ $heroKos->nama }}</p>
                                <p class="text-white/70 text-xs truncate">{{ $heroKos->kota ?? $heroKos->alamat }}</p>
                                <p class="mt-1 text-amber-300 font-extrabold">Rp{{ number_format($heroKos->harga ?? $heroKos->harga_termurah ?? 0, 0, ',', '.') }}<span class="text-xs font-medium text-white/70">/bln</span></p>
                            </div>
                        @else
                            <div class="h-80 w-full bg-white/10 backdrop-blur flex items-center justify-center">
                                <svg class="h-16 w-16 text-white/30" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
                            </div>
                        @endif
                    </div>
                    <!-- Kartu melayang: rating -->
                    <div class="beranda-float2 absolute -left-10 top-8 rounded-2xl bg-white dark:bg-gray-800 shadow-xl px-4 py-3 flex items-center gap-3 ring-1 ring-stone-200 dark:ring-gray-700">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-500/20">
                            <svg class="h-5 w-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 1.5l2.6 5.3 5.9.9-4.2 4.1 1 5.8L10 14.9l-5.3 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z" /></svg>
                        </span>
                        <div>
                            <p class="text-sm font-extrabold text-slate-900 dark:text-white">4.9/5.0</p>
                            <p class="text-[11px] text-slate-500 dark:text-gray-400">Ribuan ulasan penghuni</p>
                        </div>
                    </div>
                    <!-- Kartu melayang: verifikasi -->
                    <div class="beranda-float absolute -right-6 bottom-10 rounded-2xl bg-white dark:bg-gray-800 shadow-xl px-4 py-3 flex items-center gap-3 ring-1 ring-stone-200 dark:ring-gray-700" style="animation-delay:1.2s">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 dark:bg-brand-500/10">
                            <svg class="h-5 w-5 text-brand-700 dark:text-brand-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                        </span>
                        <div>
                            <p class="text-sm font-extrabold text-slate-900 dark:text-white">Terverifikasi</p>
                            <p class="text-[11px] text-slate-500 dark:text-gray-400">Foto asli & lokasi jelas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Wave pemisah -->
        <svg class="block w-full text-paper dark:text-gray-950 -mb-px" viewBox="0 0 1440 48" fill="currentColor" preserveAspectRatio="none"><path d="M0,24 C240,48 480,0 720,16 C960,32 1200,48 1440,24 L1440,48 L0,48 Z"></path></svg>
    </section>

    <!-- Strip kepercayaan -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 mt-5 sm:mt-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
            @foreach ([
                ['Foto Asli & Akurat', 'Semua foto dari pemilik langsung', 'M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z|M15 13a3 3 0 11-6 0 3 3 0 016 0z'],
                ['Harga Transparan', 'Tanpa biaya tersembunyi', 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['Chat Langsung', 'Tanya pemilik, gratis & cepat', 'M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm3.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm3.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z'],
                ['Pembayaran Aman', 'Bukti sewa & kwitansi resmi', 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z'],
            ] as [$judulFitur, $descFitur, $iconFitur])
                <div class="flex items-center gap-2.5 sm:gap-3 rounded-2xl bg-white dark:bg-gray-800 border border-stone-200/80 dark:border-gray-700 px-3 sm:px-4 py-3 shadow-card hover:shadow-card-hover hover:-translate-y-0.5 transition">
                    <span class="flex h-9 w-9 sm:h-10 sm:w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 dark:bg-brand-500/10 text-brand-700 dark:text-brand-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            @foreach (explode('|', $iconFitur) as $pathFitur)
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $pathFitur }}" />
                            @endforeach
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs sm:text-sm font-bold text-slate-900 dark:text-gray-100 truncate">{{ $judulFitur }}</p>
                        <p class="text-[11px] sm:text-xs text-slate-500 dark:text-gray-400 truncate">{{ $descFitur }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Promo -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 mt-8 sm:mt-10">
        <div class="flex items-end justify-between gap-2 sm:gap-3">
            <div class="min-w-0">
                <p class="inline-flex max-w-full items-center gap-1.5 rounded-full bg-brand-50 dark:bg-brand-500/10 border border-brand-100 dark:border-brand-500/20 px-2.5 py-1 text-[11px] font-bold uppercase tracking-widest text-brand-700 dark:text-brand-300 truncate">
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" /></svg>
                    <span class="truncate">Hemat s.d. 800rb</span>
                </p>
                <h2 class="mt-2 text-lg sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-gray-100 text-balance">Promo spesial buat kamu</h2>
                <p class="mt-0.5 text-xs sm:text-sm text-slate-500 dark:text-gray-400">Klaim sebelum kehabisan — diperbarui tiap bulan</p>
            </div>
            <a href="{{ route('kos.index') }}" wire:navigate class="shrink-0 inline-flex items-center gap-1 whitespace-nowrap rounded-full border border-stone-300 dark:border-gray-700 px-2.5 sm:px-3 py-1.5 text-xs sm:text-sm font-semibold text-brand-700 hover:border-brand-400 hover:bg-brand-50 dark:text-brand-300 dark:hover:bg-brand-500/10 transition">Lihat semua
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
            </a>
        </div>
        <div class="mt-4 flex gap-2 sm:gap-3 overflow-x-auto scrollbar-hide snap-x snap-mandatory pb-2 -mx-4 px-4 scroll-px-4 sm:mx-0 sm:px-0">
            @foreach ([
                ['Pindah Kos Jadi Lebih Ringan', 'Diskon s.d. 800 RIBU!', 'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z'],
                ['Voucher Ngekos 100RB', 'Udah siap buat kamu!', 'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z'],
                ['Ngekos Aman Bisa Refund', 'Perlindungan lebih luas', 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z'],
                ['Gratis 1 Bulan', 'Sewa 11 bulan, gratis 1 bulan', 'M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H4.5a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z'],
                ['Bisa Masuk Tanpa Deposit!', 'Syarat dan ketentuan berlaku', 'M15 8.25H9m6 3H9m3 6l-3-3h1.5V12h3v2.25H12l3 3zm-9 3h12a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15A2.25 2.25 0 002.25 6.75v10.5A2.25 2.25 0 004.5 19.5z'],
                ['Kos di ' . ($kotaRekomendasi ?? 'Kotamu'), 'Banyak pilihan kamar tersedia', 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0z|M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z'],
            ] as [$judulPromo, $subPromo, $iconPromo])
                <a href="{{ route('kos.index') }}" wire:navigate
                   class="snap-start snap-always shrink-0 w-[72vw] max-w-[250px] sm:w-[290px] sm:max-w-none rounded-2xl bg-brand-900 dark:bg-brand-950 border border-brand-800 dark:border-brand-500/20 p-4 sm:p-5 text-white shadow-lg hover:shadow-xl hover:-translate-y-1 transition overflow-hidden relative group">
                    <div class="absolute -right-8 -top-8 h-28 w-28 rounded-full bg-amber-400/20 blur-2xl group-hover:scale-125 transition duration-500"></div>
                    <span class="relative flex h-10 w-10 items-center justify-center rounded-xl bg-amber-400 text-brand-950">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            @foreach (explode('|', $iconPromo) as $pathPromo)
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $pathPromo }}" />
                            @endforeach
                        </svg>
                    </span>
                    <p class="relative mt-3 text-sm font-bold leading-snug">{{ $judulPromo }}</p>
                    <p class="relative mt-1 text-xs text-brand-100">{{ $subPromo }}</p>
                    <span class="relative mt-4 inline-flex items-center gap-1.5 rounded-full bg-amber-400 px-3 py-1.5 text-[11px] font-bold text-brand-950 group-hover:gap-2.5 transition-all">Ambil promo
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    <!-- Kenapa Ngekos.in + Cara sewa -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 mt-10">
        <div class="text-center max-w-2xl mx-auto">
            <p class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 dark:bg-brand-500/10 border border-brand-100 dark:border-brand-500/20 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-brand-700 dark:text-brand-300">Kenapa Ngekos.in?</p>
            <h2 class="mt-2 text-lg sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-gray-100">Semua urusan kos beres dalam satu aplikasi</h2>
            <p class="mt-1 text-xs sm:text-sm text-slate-500 dark:text-gray-400">Dari cari sampai bayar, tanpa drama perantara.</p>
        </div>
        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach ([
                ['01', 'Cari & Filter Cerdas', 'Filter lokasi, harga, tipe putra/putri/campur, dan fasilitas lengkap.'],
                ['02', 'Survei & Chat Gratis', 'Jadwalkan survei dan chat pemilik langsung tanpa perantara.'],
                ['03', 'Booking & Bayar Aman', 'Amankan kamarmu dengan pembayaran tercatat + kwitansi resmi.'],
                ['04', 'Tagihan Otomatis', 'Pengingat bulanan otomatis, riwayat bayar rapi, anti lupa.'],
            ] as [$nomor, $judulLangkah, $descLangkah])
                <div class="relative rounded-2xl bg-white dark:bg-gray-800 border border-stone-200/80 dark:border-gray-700 p-5 shadow-card hover:shadow-card-hover hover:-translate-y-1 transition overflow-hidden group">
                    <span class="absolute -right-2 -top-4 text-6xl font-extrabold text-stone-100 dark:text-gray-700/50 select-none group-hover:text-brand-50 dark:group-hover:text-brand-500/10 transition">{{ $nomor }}</span>
                    <span class="relative flex h-10 w-10 items-center justify-center rounded-xl bg-brand-700 text-white text-sm font-extrabold shadow-md shadow-brand-900/25">{{ $nomor }}</span>
                    <h3 class="relative mt-3 text-sm font-bold text-slate-900 dark:text-gray-100">{{ $judulLangkah }}</h3>
                    <p class="relative mt-1 text-xs text-slate-500 dark:text-gray-400 leading-relaxed">{{ $descLangkah }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Pemilik + Survei -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 mt-10 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="relative rounded-2xl overflow-hidden bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 p-6 sm:p-8 text-white shadow-lg">
            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-amber-400/20 blur-2xl"></div>
            <div class="absolute -left-8 -bottom-10 h-32 w-32 rounded-full bg-emerald-400/20 blur-2xl"></div>
            <p class="relative inline-flex items-center gap-1.5 rounded-full bg-white/15 border border-white/20 px-2.5 py-1 text-[11px] font-bold uppercase tracking-widest text-amber-300">Untuk pemilik kos</p>
            <h2 class="relative mt-2 text-lg sm:text-xl font-extrabold tracking-tight">Penuhkan Kamarmu Lebih Cepat</h2>
            <p class="relative mt-1.5 text-sm text-brand-100 leading-relaxed">Iklankan kos, kelola kamar, tagihan otomatis, dan chat calon penyewa — semua dari HP.</p>
            <ul class="relative mt-3 space-y-1.5 text-xs text-brand-100">
                <li class="flex items-center gap-2"><svg class="h-4 w-4 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg> Pasang iklan gratis, tanpa komisi tersembunyi</li>
                <li class="flex items-center gap-2"><svg class="h-4 w-4 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg> Dashboard okupansi & laporan keuangan</li>
            </ul>
            <a href="{{ route('register') }}" wire:navigate class="relative mt-5 inline-flex items-center gap-1.5 btn-accent !text-xs">Daftarkan Kos Gratis
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
            </a>
        </div>
        <div class="rounded-2xl bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 p-6 sm:p-8 shadow-card">
            <p class="text-[11px] font-bold uppercase tracking-widest text-brand-700 dark:text-brand-300">Survei dulu, sewa kemudian</p>
            <h2 class="mt-1 text-lg sm:text-xl font-extrabold tracking-tight text-slate-900 dark:text-gray-100">Yakin Dulu Baru Bayar</h2>
            <p class="mt-1.5 text-sm text-slate-500 dark:text-gray-400 leading-relaxed">Cari, pilih, survei langsung ke lokasi, chat pemilik gratis. Kalau cocok, booking detik itu juga.</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('kos.index') }}" wire:navigate class="btn-primary !text-xs">Mulai Cari Kos</a>
                <a href="{{ route('bantuan') }}" wire:navigate class="btn-secondary !text-xs">Cara Survei Aman</a>
            </div>
        </div>
    </section>

    <!-- Promo Ngebut -->
    @if ($propertiPromo->isNotEmpty())
    <section class="max-w-7xl mx-auto px-4 sm:px-6 pt-10"
        x-data="{ hari: '–', jam: '–', mnt: '–', dtk: '–', init() { const akhir = @js($akhirPromo); const tick = () => { let s = Math.max(0, akhir - Math.floor(Date.now() / 1000)); this.hari = Math.floor(s / 86400); const p = (n) => String(n).padStart(2, '0'); this.jam = p(Math.floor(s % 86400 / 3600)); this.mnt = p(Math.floor(s % 3600 / 60)); this.dtk = p(s % 60); }; tick(); setInterval(tick, 1000); } }">
        <div class="relative overflow-hidden rounded-2xl bg-brand-950 dark:bg-brand-950 border border-brand-800 dark:border-brand-500/20 p-4 sm:p-6 text-white shadow-lg">
            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-amber-400/20 blur-2xl"></div>
            <div class="absolute right-1/3 -bottom-12 h-32 w-32 rounded-full bg-brand-500/20 blur-2xl"></div>
            <div class="relative flex flex-col sm:flex-row sm:flex-wrap sm:items-center sm:justify-between gap-3 sm:gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="flex h-10 w-10 sm:h-11 sm:w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-400 text-brand-950">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-base sm:text-xl font-extrabold tracking-tight">Promo Ngebut</h2>
                        <p class="text-xs sm:text-sm text-brand-100 truncate sm:whitespace-normal">Diskon khusus bulan ini — siapa cepat dia dapat</p>
                    </div>
                </div>
                <div class="grid w-full sm:w-auto grid-cols-4 items-center gap-1 sm:flex sm:gap-1.5 text-center">
                    <div class="rounded-xl bg-white/10 border border-white/15 backdrop-blur px-1.5 sm:px-2.5 py-1.5 min-w-0"><p class="text-base sm:text-lg font-extrabold tabular-nums" x-text="hari"></p><p class="text-[10px] uppercase tracking-wider text-brand-100">Hari</p></div>
                    <div class="rounded-xl bg-white/10 border border-white/15 backdrop-blur px-1.5 sm:px-2.5 py-1.5 min-w-0"><p class="text-base sm:text-lg font-extrabold tabular-nums" x-text="jam"></p><p class="text-[10px] uppercase tracking-wider text-brand-100">Jam</p></div>
                    <div class="rounded-xl bg-white/10 border border-white/15 backdrop-blur px-1.5 sm:px-2.5 py-1.5 min-w-0"><p class="text-base sm:text-lg font-extrabold tabular-nums" x-text="mnt"></p><p class="text-[10px] uppercase tracking-wider text-brand-100">Mnt</p></div>
                    <div class="rounded-xl bg-amber-400 px-1.5 sm:px-2.5 py-1.5 min-w-0 text-brand-950"><p class="text-base sm:text-lg font-extrabold tabular-nums" x-text="dtk"></p><p class="text-[10px] uppercase tracking-wider font-bold">Dtk</p></div>
                </div>
            </div>
        </div>
        <div class="mt-4 flex gap-2 sm:gap-3 overflow-x-auto scrollbar-hide snap-x snap-mandatory pb-2 -mx-4 px-4 scroll-px-4 sm:mx-0 sm:px-0">
            @foreach ($propertiPromo as $properti)
                <x-kartu-kos :properti="$properti" class="snap-start snap-always w-[68vw] max-w-[240px] sm:w-[260px] sm:max-w-none shrink-0 !shadow-card hover:!shadow-card-hover hover:-translate-y-1 transition" />
            @endforeach
        </div>
    </section>
    @endif

    <!-- Rekomendasi kos -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 pt-8 sm:pt-10">
        <div class="flex items-end justify-between gap-2 sm:gap-3">
            <div class="min-w-0">
                <p class="inline-flex max-w-full items-center gap-1.5 rounded-full bg-brand-50 dark:bg-brand-500/10 border border-brand-100 dark:border-brand-500/20 px-2.5 py-1 text-[11px] font-bold uppercase tracking-widest text-brand-700 dark:text-brand-300">
                    <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M10 1.5l2.6 5.3 5.9.9-4.2 4.1 1 5.8L10 14.9l-5.3 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z" /></svg>
                    <span class="truncate">Pilihan penghuni</span>
                </p>
                <h2 class="mt-2 text-lg sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-gray-100 text-balance">Rekomendasi kos di {{ $kotaRekomendasi ?? 'kotamu' }}</h2>
                <p class="mt-0.5 text-xs sm:text-sm text-slate-500 dark:text-gray-400">Kos dengan kamar tersedia terbanyak & rating terbaik</p>
            </div>
            <a href="{{ $kotaRekomendasi ? route('kos.index', ['kota' => $kotaRekomendasi]) : route('kos.index') }}" wire:navigate class="shrink-0 inline-flex items-center gap-1 whitespace-nowrap rounded-full border border-stone-300 dark:border-gray-700 px-2.5 sm:px-3 py-1.5 text-xs sm:text-sm font-semibold text-brand-700 hover:border-brand-400 hover:bg-brand-50 dark:text-brand-300 dark:hover:bg-brand-500/10 transition">Lihat semua
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
            </a>
        </div>
        <div class="mt-4 flex gap-2 sm:gap-3 overflow-x-auto scrollbar-hide snap-x snap-mandatory pb-2 -mx-4 px-4 scroll-px-4 sm:mx-0 sm:px-0">
            @forelse ($propertiList->take(8) as $properti)
                <x-kartu-kos :properti="$properti" class="snap-start snap-always w-[68vw] max-w-[240px] sm:w-[260px] sm:max-w-none shrink-0 hover:-translate-y-1 transition" />
            @empty
                <div class="w-full rounded-2xl border border-dashed border-stone-300 dark:border-gray-700 p-10 text-center">
                    <p class="text-sm font-semibold text-slate-600 dark:text-gray-300">Belum ada kos terdaftar.</p>
                    <p class="mt-1 text-xs text-slate-500">Jadilah pemilik pertama yang pasang kos di sini.</p>
                </div>
            @endforelse
        </div>
    </section>

    <!-- Kos yang lagi promo -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 pt-8 sm:pt-10 scroll-mb-24">
        <div class="flex items-end justify-between gap-2 sm:gap-3">
            <div class="min-w-0">
                <h2 class="text-lg sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-gray-100 text-balance">Jelajahi semua kos</h2>
                <p class="mt-0.5 text-xs sm:text-sm text-slate-500 dark:text-gray-400">{{ $propertiList->count() }} kos terbaru menunggumu — harga bulan pertama sudah termasuk diskon</p>
            </div>
            <a href="{{ route('kos.index') }}" wire:navigate class="shrink-0 whitespace-nowrap text-xs sm:text-sm font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-300 hover:underline">Lihat katalog lengkap →</a>
        </div>
        <div class="mt-4 grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
            @forelse ($propertiList as $properti)
                <x-kartu-kos :properti="$properti" class="w-full hover:-translate-y-1 hover:shadow-card-hover transition" />
            @empty
                <p class="col-span-full text-sm text-slate-500 dark:text-gray-400 py-6 text-center">Belum ada kos terdaftar.</p>
            @endforelse
        </div>
    </section>

    <!-- Area populer + sekitar kampus -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 pt-10 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="rounded-2xl bg-white dark:bg-gray-800 border border-stone-200/80 dark:border-gray-700 p-5 sm:p-6 shadow-card">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-700 text-white">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                </span>
                <div>
                    <h2 class="text-base font-extrabold tracking-tight text-slate-900 dark:text-gray-100">Area Kos Terpopuler</h2>
                    <p class="text-xs text-slate-500 dark:text-gray-400">Paling banyak dicari minggu ini</p>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($kotaStatistik->take(7) as $kota)
                    <a href="{{ route('kos.index', ['kota' => $kota['nama']]) }}" wire:navigate
                       class="group rounded-full border border-stone-300 dark:border-gray-700 pl-3 pr-2 py-1.5 text-xs font-semibold text-slate-600 dark:text-gray-300 hover:border-brand-600 hover:bg-brand-700 hover:text-white transition">
                        Kos {{ $kota['nama'] }} <span class="ml-1 rounded-full bg-brand-50 dark:bg-gray-700 group-hover:bg-white/20 px-1.5 py-0.5 text-[10px] font-bold text-brand-700 dark:text-gray-200 group-hover:text-white">{{ $kota['jumlah'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="rounded-2xl bg-white dark:bg-gray-800 border border-stone-200/80 dark:border-gray-700 p-5 sm:p-6 shadow-card">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-400 text-brand-950">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" /></svg>
                </span>
                <div>
                    <h2 class="text-base font-extrabold tracking-tight text-slate-900 dark:text-gray-100">Kos Sekitar Kampus</h2>
                    <p class="text-xs text-slate-500 dark:text-gray-400">Jalan kaki ke kampus, hemat ongkos</p>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ([['UGM', 'Jogja'], ['UNDIP', 'Semarang'], ['UI', 'Depok'], ['UNPAD', 'Jatinangor'], ['STAN', 'Jakarta'], ['UB', 'Malang'], ['UNAIR', 'Surabaya']] as [$kampus, $kotaKampus])
                    <a href="{{ route('kos.index', ['cari' => $kotaKampus]) }}" wire:navigate
                       class="rounded-full bg-paper dark:bg-gray-900 border border-stone-200 dark:border-gray-700 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:text-gray-300 hover:border-brand-500 hover:text-brand-700 dark:hover:text-brand-300 transition">
                        {{ $kampus }} <span class="text-slate-400 dark:text-gray-500 font-medium">· {{ $kotaKampus }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Testimoni penghuni -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 pt-10">
        <div class="text-center max-w-2xl mx-auto">
            <p class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 dark:bg-brand-500/10 border border-brand-100 dark:border-brand-500/20 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-brand-700 dark:text-brand-300">Rating 4.9 dari ribuan penghuni</p>
            <h2 class="mt-2 text-lg sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-gray-100">Kata mereka yang sudah ngekos</h2>
            <p class="mt-1 text-xs sm:text-sm text-slate-500 dark:text-gray-400">Cerita nyata anak kos dari berbagai kota</p>
        </div>
        <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
            @foreach ([
                ['Rizky Pratama', 'Mahasiswa • Jogja', 'Cari kos 2 hari langsung dapat! Foto sama aslinya persis, pemiliknya fast respon banget di chat.', 'RP'],
                ['Sinta Amelia', 'Karyawan • Jakarta', 'Bayar kos jadi gampang, ada pengingat tiap bulan + kwitansi otomatis. Nggak takut lupa lagi.', 'SA'],
                ['Dimas Saputra', 'Mahasiswa • Malang', 'Survei dulu sebelum booking bikin tenang. Lokasinya sesuai peta, lingkungannya nyaman.', 'DS'],
            ] as [$namaTesti, $peranTesti, $isiTesti, $inisialTesti])
                <figure class="rounded-2xl bg-white dark:bg-gray-800 border border-stone-200/80 dark:border-gray-700 p-5 shadow-card hover:shadow-card-hover hover:-translate-y-1 transition">
                    <div class="flex gap-0.5">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 1.5l2.6 5.3 5.9.9-4.2 4.1 1 5.8L10 14.9l-5.3 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z" /></svg>
                        @endfor
                    </div>
                    <blockquote class="mt-3 text-sm text-slate-600 dark:text-gray-300 leading-relaxed">“{{ $isiTesti }}”</blockquote>
                    <figcaption class="mt-4 flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-700 text-white text-xs font-extrabold">{{ $inisialTesti }}</span>
                        <div>
                            <p class="text-sm font-bold text-slate-900 dark:text-gray-100">{{ $namaTesti }}</p>
                            <p class="text-xs text-slate-500 dark:text-gray-400">{{ $peranTesti }}</p>
                        </div>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </section>

    <!-- Persebaran Kos -->
    @if ($kotaStatistik->isNotEmpty())
    <section x-data="{
                semua: false,
                buka: null,
                cariKota: '',
                muat: 15,
                data: @js($kotaStatistik),
                get totalKota() { return this.data.length; },
                get sisa() { return this.data.slice(6); },
                get kotaLain() {
                    const s = this.sisa;
                    if (!s.length) return null;
                    return { nama: 'Kota Lainnya', jumlah: s.reduce((a, k) => a + k.jumlah, 0), daerah: s };
                },
                get awal() {
                    const rows = this.data.slice(0, 6);
                    const lain = this.kotaLain;
                    return lain ? rows.concat([lain]) : rows;
                },
                get hasilFilter() {
                    const q = this.cariKota.trim().toLowerCase();
                    if (!q) return this.data;
                    return this.data.filter((k) => k.nama.toLowerCase().includes(q));
                },
                get tampil() {
                    if (!this.semua) return this.awal;
                    return this.hasilFilter.slice(0, this.muat);
                },
                get sisaMuat() { return this.hasilFilter.length - this.muat; },
                get kosong() { return this.semua && this.hasilFilter.length === 0; },
                get adaDaerahTampil() {
                    return this.tampil.filter((k) => k.nama !== 'Kota Lainnya').some((kota) => kota.daerah.length);
                },
                kotakBuka() {
                    if (!this.buka) return null;
                    const k = this.tampil.find((kota) => kota.nama === this.buka);
                    return k && k.daerah.length ? k : null;
                },
                bukaModeSemua() {
                    this.buka = null;
                    this.cariKota = '';
                    this.muat = 15;
                    this.semua = true;
                },
                kembaliTeratas() {
                    this.semua = false;
                    this.buka = null;
                    this.cariKota = '';
                    this.muat = 15;
                },
                muatLagi() { this.muat += 15; },
                renderGrafik() {
                    const el = document.getElementById('chart-persebaran-kos');
                    if (!el || !this.tampil.length || typeof window.kosPerKotaChart !== 'function') return;
                    const labels = this.tampil.map((kota) => kota.nama);
                    const values = this.tampil.map((kota) => kota.jumlah);
                    window.kosPerKotaChart('chart-persebaran-kos', labels, values, {
                        onBarClick: (i) => {
                            const k = this.tampil[i];
                            if (!k) return;
                            if (k.nama === 'Kota Lainnya') { this.bukaModeSemua(); return; }
                            this.buka = k.daerah.length ? (this.buka === k.nama ? null : k.nama) : this.buka;
                        },
                    });
                },
                // Listener dokumen didaftarkan sekali saja (navigasi SPA membuat
                // init() jalan ulang; tanpa penjagaan handler menumpuk).
                __dengarTemaBeranda() {
                    if (window.__berandaTemaOn) return;
                    window.__berandaTemaOn = true;
                    document.addEventListener('ngekos:theme-changed', () => {
                        try { window.__berandaGrafikAktif && window.__berandaGrafikAktif(); } catch (e) {}
                    });
                },
                __dengarNavigasiBeranda() {
                    if (window.__berandaNavOn) return;
                    window.__berandaNavOn = true;
                    document.addEventListener('livewire:navigated', () => {
                        if (!document.getElementById('chart-persebaran-kos')) return;
                        try { window.__berandaGrafikAktif && window.__berandaGrafikAktif(); } catch (e) {}
                    });
                },
                init() {
                    this.$nextTick(() => {
                        this.renderGrafik();
                        this.$watch('semua', () => this.$nextTick(() => this.renderGrafik()));
                        this.$watch('cariKota', () => {
                            this.muat = 15;
                            this.buka = null;
                            this.$nextTick(() => this.renderGrafik());
                        });
                        this.$watch('muat', () => this.$nextTick(() => this.renderGrafik()));
                        try {
                            if (this.$store && this.$store.theme) {
                                this.$watch(() => this.$store.theme.dark, () => this.$nextTick(() => this.renderGrafik()));
                            } else {
                                window.__berandaGrafikAktif = () => this.$nextTick(() => this.renderGrafik());
                                this.__dengarTemaBeranda();
                            }
                        } catch (e) {
                            window.__berandaGrafikAktif = () => this.$nextTick(() => this.renderGrafik());
                            this.__dengarTemaBeranda();
                        }
                        window.__berandaGrafikAktif = () => this.$nextTick(() => this.renderGrafik());
                        this.__dengarNavigasiBeranda();
                    });
                },
            }" class="max-w-7xl mx-auto px-4 pb-8 sm:pb-10">
        <div class="reveal relative overflow-hidden rounded-xl border border-stone-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 sm:p-6">
            <div class="relative flex items-center gap-3 mb-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand-700 text-white">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z"/></svg>
                </span>
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-gray-100">Persebaran Kos</h2>
                    <p class="text-xs text-slate-500 dark:text-gray-400">Jumlah kos aktif per kota</p>
                </div>
            </div>
            <div class="relative">
                <!-- Pencarian (mode semua) -->
                <div x-show="semua" x-cloak class="mb-3">
                    <div class="flex items-center gap-2 bg-gray-100/80 dark:bg-gray-800/80 rounded-xl px-3 py-2 focus-within:ring-2 focus-within:ring-teal-500/20 transition-all">
                        <svg class="h-4 w-4 text-gray-400 dark:text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        <input type="text" x-model="cariKota" placeholder="Cari kota atau daerah..."
                            class="w-full bg-transparent text-xs sm:text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 border-0 focus:ring-0 focus:outline-none p-0">
                        <template x-if="cariKota">
                            <button type="button" @click="cariKota = ''" class="rounded-lg p-1 text-gray-400 hover:text-teal-600 dark:hover:text-teal-400 transition-colors" title="Bersihkan pencarian">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </template>
                    </div>
                </div>

                <div x-show="!kosong" class="relative transition-all duration-200" :style="'height:' + Math.max(tampil.length * 32, 150) + 'px'" :class="semua ? 'max-h-80 overflow-y-auto pr-1' : ''">
                    <canvas id="chart-persebaran-kos" class="cursor-pointer"></canvas>
                </div>

                <!-- Hasil pencarian kosong -->
                <div x-show="kosong" x-cloak class="py-10 text-center">
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        Kota "<span class="font-semibold text-gray-600 dark:text-gray-300" x-text="cariKota"></span>" tidak ditemukan.
                    </p>
                </div>

                <!-- Counter mode semua -->
                <p x-show="semua && !kosong" class="mt-2 text-center text-[10px] text-gray-400 dark:text-gray-500">
                    Menampilkan <span class="font-bold text-gray-600 dark:text-gray-300" x-text="Math.min(muat, hasilFilter.length).toLocaleString('id-ID')"></span> dari <span class="font-bold text-gray-600 dark:text-gray-300" x-text="hasilFilter.length.toLocaleString('id-ID')"></span> kota
                </p>

                <!-- Muat lebih banyak -->
                <template x-if="semua && sisaMuat > 0">
                    <button type="button" @click="muatLagi()"
                        class="mt-3 w-full inline-flex items-center justify-center gap-1.5 rounded-lg border border-stone-300 dark:border-gray-700 px-3 py-2 text-xs font-semibold text-brand-800 dark:text-brand-200 transition hover:border-brand-300 hover:bg-brand-50 dark:hover:bg-brand-500/10">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Muat lebih banyak (<span x-text="sisaMuat"></span>)
                    </button>
                </template>

                <template x-if="totalKota > 6">
                    <button type="button" @click="semua ? kembaliTeratas() : bukaModeSemua()"
                        class="mt-3 inline-flex items-center gap-1.5 rounded-lg border border-stone-300 dark:border-gray-700 px-3 py-2 text-xs font-semibold text-brand-800 dark:text-brand-200 transition hover:border-brand-300 hover:bg-brand-50 dark:hover:bg-brand-500/10">
                        <svg x-show="!semua" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        <svg x-show="semua" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
                        <span x-text="semua ? 'Tampilkan teratas' : 'Lihat semua kota (' + totalKota + ')'"></span>
                    </button>
                </template>

                <template x-if="kotakBuka()">
                    <div class="mt-4 rounded-lg border border-brand-100 dark:border-brand-500/20 bg-brand-50/60 dark:bg-brand-500/5">
                        <div class="flex items-center justify-between gap-2 border-b border-brand-100 dark:border-brand-500/20 px-3 py-2.5">
                            <p class="text-xs font-bold text-brand-800 dark:text-brand-200">
                                <span x-text="buka"></span> — pecahan daerah
                            </p>
                            <button type="button" @click="buka = null" class="rounded-md p-1 text-brand-700 transition-colors hover:bg-brand-100 dark:hover:bg-brand-500/10" title="Tutup">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <div class="divide-y divide-brand-100/70 dark:divide-brand-500/10 px-3 py-1">
                            <template x-for="d in kotakBuka().daerah" :key="d.nama">
                                <div class="flex items-center justify-between gap-3 py-2">
                                    <span class="truncate text-xs font-medium text-slate-700 dark:text-gray-200" x-text="d.nama"></span>
                                    <span class="shrink-0 text-xs font-bold text-brand-800 dark:text-brand-200" x-text="d.jumlah.toLocaleString('id-ID') + ' kos'"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <p x-show="adaDaerahTampil && !buka" class="mt-4 text-center text-[10px] text-gray-400 dark:text-gray-500">
                    Klik bar kota untuk melihat pecahan daerahnya.
                </p>
            </div>
        </div>
    </section>
    @endif

    <!-- Peta Semua Kos -->
    @if ($markers->isNotEmpty())
    <section class="max-w-7xl mx-auto px-4 pb-8 sm:pb-10" x-data="{ tampilkanPeta: false }">
        <div class="reveal relative overflow-hidden rounded-xl border border-stone-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 sm:p-6">
            <div class="relative flex items-center justify-between gap-3 mb-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand-700 text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" /></svg>
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-gray-100">Peta Kos</h2>
                        <p class="text-xs text-slate-500 dark:text-gray-400">{{ $markers->count() }} titik lokasi kos aktif</p>
                    </div>
                </div>
                <button @click="tampilkanPeta = !tampilkanPeta; $nextTick(() => { if (tampilkanPeta) initPetaBeranda(); else if (typeof resetPetaBeranda === 'function') resetPetaBeranda(); })"
                    class="shrink-0 inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold transition {{ $markers->count() ? 'bg-brand-700 text-white hover:bg-brand-800' : 'bg-stone-100 text-slate-400' }}">
                    <svg x-show="!tampilkanPeta" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                    <svg x-show="tampilkanPeta" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M9 21V9h6v12" /></svg>
                    <span x-text="tampilkanPeta ? 'Tutup Peta' : 'Lihat Peta'"></span>
                </button>
            </div>
            <div x-show="tampilkanPeta" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="relative">
                <div id="peta-kos-beranda" class="h-80 sm:h-96 w-full rounded-xl bg-gray-100 dark:bg-gray-800"></div>
            </div>
        </div>
    </section>
    @endif

    <!-- CTA penutup + Tentang Ngekos.in -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 pt-8 sm:pt-10 pb-2">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 p-6 sm:p-12 text-center shadow-2xl">
            <div class="absolute -left-16 -top-16 h-56 w-56 rounded-full bg-emerald-400/20 blur-3xl"></div>
            <div class="absolute -right-16 -bottom-16 h-56 w-56 rounded-full bg-amber-400/20 blur-3xl"></div>
            <div class="relative max-w-2xl mx-auto">
                <p class="inline-flex items-center gap-1.5 rounded-full bg-white/10 border border-white/15 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-amber-300">Gratis • Tanpa perantara • Bisa refund</p>
                <h2 class="mt-3 text-2xl sm:text-4xl font-extrabold tracking-tight text-white leading-tight">Siap Pindah ke<br>Kos yang Lebih Nyaman?</h2>
                <p class="mt-3 text-sm sm:text-base text-brand-100">{{ number_format($totalKamar, 0, ',', '.') }} kamar tersedia menunggumu di {{ $daftarKota->count() }}+ kota. Daftar gratis, chat pemilik langsung.</p>
                <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="{{ route('kos.index') }}" wire:navigate class="btn-accent w-full sm:w-auto !px-8 !py-3.5 !text-base">Cari Kos Sekarang
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                    </a>
                    <a href="{{ route('register') }}" wire:navigate class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/10 backdrop-blur px-8 py-3.5 text-base font-bold text-white hover:bg-white/20 transition">Saya Pemilik Kos</a>
                </div>
                <p class="mt-4 text-xs text-brand-100">Sudah dipercaya ribuan anak kos & ratusan pemilik di Indonesia</p>
            </div>
        </div>
    </section>

    <section class="bg-white dark:bg-gray-800 border-t border-stone-200 dark:border-gray-700 mt-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10 grid lg:grid-cols-[1fr_320px] gap-8">
            <div>
                <h2 class="text-lg font-extrabold tracking-tight text-slate-900 dark:text-gray-100">{{ \App\Models\Pengaturan::namaSitus() }} — Aplikasi Anak Kos No. 1 di Indonesia</h2>
                <p class="mt-3 text-xs sm:text-sm text-slate-500 dark:text-gray-400 leading-relaxed">
                    {{ \App\Models\Pengaturan::namaSitus() }} memanfaatkan teknologi untuk berkembang dari aplikasi cari kos menjadi aplikasi yang memudahkan
                    calon anak kos untuk booking properti kos dan melakukan pembayaran kos. Saat ini kami memiliki {{ $totalKamar }} kamar tersedia
                    yang tersebar di berbagai kota di Indonesia. Data ketersediaan akurat, fasilitas terperinci, foto asli, dan harga transparan.
                </p>
                <p class="mt-2 text-xs sm:text-sm text-slate-500 dark:text-gray-400 leading-relaxed">
                    Fitur unggulan: pencarian berdasarkan lokasi, harga, dan fasilitas; chat langsung dengan pemilik; tagihan bulanan otomatis;
                    serta dashboard pemilik untuk mengelola kamar, penyewaan, dan pembayaran dalam satu aplikasi.
                </p>
            </div>
            <div class="rounded-2xl bg-paper dark:bg-gray-900 border border-stone-200 dark:border-gray-700 p-5 h-fit">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Mulai cepat</p>
                <div class="mt-3 space-y-2">
                    <a href="{{ route('kos.index') }}" wire:navigate class="flex items-center justify-between rounded-xl bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 px-4 py-3 text-sm font-bold text-slate-800 dark:text-gray-100 hover:border-brand-400 hover:shadow transition">Cari kos <span aria-hidden="true">→</span></a>
                    <a href="{{ route('register') }}" wire:navigate class="flex items-center justify-between rounded-xl bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 px-4 py-3 text-sm font-bold text-slate-800 dark:text-gray-100 hover:border-brand-400 hover:shadow transition">Daftarkan kos <span aria-hidden="true">→</span></a>
                    <a href="{{ route('bantuan') }}" wire:navigate class="flex items-center justify-between rounded-xl bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 px-4 py-3 text-sm font-bold text-slate-800 dark:text-gray-100 hover:border-brand-400 hover:shadow transition">Butuh bantuan? <span aria-hidden="true">→</span></a>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            window._mapBeranda = window._mapBeranda || null;
            window._boundsBeranda = window._boundsBeranda || null;
            window.dataPetaBeranda = @js($markers);

            async function berandaFallback() {
                const el = document.getElementById('peta-kos-beranda');
                const data = window.dataPetaBeranda || [];
                if (!el || !data.length) return;
                if (typeof window.buatPetaDaftarOsm === 'function') {
                    try {
                        await window.buatPetaDaftarOsm(el, data);
                        return;
                    } catch (e) {}
                }
                if (typeof window.pasangOsmEmbed === 'function') {
                    const sum = dataPetaBeranda.reduce((a, m) => ({ lat: a.lat + m.lat, lng: a.lng + m.lng }), { lat: 0, lng: 0 });
                    window.pasangOsmEmbed(el, sum.lat / dataPetaBeranda.length, sum.lng / dataPetaBeranda.length, 10, dataPetaBeranda);
                } else if (typeof window.pasangGoogleEmbed === 'function') {
                    const sum = dataPetaBeranda.reduce((a, m) => ({ lat: a.lat + m.lat, lng: a.lng + m.lng }), { lat: 0, lng: 0 });
                    window.pasangGoogleEmbed(el, sum.lat / dataPetaBeranda.length, sum.lng / dataPetaBeranda.length, dataPetaBeranda.length <= 1 ? 14 : 10);
                } else {
                    el.innerHTML = '<div class="h-full w-full flex items-center justify-center p-4 text-center text-xs text-gray-400">Peta tidak dapat dimuat saat ini.</div>';
                }
            }

            function berandaBuatPeta(percobaan) {
                const el = document.getElementById('peta-kos-beranda');
                const data = window.dataPetaBeranda || [];
                if (!el || !data.length) return;
                if (el.offsetWidth === 0) {
                    if ((percobaan || 0) < 20) setTimeout(() => berandaBuatPeta((percobaan || 0) + 1), 120);
                    else { try { const r = berandaFallback(); if (r && typeof r.catch === 'function') r.catch(() => {}); } catch (e) {} }
                    return;
                }
                if (el._petaLeaflet) {
                    try { el._petaLeaflet.invalidateSize(); } catch (e) {}
                    return;
                }
                if (typeof google === 'undefined' || !google.maps) {
                    try { const r = berandaFallback(); if (r && typeof r.catch === 'function') r.catch(() => {}); } catch (e) {}
                    return;
                }
                if (window._mapBeranda) {
                    try {
                        google.maps.event.trigger(window._mapBeranda, 'resize');
                        if (window._boundsBeranda) window._mapBeranda.fitBounds(window._boundsBeranda);
                    } catch (e) {}
                    return;
                }

                const bounds = new google.maps.LatLngBounds();
                data.forEach((m) => bounds.extend({ lat: m.lat, lng: m.lng }));
                window._boundsBeranda = bounds;

                window._mapBeranda = new google.maps.Map(el, { mapTypeId: 'roadmap', disableDefaultUI: false });
                if (data.length === 1) {
                    window._mapBeranda.setCenter(bounds.getCenter());
                    window._mapBeranda.setZoom(14);
                } else {
                    window._mapBeranda.fitBounds(bounds);
                }

                const markers = data.map((m) => {
                    const pemuat = new google.maps.Marker({ position: { lat: m.lat, lng: m.lng }, map: window._mapBeranda, title: m.nama });
                    const info = new google.maps.InfoWindow();
                    pemuat.addListener('click', () => {
                        const isi = '<strong>' + String(m.nama || '').replace(/</g, '&lt;') + '</strong><br>' +
                            (m.alamat ? String(m.alamat).replace(/</g, '&lt;') + ', ' : '') +
                            (m.kota ? String(m.kota).replace(/</g, '&lt;') : '') +
                            (m.id ? '<br><a href="/kos/' + m.id + '">Lihat detail</a>' : '');
                        info.setContent(isi);
                        info.open({ map: window._mapBeranda, anchor: pemuat });
                    });
                    return pemuat;
                });
                if (typeof window.pasangCluster === 'function') { try { window.pasangCluster(markers, window._mapBeranda); } catch (e) {} }
            }

            window.initPetaBeranda = function () {
                const el = document.getElementById('peta-kos-beranda');
                if (!el) return;
                requestAnimationFrame(() => {
                    berandaBuatPeta();
                    if (typeof window.loadNgekosMaps === 'function') window.loadNgekosMaps(berandaBuatPeta);
                });
            };

            window.resetPetaBeranda = function () {
                const el = document.getElementById('peta-kos-beranda');
                if (el && typeof window.bersihkanWadahLeaflet === 'function') {
                    try { window.bersihkanWadahLeaflet(el); } catch (e) { el.innerHTML = ''; }
                } else if (el) {
                    el.innerHTML = '';
                }
                window._mapBeranda = null;
                window._boundsBeranda = null;
            };

            (() => {
                // Didaftarkan sekali saja agar tidak menumpuk tiap navigasi SPA.
                if (!window.__berandaPetaNavOn) {
                    window.__berandaPetaNavOn = true;
                    document.addEventListener('livewire:navigated', () => {
                        try {
                            const wadah = document.getElementById('peta-kos-beranda');
                            if (wadah && typeof window.bersihkanWadahLeaflet === 'function') window.bersihkanWadahLeaflet(wadah);
                        } catch (e) {}
                        window._mapBeranda = null;
                        window._boundsBeranda = null;
                    });
                }
                if (typeof window.loadNgekosMaps === 'function') { try { window.loadNgekosMaps(window.berandaBuatPeta || berandaBuatPeta); } catch (e) {} }
                window.berandaBuatPeta = berandaBuatPeta;
                window.berandaFallback = berandaFallback;
            })();
        </script>
    @endpush
</div>
