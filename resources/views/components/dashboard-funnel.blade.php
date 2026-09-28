@props([
    'stages' => [],
    'title' => 'Pipeline',
    'subtitle' => 'Tahapan kunjungan hingga pembayaran lunas',
])

@php
    $nilaiMaks = max(array_column($stages, 'nilai'));
    $fmt = fn ($n) => number_format((int) $n, 0, ',', '.');
    $bars = ['bg-brand-700', 'bg-brand-600', 'bg-brand-500', 'bg-brand-400'];
@endphp

<div class="card p-5">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-gray-100">{{ $title }}</h3>
            <p class="mt-0.5 text-xs text-slate-400 dark:text-gray-500">{{ $subtitle }}</p>
        </div>
        <span class="shrink-0 inline-flex items-center gap-1.5 rounded-full bg-brand-50 dark:bg-brand-500/10 px-2.5 py-1 text-[10px] font-bold text-brand-700 dark:text-brand-300">
            <span class="h-1.5 w-1.5 rounded-full bg-brand-600"></span> Live
        </span>
    </div>

    <div class="mt-6 space-y-4" x-data="{ tunjuk: null }" @mouseleave="tunjuk = null">
        @foreach ($stages as $i => $s)
            @php
                $pct = $nilaiMaks > 0 ? ($s['nilai'] / $nilaiMaks) * 100 : 4;
                $pctTampil = $pct < 3 ? 3 : $pct;
                $prev = $i > 0 ? $stages[$i - 1]['nilai'] : null;
                $konversi = $prev && $prev > 0 ? round(($s['nilai'] / $prev) * 100) : null;
                $bar = $bars[$i % count($bars)];
            @endphp
            <div class="group">
                <div class="flex items-center justify-between gap-3 mb-1.5">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="flex items-center justify-center h-6 w-6 shrink-0 rounded-full bg-brand-50 dark:bg-brand-500/10 text-brand-700 dark:text-brand-300 text-[10px] font-bold">{{ $i + 1 }}</span>
                        <span class="text-sm font-semibold text-slate-800 dark:text-gray-100 truncate">{{ $s['label'] }}</span>
                        @if (! empty($s['sub']))
                            <span class="hidden md:inline text-[10px] text-slate-400 dark:text-gray-500 truncate">{{ $s['sub'] }}</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if ($konversi !== null)
                            <span class="inline-flex items-center gap-1 rounded-full bg-stone-100 dark:bg-gray-700 px-2 py-0.5 text-[10px] font-semibold text-slate-500 dark:text-gray-400" title="{{ $s['label'] }} / tahap sebelumnya">
                                <svg class="h-3 w-3 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3" /></svg>
                                {{ $konversi }}%
                            </span>
                        @endif
                        <span class="text-sm font-bold text-slate-900 dark:text-gray-100 tabular-nums">{{ $fmt($s['nilai']) }}</span>
                    </div>
                </div>
                <div class="h-3 rounded-full bg-stone-200 dark:bg-gray-700/50 overflow-hidden">
                    <div class="h-full rounded-full {{ $bar }}"
                        style="width: {{ $pctTampil }}%;"></div>
                </div>
            </div>
        @endforeach
    </div>
</div>