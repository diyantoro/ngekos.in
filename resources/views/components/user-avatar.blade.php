@props(['user' => null, 'size' => 'md'])

@php
    $u = $user ?? auth()->user();
    $sizes = [
        'xs' => 'h-6 w-6 text-[10px]',
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-14 w-14 text-base',
        'xl' => 'h-20 w-20 text-xl',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

@if ($u?->avatar_url)
    <img src="{{ $u->avatar_url }}" alt="{{ $u->nama }}" {{ $attributes->merge(['class' => "$sizeClass rounded-full object-cover"]) }}>
@else
    <span {{ $attributes->merge(['class' => "$sizeClass inline-flex items-center justify-center rounded-full bg-gradient-to-br from-teal-500 to-emerald-500 font-bold text-white"]) }}>
        {{ $u?->inisial ?? '?' }}
    </span>
@endif
