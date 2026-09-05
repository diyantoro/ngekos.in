@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-md shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100']) }}>
