<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0d9488">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

        <title>{{ \App\Models\Pengaturan::namaSitus() }}</title>

        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="48x48">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .pb-safe { padding-bottom: env(safe-area-inset-bottom, 0px); }
            .pt-safe { padding-top: env(safe-area-inset-top, 0px); }
            .scrollbar-hide::-webkit-scrollbar { display: none; }
            .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50">
        @auth
            {{-- Logged-in: pakai sidebar kiri yang sama di semua halaman --}}
            <livewire:layout.navigation />
            <div class="min-h-screen lg:pl-64">
                <main class="pb-20 sm:pb-0">
                    {{ $slot }}
                </main>
                <x-bottom-nav />
            </div>
        @else
        <div class="min-h-screen flex flex-col">

            <!-- Top Header (Desktop + Mobile simplified) -->
            <header x-data="{ open: false }" class="sticky top-0 z-40 bg-white/95 backdrop-blur-lg border-b border-gray-100 pt-safe">
                <div class="max-w-7xl mx-auto px-4">
                    <div class="flex items-center justify-between h-14">
                        <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2 shrink-0">
                            <x-application-logo class="h-8 w-8" />
                            <span class="text-lg font-extrabold text-gray-800">Ngekos<span class="text-teal-600">.in</span></span>
                        </a>

                        <!-- Desktop nav -->
                        <nav class="hidden sm:flex items-center gap-1">
                            <a href="{{ route('kos.index') }}" wire:navigate
                               class="px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('kos.*') ? 'text-teal-600 bg-teal-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                                Cari Kos
                            </a>
                            <a href="{{ route('bantuan') }}" wire:navigate
                               class="px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('bantuan') ? 'text-teal-600 bg-teal-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                                Bantuan
                            </a>
                            @auth
                                @php $unread = auth()->user()->pesanBelumDibaca(); @endphp
                                <a href="{{ route('chat.index') }}" wire:navigate
                                   class="relative px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('chat.*') ? 'text-teal-600 bg-teal-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                                    Pesan
                                    @if ($unread > 0)
                                        <span class="absolute -top-0.5 -right-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">
                                            {{ $unread > 9 ? '9+' : $unread }}
                                        </span>
                                    @endif
                                </a>
                                @if (auth()->user()->hasRole('pemilik'))
                                    <a href="{{ route('pemilik.properti') }}" wire:navigate
                                       class="px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('pemilik.properti*') ? 'text-teal-600 bg-teal-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                                        Kelola Kos
                                    </a>
                                @endif
                                <div class="ms-2 flex items-center gap-2">
                                    <a href="{{ route('dashboard') }}" wire:navigate
                                       class="inline-flex items-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500 transition shadow-sm">
                                        Dashboard
                                    </a>
                                    <x-user-avatar size="sm" />
                                </div>
                            @else
                                <a href="{{ route('login') }}" wire:navigate
                                   class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100">
                                    Masuk
                                </a>
                                <a href="{{ route('register') }}" wire:navigate
                                   class="inline-flex items-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500 transition shadow-sm">
                                    Daftar
                                </a>
                            @endauth
                        </nav>

                        <!-- Mobile hamburger -->
                        <button type="button" @click="open = !open"
                            class="sm:hidden flex items-center justify-center h-10 w-10 rounded-xl text-gray-500 hover:bg-gray-100 transition">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path :class="{'hidden': open}" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                <path :class="{'hidden': !open}" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile dropdown -->
                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
                     class="sm:hidden border-t border-gray-100 bg-white shadow-lg">
                    <div class="px-4 py-3 space-y-1">
                        <a href="{{ route('kos.index') }}" wire:navigate @click="open = false"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('kos.*') ? 'text-teal-600 bg-teal-50' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                            Cari Kos
                        </a>
                        <a href="{{ route('bantuan') }}" wire:navigate @click="open = false"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('bantuan') ? 'text-teal-600 bg-teal-50' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" /></svg>
                            Bantuan
                        </a>
                        @auth
                            <a href="{{ route('chat.index') }}" wire:navigate @click="open = false"
                               class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('chat.*') ? 'text-teal-600 bg-teal-50' : 'text-gray-600 hover:bg-gray-50' }}">
                                <span class="flex items-center gap-3">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                                    Pesan
                                </span>
                                @if (auth()->user()->pesanBelumDibaca() > 0)
                                    <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1.5 text-[10px] font-bold text-white">
                                        {{ auth()->user()->pesanBelumDibaca() > 9 ? '9+' : auth()->user()->pesanBelumDibaca() }}
                                    </span>
                                @endif
                            </a>
                            @if (auth()->user()->hasRole('pemilik'))
                                <a href="{{ route('pemilik.properti') }}" wire:navigate @click="open = false"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('pemilik.*') ? 'text-teal-600 bg-teal-50' : 'text-gray-600 hover:bg-gray-50' }}">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
                                    Kelola Kos
                                </a>
                            @endif
                        @endauth
                    </div>
                    <div class="px-4 pb-4 pt-2 border-t border-gray-100 space-y-2">
                        @auth
                            <a href="{{ route('dashboard') }}" wire:navigate @click="open = false"
                               class="flex items-center justify-center gap-2 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-500 transition">
                                <x-user-avatar size="xs" />
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" wire:navigate @click="open = false"
                               class="flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">
                                Masuk
                            </a>
                            <a href="{{ route('register') }}" wire:navigate @click="open = false"
                               class="flex items-center justify-center rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-500 transition">
                                Daftar Sekarang
                            </a>
                        @endauth
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 pb-20 sm:pb-0">
                {{ $slot }}
            </main>

            <!-- Footer (hidden on mobile when bottom nav is shown) -->
            <footer class="hidden sm:block bg-white border-t border-gray-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-3">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                        <p class="text-sm text-gray-500">&copy; {{ date('Y') }} {{ \App\Models\Pengaturan::namaSitus() }} &mdash; {{ \App\Models\Pengaturan::deskripsiSitus() }}</p>
                        @auth
                            <a href="{{ route('dashboard') }}" wire:navigate class="text-sm font-medium text-teal-600 hover:text-teal-500">Masuk ke dashboard</a>
                        @else
                            <a href="{{ route('register') }}" wire:navigate class="text-sm font-medium text-teal-600 hover:text-teal-500">Daftarkan kos Anda</a>
                        @endauth
                    </div>
                    @php
                        $emailKontak = \App\Models\Pengaturan::ambil('situs.email');
                        $teleponKontak = \App\Models\Pengaturan::ambil('situs.telepon');
                        $alamatKontak = \App\Models\Pengaturan::ambil('situs.alamat');
                    @endphp
                    @if ($emailKontak || $teleponKontak || $alamatKontak)
                        <p class="text-xs text-gray-400 text-center sm:text-left">
                            Kontak:
                            @if ($alamatKontak)<span class="ms-1">{{ $alamatKontak }}</span>@endif
                            @if ($teleponKontak)<span class="ms-1">&bull; {{ $teleponKontak }}</span>@endif
                            @if ($emailKontak)<span class="ms-1">&bull; <a href="mailto:{{ $emailKontak }}" class="hover:text-teal-600">{{ $emailKontak }}</a></span>@endif
                        </p>
                    @endif
                </div>
            </footer>

            <!-- Bottom Navigation (Mobile only) -->
            <x-bottom-nav />
        </div>
        @endif

        <livewire:chatbot />
    </body>
</html>
