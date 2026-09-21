@props(['requiredPlan' => 'pro', 'title' => 'Fitur Premium', 'message' => null])

@php
    $planName = strtoupper($requiredPlan);
    $message = $message ?? "Fitur ini tersedia pada paket {$planName}.";
@endphp

<div class="rounded-2xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-6 text-center">
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-500">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
    </div>
    <h3 class="mt-3 text-base font-bold text-gray-900 dark:text-gray-100">🔒 {{ $title }}</h3>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $message }}</p>
    <a href="{{ route('langganan.plans') }}" wire:navigate class="btn-primary mt-4 inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white">
        Upgrade ke {{ $planName }}
    </a>
</div>
