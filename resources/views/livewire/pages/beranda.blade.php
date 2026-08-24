<?php

use App\Models\Kamar;
use App\Models\Properti;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.publik')] class extends Component
{
    public function mount(): void
    {
        if (auth()->check()) {
            $this->redirect(route('dashboard'), navigate: true);
        }
    }

    public function with(): array
    {
        return [
            'totalProperti' => Properti::where('status', 'aktif')->count(),
            'totalKamar' => Kamar::where('status', 'tersedia')->count(),
            'propertiContoh' => Properti::query()
                ->where('status', 'aktif')
                ->withCount(['kamars as kamar_tersedia' => fn ($q) => $q->where('status', 'tersedia')])
                ->withMin(['kamars as harga_termurah' => fn ($q) => $q->where('status', 'tersedia')], 'harga_sewa_bulanan')
                ->orderByDesc('kamar_tersedia')
                ->take(3)
                ->get(),
        ];
    }
}; ?>

<div>
    <!-- Hero -->
    <section class="relative bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-600 overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-64 bg-gradient-to-b from-white/10 to-transparent"></div>
        <div class="absolute -top-32 left-1/3 h-80 w-80 rounded-full bg-emerald-400/30 blur-3xl"></div>
        <div class="absolute bottom-0 right-0 h-72 w-72 rounded-full bg-cyan-300/20 blur-3xl translate-x-1/3 translate-y-1/3"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
            <div class="grid grid-cols-1 lg:grid-cols-[1.1fr_0.9fr] gap-12 lg:gap-16 items-center">
                <!-- Teks -->
                <div class="max-w-xl">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 ring-1 ring-white/25 px-3 py-1 text-xs font-semibold text-white">
                        Platform kos untuk pencari &amp; pemilik
                    </span>
                    <h1 class="mt-5 text-4xl sm:text-5xl font-extrabold text-white leading-tight tracking-tight">
                        Cari Kos Jadi Mudah,
                        <span class="block text-teal-200">Kelola Kos Jadi Praktis</span>
                    </h1>
                    <p class="mt-4 text-base sm:text-lg text-teal-100 leading-relaxed">
                        Temukan kamar kos sesuai budget, booking tanpa ribet, dan pantau tagihan dalam satu aplikasi. Pemilik kos pun bisa mengelola kamar &amp; pembayaran dengan rapi.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <a href="{{ route('register') }}" wire:navigate
                           class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3.5 text-sm font-bold text-teal-700 shadow-lg shadow-teal-950/20 hover:bg-teal-50 transition">
                            Daftar Sekarang &mdash; Gratis
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                        </a>
                        <a href="{{ route('login') }}" wire:navigate
                           class="inline-flex items-center rounded-xl bg-white/15 ring-1 ring-white/30 px-6 py-3.5 text-sm font-bold text-white hover:bg-white/25 transition backdrop-blur">
                            Masuk
                        </a>
                    </div>
                    <p class="mt-4 text-sm text-teal-200">
                        Sudah punya akun? <a href="{{ route('kos.index') }}" wire:navigate class="font-semibold text-white underline underline-offset-2 hover:text-teal-100">Langsung cari kos</a> tanpa daftar.
                    </p>
                </div>

                <!-- Ilustrasi kartu kos -->
                <div class="relative hidden lg:block">
                    <div class="absolute -inset-6 bg-white/10 rounded-[2rem] rotate-2"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl shadow-teal-950/30 p-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-bold text-gray-900">Kamar tersedia hari ini</p>
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 ring-1 ring-emerald-200 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Live
                            </span>
                        </div>

                        @forelse ($propertiContoh as $p)
                            <a href="{{ route('kos.detail', $p) }}" wire:navigate
                               class="flex items-center gap-3 rounded-xl ring-1 ring-gray-100 p-3 hover:bg-gray-50 hover:ring-teal-200 transition">
                                <span class="h-14 w-14 shrink-0 rounded-lg bg-gradient-to-br from-teal-100 via-emerald-100 to-cyan-100 flex items-center justify-center overflow-hidden">
                                    @if ($p->foto)
                                        <img src="{{ asset('storage/' . $p->foto) }}" alt="" class="h-full w-full object-cover">
                                    @else
                                        <svg class="h-6 w-6 text-teal-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
                                    @endif
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-semibold text-gray-900 truncate">{{ $p->nama }}</span>
                                    <span class="block text-xs text-gray-500 truncate">{{ $p->kota ?? 'Indonesia' }} &middot; {{ $p->kamar_tersedia }} kamar</span>
                                </span>
                                <span class="shrink-0 text-right">
                                    @if ($p->harga_termurah)
                                        <span class="block text-sm font-extrabold text-teal-600">Rp{{ number_format($p->harga_termurah, 0, ',', '.') }}</span>
                                        <span class="block text-[11px] text-gray-400">per bulan</span>
                                    @else
                                        <span class="block text-xs font-medium text-gray-400">Penuh</span>
                                    @endif
                                </span>
                            </a>
                        @empty
                            <div class="rounded-xl ring-1 ring-dashed ring-gray-200 p-6 text-center text-sm text-gray-400">
                                Belum ada kos terdaftar.
                            </div>
                        @endforelse

                        <a href="{{ route('kos.index') }}" wire:navigate
                           class="block rounded-xl bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 py-3 text-center text-sm font-bold text-white hover:opacity-90 transition">
                            Jelajahi Semua Kos
                        </a>
                    </div>

                    <!-- Chip statistik mengambang -->
                    <div class="absolute -bottom-6 -left-8 rounded-xl bg-white shadow-xl shadow-teal-950/20 ring-1 ring-gray-100 px-4 py-3">
                        <p class="text-xl font-extrabold text-gray-900">{{ $totalProperti }}</p>
                        <p class="text-xs text-gray-500 font-medium">Kos aktif terdaftar</p>
                    </div>
                    <div class="absolute -top-6 -right-6 rounded-xl bg-white shadow-xl shadow-teal-950/20 ring-1 ring-gray-100 px-4 py-3">
                        <p class="text-xl font-extrabold text-emerald-600">{{ $totalKamar }}</p>
                        <p class="text-xs text-gray-500 font-medium">Kamar tersedia</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Fitur -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 space-y-16">
        <div class="text-center max-w-2xl mx-auto">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Semua Kebutuhan Kos, Satu Aplikasi</h2>
            <p class="mt-3 text-gray-500">Dari pencarian pertama sampai pembayaran bulanan, Ngekos.in menghilangkan catatan manual dan chat yang berantakan.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach ([
                ['Cari & Bandingkan Kos', 'Filter berdasarkan kota, harga, dan kapasitas. Lihat jumlah kamar tersedia secara real-time sebelum memutuskan.', 'M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z'],
                ['Booking Tanpa Ribet', 'Ajukan booking kamar langsung ke pemilik dan pantau status pengajuannya sampai disetujui.', 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['Tagihan & Pembayaran Rapi', 'Tagihan bulanan otomatis dan verifikasi pembayaran yang transparan. Semua tercatat di dashboard.', 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z'],
            ] as [$judul, $deskripsi, $ikon])
                <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6 hover:shadow-md hover:ring-teal-200 transition">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-teal-500 via-emerald-500 to-green-500 text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon }}" /></svg>
                    </span>
                    <h3 class="mt-4 text-lg font-bold text-gray-900">{{ $judul }}</h3>
                    <p class="mt-2 text-sm text-gray-500 leading-relaxed">{{ $deskripsi }}</p>
                </div>
            @endforeach
        </div>

        <!-- Untuk siapa -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-8">
                <h3 class="text-xl font-extrabold text-gray-900">Untuk Pencari Kos</h3>
                <ul class="mt-4 space-y-3 text-sm text-gray-600">
                    @foreach (['Temukan kos sesuai budget & lokasi', 'Booking kamar tanpa datang langsung', 'Riwayat sewa & tagihan dalam satu tempat', 'Tanya admin lewat pusat bantuan'] as $poin)
                        <li class="flex items-start gap-2.5">
                            <svg class="h-5 w-5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            {{ $poin }}
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('register') }}" wire:navigate
                   class="mt-6 inline-flex items-center rounded-lg bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-500 transition">
                    Daftar sebagai Anak Kos
                </a>
            </div>
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-8">
                <h3 class="text-xl font-extrabold text-gray-900">Untuk Pemilik Kos</h3>
                <ul class="mt-4 space-y-3 text-sm text-gray-600">
                    @foreach (['Daftarkan & promosikan kos Anda gratis', 'Kelola kamar, harga, dan status hunian', 'Terima & kelola booking penghuni', 'Verifikasi pembayaran dengan mudah'] as $poin)
                        <li class="flex items-start gap-2.5">
                            <svg class="h-5 w-5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            {{ $poin }}
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('register') }}" wire:navigate
                   class="mt-6 inline-flex items-center rounded-lg border border-teal-200 bg-teal-50 px-5 py-2.5 text-sm font-semibold text-teal-700 hover:bg-teal-100 transition">
                    Daftarkan Kos Anda
                </a>
            </div>
        </div>

        <!-- CTA akhir -->
        <div class="bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-600 rounded-2xl p-8 sm:p-12 text-center relative overflow-hidden">
            <div class="absolute -top-20 left-1/4 h-56 w-56 rounded-full bg-emerald-300/20 blur-3xl"></div>
            <div class="absolute -bottom-24 -right-10 h-56 w-56 rounded-full bg-cyan-200/20 blur-3xl"></div>
            <div class="relative">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Siap Mencari atau Mengelola Kos?</h2>
                <p class="mt-2 text-teal-100 max-w-xl mx-auto">Buat akun gratis sekarang, atau jelajahi dulu kamar-kamar yang sedang tersedia.</p>
                <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('register') }}" wire:navigate
                       class="inline-flex items-center rounded-xl bg-white px-6 py-3 text-sm font-bold text-teal-700 hover:bg-teal-50 transition">
                        Daftar Gratis
                    </a>
                    <a href="{{ route('kos.index') }}" wire:navigate
                       class="inline-flex items-center rounded-xl bg-white/15 ring-1 ring-white/30 px-6 py-3 text-sm font-bold text-white hover:bg-white/25 transition backdrop-blur">
                        Cari Kos
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
