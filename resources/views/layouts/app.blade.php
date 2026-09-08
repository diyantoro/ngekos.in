<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0d9488">
        <meta name="gmaps-key" content="{{ config('services.google_maps.key') }}">

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
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .pb-safe { padding-bottom: env(safe-area-inset-bottom, 0px); }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-950 dark:text-gray-100">
        <livewire:layout.navigation />

        <div class="min-h-screen lg:pl-64">
            @if (isset($header))
                <header class="bg-white dark:bg-gray-900 shadow-sm border-b border-gray-100/80 dark:border-gray-800">
                    <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main class="pb-20 lg:pb-0">
                {{ $slot }}
            </main>

            <!-- Bottom Navigation (Mobile) -->
            @auth
                <x-bottom-nav />
            @endauth
        </div>

        @stack('scripts')
        <livewire:chatbot />
    </body>
</html>
