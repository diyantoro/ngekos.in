@props(['fitur' => []])

@php
    use App\Services\SubscriptionService;

    $user = auth()->user();
    $plan = $user ? SubscriptionService::getPlan($user) : 'free';
    $isPremium = in_array($plan, ['pro', 'business'], true);

    $defaultFitur = [
        ['ikon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z', 'label' => 'Laporan premium & ekspor PDF/Excel'],
        ['ikon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z', 'label' => 'Perbandingan performa antar properti'],
        ['ikon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => 'Tagihan & denda otomatis tiap bulan'],
        ['ikon' => 'M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819', 'label' => 'Hingga 5 properti & 100 kamar'],
        ['ikon' => 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7A9 9 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0', 'label' => 'Broadcast pengumuman ke semua penyewa'],
        ['ikon' => 'M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z', 'label' => 'Prioritas di pencarian & notifikasi calon penyewa'],
    ];
    $fitur = filled($fitur) ? $fitur : $defaultFitur;
@endphp

@if ($isPremium)
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-600 dark:from-violet-700 dark:via-purple-700 dark:to-fuchsia-700 shadow-lg shadow-violet-500/10 ring-1 ring-white/15">
        <div class="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/15 blur-3xl"></div>
        <div class="absolute -left-12 bottom-0 h-32 w-48 rounded-full bg-white/10 blur-2xl"></div>

        <div class="relative px-5 py-5 sm:px-7 sm:py-6">
            <div class="flex flex-col lg:flex-row lg:items-center gap-5">
                <div class="min-w-0 flex-1">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-white ring-1 ring-white/25 backdrop-blur">
                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" /></svg>
                        Paket {{ strtoupper($plan) }} Aktif
                    </span>
                    <h3 class="mt-2 text-lg sm:text-xl font-extrabold tracking-tight text-white drop-shadow-sm">Fitur premium Anda sudah aktif</h3>
                    <p class="mt-1 text-xs sm:text-sm text-white/85 max-w-xl leading-relaxed">
                        Nikmati semua keunggulan premium untuk mengelola kos Anda lebih maksimal.
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="{{ route('langganan.subscription') }}" wire:navigate
                            class="inline-flex items-center gap-1.5 rounded-xl bg-white px-4 py-2 text-xs font-bold text-violet-700 shadow-sm hover:bg-white/90 transition">
                            Kelola Paket
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12l-7.5 7.5M21 12H3" /></svg>
                        </a>
                    </div>
                </div>

                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach ($fitur as $f)
                        <div class="rounded-2xl bg-white/10 ring-1 ring-white/15 backdrop-blur px-3 py-3">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-white/15 text-white">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $f['ikon'] }}" /></svg>
                            </span>
                            <p class="mt-2 text-xs font-semibold leading-snug text-white/90">{{ $f['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@else
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-600 dark:from-violet-700 dark:via-purple-700 dark:to-fuchsia-700 shadow-lg shadow-violet-500/10 ring-1 ring-white/15">
        <div class="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/15 blur-3xl"></div>
        <div class="absolute -left-12 bottom-0 h-32 w-48 rounded-full bg-white/10 blur-2xl"></div>

        <div class="relative px-5 py-5 sm:px-7 sm:py-6">
            <div class="flex flex-col lg:flex-row lg:items-center gap-5">
                <div class="min-w-0 flex-1">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-white ring-1 ring-white/25 backdrop-blur">
                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" /></svg>
                        Paket Premium Kos
                    </span>
                    <h3 class="mt-2 text-lg sm:text-xl font-extrabold tracking-tight text-white drop-shadow-sm">Naikkan peringkat & keunggulan kos Anda</h3>
                    <p class="mt-1 text-xs sm:text-sm text-white/85 max-w-xl leading-relaxed">
                        Kelola lebih banyak properti, otomatiskan tagihan, dan tarik lebih banyak calon penyewa.
                    </p>
                    <div class="mt-3 flex flex-wrap items-center gap-3">
                        <a href="{{ route('langganan.plans') }}" wire:navigate
                            class="inline-flex items-center gap-1.5 rounded-xl bg-white px-4 py-2 text-xs font-bold text-violet-700 shadow-sm hover:bg-white/90 transition">
                            Upgrade ke Pro
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12l-7.5 7.5M21 12H3" /></svg>
                        </a>
                        <span class="text-[11px] font-semibold text-white/80">Mulai Rp49.000/bulan</span>
                    </div>
                </div>

                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach ($fitur as $f)
                        <div class="rounded-2xl bg-white/10 ring-1 ring-white/15 backdrop-blur px-3 py-3">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-white/15 text-white">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $f['ikon'] }}" /></svg>
                            </span>
                            <p class="mt-2 text-xs font-semibold leading-snug text-white/90">{{ $f['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif