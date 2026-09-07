@props(['disabled' => false, 'error' => null])

@php
$baseClasses = 'rounded-xl shadow-sm transition-colors dark:bg-gray-800 dark:text-gray-100 ';
$stateClasses = $error
    ? 'border-rose-500 focus:border-rose-500 focus:ring-rose-500 dark:border-rose-500'
    : 'border-gray-300 focus:border-teal-500 focus:ring-teal-500 dark:border-gray-600';
@endphp

<input @disabled($disabled)
       @if ($error) aria-invalid="true" @endif
       {{ $attributes->merge(['class' => $baseClasses . $stateClasses]) }}>
