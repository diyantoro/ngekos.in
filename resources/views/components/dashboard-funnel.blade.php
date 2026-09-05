@props([
    'stages' => [],
    'title' => 'Pipeline',
    'subtitle' => 'Tahapan kunjungan hingga pembayaran lunas',
])

@php
    $nilaiMaks = max(array_column($stages, 'nilai'));
    $fmt = fn ($n) => number_format((int) $n, 0, ',', '.');
    $gradients = ['from-teal-500 to-emerald-500', 'from-emerald-500 to-green-500', 'from-cyan-500 to-teal-500', 'from-teal-500 to-cyan-600'];
@endphp

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card ring-1 ring-gray-100 dark:ring-gray-700 p-5">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">{{ $title }}</h3>
            <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">{{ $subtitle }}</p>
        </div>
        <span class="shrink-0 inline-flex items-center gap-1.5 rounded-full bg-teal-50 dark:bg-teal-500/10 px-2.5 py-1 text-[10px] font-bold text-teal-600 dark:text-teal-400">
            <span class="h-1.5 w-1.5 rounded-full bg-teal-500 animate-pulse"></span> Live
        </span>
    </div>

    <div class="mt-6 space-y-4" x-data="{ tunjuk: null }" @mouseleave="tunjuk = null">
        @foreach ($stages as $i => $s)
            @php
                $pct = $nilaiMaks > 0 ? ($s['nilai'] / $nilaiMaks) * 100 : 4;
                $pctTampil = $pct < 3 ? 3 : $pct;
                $prev = $i > 0 ? $stages[$i - 1]['nilai'] : null;
                $konversi = $prev && $prev > 0 ? round(($s['nilai'] / $prev) * 100) : null;
                $grad = $gradients[$i % count($gradients)];
            @endphp
            <div class="group">
                <div class="flex items-center justify-between gap-3 mb-1.5">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="flex items-center justify-center h-6 w-6 shrink-0 rounded-lg bg-teal-50 dark:bg-teal-500/10 text-teal-600 dark:text-teal-400 text-[10px] font-extrabold">{{ $i + 1 }}</span>
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">{{ $s['label'] }}</span>
                        @if (! empty($s['sub']))
                            <span class="hidden md:inline text-[10px] text-gray-400 dark:text-gray-500 truncate">{{ $s['sub'] }}</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if ($konversi !== null)
                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-[10px] font-semibold text-gray-500 dark:text-gray-400" title="{{ $s['label'] }} / tahap sebelumnya">
                                <svg class="h-3 w-3 text-teal-500" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3" /></svg>
                                {{ $konversi }}%
                            </span>
                        @endif
                        <span class="text-sm font-extrabold text-gray-900 dark:text-gray-100 tabular-nums">{{ $fmt($s['nilai']) }}</span>
                    </div>
                </div>
                <div class="h-3.5 rounded-full bg-gray-100 dark:bg-gray-700/50 overflow-hidden group-hover:ring-2 group-hover:ring-teal-200 dark:group-hover:ring-teal-800 transition">
                    <div class="h-full rounded-full bg-gradient-to-r {{ $grad }} shadow-[0_0_12px_rgba(20,184,166,0.35)] transition-all duration-500 hover:brightness-110"
                        style="width: {{ $pctTampil }}%; animation: funnelGrow 0.9s cubic-bezier(0.22, 1, 0.36, 1) both; animation-delay: {{ $i * 0.12 }}s;"></div>
                </div>
            </div>
        @endforeach
    </div>

    <style>
        @keyframes funnelGrow { from { width: 0; } }
    </style>
</div>