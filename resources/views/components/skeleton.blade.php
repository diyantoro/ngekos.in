@props([
    'type' => 'card', // card | row | stat
    'count' => 3,
    'class' => '',
    'target' => '',
])

@php
$shapes = [
    'card' => '
        <div class="rounded-2xl border border-gray-100 dark:border-gray-700/80 bg-white dark:bg-gray-800 p-4 shadow-sm overflow-hidden">
            <div class="h-36 rounded-xl bg-gradient-to-r from-gray-100 via-gray-200 to-gray-100 dark:from-gray-700 dark:via-gray-600 dark:to-gray-700 animate-shimmer bg-[length:200%_100%]"></div>
            <div class="mt-4 space-y-3">
                <div class="h-4 w-2/3 rounded-md bg-gradient-to-r from-gray-100 via-gray-200 to-gray-100 dark:from-gray-700 dark:via-gray-600 dark:to-gray-700 animate-shimmer bg-[length:200%_100%]"></div>
                <div class="h-3 w-1/3 rounded-md bg-gradient-to-r from-gray-100 via-gray-200 to-gray-100 dark:from-gray-700 dark:via-gray-600 dark:to-gray-700 animate-shimmer bg-[length:200%_100%]"></div>
                <div class="flex items-end justify-between pt-1">
                    <div class="h-3 w-16 rounded-md bg-gradient-to-r from-gray-100 via-gray-200 to-gray-100 dark:from-gray-700 dark:via-gray-600 dark:to-gray-700 animate-shimmer bg-[length:200%_100%]"></div>
                    <div class="h-5 w-24 rounded-md bg-gradient-to-r from-gray-100 via-gray-200 to-gray-100 dark:from-gray-700 dark:via-gray-600 dark:to-gray-700 animate-shimmer bg-[length:200%_100%]"></div>
                </div>
            </div>
        </div>',
    'row' => '
        <div class="rounded-xl border border-gray-100 dark:border-gray-700/80 bg-white dark:bg-gray-800 px-4 py-3 shadow-sm flex items-center gap-3">
            <div class="h-10 w-10 shrink-0 rounded-xl bg-gradient-to-r from-gray-100 via-gray-200 to-gray-100 dark:from-gray-700 dark:via-gray-600 dark:to-gray-700 animate-shimmer bg-[length:200%_100%]"></div>
            <div class="flex-1 space-y-2">
                <div class="h-3 w-1/2 rounded-md bg-gradient-to-r from-gray-100 via-gray-200 to-gray-100 dark:from-gray-700 dark:via-gray-600 dark:to-gray-700 animate-shimmer bg-[length:200%_100%]"></div>
                <div class="h-2.5 w-1/3 rounded-md bg-gradient-to-r from-gray-100 via-gray-200 to-gray-100 dark:from-gray-700 dark:via-gray-600 dark:to-gray-700 animate-shimmer bg-[length:200%_100%]"></div>
            </div>
            <div class="h-5 w-16 rounded-md bg-gradient-to-r from-gray-100 via-gray-200 to-gray-100 dark:from-gray-700 dark:via-gray-600 dark:to-gray-700 animate-shimmer bg-[length:200%_100%]"></div>
        </div>',
    'stat' => '
        <div class="rounded-2xl border border-gray-100 dark:border-gray-700/80 bg-white dark:bg-gray-800 p-5 shadow-sm flex items-center gap-4">
            <div class="h-12 w-12 shrink-0 rounded-xl bg-gradient-to-r from-gray-100 via-gray-200 to-gray-100 dark:from-gray-700 dark:via-gray-600 dark:to-gray-700 animate-shimmer bg-[length:200%_100%]"></div>
            <div class="flex-1 space-y-2">
                <div class="h-3 w-2/3 rounded-md bg-gradient-to-r from-gray-100 via-gray-200 to-gray-100 dark:from-gray-700 dark:via-gray-600 dark:to-gray-700 animate-shimmer bg-[length:200%_100%]"></div>
                <div class="h-5 w-1/3 rounded-md bg-gradient-to-r from-gray-100 via-gray-200 to-gray-100 dark:from-gray-700 dark:via-gray-600 dark:to-gray-700 animate-shimmer bg-[length:200%_100%]"></div>
            </div>
        </div>',
];
@endphp

<div wire:loading @if ($target) wire:target="{{ $target }}" @endif class="grid grid-cols-1 gap-4 {{ $class }}">
    @for ($i = 0; $i < $count; $i++)
        {!! $shapes[$type] !!}
    @endfor
</div>