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
        <div class="min-h-screen lg:grid lg:grid-cols-2">
            <!-- Branding Panel -->
            <div class="hidden lg:flex flex-col justify-between bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-600 p-12 relative overflow-hidden">
                <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full bg-white/10 blur-2xl"></div>
                <div class="absolute -bottom-32 -left-16 h-80 w-80 rounded-full bg-white/10 blur-3xl"></div>

                <a href="/" wire:navigate class="relative flex items-center gap-3">
                    <x-application-logo class="h-12 w-12" />
                    <span class="text-2xl font-extrabold text-white">Ngekos<span class="text-teal-200">.in</span></span>
                </a>

                <div class="relative">
                    <h1 class="text-4xl font-extrabold text-white leading-tight">
                        Kelola Kos Lebih<br>Mudah &amp; Terorganisir
                    </h1>
                    <p class="mt-4 text-teal-100 max-w-md">
                        Cari kamar, ajukan booking, pantau tagihan, dan verifikasi pembayaran semua dalam satu aplikasi.
                    </p>

                    <ul class="mt-8 space-y-4">
                        <li class="flex items-center gap-3 text-teal-50">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/25">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </span>
                            Booking kamar dalam beberapa klik
                        </li>
                        <li class="flex items-center gap-3 text-teal-50">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/25">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </span>
                            Pantau tagihan &amp; pembayaran real-time
                        </li>
                        <li class="flex items-center gap-3 text-teal-50">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/25">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </span>
                            Dashboard terpisah untuk setiap peran
                        </li>
                    </ul>
                </div>

                <p class="relative text-sm text-teal-200">&copy; {{ date('Y') }} Ngekos.in &mdash; Sistem Manajemen Kos &amp; Sewa Kamar</p>
            </div>

            <!-- Form Panel -->
            <div class="flex items-center justify-center px-6 py-12 bg-gray-50">
                <div class="w-full max-w-md">
                    <a href="/" wire:navigate class="lg:hidden flex items-center justify-center gap-2.5 mb-8">
                        <x-application-logo class="h-12 w-12" />
                        <span class="text-2xl font-extrabold text-gray-800">Ngekos<span class="text-teal-600">.in</span></span>
                    </a>

                    <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/60 ring-1 ring-gray-100 p-8">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>

        <livewire:chatbot />
    </body>
</html>