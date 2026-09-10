@props(['fotos' => [], 'nama' => 'Foto kos', 'kelas' => 'h-56 sm:h-72', 'ringkas' => false])

@php
    $daftar = collect($fotos)->filter()->values()->all();
@endphp

<div x-data="{ aktif: 0, total: {{ count($daftar) }} }" class="relative {{ $kelas }} bg-gradient-to-br from-teal-100 via-emerald-100 to-cyan-100 dark:from-teal-500/20 dark:via-emerald-500/20 dark:to-cyan-500/20 overflow-hidden group">
    @if (count($daftar) > 0)
        <div class="flex h-full w-full overflow-x-auto snap-x snap-mandatory scrollbar-hide"
            x-ref="track"
            @scroll.debounce.100ms="aktif = Math.round($el.scrollLeft / $el.clientWidth)">
            @foreach ($daftar as $foto)
                <div class="h-full w-full shrink-0 snap-center">
                    <img src="{{ $foto }}" alt="{{ $nama }}" class="h-full w-full object-cover" loading="lazy">
                </div>
            @endforeach
        </div>

        @if (count($daftar) > 1)
            @if (! $ringkas)
            <button type="button" @click="$refs.track.scrollBy({ left: -$refs.track.clientWidth, behavior: 'smooth' })"
                class="absolute left-2 top-1/2 -translate-y-1/2 h-8 w-8 rounded-full bg-black/40 text-white backdrop-blur-sm hover:bg-black/60 transition flex items-center justify-center sm:opacity-0 sm:group-hover:opacity-100" aria-label="Sebelumnya">
                <svg class="h-4 w-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </button>
            <button type="button" @click="$refs.track.scrollBy({ left: $refs.track.clientWidth, behavior: 'smooth' })"
                class="absolute right-2 top-1/2 -translate-y-1/2 h-8 w-8 rounded-full bg-black/40 text-white backdrop-blur-sm hover:bg-black/60 transition flex items-center justify-center sm:opacity-0 sm:group-hover:opacity-100" aria-label="Berikutnya">
                <svg class="h-4 w-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
            </button>

            <div class="absolute bottom-2 inset-x-0 flex items-center justify-center gap-1.5">
                @foreach ($daftar as $i => $foto)
                    <button type="button" @click="$refs.track.scrollTo({ left: {{ $i }} * $refs.track.clientWidth, behavior: 'smooth' })"
                        :class="aktif === {{ $i }} ? 'w-5 bg-white' : 'w-1.5 bg-white/60'"
                        class="h-1.5 rounded-full transition-all" aria-label="Foto {{ $i + 1 }}"></button>
                @endforeach
            </div>
            @endif

            <span class="absolute top-2 right-2 rounded-full bg-black/50 px-2 py-0.5 text-[10px] font-bold text-white backdrop-blur-sm">
                <span x-text="aktif + 1"></span>/{{ count($daftar) }}
            </span>
        @endif
    @else
        <div class="h-full w-full flex items-center justify-center">
            <svg class="h-20 w-20 text-teal-300 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
        </div>
    @endif

    {{ $slot }}
</div>
