<?php

use App\Livewire\Actions\Logout;
use App\Services\SubscriptionService;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }

    public function with(): array
    {
        $links = [];

        $links[] = [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'active' => 'dashboard.*',
            'routeName' => route('dashboard', absolute: false),
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />',
            'group' => 'UTAMA',
        ];

        $links[] = [
            'label' => 'Cari Kos',
            'route' => 'kos.index',
            'active' => 'kos.*',
            'routeName' => route('kos.index', absolute: false),
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />',
            'group' => 'UTAMA',
        ];

        $user = auth()->user();

        if ($user->hasAnyRole(['anak_kos', 'pemilik'])) {
            $belumDibaca = $user->pesanBelumDibaca();
            $links[] = [
                'label' => 'Pesan', 'route' => 'chat.index', 'active' => 'chat.*',
                'routeName' => route('chat.index', absolute: false),
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />',
                'belum_dibaca' => $belumDibaca,
                'group' => 'UTAMA',
            ];
        }

        if ($user->hasRole('anak_kos')) {
            $links[] = [
                'label' => 'Kos Favorit', 'route' => 'favorit', 'active' => 'favorit',
                'routeName' => route('favorit', absolute: false),
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />',
                'group' => 'UTAMA',
            ];
        }

        if ($user->hasAnyRole(['pemilik', 'admin', 'super_admin'])) {
            $links[] = [
                'label' => 'Kelola Kos', 'route' => 'pemilik.properti', 'active' => 'pemilik.properti*',
                'routeName' => route('pemilik.properti', absolute: false),
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819" />',
                'group' => 'KELOLA',
            ];
        }

        if ($user->hasAnyRole(['pemilik', 'admin', 'super_admin'])) {
            $links[] = [
                'label' => 'Pengeluaran', 'route' => 'pemilik.pengeluaran', 'active' => 'pemilik.pengeluaran',
                'routeName' => route('pemilik.pengeluaran', absolute: false),
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2.25 2.25 0 002.25-2.25v-1.5a2.25 2.25 0 00-2.25-2.25H6a2.25 2.25 0 00-2.25 2.25v1.5A2.25 2.25 0 006 21zm12-8.25v-6.5A2.25 2.25 0 0015.75 4H8.25A2.25 2.25 0 006 6.25v6.5m18 0h-18" />',
                'group' => 'KELOLA',
            ];
        }

        if ($user->hasRole('pemilik')) {
            $links[] = [
                'label' => 'Grafik', 'route' => 'pemilik.grafik', 'active' => 'pemilik.grafik',
                'routeName' => route('pemilik.grafik', absolute: false),
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />',
                'group' => 'KELOLA',
            ];
            $links[] = [
                'label' => 'Laporan Premium', 'route' => 'pemilik.laporan', 'active' => 'pemilik.laporan',
                'routeName' => route('pemilik.laporan', absolute: false),
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />',
                'group' => 'KELOLA',
            ];
        }

        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            $links[] = [
                'label' => 'Pesan Masuk', 'route' => 'bantuan.masuk', 'active' => 'bantuan.masuk',
                'routeName' => route('bantuan.masuk', absolute: false),
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z" />',
                'belum_dibaca' => $user->bantuanMasukBelumDibaca(),
                'group' => 'KELOLA',
            ];
        }

        if ($user->hasRole('super_admin')) {
            $links[] = [
                'label' => 'Pengguna', 'route' => 'pengguna', 'active' => 'pengguna',
                'routeName' => route('pengguna', absolute: false),
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />',
                'group' => 'KELOLA',
            ];
            $links[] = [
                'label' => 'Langganan', 'route' => 'langganan.kelola', 'active' => 'langganan.kelola',
                'routeName' => route('langganan.kelola', absolute: false),
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />',
                'group' => 'KELOLA',
            ];
        }

        if ($user->hasRole('pemilik')) {
            $links[] = [
                'label' => 'Paket', 'route' => 'langganan.plans', 'active' => 'langganan.*',
                'routeName' => route('langganan.plans', absolute: false),
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />',
                'group' => 'KELOLA',
            ];
        }

        $links[] = [
            'label' => 'Bantuan', 'route' => 'bantuan', 'active' => 'bantuan',
            'routeName' => route('bantuan', absolute: false),
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />',
            'group' => 'LAINNYA',
        ];
        $links[] = [
            'label' => 'Pengaturan', 'route' => 'pengaturan', 'active' => 'pengaturan',
            'routeName' => route('pengaturan', absolute: false),
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.28z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />',
            'group' => 'LAINNYA',
        ];

        $groups = [];
        foreach ($links as $link) {
            $groups[$link['group']][] = $link;
        }

        // Ringkasan paket untuk kartu "Paket Anda" (khusus pemilik, cache singkat).
        // Catatan: tanggal disimpan sebagai string ISO (bukan objek Carbon) agar
        // aman di-serialize ke file cache.
        $paket = null;
        if ($user->hasRole('pemilik')) {
            $paket = cache()->remember("navigasi.paket.v2.{$user->id}", 120, function () use ($user) {
                $plan = strtolower(SubscriptionService::getPlan($user) ?: 'free');
                if (! in_array($plan, ['free', 'pro', 'business'], true)) {
                    $plan = 'free';
                }
                $langganan = SubscriptionService::getSubscription($user);

                return [
                    'plan' => $plan,
                    'aktif' => (bool) ($langganan?->isActive()),
                    'kedaluwarsa' => $langganan?->expires_at?->toIso8601String(),
                ];
            });
        }

        return ['groups' => $groups, 'paket' => $paket];
    }
}; ?>

