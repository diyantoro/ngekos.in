<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $peran = '';

    public function mount(): void
    {
        $this->peran = request()->query('peran', '');
    }
}; ?>

<div>
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Masuk sebagai</h2>
        <p class="mt-1 text-sm text-gray-500">Pilih peran kamu untuk melanjutkan.</p>
    </div>

    <div class="space-y-3">
        <a href="{{ route('login.form', ['peran' => 'anak_kos']) }}" wire:navigate
           class="group flex items-center gap-4 rounded-2xl border-2 border-teal-200 bg-white p-4 transition-all duration-200 hover:border-teal-500 hover:bg-teal-50 hover:shadow-md hover:shadow-teal-100">
            <img src="{{ asset('images/login-tenant.svg') }}" alt="Pencari Kos" class="h-20 w-auto shrink-0">
            <div class="min-w-0">
                <span class="block text-sm font-bold text-gray-900 group-hover:text-teal-700">Pencari Kos</span>
                <span class="block text-xs text-gray-500 mt-0.5">Cari & tanya kamar kos</span>
            </div>
            <svg class="ml-auto h-5 w-5 shrink-0 text-gray-300 group-hover:text-teal-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
        </a>

        <a href="{{ route('login.form', ['peran' => 'pemilik']) }}" wire:navigate
           class="group flex items-center gap-4 rounded-2xl border-2 border-emerald-200 bg-white p-4 transition-all duration-200 hover:border-emerald-500 hover:bg-emerald-50 hover:shadow-md hover:shadow-emerald-100">
            <img src="{{ asset('images/login-owner.svg') }}" alt="Pemilik Kos" class="h-20 w-auto shrink-0">
            <div class="min-w-0">
                <span class="block text-sm font-bold text-gray-900 group-hover:text-emerald-700">Pemilik Kos</span>
                <span class="block text-xs text-gray-500 mt-0.5">Kelola & promosikan kos</span>
            </div>
            <svg class="ml-auto h-5 w-5 shrink-0 text-gray-300 group-hover:text-emerald-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
        </a>
    </div>

    <p class="text-center text-sm text-gray-500 pt-4 mt-4 border-t border-gray-100">
        Belum punya akun?
        <a class="font-semibold text-teal-600 hover:text-teal-500" href="{{ route('register') }}" wire:navigate>
            Daftar sekarang
        </a>
    </p>
</div>
