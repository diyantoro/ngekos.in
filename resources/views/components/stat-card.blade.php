@props(['label', 'value', 'icon', 'tone' => 'teal', 'hint' => null, 'href' => null])

@php
    $tones = [
        'teal' => 'from-teal-500 to-emerald-500',
        'cyan' => 'from-cyan-500 to-sky-500',
        'emerald' => 'from-emerald-500 to-green-500',
        'sky' => 'from-sky-500 to-blue-500',
        'amber' => 'from-amber-500 to-orange-500',
        'rose' => 'from-rose-500 to-pink-500',
    ];
    $shadows = [
        'teal' => 'shadow-teal-500/20',
        'cyan' => 'shadow-cyan-500/20',
        'sky' => 'shadow-sky-500/20',
        'emerald' => 'shadow-emerald-500/20',
        'amber' => 'shadow-amber-500/20',
        'rose' => 'shadow-rose-500/20',
    ];
    $bgTones = [
        'teal' => 'bg-teal-50 text-teal-600 dark:bg-teal-900/40 dark:text-teal-300',
        'cyan' => 'bg-cyan-50 text-cyan-600 dark:bg-cyan-900/40 dark:text-cyan-300',
        'emerald' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-300',
        'sky' => 'bg-sky-50 text-sky-600 dark:bg-sky-900/40 dark:text-sky-300',
        'amber' => 'bg-amber-50 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300',
        'rose' => 'bg-rose-50 text-rose-600 dark:bg-rose-900/40 dark:text-rose-300',
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" wire:navigate class="block card group hover:shadow-card-hover hover:-translate-y-0.5 p-5 flex items-center gap-4 transition cursor-pointer">
@else
    <div class="card group hover:shadow-card-hover hover:-translate-y-0.5 p-5 flex items-center gap-4">
@endif
    <div class="shrink-0 h-12 w-12 rounded-xl flex items-center justify-center bg-gradient-to-br {{ $tones[$tone] }} text-white shadow-lg {{ $shadows[$tone] ?? 'shadow-teal-500/20' }} transition-transform duration-300 group-hover:scale-110">
        {!! $icon !!}
    </div>
    <div class="min-w-0 flex-1">
        <p class="truncate text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $label }}</p>
        <p class="mt-0.5 text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-gray-100 break-words leading-snug">{{ $value }}</p>
        @if ($hint)
            <p class="mt-0.5 flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">{!! $hint !!}</p>
        @endif
    </div>
    @if ($href)
        <svg class="ml-auto h-5 w-5 shrink-0 text-gray-300 dark:text-gray-600 group-hover:text-teal-500 transition" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
    @endif
@if ($href)
    </a>
@else
</div>
@endif