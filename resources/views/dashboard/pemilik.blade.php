<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Dashboard Pemilik') }}
        </h2>
    </x-slot>

    <livewire:pages.dashboard.pemilik />
</x-app-layout>