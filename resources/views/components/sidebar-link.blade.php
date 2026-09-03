@props(['active'])

@php
$classes = ($active ?? false)
    ? 'inline-flex w-full items-center rounded-lg bg-teal-50 px-3 py-2 text-sm font-semibold text-teal-700 ring-1 ring-inset ring-teal-200 focus:outline-none transition duration-150 ease-in-out'
    : 'inline-flex w-full items-center rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
