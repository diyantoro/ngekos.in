@props(['label', 'value', 'icon', 'tone' => 'teal'])

@php
    $tones = [
        'teal' => 'bg-teal-50 text-teal-600',
        'cyan' => 'bg-cyan-50 text-cyan-600',
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'sky' => 'bg-sky-50 text-sky-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'rose' => 'bg-rose-50 text-rose-600',
    ];
@endphp

<div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-5 flex items-center gap-4 transition hover:shadow-md">
    <div class="shrink-0 h-12 w-12 rounded-xl flex items-center justify-center {{ $tones[$tone] }}">
        {!! $icon !!}
    </div>
    <div class="min-w-0">
        <p class="truncate text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $label }}</p>
        <p class="mt-0.5 text-2xl font-bold text-gray-900">{{ $value }}</p>
    </div>
</div>