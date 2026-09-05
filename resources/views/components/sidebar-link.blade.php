@props(['active'])

@php
$classes = ($active ?? false)
    ? 'inline-flex w-full items-center gap-3 rounded-xl bg-gradient-to-r from-teal-50 to-emerald-50/50 dark:from-teal-900/40 dark:to-emerald-900/30 px-3 py-2.5 text-sm font-semibold text-teal-700 dark:text-teal-400 ring-1 ring-inset ring-teal-200/60 dark:ring-teal-800/60 shadow-sm shadow-teal-100/50 dark:shadow-none focus:outline-none transition-all duration-200'
    : 'inline-flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100 focus:outline-none transition-all duration-200 active:scale-[0.98]';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
