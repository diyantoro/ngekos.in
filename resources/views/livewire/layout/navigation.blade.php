<?php

use App\Livewire\Actions\Logout;
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
            'color' => 'teal',
        ];

        $links[] = [
            'label' => 'Cari Kos',
            'route' => 'kos.index',
            'active' => 'kos.*',
            'routeName' => route('kos.index', absolute: false),
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />',
            'color' => 'sky',
        ];

        $user = auth()->user();

        if ($user->hasAnyRole(['anak_kos', 'pemilik'])) {
            $belumDibaca = $user->pesanBelumDibaca();
            $links[] = [
                'label' => 'Pesan', 'route' => 'chat.index', 'active' => 'chat.*',
                'routeName' => route('chat.index', absolute: false),
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />',
                'belum_dibaca' => $belumDibaca,
                'color' => 'amber',
            ];
        }

        if ($user->hasRole('anak_kos')) {
            $links[] = [
                'label' => 'Kos Favorit', 'route' => 'favorit', 'active' => 'favorit',
                'routeName' => route('favorit', absolute: false),
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />',
                'color' => 'rose',
            ];
        }

        if ($user->hasAnyRole(['pemilik', 'admin', 'super_admin'])) {
            $links[] = [
                'label' => 'Kelola Kos', 'route' => 'pemilik.properti', 'active' => 'pemilik.properti*',
                'routeName' => route('pemilik.properti', absolute: false),
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819" />',
                'color' => 'emerald',
            ];
        }

        if ($user->hasAnyRole(['pemilik', 'admin', 'super_admin'])) {
            $links[] = [
                'label' => 'Pengeluaran', 'route' => 'pemilik.pengeluaran', 'active' => 'pemilik.pengeluaran',
                'routeName' => route('pemilik.pengeluaran', absolute: false),
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2.25 2.25 0 002.25-2.25v-1.5a2.25 2.25 0 00-2.25-2.25H6a2.25 2.25 0 00-2.25 2.25v1.5A2.25 2.25 0 006 21zm12-8.25v-6.5A2.25 2.25 0 0015.75 4H8.25A2.25 2.25 0 006 6.25v6.5m18 0h-18" />',
                'color' => 'amber',
            ];
        }

        if ($user->hasRole('pemilik')) {
            $links[] = [
                'label' => 'Grafik', 'route' => 'pemilik.grafik', 'active' => 'pemilik.grafik',
                'routeName' => route('pemilik.grafik', absolute: false),
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />',
                'color' => 'cyan',
            ];
            $links[] = [
                'label' => 'Laporan Premium', 'route' => 'pemilik.laporan', 'active' => 'pemilik.laporan',
                'routeName' => route('pemilik.laporan', absolute: false),
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />',
                'color' => 'violet',
            ];
        }

        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            $links[] = [
                'label' => 'Pesan Masuk', 'route' => 'bantuan.masuk', 'active' => 'bantuan.masuk',
                'routeName' => route('bantuan.masuk', absolute: false),
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z" />',
                'belum_dibaca' => $user->bantuanMasukBelumDibaca(),
                'color' => 'indigo',
            ];
        }

        if ($user->hasRole('super_admin')) {
            $links[] = [
                'label' => 'Pengguna', 'route' => 'pengguna', 'active' => 'pengguna',
                'routeName' => route('pengguna', absolute: false),
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />',
                'color' => 'purple',
            ];
            $links[] = [
                'label' => 'Langganan', 'route' => 'langganan.kelola', 'active' => 'langganan.kelola',
                'routeName' => route('langganan.kelola', absolute: false),
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />',
                'color' => 'violet',
            ];
        }

        if ($user->hasRole('pemilik')) {
            $links[] = [
                'label' => 'Paket', 'route' => 'langganan.plans', 'active' => 'langganan.*',
                'routeName' => route('langganan.plans', absolute: false),
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />',
                'color' => 'violet',
            ];
        }

        $links[] = [
            'label' => 'Bantuan', 'route' => 'bantuan', 'active' => 'bantuan',
            'routeName' => route('bantuan', absolute: false),
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />',
            'color' => 'orange',
        ];
        $links[] = [
            'label' => 'Pengaturan', 'route' => 'pengaturan', 'active' => 'pengaturan',
            'routeName' => route('pengaturan', absolute: false),
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.28z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />',
            'color' => 'gray',
        ];

        return ['links' => $links];
    }
}; ?>

