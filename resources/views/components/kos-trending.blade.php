@props(['limit' => 6])

@php
    $trending = \App\Models\Properti::query()
        ->where('status', 'aktif')
        ->withCount([
            'kamars as total_kamar',
            'kamars as kamar_terisi' => fn ($q) => $q->where('status', 'terisi'),
        ])
        ->orderByDesc('kamar_terisi')
        ->limit($limit)
        ->get()
        ->filter(fn ($p) => $p->kamar_terisi > 0);
@endphp

@if ($trending->isNotEmpty())
    <div>
        <div class="flex items-end justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-gray-900 dark:text-gray-100">Kos Trending</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kos paling laris &amp; banyak dicari</p>
            </div>
            <a href="{{ route('kos.index') }}" wire:navigate
                class="shrink-0 inline-flex items-center gap-0.5 text-sm font-semibold text-teal-600 hover:text-teal-700 dark:text-teal-400 dark:hover:text-teal-300">
                Lihat Semua
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
            </a>
        </div>

        <div class="mt-4 flex gap-4 overflow-x-auto scrollbar-hide pb-2 -mb-2 snap-x snap-mandatory">
            @foreach ($trending as $p)
                <a href="{{ route('kos.detail', $p) }}" wire:navigate
                    class="group w-[280px] shrink-0 snap-start rounded-2xl bg-white dark:bg-gray-800 ring-1 ring-gray-100 dark:ring-gray-700 shadow-sm overflow-hidden hover:shadow-md transition">
                    <div class="relative h-36 bg-gradient-to-br from-orange-50 to-amber-100 dark:from-orange-500/10 dark:to-amber-500/10 overflow-hidden">
                        @if ($p->foto)
                            <img src="{{ Storage::url($p->foto) }}" alt="{{ $p->nama }}"
                                class="h-full w-full object-cover">
                        @else
                            <div class="h-full w-full flex items-center justify-center">
                                <svg class="h-10 w-10 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                            </div>
                        @endif

                        <span class="absolute top-2 left-2 inline-flex items-center gap-1 rounded-md bg-gradient-to-r from-amber-500 to-orange-500 px-2 py-1 text-[10px] font-extrabold text-white shadow-sm">
                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2l.967 4.602L15 6.5l-3.5 3.198L13.2 14 10 11.5 6.8 14l1.7-4.302L5 6.5l4.033-.898L10 2z" /></svg>
                            Trending
                        </span>

                        <span class="absolute top-2 right-2 rounded-md px-2 py-1 text-[10px] font-bold text-white {{ $p->kamar_tersedia > 0 ? 'bg-emerald-600' : 'bg-gray-800' }}">
                            {{ $p->kamar_tersedia > 0 ? $p->kamar_tersedia . ' Kamar' : 'Penuh' }}
                        </span>
                    </div>

                    <div class="p-3">
                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate group-hover:text-teal-700 dark:group-hover:text-teal-300 transition">{{ $p->nama }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 truncate flex items-center gap-1">
                            <svg class="h-3.5 w-3.5 shrink-0 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                            {{ $p->kota }}{{ $p->alamat ? ', ' . $p->alamat : '' }}
                        </p>
                        <div class="mt-3 flex items-center justify-between rounded-lg bg-teal-50 dark:bg-teal-500/10 px-2.5 py-1.5">
                            <span class="text-[11px] font-bold text-teal-700 dark:text-teal-300">{{ $p->kamar_terisi }}/{{ $p->total_kamar }} terisi</span>
                            <svg class="h-3.5 w-3.5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" /></svg>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endif