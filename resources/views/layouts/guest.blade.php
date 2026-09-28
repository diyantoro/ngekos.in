<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ \App\Models\Pengaturan::namaSitus() }}</title>

        <!-- Favicon -->
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="48x48">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <script>
            (function () {
                var t = localStorage.getItem('theme');
                var gelap = t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', gelap);
            })();
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .pb-safe { padding-bottom: env(safe-area-inset-bottom, 0px); }
        </style>
    </head>
    <body class="font-sans text-gray-900 dark:text-gray-100 antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-2">
            <!-- Branding Panel -->
            <div class="hidden lg:flex flex-col justify-between bg-teal-800 p-12">
                <a href="/" wire:navigate class="relative flex items-center gap-3">
                    <x-application-logo class="h-12 w-12" />
                    <span class="text-2xl font-extrabold text-white">Ngekos.in</span>
                </a>

                <div class="relative">
                    <h1 class="text-4xl font-extrabold text-white leading-tight">
                        Cari kos sampai bayar,<br>semua di satu tempat
                    </h1>
                    <p class="mt-4 text-teal-100 max-w-md">
                        Cari kamar, chat pemilik langsung, pantau tagihan, dan bayar kos tanpa ribet.
                    </p>

                    <ul class="mt-8 space-y-4">
                        @foreach ([
                            'Chat pemilik kamar langsung',
                            'Tagihan &amp; pembayaran tercatat rapi',
                            'Dashboard sendiri buat tiap peran',
                        ] as $fitur)
                            <li class="flex items-center gap-3 text-teal-50">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/25">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </span>
                                {!! $fitur !!}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <p class="relative text-sm text-teal-200">&copy; {{ date('Y') }} Ngekos.in</p>
            </div>

            <!-- Form Panel -->
            <div class="flex items-center justify-center px-6 py-12 bg-gray-50 dark:bg-gray-900">
                <div class="w-full max-w-md">
                    <a href="/" wire:navigate class="lg:hidden flex items-center justify-center gap-2.5 mb-8">
                        <x-application-logo class="h-12 w-12" />
                        <span class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">Ngekos.in</span>
                    </a>

                    <button @click="$store.theme.toggle()" type="button" aria-label="Ganti tema"
                        class="lg:hidden ms-auto mb-4 flex items-center justify-center h-10 w-10 rounded-xl text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <svg x-show="!$store.theme.dark" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" /></svg>
                        <svg x-show="$store.theme.dark" x-cloak class="h-5 w-5 text-amber-300" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                    </button>

                    <div class="bg-white dark:bg-gray-800 dark:ring-gray-700 rounded-2xl ring-1 ring-gray-200 dark:ring-gray-700 p-8">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>

        <livewire:chatbot />
    </body>
</html>
