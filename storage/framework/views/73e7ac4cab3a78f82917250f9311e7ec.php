<?php

use App\Models\Properti;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

?>

<div>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-gray-100">Kos Favorit</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Semua kos yang kamu tandai untuk dipertimbangkan.</p>
            </div>
            <a href="<?php echo e(route('kos.index')); ?>" wire:navigate
               class="shrink-0 inline-flex items-center gap-1.5 rounded-xl bg-teal-600 px-4 py-2 text-xs font-semibold text-white hover:bg-teal-500 transition shadow-sm">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Cari Kos Baru
            </a>
        </div>

        <div class="mt-6">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($favorits->isEmpty()): ?>
                <div class="rounded-2xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 px-6 py-16 text-center">
                    <div class="mx-auto h-16 w-16 rounded-full bg-rose-50 dark:bg-rose-500/10 flex items-center justify-center mb-4">
                        <svg class="h-8 w-8 text-rose-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
                    </div>
                    <p class="font-semibold text-gray-900 dark:text-gray-100">Belum ada kos favorit</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">Kamu belum menandai kos apa pun. Jelajahi katalog dan tekan ikon hati pada kos yang kamu suka.</p>
                    <a href="<?php echo e(route('kos.index')); ?>" wire:navigate class="mt-4 inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2 text-xs font-semibold text-white hover:bg-teal-500 transition">
                        Jelajahi Katalog
                    </a>
                </div>
            <?php else: ?>
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo e($favorits->count()); ?> kos tersimpan</p>
                    <button wire:click="hapusSemua" wire:confirm="Hapus semua kos dari favorit?"
                        class="inline-flex items-center gap-1 text-xs font-medium text-rose-600 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 transition">
                        Hapus Semua
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $favorits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $properti): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden hover:shadow-md hover:ring-rose-200 dark:hover:ring-rose-800 transition-all duration-200">
                            <a href="<?php echo e(route('kos.detail', $properti)); ?>" wire:navigate>
                                <div class="relative h-40 bg-gradient-to-br from-teal-100 via-emerald-100 to-cyan-100 dark:from-teal-500/20 dark:via-emerald-500/20 dark:to-cyan-500/20">
                                    <?php $coverFav = $properti->fotoCover(); ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($coverFav): ?>
                                        <img src="<?php echo e($coverFav); ?>" alt="<?php echo e($properti->nama); ?>" loading="lazy" decoding="async" class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
                                    <?php else: ?>
                                        <div class="h-full w-full flex items-center justify-center">
                                            <svg class="h-12 w-12 text-teal-300 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($properti->galeriUrls()) > 1): ?>
                                        <span class="absolute bottom-2 left-2 rounded-full bg-black/50 px-1.5 py-0.5 text-[9px] font-bold text-white backdrop-blur-sm"><?php echo e(count($properti->galeriUrls())); ?> foto</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($properti->kamar_tersedia > 0): ?>
                                        <span class="absolute top-2 right-2 inline-flex items-center rounded-full bg-emerald-600 px-1.5 py-0.5 text-[9px] font-bold text-white shadow-sm">
                                            <?php echo e($properti->kamar_tersedia); ?> Kamar
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </a>
                            <div class="p-4">
                                <a href="<?php echo e(route('kos.detail', $properti)); ?>" wire:navigate>
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition line-clamp-1"><?php echo e($properti->nama); ?></h3>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 line-clamp-1"><?php echo e($properti->kota ?? $properti->alamat ?? 'Lokasi belum diisi'); ?></p>
                                </a>
                                <?php
                                    $hargaTampil = $properti->harga ?? $properti->harga_termurah;
                                    $periode = $properti->jenis_harga ?? 'bulanan';
                                    $adaDiskon = $properti->harga_asli && $hargaTampil && $properti->harga_asli > $hargaTampil;
                                ?>
                                <div class="mt-3 flex items-center justify-between gap-2">
                                    <div class="text-end">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hargaTampil): ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($adaDiskon): ?>
                                                <span class="block text-xs font-semibold text-gray-400 dark:text-gray-500 line-through">Rp<?php echo e(number_format($properti->harga_asli, 0, ',', '.')); ?></span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <span class="text-sm font-extrabold text-teal-600 dark:text-teal-400">
                                                Rp<?php echo e(number_format($hargaTampil, 0, ',', '.')); ?><span class="text-[10px] font-medium text-gray-400 dark:text-gray-500">/<?php echo e($periode === 'harian' ? 'hari' : 'bln'); ?></span>
                                            </span>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($properti->harga_harian): ?>
                                                <span class="block text-[10px] font-medium text-gray-400 dark:text-gray-500">Rp<?php echo e(number_format($properti->harga_harian, 0, ',', '.')); ?>/hari</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-xs font-medium text-gray-400 dark:text-gray-500">Penuh</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <button wire:click="hapusFavorit(<?php echo e($properti->id); ?>)" wire:target="hapusFavorit(<?php echo e($properti->id); ?>)" wire:loading.attr="disabled"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-rose-50 dark:bg-rose-500/10 text-rose-500 hover:bg-rose-100 dark:hover:bg-rose-500/20 transition" aria-label="Hapus dari favorit <?php echo e($properti->nama); ?>">
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div><?php /**PATH C:\laragon\www\Ngekos.in\resources\views\livewire\pages\favorit.blade.php ENDPATH**/ ?>