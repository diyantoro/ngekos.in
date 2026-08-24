<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

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
            <header class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-gray-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2.5">
                            <x-application-logo class="h-9 w-9" />
                            <span class="text-lg font-bold text-gray-800">Ngekos<span class="text-teal-600">.in</span></span>
                        </a>

                        <nav class="flex items-center gap-2 sm:gap-4">
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
                    </div>
                </div>
            </header>

            <!-- Main -->
            <main class="flex-1">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p class="text-sm text-gray-500">&copy; {{ date('Y') }} Ngekos.in &mdash; Platform pencari kos &amp; pemilik kos.</p>
                    @auth
                        <a href="{{ route('dashboard') }}" wire:navigate class="text-sm font-medium text-teal-600 hover:text-teal-500">Masuk ke dashboard</a>
                    @else
                        <a href="{{ route('register') }}" wire:navigate class="text-sm font-medium text-teal-600 hover:text-teal-500">Daftarkan kos Anda</a>
                    @endauth
                </div>
            </footer>
        </div>

        <livewire:chatbot />
    </body>
</html>