<?php

use App\Models\Kamar;
use App\Models\Properti;
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
            ->withCount(['kamars as kamar_tersedia' => fn ($q) => $q->where('status', 'tersedia')])
            ->withMin(['kamars as harga_termurah' => fn ($q) => $q->where('status', 'tersedia')], 'harga_sewa_bulanan');

        if ($this->cari) {
            $query->where(fn ($q) => $q
                ->where('nama', 'like', "%{$this->cari}%")
                ->orWhere('kota', 'like', "%{$this->cari}%")
                ->orWhere('alamat', 'like', "%{$this->cari}%"));
        }

        return [
            'totalProperti' => Properti::where('status', 'aktif')->count(),
            'totalKamar' => Kamar::where('status', 'tersedia')->count(),
            'propertiList' => $query
                ->orderByDesc('kamar_tersedia')
                ->take(6)
                ->get(),
            'daftarKota' => Properti::where('status', 'aktif')
                ->whereNotNull('kota')
                ->distinct()
                ->orderBy('kota')
                ->pluck('kota'),
        ];
    }
}; ?>

<div>
    <!-- Hero Section - Mobile First -->
    <section class="relative bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-600 overflow-hidden">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg%20width%3D%2260%22%20height%3D%2260%22%20viewBox%3D%220%200%2060%2060%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cg%20fill%3D%22none%22%20fill-rule%3D%22evenodd%22%3E%3Cg%20fill%3D%22%23ffffff%22%20fill-opacity%3D%220.05%22%3E%3Cpath%20d%3D%22M36%2034v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6%2034v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6%204V0H4v4H0v2h4v4h2V6h4V4H6z%22%2F%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E')] opacity-30"></div>

        <div class="relative max-w-7xl mx-auto px-4 py-10 sm:py-16">
            <div class="text-center max-w-2xl mx-auto">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 ring-1 ring-white/25 px-3 py-1 text-xs font-semibold text-white">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                    {{ $totalKamar }} kamar tersedia saat ini
                </span>
                <h1 class="mt-4 text-2xl sm:text-4xl lg:text-5xl font-extrabold text-white leading-tight">
                    Cari Kos<br class="sm:hidden"> <span class="text-teal-200">Gak Pake Ribet</span>
                </h1>
                <p class="mt-3 text-sm sm:text-base text-teal-100 leading-relaxed">
                    Temukan kamar kos impianmu, tanya pemilik langsung lewat chat, dan kelola semua dalam satu aplikasi.
                </p>
            </div>

            <!-- Search Bar - Prominent like Mamikos -->
            <div class="mt-6 max-w-2xl mx-auto">
                <form action="{{ route('kos.index') }}" method="GET" wire:navigate
                      class="bg-white rounded-2xl shadow-xl shadow-teal-950/20 p-2 flex items-center gap-2">
                    <div class="flex-1 flex items-center gap-2 px-3">
                        <svg class="h-5 w-5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        <input type="text" name="cari" value="{{ $cari }}" placeholder="Ketik nama kos, kota, atau lokasi..."
                            class="w-full border-0 bg-transparent text-sm text-gray-900 placeholder-gray-400 focus:ring-0 focus:outline-none py-2.5">
                    </div>
                    <button type="submit"
                            class="shrink-0 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-2.5 text-sm font-bold text-white hover:from-emerald-500 hover:to-teal-500 transition shadow-sm">
                        Cari Kos
                    </button>
                </form>

                <!-- Quick filter chips -->
                <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                    <a href="{{ route('kos.index') }}?cari=" wire:navigate
                       class="inline-flex items-center gap-1.5 rounded-full bg-white/20 ring-1 ring-white/25 px-3 py-1.5 text-xs font-medium text-white hover:bg-white/30 transition backdrop-blur">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                        Semua Kos
                    </a>
                    @foreach ($daftarKota->take(4) as $kota)
                        <a href="{{ route('kos.index') }}?kota={{ $kota }}" wire:navigate
                           class="inline-flex items-center gap-1.5 rounded-full bg-white/20 ring-1 ring-white/25 px-3 py-1.5 text-xs font-medium text-white hover:bg-white/30 transition backdrop-blur">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                            {{ $kota }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Banner / Promo Section -->
    <section class="max-w-7xl mx-auto px-4 -mt-5 relative z-10">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <!-- Banner 1 -->
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl p-4 sm:p-5 text-white relative overflow-hidden">
                <div class="absolute -top-6 -right-6 h-24 w-24 rounded-full bg-white/10"></div>
                <div class="absolute bottom-0 right-4 h-16 w-16 rounded-full bg-white/10"></div>
                <div class="relative">
                    <span class="inline-block rounded-full bg-white/20 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide">Promo</span>
                    <h3 class="mt-2 text-sm sm:text-base font-bold">Daftar Gratis!</h3>
                    <p class="mt-1 text-xs text-blue-100">Buat akun dan langsung cari kos impianmu tanpa biaya apapun.</p>
                    <a href="{{ route('register') }}" wire:navigate class="mt-3 inline-flex items-center gap-1 text-xs font-bold text-white hover:underline">
                        Daftar Sekarang
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                    </a>
                </div>
            </div>
            <!-- Banner 2 -->
            <div class="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-2xl p-4 sm:p-5 text-white relative overflow-hidden">
                <div class="absolute -top-4 -right-4 h-20 w-20 rounded-full bg-white/10"></div>
                <div class="relative">
                    <span class="inline-block rounded-full bg-white/20 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide">Pemilik Kos</span>
                    <h3 class="mt-2 text-sm sm:text-base font-bold">Promosikan Kos Anda</h3>
                    <p class="mt-1 text-xs text-emerald-100">Daftarkan kos, kelola kamar, dan balas pertanyaan pencari kos lewat chat.</p>
                    <a href="{{ route('register') }}" wire:navigate class="mt-3 inline-flex items-center gap-1 text-xs font-bold text-white hover:underline">
                        Mulai Gratis
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                    </a>
                </div>
            </div>
            <!-- Banner 3 - Stats -->
            <div class="bg-gradient-to-r from-amber-500 to-orange-500 rounded-2xl p-4 sm:p-5 text-white relative overflow-hidden sm:col-span-2 lg:col-span-1">
                <div class="absolute -top-4 -right-4 h-20 w-20 rounded-full bg-white/10"></div>
                <div class="relative">
                    <span class="inline-block rounded-full bg-white/20 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide">Statistik</span>
                    <div class="mt-2 flex items-baseline gap-3">
                        <div>
                            <p class="text-2xl font-extrabold">{{ $totalProperti }}</p>
                            <p class="text-xs text-orange-100">Kos Aktif</p>
                        </div>
                        <div class="h-8 w-px bg-white/25"></div>
                        <div>
                            <p class="text-2xl font-extrabold">{{ $totalKamar }}</p>
                            <p class="text-xs text-orange-100">Kamar Tersedia</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Iklan Partner -->
    <section class="max-w-7xl mx-auto px-4 pt-8">
        <x-promo-ads />
    </section>

    <!-- Daftar Kos Terbaru -->
    <section class="max-w-7xl mx-auto px-4 py-8 sm:py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg sm:text-xl font-extrabold text-gray-900">Kos Terbaru</h2>
                <p class="text-xs sm:text-sm text-gray-500">Kos yang baru ditambahkan pemilik</p>
            </div>
            <a href="{{ route('kos.index') }}" wire:navigate
               class="text-xs sm:text-sm font-semibold text-teal-600 hover:text-teal-500 flex items-center gap-1">
                Lihat Semua
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($propertiList as $properti)
                <a href="{{ route('kos.detail', $properti) }}" wire:navigate
                   class="group bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 overflow-hidden hover:shadow-md hover:ring-teal-200 transition-all duration-200">
                    <div class="relative h-40 sm:h-44 bg-gradient-to-br from-teal-100 via-emerald-100 to-cyan-100">
                        @if ($properti->foto)
                            <img src="{{ asset('storage/' . $properti->foto) }}" alt="{{ $properti->nama }}"
                                 class="h-full w-full object-cover group-hover:scale-105 transition duration-300" loading="lazy">
                        @else
                            <div class="h-full w-full flex items-center justify-center">
                                <svg class="h-14 w-14 text-teal-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
                            </div>
                        @endif
                        @if ($properti->kamar_tersedia > 0)
                            <span class="absolute top-2.5 right-2.5 inline-flex items-center rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-bold text-white shadow-sm">
                                {{ $properti->kamar_tersedia }} Kamar
                            </span>
                        @else
                            <span class="absolute top-2.5 right-2.5 inline-flex items-center rounded-full bg-gray-800 px-2 py-0.5 text-[10px] font-bold text-white shadow-sm">
                                Penuh
                            </span>
                        @endif
                    </div>
                    <div class="p-3.5 sm:p-4">
                        <h3 class="text-sm sm:text-base font-bold text-gray-900 truncate group-hover:text-teal-600 transition">{{ $properti->nama }}</h3>
                        <p class="mt-0.5 text-xs text-gray-500 flex items-center gap-1">
                            <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                            {{ $properti->kota ?? $properti->alamat ?? 'Lokasi belum diisi' }}
                        </p>
                        <div class="mt-2.5 pt-2.5 border-t border-gray-100 flex items-end justify-between">
                            <span class="text-[10px] text-gray-400 font-medium">Mulai dari</span>
                            <span class="text-base font-extrabold text-teal-600">
                                @if ($properti->harga_termurah)
                                    Rp{{ number_format($properti->harga_termurah, 0, ',', '.') }}<span class="text-[10px] font-medium text-gray-400">/bln</span>
                                @else
                                    <span class="text-xs font-medium text-gray-400">Penuh</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-12 text-center">
                    <div class="mx-auto h-14 w-14 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                        <svg class="h-7 w-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
                    </div>
                    <p class="text-sm text-gray-500 font-medium">Belum ada kos terdaftar.</p>
                </div>
            @endforelse
        </div>
    </section>

    <!-- Kenapa Ngekos.in -->
    <section class="bg-white border-y border-gray-100">
        <div class="max-w-7xl mx-auto px-4 py-10 sm:py-14">
            <div class="text-center max-w-xl mx-auto mb-8">
                <h2 class="text-lg sm:text-2xl font-extrabold text-gray-900">Kenapa Pilih Ngekos.in?</h2>
                <p class="mt-2 text-xs sm:text-sm text-gray-500">Solusi praktis untuk pencari kos dan pemilik kos</p>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
                @foreach ([
                    ['Cari Mudah', 'Filter berdasarkan lokasi, harga, dan fasilitas yang kamu mau.', 'M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z'],
                    ['Chat Langsung', 'Tanya pemilik kos langsung dari HP tanpa harus datang.', 'M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155'],
                    ['Bayar Praktis', 'Tagihan bulanan otomatis, bayar lewat transfer.', 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z'],
                    ['Kelola Mudah', 'Pemilik kelola kamar, chat, dan pembayaran dari dashboard.', 'M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35'],
                ] as [$judul, $deskripsi, $ikon])
                    <div class="bg-gray-50 rounded-2xl p-4 sm:p-5 hover:bg-teal-50 transition-colors duration-200">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-teal-500 to-emerald-500 text-white shadow-sm">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon }}" /></svg>
                        </span>
                        <h3 class="mt-3 text-sm sm:text-base font-bold text-gray-900">{{ $judul }}</h3>
                        <p class="mt-1.5 text-xs text-gray-500 leading-relaxed">{{ $deskripsi }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="max-w-7xl mx-auto px-4 py-10 sm:py-14">
        <div class="bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-600 rounded-2xl p-6 sm:p-10 text-center relative overflow-hidden">
            <div class="absolute -top-16 left-1/4 h-48 w-48 rounded-full bg-emerald-300/20 blur-3xl"></div>
            <div class="absolute -bottom-20 -right-8 h-48 w-48 rounded-full bg-cyan-200/20 blur-3xl"></div>
            <div class="relative">
                <h2 class="text-lg sm:text-2xl font-extrabold text-white">Siap Cari atau Punya Kos?</h2>
                <p class="mt-2 text-xs sm:text-sm text-teal-100 max-w-md mx-auto">Buat akun gratis sekarang dan mulai cari kos impianmu.</p>
                <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('register') }}" wire:navigate
                       class="inline-flex items-center rounded-xl bg-white px-5 sm:px-6 py-2.5 sm:py-3 text-sm font-bold text-teal-700 shadow-lg hover:bg-teal-50 transition">
                        Daftar Gratis
                    </a>
                    <a href="{{ route('kos.index') }}" wire:navigate
                       class="inline-flex items-center rounded-xl bg-white/15 ring-1 ring-white/30 px-5 sm:px-6 py-2.5 sm:py-3 text-sm font-bold text-white hover:bg-white/25 transition backdrop-blur">
                        Lihat Semua Kos
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
