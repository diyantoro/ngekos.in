<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Akses Ditolak | Ngekos.in</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-gray-100">
            <p class="text-6xl font-extrabold text-teal-600">403</p>
            <h1 class="mt-3 text-xl font-bold">Akses Ditolak</h1>
            <p class="mt-2 text-sm text-gray-500">
                {{ $exception?->getMessage() ?: 'Akun Anda tidak memiliki hak akses ke halaman ini.' }}
                Silakan kembali ke dashboard sesuai peran Anda.
            </p>
            <div class="mt-6 flex flex-col gap-2">
                @auth
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-teal-500 transition">
                        Ke Dashboard Saya
                    </a>
                @endauth
                <a href="{{ route('home') }}"
                    class="inline-flex items-center justify-center rounded-xl px-5 py-2.5 text-sm font-semibold text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50 transition">
                    Ke Beranda
                </a>
            </div>
        </div>
    </div>
</body>
</html>