{{-- Sidebar desktop (sebelah kiri); navbar + hamburger untuk mobile --}}
<nav x-data="{ open: false }">

    {{-- ===== Desktop sidebar ===== --}}
    <aside class="hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-40 lg:flex lg:flex-col lg:w-64 bg-gradient-to-b from-teal-600 via-teal-500 to-emerald-600 dark:from-teal-950 dark:via-teal-900 dark:to-emerald-950 border-r border-teal-700/60 dark:border-teal-800/50 shadow-xl shadow-teal-600/10 dark:shadow-black/30">
        {{-- Soft brand gradient overlay --}}
        <div class="absolute inset-0 bg-gradient-to-b from-white/10 via-transparent to-emerald-300/10 dark:from-teal-800/20 dark:via-transparent dark:to-emerald-800/20 pointer-events-none"></div>
        {{-- Top accent line --}}
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-teal-300 via-emerald-400 to-cyan-400"></div>
        
        {{-- Brand Header --}}
        <a href="{{ route('dashboard') }}" wire:navigate class="relative flex h-16 shrink-0 items-center gap-2.5 border-b border-white/20 dark:border-gray-800/40 px-5 group z-10">
            <div class="relative flex h-8 w-8 items-center justify-center rounded-xl bg-white/20 shadow-lg shadow-teal-900/20 backdrop-blur transition-transform duration-300 group-hover:scale-110">
                <x-application-logo class="h-5 w-5 text-white" />
            </div>
            <x-brand-name class="text-lg font-extrabold text-white dark:text-gray-50" :suffix-class="'text-emerald-100 dark:text-teal-300'" />
        </a>

        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1 scrollbar-hide relative z-10">
            @foreach ($links as $link)
                <x-sidebar-link :href="$link['routeName']" :active="request()->routeIs($link['active'])" wire:navigate>
                    <svg class="h-5 w-5 shrink-0 transition-all duration-200 {{ request()->routeIs($link['active']) ? 'scale-110' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">{!! $link['icon'] !!}</svg>
                    <span class="flex-1 truncate transition-colors duration-200">{{ $link['label'] }}</span>
                    @if (isset($link['belum_dibaca']) && $link['belum_dibaca'] > 0)
                        <span class="ms-auto inline-flex items-center justify-center h-4 min-w-4 px-1 rounded-full bg-gradient-to-r from-rose-500 to-pink-500 text-[10px] font-bold text-white shadow-sm shadow-rose-500/20 animate-bounce-gentle">
                            {{ $link['belum_dibaca'] > 9 ? '9+' : $link['belum_dibaca'] }}
                        </span>
                    @endif
                </x-sidebar-link>
            @endforeach

            {{-- Theme toggle di sidebar --}}
            <button @click="toggleNgekosTheme()" type="button"
                class="inline-flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-white/90 dark:text-teal-300 hover:bg-white/10 dark:hover:bg-teal-900/20 hover:text-white dark:hover:text-teal-100 focus:outline-none transition-all duration-200 active:scale-[0.98] backdrop-blur-sm border border-transparent hover:border-white/20 dark:hover:border-teal-800/50">
                <svg class="h-5 w-5 shrink-0 dark:hidden transition-transform duration-300 hover:rotate-12" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" /></svg>
                <svg class="hidden h-5 w-5 shrink-0 text-amber-300 dark:block transition-transform duration-300 hover:-rotate-12" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                <span class="flex-1 text-start"><span class="dark:hidden">Mode Gelap</span><span class="hidden dark:inline">Mode Terang</span></span>
            </button>
        </nav>

        {{-- User Profile --}}
        <div class="relative border-t border-white/20 dark:border-gray-800/40 px-4 py-3" x-data="{ open: false }" @click.outside="open = false">
            <div @click="open = ! open" class="cursor-pointer">
                <button type="button" class="flex w-full items-center gap-3 rounded-2xl px-3 py-2.5 hover:bg-white/10 dark:hover:bg-teal-900/20 transition-all duration-200 active:scale-[0.98] backdrop-blur-sm border border-transparent hover:border-white/20 dark:hover:border-teal-800/50">
                    <x-user-avatar size="sm" />
                    <div class="text-left min-w-0 flex-1">
                        <div x-data="{{ json_encode(['nama' => auth()->user()->nama]) }}" x-text="nama" x-on:profile-updated.window="nama = $event.detail.nama"
                             class="text-sm font-semibold text-white dark:text-gray-50 leading-tight truncate"></div>
                        @if (auth()->user()->roles->isNotEmpty())
                            <span class="text-[10px] font-semibold text-emerald-100 dark:text-teal-300">
                                {{ str(auth()->user()->roles->first()->name)->replace('_', ' ')->title() }}
                            </span>
                        @endif
                    </div>
                    <svg class="h-4 w-4 shrink-0 text-white/80 dark:text-teal-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                </button>
            </div>
            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 style="position: absolute; bottom: 100%; margin-bottom: 0.5rem; left: 1rem; right: 1rem;"
                 class="z-50 rounded-2xl bg-white/90 dark:bg-gray-900/90 backdrop-blur-2xl shadow-2xl ring-1 ring-teal-100/50 dark:ring-gray-800/50 p-1 border border-teal-100/50 dark:border-gray-800/50">
                <x-dropdown-link :href="route('pengaturan')" wire:navigate class="rounded-xl px-3 py-2 text-sm font-medium text-gray-800 dark:text-gray-100 hover:bg-teal-50/50 dark:hover:bg-teal-900/20 transition-all duration-200">
                    {{ __('Pengaturan') }}
                </x-dropdown-link>
                <button wire:click="logout" class="w-full text-start">
                    <x-dropdown-link class="rounded-xl px-3 py-2 text-sm font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-50/50 dark:hover:bg-rose-500/10 transition-all duration-200">
                        Keluar
                    </x-dropdown-link>
                </button>
            </div>
        </div>
    </aside>

    {{-- ===== Mobile navbar atas (dengan hamburger) ===== --}}
    <div class="lg:hidden bg-white/80 dark:bg-gray-900/80 backdrop-blur-2xl border-b border-gray-100/60 dark:border-gray-800/60 sticky top-0 z-40 shadow-sm shadow-black/5 dark:shadow-black/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex justify-between h-14">
                <div class="flex items-center gap-2">
                    <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2">
                        <x-application-logo class="h-7 w-7 transition-transform duration-300 hover:scale-110" />
                        <x-brand-name class="hidden sm:block text-lg font-bold text-gray-900 dark:text-gray-100" />
                    </a>
                </div>

                <div class="flex items-center gap-2">
                    <button @click="toggleNgekosTheme()" type="button" aria-label="Ganti tema"
                        class="inline-flex items-center justify-center p-2 rounded-xl text-gray-400 dark:text-gray-300 hover:text-gray-500 dark:hover:text-white hover:bg-white/50 dark:hover:bg-white/10 transition-all duration-300 active:scale-95 backdrop-blur-sm">
                        <svg class="h-5 w-5 dark:hidden transition-transform duration-300 hover:rotate-12" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" /></svg>
                        <svg class="hidden h-5 w-5 text-amber-300 dark:block transition-transform duration-300 hover:-rotate-12" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                    </button>
                    <x-user-avatar size="sm" class="me-2" />
                    <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-xl text-gray-400 dark:text-gray-300 hover:text-gray-500 dark:hover:text-white hover:bg-white/50 dark:hover:bg-white/10 transition-all duration-300 active:scale-95 backdrop-blur-sm">
                        <svg class="h-5 w-5 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Menu mobile (dropdown) --}}
        <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-gray-100/60 dark:border-gray-800/60 bg-white/90 dark:bg-gray-900/90 backdrop-blur-2xl">
            <div class="px-4 pt-3 pb-2 space-y-0.5">
                @foreach ($links as $link)
                    <x-responsive-nav-link :href="$link['routeName']" :active="request()->routeIs($link['active'])" wire:navigate class="rounded-xl px-3 py-2.5 transition-all duration-200">
                        <span class="inline-flex items-center gap-3">
                            <svg class="h-5 w-5 shrink-0 transition-transform duration-300 hover:scale-110" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">{!! $link['icon'] !!}</svg>
                            <span class="font-medium text-gray-700 dark:text-gray-200">{{ $link['label'] }}</span>
                        </span>
                        @if (isset($link['belum_dibaca']) && $link['belum_dibaca'] > 0)
                            <span class="ms-1 inline-flex items-center justify-center h-4 min-w-4 px-1 rounded-full bg-gradient-to-r from-rose-500 to-pink-500 text-[10px] font-bold text-white shadow-sm shadow-rose-500/20 animate-bounce-gentle">
                                {{ $link['belum_dibaca'] > 9 ? '9+' : $link['belum_dibaca'] }}
                            </span>
                        @endif
                    </x-responsive-nav-link>
                @endforeach
            </div>

            <div class="pt-3 pb-3 border-t border-gray-100/60 dark:border-gray-800/60">
                <div class="px-4 flex items-center gap-3">
                    <x-user-avatar size="md" />
                    <div>
                        <div class="font-medium text-sm text-gray-800 dark:text-gray-100" x-data="{{ json_encode(['nama' => auth()->user()->nama]) }}" x-text="nama" x-on:profile-updated.window="nama = $event.detail.nama"></div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</div>
                    </div>
                </div>
                <div class="mt-3 px-4 space-y-1">
                    <button wire:click="logout" class="w-full text-start">
                        <x-responsive-nav-link class="rounded-xl px-3 py-2.5 text-sm font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-50/50 dark:hover:bg-rose-500/10 transition-all duration-200">
                            Keluar
                        </x-responsive-nav-link>
                    </button>
                </div>
            </div>
        </div>
    </div>
</nav>
