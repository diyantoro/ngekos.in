<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0d9488">
        <meta name="gmaps-key" content="{{ config('services.google_maps.key') }}">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

        <title>{{ \App\Models\Pengaturan::namaSitus() }}</title>

        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="48x48">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <script>
            (function () {
                var t = localStorage.getItem('theme');
                var gelap = t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', gelap);
            })();
            // Fallback darurat: toggle tema tetap jalan walau bundle Vite gagal dimuat.
            window.toggleNgekosTheme = window.toggleNgekosTheme || function () {
                var gelap = !document.documentElement.classList.contains('dark');
                document.documentElement.classList.toggle('dark', gelap);
                try { localStorage.setItem('theme', gelap ? 'dark' : 'light'); } catch (e) {}
                var meta = document.querySelector('meta[name="theme-color"]');
                if (meta) meta.setAttribute('content', gelap ? '#0f172a' : '#0d9488');
                try { if (window.Alpine && Alpine.store && Alpine.store('theme')) Alpine.store('theme').dark = gelap; } catch (e) {}
                window.dispatchEvent(new CustomEvent('ngekos:theme-changed', { detail: { dark: gelap } }));
            };
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .pb-safe { padding-bottom: env(safe-area-inset-bottom, 0px); }
            .pt-safe { padding-top: env(safe-area-inset-top, 0px); }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50 dark:bg-gray-950 dark:text-gray-100">
        @auth
            {{-- Logged-in: pakai sidebar kiri yang sama di semua halaman --}}
            <livewire:layout.navigation />
            <div class="min-h-screen lg:pl-64">
                <main class="pb-20 lg:pb-0">
                    {{ $slot }}
                </main>
                <x-bottom-nav />
            </div>
        @else
        <div class="min-h-screen flex flex-col">

            <!-- Top Header (Desktop + Mobile simplified) -->
            <header x-data="{ open: false }" class="sticky top-0 z-40 bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl border-b border-gray-100/80 dark:border-gray-800 pt-safe">
                <div class="max-w-7xl mx-auto px-4 sm:px-6">
                    <div class="flex items-center justify-between h-14">
                        <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2.5 shrink-0 group">
                            <x-application-logo class="h-8 w-8 transition-transform duration-300 group-hover:scale-110" />
                            <span class="text-lg font-extrabold text-gray-900 dark:text-gray-100">Ngekos<span class="gradient-text">.in</span></span>
                        </a>

                        <!-- Desktop nav -->
                        <nav class="hidden sm:flex items-center gap-1">
                            <a href="{{ route('kos.index') }}" wire:navigate
                               class="px-3 py-2 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('kos.*') ? 'text-teal-600 bg-teal-50 dark:bg-teal-900/30 dark:text-teal-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                                Cari Kos
                            </a>
                            <a href="{{ route('bantuan') }}" wire:navigate
                               class="px-3 py-2 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('bantuan') ? 'text-teal-600 bg-teal-50 dark:bg-teal-900/30 dark:text-teal-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                                Bantuan
                            </a>
                            @auth
                                @php $unread = auth()->user()->pesanBelumDibaca(); @endphp
                                <a href="{{ route('chat.index') }}" wire:navigate
                                   class="relative px-3 py-2 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('chat.*') ? 'text-teal-600 bg-teal-50 dark:bg-teal-900/30 dark:text-teal-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                                    Pesan
                                    @if ($unread > 0)
                                        <span class="absolute -top-0.5 -right-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white animate-bounce-gentle shadow-sm">
                                            {{ $unread > 9 ? '9+' : $unread }}
                                        </span>
                                    @endif
                                </a>
                                @if (auth()->user()->hasRole('pemilik'))
                                    <a href="{{ route('pemilik.properti') }}" wire:navigate
                                       class="px-3 py-2 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('pemilik.properti*') ? 'text-teal-600 bg-teal-50 dark:bg-teal-900/30 dark:text-teal-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                                        Kelola Kos
                                    </a>
                                @endif
                                <div class="ms-2 flex items-center gap-2">
                                    <a href="{{ route('dashboard') }}" wire:navigate
                                       class="btn-primary !px-4 !py-2 !text-sm !rounded-xl">
                                        Dashboard
                                    </a>
                                    <x-user-avatar size="sm" />
                                </div>
                            @else
                                <a href="{{ route('login') }}" wire:navigate
                                   class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200">
                                    Masuk
                                </a>
                                <a href="{{ route('register') }}" wire:navigate
                                   class="btn-primary !px-4 !py-2 !text-sm !rounded-xl">
                                    Daftar
                                </a>
                            @endauth
                            <button @click="toggleNgekosTheme()" type="button" aria-label="Ganti tema"
                                class="ms-1 inline-flex items-center gap-1.5 rounded-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 active:scale-95">
                                <svg class="h-4 w-4 dark:hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" /></svg>
                                <svg class="hidden h-4 w-4 text-amber-300 dark:block" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                                <span class="dark:hidden">Gelap</span><span class="hidden dark:inline">Terang</span>
                            </button>
                        </nav>

                        <!-- Mobile hamburger -->
                        <div class="flex items-center sm:hidden gap-1">
                            <button @click="toggleNgekosTheme()" type="button" aria-label="Ganti tema"
                                class="flex items-center justify-center h-10 w-10 rounded-xl text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200 active:scale-95">
                                <svg class="h-5 w-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" /></svg>
                                <svg class="hidden h-5 w-5 text-amber-300 dark:block" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                            </button>
                            <button type="button" @click="open = !open"
                                class="flex items-center justify-center h-10 w-10 rounded-xl text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200 active:scale-95">
                                <svg class="h-5 w-5 transition-transform duration-200" :class="open ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path :class="{'hidden': open}" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                    <path :class="{'hidden': !open}" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mobile dropdown -->
                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                     class="sm:hidden border-t border-gray-100/80 dark:border-gray-800 bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl shadow-float">
                    <div class="px-4 py-3 space-y-1">
                        <a href="{{ route('kos.index') }}" wire:navigate @click="open = false"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('kos.*') ? 'text-teal-600 bg-teal-50 dark:bg-teal-900/30 dark:text-teal-400' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 active:bg-gray-100' }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                            Cari Kos
                        </a>
                        <a href="{{ route('bantuan') }}" wire:navigate @click="open = false"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('bantuan') ? 'text-teal-600 bg-teal-50 dark:bg-teal-900/30 dark:text-teal-400' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 active:bg-gray-100' }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" /></svg>
                            Bantuan
                        </a>
                        @auth
                            <a href="{{ route('chat.index') }}" wire:navigate @click="open = false"
                               class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('chat.*') ? 'text-teal-600 bg-teal-50 dark:bg-teal-900/30 dark:text-teal-400' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 active:bg-gray-100' }}">
                                <span class="flex items-center gap-3">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                                    Pesan
                                </span>
                                @if (auth()->user()->pesanBelumDibaca() > 0)
                                    <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1.5 text-[10px] font-bold text-white shadow-sm">
                                        {{ auth()->user()->pesanBelumDibaca() > 9 ? '9+' : auth()->user()->pesanBelumDibaca() }}
                                    </span>
                                @endif
                            </a>
                            @if (auth()->user()->hasRole('pemilik'))
                                <a href="{{ route('pemilik.properti') }}" wire:navigate @click="open = false"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('pemilik.*') ? 'text-teal-600 bg-teal-50 dark:bg-teal-900/30 dark:text-teal-400' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 active:bg-gray-100' }}">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
                                    Kelola Kos
                                </a>
                            @endif
                        @endauth
                    </div>
                    <div class="px-4 pb-4 pt-2 border-t border-gray-100/80 space-y-2">
                        @auth
                            <a href="{{ route('dashboard') }}" wire:navigate @click="open = false"
                               class="flex items-center justify-center gap-2 btn-primary w-full !rounded-xl !py-2.5">
                                <x-user-avatar size="xs" />
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" wire:navigate @click="open = false"
                               class="flex items-center justify-center btn-secondary w-full !rounded-xl !py-2.5">
                                Masuk
                            </a>
                            <a href="{{ route('register') }}" wire:navigate @click="open = false"
                               class="flex items-center justify-center btn-primary w-full !rounded-xl !py-2.5">
                                Daftar Sekarang
                            </a>
                        @endauth
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="bg-white dark:bg-gray-900 border-t border-gray-100/80 dark:border-gray-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-4">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <x-application-logo class="h-6 w-6" />
                            <p class="text-sm text-gray-500 dark:text-gray-400">&copy; {{ date('Y') }} {{ \App\Models\Pengaturan::namaSitus() }} &mdash; {{ \App\Models\Pengaturan::deskripsiSitus() }}</p>
                        </div>
                        @auth
                            <a href="{{ route('dashboard') }}" wire:navigate class="text-sm font-medium text-teal-600 hover:text-teal-500 transition-colors">Masuk ke dashboard</a>
                        @else
                            <a href="{{ route('register') }}" wire:navigate class="text-sm font-medium text-teal-600 hover:text-teal-500 transition-colors">Daftarkan kos Anda</a>
                        @endauth
                    </div>
                    @php
                        $emailKontak = \App\Models\Pengaturan::ambil('situs.email');
                        $teleponKontak = \App\Models\Pengaturan::ambil('situs.telepon');
                        $alamatKontak = \App\Models\Pengaturan::ambil('situs.alamat');
                    @endphp
                    @if ($emailKontak || $teleponKontak || $alamatKontak)
                        <p class="text-xs text-gray-400 dark:text-gray-500 text-center sm:text-left">
                            Kontak:
                            @if ($alamatKontak)<span class="ms-1">{{ $alamatKontak }}</span>@endif
                            @if ($teleponKontak)<span class="ms-1">&bull; {{ $teleponKontak }}</span>@endif
                            @if ($emailKontak)<span class="ms-1">&bull; <a href="mailto:{{ $emailKontak }}" class="hover:text-teal-600 transition-colors">{{ $emailKontak }}</a></span>@endif
                        </p>
                    @endif
                </div>
            </footer>

            <!-- Bottom Navigation (Mobile only) -->
            <x-bottom-nav />
        </div>
        @endif

        <livewire:chatbot />

        @stack('scripts')
    </body>
</html>
