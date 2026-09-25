<?php

use App\Models\SubscriptionRequest;
use App\Services\SubscriptionService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

?>

<div class="py-10">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">Langganan Saya</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Status paket dan penggunaan limit Anda.</p>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($permintaan): ?>
            <div class="flex items-start justify-between gap-3 rounded-xl bg-amber-50 dark:bg-amber-500/10 ring-1 ring-amber-200 dark:ring-amber-500/30 px-4 py-3 text-sm text-amber-800 dark:text-amber-200">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2 w-2 shrink-0 mt-1">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                    </span>
                    <span>Permintaan upgrade ke <strong><?php echo e(strtoupper($permintaan->requested_plan)); ?></strong> sedang menunggu persetujuan admin.</span>
                </div>
                <a href="<?php echo e(route('langganan.plans')); ?>" wire:navigate class="shrink-0 font-bold hover:underline">Lihat Paket</a>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="rounded-xl bg-teal-50 dark:bg-teal-500/10 ring-1 ring-teal-200 dark:ring-teal-500/30 px-4 py-3 text-sm text-teal-800 dark:text-teal-200">
                <?php echo e(session('status')); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('galat')): ?>
            <div class="rounded-xl bg-rose-50 dark:bg-rose-500/10 ring-1 ring-rose-200 dark:ring-rose-500/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                <?php echo e(session('galat')); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isFree && ($bisaKlaim ?? false)): ?>
            <div class="rounded-xl bg-teal-50 dark:bg-teal-500/10 ring-1 ring-teal-200 dark:ring-teal-500/30 px-4 py-3 text-sm text-teal-800 dark:text-teal-200">
                <p class="font-bold">Gratis 7 hari fitur PRO, sekali per akun. Tanpa kartu kredit.</p>
                <p class="mt-0.5">Coba laporan premium, ekspor PDF/Excel tanpa watermark, dan grafik 12 bulan.</p>
                <button wire:click="klaimTrial" wire:loading.attr="disabled" class="mt-2 inline-flex items-center rounded-xl bg-teal-600 px-4 py-2 text-xs font-bold text-white hover:bg-teal-500 disabled:opacity-50">
                    Klaim Trial 7 Hari
                </button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isFree && $sisaTrial !== null): ?>
            <div class="rounded-xl bg-amber-50 dark:bg-amber-500/10 ring-1 ring-amber-200 dark:ring-amber-500/30 px-4 py-3 text-sm text-amber-800 dark:text-amber-200">
                Masa coba gratis tinggal <strong><?php echo e($sisaTrial); ?> hari</strong>. Upgrade ke PRO untuk limit lebih besar & laporan premium.
                <a href="<?php echo e(route('langganan.plans')); ?>" wire:navigate class="font-bold hover:underline">Upgrade</a>
            </div>
        <?php elseif($isFree && $sisaTrial === null && $trialHabis): ?>
            <div class="rounded-xl bg-rose-50 dark:bg-rose-500/10 ring-1 ring-rose-200 dark:ring-rose-500/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                Masa coba 7 hari sudah habis atau belum diklaim. Data tidak hilang, tapi halaman Laporan dikunci. Tambah kos/kamar tetap bisa sampai batas paket Free.
                <a href="<?php echo e(route('langganan.plans')); ?>" wire:navigate class="font-bold hover:underline">Upgrade ke PRO</a>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="card p-6 space-y-3">
            <div class="flex items-center justify-between gap-3">
                <p class="text-2xl font-extrabold text-gray-900 dark:text-gray-100"><?php echo e(strtoupper($plan)); ?></p>
                <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $subscription?->status ?? 'active']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($subscription?->status ?? 'active')]); ?>
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
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Tanggal mulai</p>
                    <p class="font-semibold text-gray-900 dark:text-gray-100"><?php echo e($subscription?->starts_at?->translatedFormat('d F Y') ?? '—'); ?></p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Tanggal berakhir</p>
                    <p class="font-semibold text-gray-900 dark:text-gray-100"><?php echo e($subscription?->expires_at?->translatedFormat('d F Y') ?? '—'); ?></p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Penggunaan property</p>
                    <p class="font-semibold text-gray-900 dark:text-gray-100"><?php echo e($propertyUsed); ?> / <?php echo e($propertyLimit ?? '∞'); ?></p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Penggunaan kamar</p>
                    <p class="font-semibold text-gray-900 dark:text-gray-100"><?php echo e($roomUsed); ?> / <?php echo e($roomLimit ?? '∞'); ?></p>
                </div>
            </div>
            <div class="pt-2">
                <a href="<?php echo e(route('langganan.plans')); ?>" wire:navigate class="btn-primary inline-flex items-center rounded-xl px-4 py-2 text-xs font-semibold text-white">Lihat Paket</a>
            </div>
        </div>

        <div class="card p-6">
            <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">Riwayat Langganan</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Paket, tanggal, dan status Anda sejauh ini.</p>
            <div class="mt-4 divide-y divide-gray-100 dark:divide-gray-700">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $riwayat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="py-3 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-bold text-gray-900 dark:text-gray-100"><?php echo e(strtoupper($r->plan)); ?></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($r->starts_at?->translatedFormat('d M Y') ?? '—'); ?> → <?php echo e($r->expires_at?->translatedFormat('d M Y') ?? '—'); ?></p>
                        </div>
                        <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $r->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($r->status)]); ?>
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
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="py-6 text-center text-sm text-gray-400">Belum ada riwayat. Anda menggunakan paket FREE.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\laragon\www\Ngekos.in\resources\views\livewire\pages\langganan\subscription.blade.php ENDPATH**/ ?>