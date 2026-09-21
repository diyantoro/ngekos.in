@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full px-3 py-2.5 rounded-xl text-base font-medium text-teal-700 dark:text-teal-300 bg-teal-50/80 dark:bg-teal-900/30 border border-teal-200/50 dark:border-teal-800/50 focus:outline-none focus:text-teal-800 dark:focus:text-teal-200 focus:bg-teal-100 dark:focus:bg-teal-500/20 focus:border-teal-700 dark:focus:border-teal-500 transition-all duration-150 ease-in-out'
            : 'block w-full px-3 py-2.5 rounded-xl text-base font-medium text-gray-600 dark:text-gray-300 hover:text-teal-700 dark:hover:text-teal-200 hover:bg-teal-50/60 dark:hover:bg-teal-900/20 focus:outline-none focus:text-teal-700 dark:focus:text-teal-200 focus:bg-teal-50/60 dark:focus:bg-teal-900/20 transition-all duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} wire:navigate>
    {{ $slot }}
</a>
