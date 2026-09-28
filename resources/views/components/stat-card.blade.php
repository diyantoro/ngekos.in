@props(['label', 'value', 'icon', 'tone' => 'teal', 'hint' => null, 'href' => null])

@php
    // Disederhanakan: hanya 3 rumpun nada agar dashboard terlihat dikerjakan
    // manusia, bukan template AI warna-warni.
    $iconTones = [
        'teal' => 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300',
        'cyan' => 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300',
        'emerald' => 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300',
        'sky' => 'bg-stone-100 text-stone-600 dark:bg-gray-700 dark:text-gray-300',
        'amber' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
        'rose' => 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-300',
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" wire:navigate class="block card group p-5 flex items-center gap-4 transition hover:shadow-card-hover">
@else
    <div class="card p-5 flex items-center gap-4">
@endif
    <div class="shrink-0 h-11 w-11 rounded-lg flex items-center justify-center {{ $iconTones[$tone] ?? $iconTones['teal'] }}">
        {!! $icon !!}
    </div>
    <div class="min-w-0 flex-1">
        <p class="truncate text-xs font-medium text-slate-500 dark:text-gray-400">{{ $label }}</p>
        <p class="mt-0.5 text-xl font-bold text-slate-900 dark:text-gray-100 break-words leading-snug tracking-tight">{{ $value }}</p>
        @if ($hint)
            <p class="mt-1 flex items-center gap-1 text-xs text-slate-500 dark:text-gray-400">{!! $hint !!}</p>
        @endif
    </div>
    @if ($href)
        <svg class="ml-auto h-5 w-5 shrink-0 text-slate-300 dark:text-gray-600 group-hover:text-brand-600 transition" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
    @endif
@if ($href)
    </a>
@else
</div>
@endif
