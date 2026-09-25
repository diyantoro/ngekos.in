<?php

use App\Models\ChatPesan;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

?>

<div class="py-10">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Pesan</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                <?php echo e($sebagaiPenyewa ? 'Riwayat percakapanmu dengan pemilik kos.' : 'Pertanyaan penyewa tentang kos Anda.'); ?>

            </p>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $bisaChat): ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-8 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">Fitur pesan tersedia untuk peran Anak Kos dan Pemilik Kos.</p>
            </div>
        <?php elseif($percakapans->isEmpty()): ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-10 text-center">
                <div class="mx-auto h-14 w-14 rounded-full bg-teal-50 dark:bg-teal-500/10 flex items-center justify-center mb-4">
                    <svg class="h-7 w-7 text-teal-400 dark:text-teal-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                </div>
                <p class="font-medium text-gray-700 dark:text-gray-200">Belum ada percakapan.</p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sebagaiPenyewa): ?>
                    <p class="mt-1 text-sm text-gray-400 dark:text-gray-500">Buka halaman kos lalu klik "Tanya Pemilik" untuk mulai mengobrol.</p>
                    <a href="<?php echo e(route('kos.index')); ?>" wire:navigate
                       class="mt-5 inline-flex items-center rounded-lg bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-500 transition">
                        Jelajahi Kos
                    </a>
                <?php else: ?>
                    <p class="mt-1 text-sm text-gray-400 dark:text-gray-500">Pertanyaan penyewa tentang kos Anda akan muncul di sini.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php else: ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden divide-y divide-gray-100 dark:divide-gray-700">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $percakapans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e($c['url']); ?>" wire:navigate class="flex items-center gap-4 p-4 sm:p-5 hover:bg-teal-50/50 dark:hover:bg-teal-500/10 transition">
                        <span class="h-12 w-12 shrink-0 rounded-full bg-gradient-to-br from-teal-500 via-emerald-500 to-green-500 flex items-center justify-center text-base font-extrabold text-white uppercase">
                            <?php echo e(mb_substr($c['lawan']?->nama ?? '?', 0, 1)); ?>

                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-center justify-between gap-2">
                                <span class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate"><?php echo e($c['lawan']?->nama ?? 'Pengguna terhapus'); ?></span>
                                <span class="shrink-0 text-[11px] text-gray-400 dark:text-gray-500"><?php echo e($c['terakhir']->created_at->translatedFormat('d M, H:i')); ?></span>
                            </span>
                            <span class="block text-xs font-medium text-teal-500 dark:text-teal-400 truncate"><?php echo e($c['properti']?->nama); ?></span>
                            <span class="mt-0.5 block text-sm <?php echo e($c['belumDibaca'] > 0 ? 'font-semibold text-gray-800 dark:text-gray-100' : 'text-gray-400 dark:text-gray-500'); ?> truncate">
                                <?php echo e(\Illuminate\Support\Str::limit($c['terakhir']->isi, 60)); ?>

                            </span>
                        </span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($c['belumDibaca'] > 0): ?>
                            <span class="shrink-0 inline-flex items-center justify-center h-5 min-w-5 px-1.5 rounded-full bg-rose-500 text-[11px] font-bold text-white">
                                <?php echo e($c['belumDibaca'] > 9 ? '9+' : $c['belumDibaca']); ?>

                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div><?php /**PATH C:\laragon\www\Ngekos.in\resources\views\livewire\pages\chat\index.blade.php ENDPATH**/ ?>