{{-- Sidebar navy modern: desktop fixed + rail collapsed, mobile drawer off-canvas --}}
<nav x-data="{ open: false }" x-on:livewire:navigated.window="open = false" x-effect="document.body.classList.toggle('overflow-hidden', open)">

    {{-- ===== Desktop sidebar (navy) ===== --}}
    <aside class="app-sidebar hidden lg:flex lg:fixed lg:inset-y-0 lg:left-0 lg:z-40 lg:w-64 lg:flex-col bg-brand-900 dark:bg-brand-950 text-slate-300">
        {{-- Brand --}}
        <div class="sidebar-head flex h-16 shrink-0 items-center gap-2.5 border-b border-white/10 px-5">
            <a href="{{ route('dashboard') }}" wire:navigate title="Dashboard" class="sidebar-brandlink flex min-w-0 items-center gap-2.5">
                <x-application-logo class="h-9 w-9 shrink-0" />
                <x-brand-name class="sidebar-label text-lg font-bold tracking-tight text-white" :suffix-class="'text-amber-300'" />
            </a>
            <button @click="toggleNgekosSidebar()" type="button" aria-label="Buka atau tutup sidebar" title="Buka/tutup sidebar"
                class="ms-auto flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition-colors duration-200 hover:bg-white/10 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300/60">
                <svg class="icon-collapse h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
                <svg class="icon-expand h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
            </button>
        </div>

        {{-- Navigasi berkelompok --}}
        <nav class="flex-1 overflow-y-auto overscroll-contain px-3 py-4 scrollbar-hide" aria-label="Navigasi utama">
            @foreach ($groups as $namaGrup => $items)
                <div class="{{ $loop->first ? '' : 'mt-5' }}">
                    <p class="sidebar-group px-3 pb-1.5 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">{{ $namaGrup }}</p>
                    <div class="space-y-1">
                        @foreach ($items as $link)
                            @php $aktif = request()->routeIs($link['active']); @endphp
                            <a href="{{ $link['routeName'] }}" wire:navigate title="{{ $link['label'] }}"
                               class="sidebar-linkrow group flex w-full items-center gap-3 rounded-[10px] px-3 py-2.5 text-sm transition-colors duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300/60 {{ $aktif ? 'bg-white/10 font-semibold text-white ring-1 ring-inset ring-white/10' : 'font-medium text-slate-300 hover:bg-white/5 hover:text-white' }}">
                                <svg class="h-5 w-5 shrink-0 transition-colors duration-200 {{ $aktif ? 'text-amber-300' : 'text-slate-400 group-hover:text-amber-200' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">{!! $link['icon'] !!}</svg>
                                <span class="sidebar-label flex-1 truncate">{{ $link['label'] }}</span>
                                @if (isset($link['belum_dibaca']) && $link['belum_dibaca'] > 0)
                                    <span class="sidebar-badge ms-auto inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-amber-400 px-1.5 text-[10px] font-bold text-brand-950">
                                        {{ $link['belum_dibaca'] > 9 ? '9+' : $link['belum_dibaca'] }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- Ganti mode gelap/terang --}}
            <button @click="toggleNgekosTheme()" type="button" title="Ganti tema" aria-label="Ganti tema"
                class="sidebar-linkrow mt-5 flex w-full items-center gap-3 rounded-[10px] px-3 py-2.5 text-sm font-medium text-slate-300 transition-colors duration-200 hover:bg-white/5 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300/60">
                <svg class="h-5 w-5 shrink-0 dark:hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" /></svg>
                <svg class="hidden h-5 w-5 shrink-0 text-amber-300 dark:block" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                <span class="sidebar-label flex-1 text-start"><span class="dark:hidden">Mode Gelap</span><span class="hidden dark:inline">Mode Terang</span></span>
            </button>
        </nav>

        {{-- Kartu Paket Anda (khusus pemilik) --}}
        @if ($paket)
            @php
                $warnaBadge = match ($paket['plan']) {
                    'pro' => 'bg-amber-400/15 text-amber-300',
                    'business' => 'bg-sky-400/15 text-sky-300',
                    default => 'bg-white/10 text-slate-300',
                };
                if (! $paket['aktif'] && $paket['plan'] !== 'free') $warnaBadge = 'bg-red-400/15 text-red-300';
            @endphp
            <div class="sidebar-paket px-3 pb-3">
                <div class="rounded-2xl border border-white/10 bg-gradient-to-br from-white/10 to-white/[0.03] p-4">
                    <p class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-widest text-slate-300">
                        <svg class="h-3.5 w-3.5 text-amber-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" /></svg>
                        Paket Anda
                    </p>
                    <div class="mt-2 flex items-center justify-between gap-2">
                        <span class="text-lg font-extrabold tracking-tight text-white">{{ strtoupper($paket['plan']) }}</span>
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $warnaBadge }}">
                            {{ $paket['aktif'] ? 'Aktif' : ($paket['plan'] === 'free' ? 'Gratis' : 'Nonaktif') }}
                        </span>
                    </div>
                    <p class="mt-0.5 truncate text-xs text-slate-400">
                        {{ $paket['kedaluwarsa'] ? 'Aktif sampai '.\Carbon\Carbon::parse($paket['kedaluwarsa'])->translatedFormat('d M Y') : 'Tanpa batas waktu' }}
                    </p>
                    <a href="{{ route('langganan.plans') }}" wire:navigate title="Kelola paket langganan"
                       class="mt-3 flex w-full items-center justify-center gap-1.5 rounded-xl bg-amber-400 px-3 py-2 text-xs font-bold text-brand-950 transition-colors duration-200 hover:bg-amber-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-200">
                        Kelola Paket
                    </a>
                </div>
            </div>
        @endif

        {{-- Profil pengguna --}}
        <div class="relative border-t border-white/10 px-4 py-3" x-data="{ open: false }" @click.outside="open = false">
            <div @click="open = ! open" class="cursor-pointer">
                <button type="button" title="Akun saya" aria-haspopup="menu" :aria-expanded="open.toString()"
                    class="sidebar-profile flex w-full items-center gap-3 rounded-[10px] px-3 py-2 transition-colors duration-200 hover:bg-white/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300/60">
                    <x-user-avatar size="sm" />
                    <div class="sidebar-label min-w-0 flex-1 text-left">
                        <div x-data="{{ json_encode(['nama' => auth()->user()->nama]) }}" x-text="nama" x-on:profile-updated.window="nama = $event.detail.nama"
                             class="truncate text-sm font-semibold leading-tight text-white"></div>
                        @if (auth()->user()->roles->isNotEmpty())
                            <span class="text-[11px] font-medium text-slate-400">
                                {{ str(auth()->user()->roles->first()->name)->replace('_', ' ')->title() }}
                            </span>
                        @endif
                    </div>
                    <svg class="sidebar-label h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                </button>
            </div>
            <div x-show="open" x-cloak role="menu"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 style="position: absolute; bottom: 100%; margin-bottom: 0.5rem; left: 1rem; right: 1rem;"
                 class="profile-pop z-50 rounded-xl border border-white/10 bg-brand-800 dark:bg-brand-900 p-1 shadow-xl shadow-black/30">
                <x-dropdown-link :href="route('pengaturan')" wire:navigate class="rounded-lg px-3 py-2 text-sm font-medium !text-slate-200 hover:!bg-white/10">
                    {{ __('Pengaturan') }}
                </x-dropdown-link>
                <button wire:click="logout" class="w-full text-start">
                    <x-dropdown-link class="rounded-lg px-3 py-2 text-sm font-medium !text-red-300 hover:!bg-red-400/10">
                        Keluar
                    </x-dropdown-link>
                </button>
            </div>
        </div>
    </aside>

    {{-- ===== Mobile: topbar fixed + drawer off-canvas (navy) ===== --}}
    {{-- Catatan: pakai fixed (bukan sticky) karena parent <nav> tingginya hanya
         setinggi topbar sehingga sticky tidak punya ruang gerak dan ikut kegulung. --}}
    <div class="bg-brand-900 dark:bg-brand-950 border-b border-white/10 fixed inset-x-0 top-0 z-40 lg:hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex justify-between h-14">
                <div class="flex items-center gap-2">
                    <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2" aria-label="Dashboard">
                        <x-application-logo class="h-7 w-7" />
                        <x-brand-name class="hidden sm:block text-lg font-bold text-white" :suffix-class="'text-amber-300'" />
                    </a>
                </div>

                <div class="flex items-center gap-2">
                    <button @click="toggleNgekosTheme()" type="button" aria-label="Ganti tema"
                        class="inline-flex items-center justify-center p-2 rounded-xl text-slate-300 hover:text-white hover:bg-white/10 transition active:scale-95">
                        <svg class="h-5 w-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" /></svg>
                        <svg class="hidden h-5 w-5 text-amber-300 dark:block" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                    </button>
                    <x-user-avatar size="sm" class="me-2" />
                    <button @click="open = true" type="button" aria-label="Buka menu navigasi" aria-expanded="false"
                        class="inline-flex items-center justify-center p-2 rounded-xl text-slate-300 hover:text-white hover:bg-white/10 transition active:scale-95">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Overlay drawer --}}
    <div x-show="open" x-cloak @click="open = false" aria-hidden="true"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>

    {{-- Drawer navigasi --}}
    <aside x-show="open" x-cloak role="dialog" aria-modal="true" aria-label="Menu navigasi"
           x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
           class="fixed inset-y-0 left-0 z-50 flex w-72 max-w-[85vw] flex-col bg-brand-900 dark:bg-brand-950 text-slate-300 lg:hidden">
        <div class="flex h-16 shrink-0 items-center gap-2.5 border-b border-white/10 px-5">
            <a href="{{ route('dashboard') }}" wire:navigate @click="open = false" class="flex min-w-0 items-center gap-2.5" aria-label="Dashboard">
                <x-application-logo class="h-9 w-9 shrink-0" />
                <x-brand-name class="text-lg font-bold tracking-tight text-white" :suffix-class="'text-amber-300'" />
            </a>
            <button @click="open = false" type="button" aria-label="Tutup menu navigasi"
                class="ms-auto flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition-colors duration-200 hover:bg-white/10 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300/60">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto overscroll-contain px-3 py-4" aria-label="Navigasi utama">
            @foreach ($groups as $namaGrup => $items)
                <div class="{{ $loop->first ? '' : 'mt-5' }}">
                    <p class="px-3 pb-1.5 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">{{ $namaGrup }}</p>
                    <div class="space-y-1">
                        @foreach ($items as $link)
                            @php $aktif = request()->routeIs($link['active']); @endphp
                            <a href="{{ $link['routeName'] }}" wire:navigate @click="open = false"
                               class="group flex w-full items-center gap-3 rounded-[10px] px-3 py-2.5 text-sm transition-colors duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300/60 {{ $aktif ? 'bg-white/10 font-semibold text-white ring-1 ring-inset ring-white/10' : 'font-medium text-slate-300 hover:bg-white/5 hover:text-white' }}">
                                <svg class="h-5 w-5 shrink-0 transition-colors duration-200 {{ $aktif ? 'text-amber-300' : 'text-slate-400 group-hover:text-amber-200' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">{!! $link['icon'] !!}</svg>
                                <span class="flex-1 truncate">{{ $link['label'] }}</span>
                                @if (isset($link['belum_dibaca']) && $link['belum_dibaca'] > 0)
                                    <span class="ms-auto inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-amber-400 px-1.5 text-[10px] font-bold text-brand-950">
                                        {{ $link['belum_dibaca'] > 9 ? '9+' : $link['belum_dibaca'] }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>

        <div class="border-t border-white/10 px-4 py-3">
            <div class="flex items-center gap-3 px-1">
                <x-user-avatar size="md" />
                <div class="min-w-0 flex-1">
                    <div class="truncate text-sm font-semibold text-white">{{ auth()->user()->nama }}</div>
                    <div class="truncate text-xs text-slate-400">{{ auth()->user()->email }}</div>
                </div>
            </div>
            <button wire:click="logout" @click="open = false" class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl border border-white/15 px-3 py-2.5 text-sm font-semibold text-red-300 transition-colors duration-200 hover:bg-red-400/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-300/50">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
                Keluar
            </button>
        </div>
    </aside>
</nav>
