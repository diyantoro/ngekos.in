@php
    $isHome = request()->routeIs('home') || request()->routeIs('dashboard*');
    // Di dalam aplikasi (sudah login), Beranda = dashboard awal sesuai peran,
    // bukan landing publik sebelum login.
    $berandaUrl = auth()->check() ? route('dashboard') : route('home');

    $itemAktif = 'text-brand-700 dark:text-brand-300';
    $itemBiasa = 'text-slate-400 dark:text-gray-500';

    $badge = 'absolute top-0.5 right-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[9px] font-bold text-white z-10';
@endphp
<div class="fixed bottom-0 left-0 right-0 z-50 sm:hidden safe-bottom">
    <div class="border-t border-stone-200 dark:border-gray-800 bg-white dark:bg-gray-900">
        <nav class="relative flex items-center justify-around h-16 px-2" role="navigation">
            {{-- Home --}}
            <a href="{{ $berandaUrl }}" wire:navigate.hover
                aria-current="{{ $isHome ? 'page' : 'false' }}"
                class="relative flex flex-col items-center justify-center gap-0.5 w-16 py-1 transition {{ $isHome ? $itemAktif : $itemBiasa }}">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                <span class="text-[10px] font-medium">{{ __('Beranda') }}</span>
                @if ($isHome)
                    <span class="mt-0.5 h-1 w-6 rounded-full bg-brand-700 dark:bg-brand-400"></span>
                @endif
            </a>

            {{-- Cari Kos (disembunyikan untuk pemilik) --}}
            @if (! auth()->check() || ! auth()->user()->hasRole('pemilik'))
            <a href="{{ route('kos.index') }}" wire:navigate.hover
                aria-current="{{ request()->routeIs('kos.*') ? 'page' : 'false' }}"
                class="relative flex flex-col items-center justify-center gap-0.5 w-16 py-1 transition {{ request()->routeIs('kos.*') ? $itemAktif : $itemBiasa }}">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <span class="text-[10px] font-medium">{{ __('Cari Kos') }}</span>
                @if (request()->routeIs('kos.*'))
                    <span class="mt-0.5 h-1 w-6 rounded-full bg-brand-700 dark:bg-brand-400"></span>
                @endif
            </a>
            @endif

            @auth
                @if (auth()->user()->hasAnyRole(['anak_kos', 'pemilik']))
                {{-- Pesan (khusus anak kos & pemilik) --}}
                <a href="{{ route('chat.index') }}" wire:navigate.hover
                    aria-current="{{ request()->routeIs('chat.*') ? 'page' : 'false' }}"
                    class="relative flex flex-col items-center justify-center gap-0.5 w-16 py-1 transition {{ request()->routeIs('chat.*') ? $itemAktif : $itemBiasa }}">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                    </svg>
                    @php $unread = auth()->user()->pesanBelumDibaca(); @endphp
                    @if ($unread > 0)
                        <span class="{{ $badge }}">{{ $unread > 9 ? '9+' : $unread }}</span>
                    @endif
                    <span class="text-[10px] font-medium">{{ __('Pesan') }}</span>
                    @if (request()->routeIs('chat.*'))
                        <span class="mt-0.5 h-1 w-6 rounded-full bg-brand-700 dark:bg-brand-400"></span>
                    @endif
                </a>
                @endif

                @if (auth()->user()->hasAnyRole(['super_admin', 'admin']))
                    {{-- Bantuan --}}
                    <a href="{{ route('bantuan.masuk') }}" wire:navigate.hover
                        aria-current="{{ request()->routeIs('bantuan.masuk') ? 'page' : 'false' }}"
                        class="relative flex flex-col items-center justify-center gap-0.5 w-16 py-1 transition {{ request()->routeIs('bantuan.masuk') ? $itemAktif : $itemBiasa }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z" />
                        </svg>
                        @php $bantuanBaru = auth()->user()->bantuanMasukBelumDibaca(); @endphp
                        @if ($bantuanBaru > 0)
                            <span class="{{ $badge }}">{{ $bantuanBaru > 9 ? '9+' : $bantuanBaru }}</span>
                        @endif
                        <span class="text-[10px] font-medium">{{ __('Bantuan') }}</span>
                    </a>
                @endif

                    @if (auth()->user()->hasAnyRole(['pemilik', 'admin', 'super_admin']))
                    {{-- Kelola --}}
                    <div x-data="{ buka: false }" class="relative">
                        <button @click="buka = !buka" type="button" aria-label="Menu kelola"
                            class="relative flex flex-col items-center justify-center gap-0.5 w-16 py-1 transition {{ request()->routeIs('pemilik.*') ? $itemAktif : $itemBiasa }}">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .414.336.75.75.75z" />
                            </svg>
                            <span class="text-[10px] font-medium">{{ __('Kelola') }}</span>
                        </button>
                        <div x-show="buka" x-cloak @click.outside="buka = false"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-2"
                            class="absolute bottom-full mb-3 left-1/2 -translate-x-1/2 w-48 rounded-xl bg-white dark:bg-gray-800 shadow-card-hover border border-stone-200 dark:border-gray-700 overflow-hidden z-50">
                            <a href="{{ route('pemilik.properti') }}" wire:navigate.hover @click="buka = false"
                                class="flex items-center gap-3 px-4 py-3 text-sm font-medium {{ request()->routeIs('pemilik.properti*') ? 'text-brand-700 dark:text-brand-300 bg-brand-50 dark:bg-brand-500/10' : 'text-slate-700 dark:text-gray-200' }} hover:bg-stone-100 dark:hover:bg-gray-700 transition">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819" /></svg>
                                {{ __('Kelola Kos') }}
                            </a>
                            <a href="{{ route('pemilik.pengeluaran') }}" wire:navigate.hover @click="buka = false"
                                class="flex items-center gap-3 px-4 py-3 text-sm font-medium {{ request()->routeIs('pemilik.pengeluaran') ? 'text-brand-700 dark:text-brand-300 bg-brand-50 dark:bg-brand-500/10' : 'text-slate-700 dark:text-gray-200' }} hover:bg-stone-100 dark:hover:bg-gray-700 transition">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2.25 2.25 0 002.25-2.25v-1.5a2.25 2.25 0 00-2.25-2.25H6a2.25 2.25 0 00-2.25 2.25v1.5A2.25 2.25 0 006 21zm12-8.25v-6.5A2.25 2.25 0 0015.75 4H8.25A2.25 2.25 0 006 6.25v6.5m18 0h-18" /></svg>
                                {{ __('Pengeluaran') }}
                            </a>
                            <a href="{{ route('pemilik.tagihan') }}" wire:navigate.hover @click="buka = false"
                                class="flex items-center gap-3 px-4 py-3 text-sm font-medium {{ request()->routeIs('pemilik.tagihan') ? 'text-brand-700 dark:text-brand-300 bg-brand-50 dark:bg-brand-500/10' : 'text-slate-700 dark:text-gray-200' }} hover:bg-stone-100 dark:hover:bg-gray-700 transition">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                {{ __('Tagihan') }}
                            </a>
                            <a href="{{ route('pemilik.penyewa') }}" wire:navigate.hover @click="buka = false"
                                class="flex items-center gap-3 px-4 py-3 text-sm font-medium {{ request()->routeIs('pemilik.penyewa') ? 'text-brand-700 dark:text-brand-300 bg-brand-50 dark:bg-brand-500/10' : 'text-slate-700 dark:text-gray-200' }} hover:bg-stone-100 dark:hover:bg-gray-700 transition">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z" /></svg>
                                {{ __('Penyewa') }}
                            </a>

                        </div>
                    </div>
                @endif

                @if (auth()->user()->hasRole('anak_kos'))
                    {{-- Tagihan --}}
                    <a href="{{ route('anak-kos.tagihan') }}" wire:navigate.hover
                        class="relative flex flex-col items-center justify-center gap-0.5 w-16 py-1 transition {{ request()->routeIs('anak-kos.tagihan') ? $itemAktif : $itemBiasa }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-[10px] font-medium">{{ __('Tagihan') }}</span>
                        @if (request()->routeIs('anak-kos.tagihan'))
                            <span class="mt-0.5 h-1 w-6 rounded-full bg-brand-700 dark:bg-brand-400"></span>
                        @endif
                    </a>
                @endif

                @if (auth()->user()->hasRole('pemilik'))
                    {{-- Grafik --}}
                    <a href="{{ route('pemilik.grafik') }}" wire:navigate.hover
                        class="relative flex flex-col items-center justify-center gap-0.5 w-16 py-1 transition {{ request()->routeIs('pemilik.grafik') ? $itemAktif : $itemBiasa }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                        <span class="text-[10px] font-medium">{{ __('Grafik') }}</span>
                        @if (request()->routeIs('pemilik.grafik'))
                            <span class="mt-0.5 h-1 w-6 rounded-full bg-brand-700 dark:bg-brand-400"></span>
                        @endif
                    </a>
                @endif

                {{-- Pengaturan --}}
                <a href="{{ route('pengaturan') }}" wire:navigate.hover
                    aria-current="{{ request()->routeIs('pengaturan') ? 'page' : 'false' }}"
                    class="relative flex flex-col items-center justify-center gap-0.5 w-16 py-1 transition {{ request()->routeIs('pengaturan') ? $itemAktif : $itemBiasa }}">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.28z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="text-[10px] font-medium">{{ __('Pengaturan') }}</span>
                    @if (request()->routeIs('pengaturan'))
                        <span class="mt-0.5 h-1 w-6 rounded-full bg-brand-700 dark:bg-brand-400"></span>
                    @endif
                </a>
            @else
                {{-- Login --}}
                <a href="{{ route('login') }}" wire:navigate.hover
                    class="flex flex-col items-center justify-center gap-0.5 w-16 py-1 text-slate-400 dark:text-gray-500 transition">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
                    <span class="text-[10px] font-medium">{{ __('Masuk') }}</span>
                </a>
                {{-- Register --}}
                <a href="{{ route('register') }}" wire:navigate.hover
                    class="flex flex-col items-center justify-center gap-0.5 w-16 py-1 text-slate-400 dark:text-gray-500 transition">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" /></svg>
                    <span class="text-[10px] font-medium">{{ __('Daftar') }}</span>
                </a>
            @endauth
        </nav>
    </div>
</div>
