<?php

use App\Models\Tagihan;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

?>

<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <a href="<?php echo e(route('dashboard.pemilik')); ?>" wire:navigate class="text-sm font-medium text-teal-600 dark:text-teal-400 hover:text-teal-500 dark:hover:text-teal-300 inline-flex items-center gap-1">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Kembali ke dashboard
            </a>
            <h1 class="mt-2 text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">Tagihan Penyewa</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                <?php echo e($tagihans->count()); ?> tagihan · total <span class="font-bold text-gray-800 dark:text-gray-200">Rp<?php echo e(number_format($totalNominal, 0, ',', '.')); ?></span>
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto_auto] gap-2 items-center">
            <input type="text" wire:model.live.debounce.300ms="cari" placeholder="Cari penyewa, kamar, kos, atau periode..."
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-brand-500 focus:border-brand-500">
            <select wire:model.live="filter" aria-label="Filter status"
                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-brand-500 focus:border-brand-500">
                <option value="belum">Belum lunas</option>
                <option value="telat">Telat</option>
                <option value="lunas">Lunas</option>
                <option value="semua">Semua</option>
            </select>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cari !== '' || $filter !== 'belum'): ?>
                <button wire:click="$set('cari', ''); $set('filter', 'belum')"
                    class="justify-self-start inline-flex items-center gap-1 rounded-lg px-2 py-2 text-xs font-semibold text-rose-500 hover:text-rose-600 dark:text-rose-400 dark:hover:text-rose-300 transition active:scale-95 whitespace-nowrap">
                    Reset
                </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $tagihans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tagihan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="group bg-white dark:bg-gray-800 rounded-xl border border-stone-200 dark:border-gray-700 overflow-hidden hover:shadow-card-hover hover:border-brand-200 transition">
                    <div class="flex p-3 sm:p-4 gap-3">
                        <div class="relative h-20 w-20 shrink-0 rounded-lg <?php echo e($tagihan->status === 'lunas' ? 'bg-emerald-100 dark:bg-emerald-500/20' : 'bg-rose-100 dark:bg-rose-500/20'); ?> flex items-center justify-center">
                            <svg class="h-8 w-8 <?php echo e($tagihan->status === 'lunas' ? 'text-emerald-500 dark:text-emerald-400' : 'text-rose-400 dark:text-rose-400'); ?>" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="flex-1 min-w-0 flex flex-col justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-gray-100 line-clamp-1"><?php echo e($tagihan->penyewaan?->anakKos?->nama ?? 'Penyewa'); ?></h3>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 truncate">
                                    <?php echo e($tagihan->penyewaan?->properti?->nama ?? '-'); ?> · Kamar <?php echo e($tagihan->penyewaan?->kamar?->nama ?? '-'); ?>

                                </p>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 truncate">
                                    <?php echo e($tagihan->periode); ?> · jatuh tempo <?php echo e($tagihan->jatuh_tempo?->translatedFormat('d M Y')); ?>

                                </p>
                            </div>
                            <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between gap-2">
                                <div>
                                    <p class="text-sm font-extrabold <?php echo e($tagihan->status === 'lunas' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'); ?>">Rp<?php echo e(number_format($tagihan->jumlah + $tagihan->denda, 0, ',', '.')); ?></p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tagihan->denda > 0): ?>
                                        <p class="text-[11px] text-rose-500 dark:text-rose-400">termasuk denda Rp<?php echo e(number_format($tagihan->denda, 0, ',', '.')); ?></p>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $tagihan->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tagihan->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-span-full py-16 text-center">
                    <p class="text-gray-500 dark:text-gray-400 font-medium text-sm">Tidak ada tagihan pada filter ini.</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Ubah filter atau kata kunci pencarian.</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div><?php /**PATH C:\laragon\www\Ngekos.in\resources\views\livewire\pages\pemilik\tagihan.blade.php ENDPATH**/ ?>