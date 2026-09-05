<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Dashboard Super Admin') }}
        </h2>
    </x-slot>

    <livewire:pages.dashboard.super-admin />
</x-app-layout>