@props(['fitur' => []])

@php
    $defaultFitur = [
        ['ikon' => 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25', 'label' => 'Tampil di halaman depan untuk dijangkau lebih banyak pencari kos'],
        ['ikon' => 'M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228', 'label' => 'Statistik pengunjung & minat calon penyewa'],
        ['ikon' => 'M3 3v1.5M3 21v-6m0 0l2.77-.693a9 9 0 016.208.682l.108.054a9 9 0 006.086.71l3.114-.732a48.524 48.524 0 01-.005-10.499l-3.11.732a9 9 0 01-6.085-.711l-.108-.054a9 9 0 00-6.208-.682L3 4.5M3 15V4.5', 'label' => 'Prioritas saat pencarian & notifikasi calon penyewa'],
    ];
    $fitur = filled($fitur) ? $fitur : $defaultFitur;
@endphp

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
                <h3 class="mt-2 text-lg sm:text-xl font-extrabold tracking-tight text-white drop-shadow-sm">Naikkan peringkat kos Anda</h3>
                <p class="mt-1 text-xs sm:text-sm text-white/85 max-w-xl leading-relaxed">
                    Fitur promosi khusus untuk kos agar lebih mudah ditemukan pencari kos di sekitar Anda.
                </p>
                <p class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-white/15 px-2.5 py-1 text-[10px] font-bold text-white ring-1 ring-white/25 backdrop-blur">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Segera hadir
                </p>
            </div>

            <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-3">
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