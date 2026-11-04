<?php

use App\Enums\BarangKategori;
use App\Enums\BarangKondisi;
use App\Enums\BarangStatus;
use App\Models\BarangKos;
use App\Models\Pengaturan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.publik')] class extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $cari = '';

    #[Url(history: true)]
    public string $kategori = '';

    #[Url(history: true)]
    public string $kondisi = '';

    #[Url(history: true)]
    public string $kota = '';

    #[Url(history: true)]
    public string $peran = '';

    #[Url(history: true)]
    public string $status = 'tersedia';

    #[Url(history: true)]
    public $hargaMax = null;

    #[Url(history: true)]
    public string $urutkan = 'terbaru';

    public function updatedCari(): void
    {
        $this->resetPage();
    }

    public function updatedKategori(): void
    {
        $this->resetPage();
    }

    public function updatedKondisi(): void
    {
        $this->resetPage();
    }

    public function updatedKota(): void
    {
        $this->resetPage();
    }

    public function updatedPeran(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedHargaMax($val): void
    {
        $this->hargaMax = ($val === '' || $val === null) ? null : (int) $val;
        $this->resetPage();
    }

    public function updatedUrutkan(): void
    {
        $this->resetPage();
    }

    public function resetSemuaFilter(): void
    {
        $this->reset(['cari', 'kategori', 'kondisi', 'kota', 'peran', 'hargaMax', 'urutkan']);
        $this->status = 'tersedia';
        $this->resetPage();
    }

    public function with(): array
    {
        $barangUnggulanIds = Pengaturan::landingBarangIds();

        $query = BarangKos::query()
            ->with(['user', 'properti:id,nama,kota'])
            ->belumKedaluwarsa()
            ->tampilDiKatalog();

        $barangUnggulanQuery = BarangKos::query()
            ->whereIn('id', $barangUnggulanIds)
            ->tersedia()
            ->tampilDiKatalog()
            ->with('user:id,nama');

        $barangUnggulan = $barangUnggulanQuery
            ->get()
            ->sortBy(fn (BarangKos $barang) => array_search(
                $barang->id,
                $barangUnggulanIds,
                true
            ))
            ->values();

        if ($this->cari) {
            $keyword = trim($this->cari);
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%")
                    ->orWhere('deskripsi', 'like', "%{$keyword}%")
                    ->orWhere('kota', 'like', "%{$keyword}%")
                    ->orWhere('lokasi_detail', 'like', "%{$keyword}%");
            });
        }

        if ($this->kategori) {
            $query->where('kategori', $this->kategori);
        }

        if ($this->kondisi) {
            $query->where('kondisi', $this->kondisi);
        }

        if (trim($this->kota) !== '') {
            $keywordKota = trim($this->kota);
            $query->where('kota', 'like', "%{$keywordKota}%");
        }

        if ($this->status && $this->status !== 'semua') {
            $query->where('status', $this->status);
        }

        if ($this->peran) {
            $query->whereHas('user', function ($q) {
                $q->where('role', $this->peran);
            });
        }

        if ($this->hargaMax) {
            $query->where('harga', '<=', (int) $this->hargaMax);
        }

        match ($this->urutkan) {
            'termurah' => $query->orderBy('harga', 'asc'),
            'termahal' => $query->orderBy('harga', 'desc'),
            'terpopuler' => $query->orderBy('dilihat', 'desc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $daftarKotaQuery = BarangKos::query()
            ->select('kota')
            ->tersedia()
            ->tampilDiKatalog()
            ->whereNotNull('kota')
            ->where('kota', '!=', '')
            ->distinct();

        $daftarKota = $daftarKotaQuery->pluck('kota')->sort()->values();

        $daftarKategori = BarangKategori::cases();

        $totalTersediaQuery = BarangKos::query()->tersedia()->tampilDiKatalog();
        $totalTersedia = $totalTersediaQuery->count();

        $user = auth()->user();
        $jumlahBelumTerjualSaya = $user
            ? BarangKos::jumlahBelumTerjual((int) $user->id)
            : 0;
        $bisaPasangBaru = ! $user
            || $jumlahBelumTerjualSaya < BarangKos::BATAS_BARANG_PER_AKUN;

        return [
            'barangs' => $query->paginate(12),
            'barangUnggulan' => $barangUnggulan,
            'promoInterval' => Pengaturan::promoIntervalMs(),
            'daftarKota' => $daftarKota,
            'daftarKategori' => $daftarKategori,
            'totalTersedia' => $totalTersedia,
            'jumlahBelumTerjualSaya' => $jumlahBelumTerjualSaya,
            'bisaPasangBaru' => $bisaPasangBaru,
        ];
    }
}; ?>

<div class="py-6 sm:py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Hero Pasar Kos --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-brand-900 via-brand-800 to-emerald-900 p-5 sm:p-10 text-white shadow-xl mb-8">
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-amber-400/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-10 -top-10 w-64 h-64 bg-brand-500/20 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur border border-white/20 text-xs font-semibold text-amber-300 mb-3">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Pasar Barang Kos #LebihHemat
                    </div>
                    <h1 class="text-[1.6rem] leading-tight sm:text-4xl font-extrabold tracking-tight text-white">
                        Jual & Beli Perlengkapan Kos Antara Anak Kos & Pemilik
                    </h1>
                    <p class="mt-2 text-sm sm:text-base text-brand-100 leading-relaxed">
                        Mau pindah kos atau butuh perabot & elektronik murah? Jual barang tak terpakai atau temukan barang bekas layak pakai dengan harga anak kos.
                    </p>
                    <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs sm:text-sm text-brand-200">
                        <span class="flex items-center gap-1.5">
                            <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <strong class="text-white">{{ $totalTersedia }}</strong> barang kos siap dipinang
                        </span>
                        <span class="hidden sm:inline text-brand-400">•</span>
                        <span>Transaksi Langsung via WhatsApp</span>
                        <span class="hidden sm:inline text-brand-400">•</span>
                        <span>Bebas Biaya Iklan</span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row md:flex-col gap-3 shrink-0">
                    @if ($bisaPasangBaru)
                        <a href="{{ route('barang.pasang') }}" wire:navigate
                           class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-400 hover:bg-amber-300 px-5 py-3 text-sm font-bold text-brand-950 transition shadow-lg hover:shadow-amber-400/20">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Jual Barang Saya
                        </a>
                    @else
                        <span class="inline-flex items-center justify-center gap-2 rounded-xl bg-white/10 px-5 py-3 text-sm font-bold text-amber-200 ring-1 ring-white/20">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Batas 2 barang tercapai
                        </span>
                    @endif
                    @auth
                        <a href="{{ route('barang.kelola') }}" wire:navigate
                           class="inline-flex items-center justify-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 px-5 py-2.5 text-sm font-semibold text-white transition backdrop-blur">
                            <svg class="h-5 w-5 text-amber-300" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                            Kelola Barang Jualan
                        </a>
                    @endauth
                </div>
            </div>
        </div>

        {{-- Banner iklan promo partner: bergeser otomatis & bisa digeser manual --}}
        <div class="mb-8">
            <x-promo-ads wire:ignore />
        </div>

        @if ($barangUnggulan->isNotEmpty())
            <x-barang-featured-carousel :barangs="$barangUnggulan" :interval="$promoInterval" />
        @endif

        {{-- Filter & Pencarian Bar --}}
        <div class="bg-white dark:bg-gray-900 border border-stone-200 dark:border-gray-800 rounded-2xl p-4 sm:p-5 shadow-sm mb-6 space-y-4">
            {{-- Baris 1: Search & Filter Utama --}}
            <div class="grid grid-cols-2 lg:grid-cols-12 gap-3">
                {{-- Input Cari --}}
                <div class="col-span-2 lg:col-span-5 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="search"
                           wire:model.live.debounce.300ms="cari"
                           placeholder="Cari barang (cth: kulkas mini, kasur, kipas, meja)..."
                           class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-stone-300 dark:border-gray-700 bg-stone-50 dark:bg-gray-800 text-slate-800 dark:text-gray-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                </div>

                {{-- Filter Kota (ketikan, bukan dropdown) --}}
                <div class="col-span-2 lg:col-span-3 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <input type="text"
                           wire:model.live.debounce.300ms="kota"
                           placeholder="Ketik kota (cth: bandung, malang)..."
                           aria-label="Filter kota"
                           class="w-full pl-10 pr-9 py-2.5 text-sm rounded-xl border border-stone-300 dark:border-gray-700 bg-stone-50 dark:bg-gray-800 text-slate-800 dark:text-gray-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                    @if (trim($kota) !== '')
                        <button type="button"
                                wire:click="$set('kota', '')"
                                title="Hapus filter kota"
                                aria-label="Hapus filter kota"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-gray-200 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>

                {{-- Filter Penjual --}}
                <div class="col-span-1 lg:col-span-2">
                    <select wire:model.live="peran"
                            class="w-full py-2.5 px-3 text-sm rounded-xl border border-stone-300 dark:border-gray-700 bg-stone-50 dark:bg-gray-800 text-slate-800 dark:text-gray-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                        <option value="">Semua Penjual</option>
                        <option value="anak_kos">Anak Kos</option>
                        <option value="pemilik">Pemilik Kos</option>
                    </select>
                </div>

                {{-- Urutan --}}
                <div class="col-span-1 lg:col-span-2">
                    <select wire:model.live="urutkan"
                            class="w-full py-2.5 px-3 text-sm rounded-xl border border-stone-300 dark:border-gray-700 bg-stone-50 dark:bg-gray-800 text-slate-800 dark:text-gray-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                        <option value="terbaru">Terbaru</option>
                        <option value="termurah">Harga Termurah</option>
                        <option value="termahal">Harga Termahal</option>
                        <option value="terpopuler">Paling Banyak Dilihat</option>
                    </select>
                </div>
            </div>

            {{-- Saran Kota: ikut terfilter sesuai ketikan --}}
            @php
                $kataKota = trim($kota);
                $saranKota = $kataKota === ''
                    ? collect()
                    : $daftarKota
                        ->filter(fn ($k) => stripos($k, $kataKota) !== false)
                        ->take(8);
            @endphp
            @if ($kataKota !== '')
                <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-hide text-xs">
                    <span class="text-slate-500 dark:text-gray-400 font-medium shrink-0">Kota cocok:</span>
                    @forelse ($saranKota as $k)
                        <button type="button"
                                wire:click="$set('kota', @js($k))"
                                class="px-3 py-1.5 rounded-full font-medium transition shrink-0 {{ strcasecmp($kota, $k) === 0 ? 'bg-brand-700 text-white shadow-sm' : 'bg-stone-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300 hover:bg-stone-200 dark:hover:bg-gray-700' }}">
                            {{ $k }}
                        </button>
                    @empty
                        <span class="text-slate-400 dark:text-gray-500 shrink-0">
                            Tidak ada kota yang cocok dengan "{{ $kota }}".
                        </span>
                    @endforelse
                    <button type="button"
                            wire:click="$set('kota', '')"
                            class="px-3 py-1.5 rounded-full font-medium shrink-0 text-slate-500 dark:text-gray-400 hover:text-slate-700 dark:hover:text-gray-200 underline underline-offset-2">
                        Hapus
                    </button>
                </div>
            @endif

            {{-- Baris 2: Chips Kategori Cepat --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-hide text-xs">
                <button type="button"
                        wire:click="$set('kategori', '')"
                        class="px-3.5 py-1.5 rounded-full font-medium transition shrink-0 {{ $kategori === '' ? 'bg-brand-700 text-white shadow-sm' : 'bg-stone-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300 hover:bg-stone-200 dark:hover:bg-gray-700' }}">
                    Semua Kategori
                </button>
                @foreach ($daftarKategori as $kat)
                    <button type="button"
                            wire:click="$set('kategori', '{{ $kat->value }}')"
                            class="px-3.5 py-1.5 rounded-full font-medium transition shrink-0 {{ $kategori === $kat->value ? 'bg-brand-700 text-white shadow-sm' : 'bg-stone-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300 hover:bg-stone-200 dark:hover:bg-gray-700' }}">
                        {{ $kat->label() }}
                    </button>
                @endforeach
            </div>

            {{-- Baris 3: Filter Lanjutan (Kondisi & Status) --}}
            <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-stone-100 dark:border-gray-800 text-xs">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-slate-500 dark:text-gray-400 font-medium">Kondisi:</span>
                    <button type="button"
                            wire:click="$set('kondisi', '')"
                            class="px-2.5 py-1 rounded-lg border {{ $kondisi === '' ? 'border-brand-600 text-brand-700 dark:text-brand-300 bg-brand-50 dark:bg-brand-900/30 font-semibold' : 'border-stone-200 dark:border-gray-700 text-slate-600 dark:text-gray-400' }}">
                        Semua
                    </button>
                    @foreach (\App\Enums\BarangKondisi::cases() as $kond)
                        <button type="button"
                                wire:click="$set('kondisi', '{{ $kond->value }}')"
                                class="px-2.5 py-1 rounded-lg border {{ $kondisi === $kond->value ? 'border-brand-600 text-brand-700 dark:text-brand-300 bg-brand-50 dark:bg-brand-900/30 font-semibold' : 'border-stone-200 dark:border-gray-700 text-slate-600 dark:text-gray-400' }}">
                            {{ $kond->label() }}
                        </button>
                    @endforeach
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1.5">
                        <label for="filter-status" class="text-slate-500 dark:text-gray-400 font-medium">Status:</label>
                        <select id="filter-status" wire:model.live="status" class="py-1 px-2 text-xs rounded-lg border border-stone-200 dark:border-gray-700 bg-stone-50 dark:bg-gray-800 text-slate-700 dark:text-gray-300">
                            <option value="tersedia">Hanya Tersedia</option>
                            <option value="semua">Semua (Termasuk Terjual)</option>
                            <option value="terjual">Sudah Terjual</option>
                        </select>
                    </div>

                    @if ($cari || $kategori || $kondisi || $kota || $peran || $hargaMax || $status !== 'tersedia' || $urutkan !== 'terbaru')
                        <button type="button" wire:click="resetSemuaFilter"
                                class="text-red-600 dark:text-red-400 hover:underline font-semibold flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Reset Filter
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Grid Daftar Barang --}}
        @if ($barangs->count() > 0)
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2.5 sm:gap-5">
                @foreach ($barangs as $item)
                    @php
                        $isTerjual = $item->status === \App\Enums\BarangStatus::Terjual;
                        $penjualRole = $item->user?->roleValue() ?? 'anak_kos';
                    @endphp

                    {{-- Kartu Barang Asli --}}<div data-barang-id="{{ $item->id }}" class="group relative bg-white dark:bg-gray-900 border border-stone-200 dark:border-gray-800 rounded-xl sm:rounded-2xl overflow-hidden shadow-sm hover:shadow-md hover:-translate-y-1 transition duration-200 flex flex-col {{ $isTerjual ? 'opacity-75' : '' }}">
                        {{-- Foto & Badges --}}
                        <div class="relative aspect-square w-full bg-stone-100 dark:bg-gray-800 overflow-hidden">
                            <img src="{{ $item->fotoUrl() }}"
                                 alt="{{ $item->nama }}"
                                 loading="lazy"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-300">

                            {{-- Badge Status Terjual --}}
                            @if ($isTerjual)
                                <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px] flex items-center justify-center">
                                    <span class="px-3 py-1.5 rounded-full bg-red-600 text-white font-extrabold text-xs uppercase tracking-wider shadow-lg">
                                        Sudah Terjual
                                    </span>
                                </div>
                            @endif

                            {{-- Badges atas --}}
                            <div class="absolute top-2 inset-x-2 sm:top-2.5 sm:inset-x-2.5 flex items-center justify-between gap-1 pointer-events-none">
                                <div class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 rounded-md text-[10px] sm:text-[11px] font-bold uppercase tracking-wide bg-brand-950/80 backdrop-blur text-white shadow-sm">
                                        {{ $item->kondisi?->label() ?? 'Bekas' }}
                                    </span>

                                    @if ($item->bisa_nego)
                                        <span class="px-1.5 sm:px-2 py-0.5 rounded-md text-[10px] sm:text-[11px] font-bold bg-amber-400 text-brand-950 shadow-sm">
                                            Nego
                                        </span>
                                    @endif

                                </div>
                            </div>

                            {{-- Badge Penjual (Anak Kos / Pemilik) --}}
                            <div class="absolute bottom-2 left-2 sm:bottom-2.5 sm:left-2.5 pointer-events-none">
                                @if ($penjualRole === 'pemilik')
                                    <span class="inline-flex items-center gap-1 px-1.5 sm:px-2 py-0.5 rounded-md text-[9px] sm:text-[10px] font-bold uppercase bg-amber-500/90 text-white backdrop-blur shadow-sm">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                        Pemilik<span class="hidden sm:inline"> Kos</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-1.5 sm:px-2 py-0.5 rounded-md text-[9px] sm:text-[10px] font-bold uppercase bg-brand-700/90 text-white backdrop-blur shadow-sm">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                        Anak<span class="hidden sm:inline"> Kos</span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Konten Kartu --}}
                        <div class="p-2.5 sm:p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <p class="text-[10px] sm:text-[11px] font-semibold text-brand-700 dark:text-brand-300 uppercase tracking-wider mb-0.5 sm:mb-1">
                                    {{ $item->kategori?->label() ?? 'Lainnya' }}
                                </p>

                                <a href="{{ route('barang.detail', $item) }}" wire:navigate class="block">
                                    <h2 class="text-xs sm:text-sm font-bold leading-snug text-slate-900 dark:text-gray-100 line-clamp-2 hover:text-brand-700 dark:hover:text-brand-300 transition" title="{{ $item->nama }}">
                                        {{ $item->nama }}
                                    </h2>
                                </a>

                                <div class="mt-1.5 sm:mt-2 text-sm sm:text-base font-extrabold text-slate-900 dark:text-white">
                                    {{ $item->formattedHarga() }}
                                </div>

                                {{-- Lokasi --}}
                                <div class="mt-1.5 sm:mt-2.5 flex items-center gap-1 sm:gap-1.5 text-[11px] sm:text-xs text-slate-500 dark:text-gray-400">
                                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="truncate">
                                        {{ $item->kota }}@if ($item->lokasi_detail)<span class="hidden sm:inline"> • {{ $item->lokasi_detail }}</span>@endif
                                    </span>
                                </div>
                            </div>

                            {{-- Footer Penjual & Tombol Aksi --}}
                            <div class="mt-3 sm:mt-4 pt-2.5 sm:pt-3 border-t border-stone-100 dark:border-gray-800 flex items-center justify-between gap-1.5 sm:gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="hidden sm:flex h-6 w-6 rounded-full bg-brand-100 dark:bg-brand-900/60 text-brand-700 dark:text-brand-300 items-center justify-center font-bold text-[10px] shrink-0">
                                        {{ strtoupper(substr($item->user?->nama ?? 'U', 0, 1)) }}
                                    </div>
                                    @if ($item->user)
                                        <a href="{{ route('pasar.toko', $item->user) }}" wire:navigate
                                           class="min-w-0 truncate text-[11px] sm:text-xs text-slate-600 dark:text-gray-300 font-medium hover:text-brand-700 dark:hover:text-brand-300 hover:underline">
                                            {{ $item->user->nama }}
                                        </a>
                                    @else
                                        <span class="min-w-0 truncate text-[11px] sm:text-xs text-slate-600 dark:text-gray-300 font-medium">Penjual</span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-1 sm:gap-1.5 shrink-0">
                                    @if (! $isTerjual)
                                        <a href="{{ $item->waLink() }}" target="_blank" rel="noopener noreferrer"
                                           title="Chat via WhatsApp"
                                           class="inline-flex items-center justify-center h-7 w-7 sm:h-8 sm:w-8 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white transition shadow-sm">
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 fill-current" viewBox="0 0 24 24">
                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.746.953 3.71 1.456 5.711 1.457h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                            </svg>
                                        </a>
                                    @endif

                                    <a href="{{ route('barang.detail', $item) }}" wire:navigate
                                       class="px-2 py-1 sm:px-2.5 sm:py-1.5 rounded-lg bg-stone-100 hover:bg-stone-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-200 font-semibold text-[11px] sm:text-xs transition">
                                        Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($loop->iteration === 4 && $barangs->count() > 4)
                        {{-- Sisipan iklan promo partner di tengah katalog --}}
                        <div class="col-span-full my-2 sm:my-3">
                            <x-promo-ads wire:ignore />
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="mt-8">
                {{ $barangs->links() }}
            </div>
        @else
            <div class="bg-white dark:bg-gray-900 border border-stone-200 dark:border-gray-800 rounded-3xl p-10 text-center shadow-sm">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-stone-100 dark:bg-gray-800 text-stone-400 mb-4">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tidak Ada Barang yang Cocok</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-gray-400 max-w-md mx-auto">
                    Coba sesuaikan kata kunci pencarian, ubah pilihan kategori atau bersihkan filter yang aktif.
                </p>
                <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
                    <button type="button" wire:click="resetSemuaFilter"
                            class="px-4 py-2 rounded-xl bg-stone-100 dark:bg-gray-800 hover:bg-stone-200 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-200 font-semibold text-xs transition">
                        Reset Filter
                    </button>
                    <a href="{{ route('barang.pasang') }}" wire:navigate
                       class="px-4 py-2 rounded-xl bg-brand-700 hover:bg-brand-800 text-white font-semibold text-xs transition">
                        Jual Barang Pertama di Sini
                    </a>
                </div>
            </div>
        @endif

    </div>
</div>
