<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

?>

<div>
    <div class="mb-4">
        <a href="<?php echo e(route('home')); ?>" wire:navigate
           class="group inline-flex items-center gap-1 text-sm font-medium text-teal-600 hover:text-teal-700 dark:text-teal-400 dark:hover:text-teal-300 transition-colors duration-200">
            <svg class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
            Kembali ke Beranda
        </a>
    </div>

    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Masuk sebagai</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pilih peran kamu untuk melanjutkan.</p>
    </div>

    <div class="space-y-3">
        <a href="<?php echo e(route('login.form', ['peran' => 'anak_kos'])); ?>" wire:navigate
           class="group flex items-center gap-4 rounded-2xl border-2 border-teal-200 bg-white dark:border-teal-500/30 dark:bg-gray-800 p-4 transition-all duration-200 hover:border-teal-500 hover:bg-teal-50 hover:shadow-md hover:shadow-teal-100 dark:hover:border-teal-400 dark:hover:bg-teal-500/10 dark:hover:shadow-none">
            <img src="<?php echo e(asset('images/login-tenant.svg')); ?>" alt="Pencari Kos" class="h-20 w-auto shrink-0">
            <div class="min-w-0">
                <span class="block text-sm font-bold text-gray-900 dark:text-gray-100 group-hover:text-teal-700 dark:group-hover:text-teal-300">Pencari Kos</span>
                <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">Cari & tanya kamar kos</span>
            </div>
            <svg class="ml-auto h-5 w-5 shrink-0 text-gray-300 dark:text-gray-600 group-hover:text-teal-500 dark:group-hover:text-teal-300 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
        </a>

        <a href="<?php echo e(route('login.form', ['peran' => 'pemilik'])); ?>" wire:navigate
           class="group flex items-center gap-4 rounded-2xl border-2 border-emerald-200 bg-white dark:border-emerald-500/30 dark:bg-gray-800 p-4 transition-all duration-200 hover:border-emerald-500 hover:bg-emerald-50 hover:shadow-md hover:shadow-emerald-100 dark:hover:border-emerald-400 dark:hover:bg-emerald-500/10 dark:hover:shadow-none">
            <img src="<?php echo e(asset('images/login-owner.svg')); ?>" alt="Pemilik Kos" class="h-20 w-auto shrink-0">
            <div class="min-w-0">
                <span class="block text-sm font-bold text-gray-900 dark:text-gray-100 group-hover:text-emerald-700 dark:group-hover:text-emerald-300">Pemilik Kos</span>
                <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola & promosikan kos</span>
            </div>
            <svg class="ml-auto h-5 w-5 shrink-0 text-gray-300 dark:text-gray-600 group-hover:text-emerald-500 dark:group-hover:text-emerald-300 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
        </a>
    </div>

    <p class="text-center text-sm text-gray-500 dark:text-gray-400 pt-4 mt-4 border-t border-gray-100 dark:border-gray-700">
        Belum punya akun?
        <a class="font-semibold text-teal-600 hover:text-teal-500 dark:text-teal-400 dark:hover:text-teal-300" href="<?php echo e(route('register')); ?>" wire:navigate>
            Daftar sekarang
        </a>
    </p>
</div><?php /**PATH C:\laragon\www\Ngekos.in\resources\views\livewire\pages\auth\pilih-peran.blade.php ENDPATH**/ ?>