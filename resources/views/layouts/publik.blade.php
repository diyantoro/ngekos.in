<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ \App\Models\Pengaturan::namaSitus() }}</title>

        <!-- Favicon -->
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="48x48">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen bg-gray-50 flex flex-col">
            <!-- Header -->
            <header x-data="{ open: false }" class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-gray-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2.5 min-w-0">
                            <x-application-logo class="h-9 w-9 shrink-0" />
                            <x-brand-name class="text-lg font-bold text-gray-800 truncate" />
                        </a>

                        <nav class="hidden sm:flex items-center gap-2 sm:gap-4">
                            <a href="{{ route('kos.index') }}" wire:navigate
                               class="px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('kos.*') ? 'text-teal-600 bg-teal-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                                Cari Kos
                            </a>
                            <a href="{{ route('bantuan') }}" wire:navigate
                               class="px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('bantuan') ? 'text-teal-600 bg-teal-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                                Bantuan
                            </a>

                            @auth
                                @php
                                    $pesanBelumDibaca = auth()->user()->pesanBelumDibaca();
                                @endphp
                                <a href="{{ route('chat.index') }}" wire:navigate
                                   class="relative px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('chat.*') ? 'text-teal-600 bg-teal-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                                    Pesan
                                    @if ($pesanBelumDibaca > 0)
                                        <span class="absolute -top-1 -right-1 inline-flex items-center justify-center h-4 min-w-4 px-1 rounded-full bg-rose-500 text-[10px] font-bold text-white">
                                            {{ $pesanBelumDibaca > 9 ? '9+' : $pesanBelumDibaca }}
                                        </span>
                                    @endif
                                </a>
                            @endauth

                            @auth
                                @if (auth()->user()->hasRole('pemilik'))
                                    <a href="{{ route('pemilik.properti') }}" wire:navigate
                                       class="px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('pemilik.properti*') ? 'text-teal-600 bg-teal-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                                        Kelola Kos
                                    </a>
                                @endif
                                <a href="{{ route('dashboard') }}" wire:navigate
                                   class="inline-flex items-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500 transition">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" wire:navigate
                                   class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100">
                                    Masuk
                                </a>
                                <a href="{{ route('register') }}" wire:navigate
                                   class="inline-flex items-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500 transition">
                                    Daftar
                                </a>
                            @endauth
                        </nav>

                        <!-- Hamburger (mobile) -->
                        <button type="button" @click="open = ! open"
                            class="sm:hidden inline-flex items-center justify-center h-10 w-10 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile menu -->
                <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-gray-100 bg-white">
                    <div class="px-4 pt-3 pb-4 space-y-1">
                        <a href="{{ route('kos.index') }}" wire:navigate @click="open = false"
                           class="block px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('kos.*') ? 'text-teal-600 bg-teal-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                            Cari Kos
                        </a>
                        <a href="{{ route('bantuan') }}" wire:navigate @click="open = false"
                           class="block px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('bantuan') ? 'text-teal-600 bg-teal-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                            Bantuan
                        </a>
                        @auth
                            <a href="{{ route('chat.index') }}" wire:navigate @click="open = false"
                               class="flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('chat.*') ? 'text-teal-600 bg-teal-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                                <span>Pesan</span>
                                @if (auth()->user()->pesanBelumDibaca() > 0)
                                    <span class="inline-flex items-center justify-center h-4 min-w-4 px-1 rounded-full bg-rose-500 text-[10px] font-bold text-white">
                                        {{ auth()->user()->pesanBelumDibaca() > 9 ? '9+' : auth()->user()->pesanBelumDibaca() }}
                                    </span>
                                @endif
                            </a>
                            @if (auth()->user()->hasRole('pemilik'))
                                <a href="{{ route('pemilik.properti') }}" wire:navigate @click="open = false"
                                   class="block px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('pemilik.properti*') ? 'text-teal-600 bg-teal-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                                    Kelola Kos
                                </a>
                            @endif
                        @endauth
                    </div>
                    <div class="px-4 pb-4 pt-3 border-t border-gray-100 space-y-2">
                        @auth
                            <a href="{{ route('dashboard') }}" wire:navigate @click="open = false"
                               class="flex items-center justify-center rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-500 transition">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" wire:navigate @click="open = false"
                               class="flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">
                                Masuk
                            </a>
                            <a href="{{ route('register') }}" wire:navigate @click="open = false"
                               class="flex items-center justify-center rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-500 transition">
                                Daftar
                            </a>
                        @endauth
                    </div>
                </div>
            </header>

            <!-- Main -->
            <main class="flex-1">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-100">
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
        </div>

        <livewire:chatbot />
    </body>
</html>