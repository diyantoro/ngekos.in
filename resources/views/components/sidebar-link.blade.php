@props(['active', 'color' => 'teal'])

@php
// Sidebar terang yang tenang: teks slate, aktif = latar brand-50 + teks brand-800.
// Parameter $color dipertahankan agar pemanggil lama tidak error, tapi tidak lagi
// dipakai untuk gradasi warna-warni.
$isActive = (bool) ($active ?? false);

$activeClasses = $isActive
    ? 'inline-flex w-full items-center gap-3 rounded-lg bg-brand-50 px-3 py-2.5 text-sm font-semibold text-brand-800 ring-1 ring-inset ring-brand-100 dark:bg-brand-500/10 dark:text-brand-200 dark:ring-brand-500/20 focus:outline-none transition relative'
    : 'inline-flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 dark:text-gray-300 hover:bg-stone-100 dark:hover:bg-gray-800 hover:text-slate-900 dark:hover:text-white focus:outline-none transition relative';
@endphp

<a {{ $attributes->merge(['class' => $activeClasses]) }} wire:navigate>
    @if ($isActive)
        <span class="absolute left-0 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r-full bg-brand-700 dark:bg-brand-400"></span>
    @endif

    <span class="relative shrink-0 {{ $isActive ? 'text-brand-700 dark:text-brand-300' : 'text-slate-400 dark:text-gray-500' }}">{{ $slot }}</span>
    <span class="relative flex-1 truncate">{{ $label ?? '' }}</span>
</a>
