<?php

use App\Models\Properti;
use App\Services\PemilikLaporanPremiumService;
use App\Services\SubscriptionService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

?>

<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <a href="<?php echo e(route('dashboard.pemilik')); ?>" wire:navigate class="text-sm font-medium text-teal-600 dark:text-teal-400 hover:text-teal-500 inline-flex items-center gap-1">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Kembali ke Dashboard
                </a>
                <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">Laporan Premium</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ringkasan keuangan, tagihan belum bayar, kamar terisi & pemasukan tiap kos.</p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! ($terkunci ?? false) && ($data['tier'] ?? null)): ?>
                    <span class="mt-2 inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-extrabold uppercase tracking-wider ring-1 <?php echo e(($data['tier'] ?? 'pro') === 'business' ? 'bg-violet-600 text-white ring-violet-600 shadow-lg shadow-violet-500/30' : 'bg-teal-50 text-teal-700 ring-teal-200 dark:bg-teal-500/10 dark:text-teal-300 dark:ring-teal-500/30'); ?>">
                        Paket <?php echo e(strtoupper($data['tier'])); ?>

                    </span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($data['tier'] ?? 'pro') === 'pro'): ?>
                        <a href="<?php echo e(route('langganan.plans')); ?>" wire:navigate class="ms-2 text-xs font-semibold text-violet-600 dark:text-violet-400 hover:underline">Naik ke BUSINESS untuk 4 bagian analisis tambahan →</a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <input type="month" wire:model.live="bulan"
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                <select wire:model.live="propertiId"
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                    <option value="">Semua Properti</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $daftarProperti; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($p['id']); ?>"><?php echo e($p['nama']); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
                <select wire:model.live="periode"
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                    <option value="3">3 bulan</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($maxPeriode ?? 12) >= 6): ?><option value="6">6 bulan</option><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($maxPeriode ?? 12) >= 12): ?><option value="12">12 bulan</option><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($maxPeriode ?? 12) >= 24): ?><option value="24">24 bulan</option><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($isFree ?? false) && ($sisaTrial ?? null) !== null && ! ($terkunci ?? false)): ?>
            <div class="rounded-xl bg-amber-50 dark:bg-amber-500/10 ring-1 ring-amber-200 dark:ring-amber-500/30 px-4 py-3 text-sm text-amber-800 dark:text-amber-200">
                Masa coba gratis tinggal <strong><?php echo e($sisaTrial); ?> hari</strong>. Halaman Laporan dikunci di Free — upgrade ke PRO untuk membukanya.
                <a href="<?php echo e(route('langganan.plans')); ?>" wire:navigate class="font-bold hover:underline">Upgrade</a>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($galat): ?>
            <div class="flex items-center justify-between gap-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 ring-1 ring-rose-200 dark:ring-rose-500/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                <span><?php echo e($galat); ?></span>
                <button wire:click="$set('galat', null)" class="font-bold">&times;</button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($terkunci): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($isFree ?? false) && ($sisaTrial ?? null) === null && ($trialHabis ?? false)): ?>
                <div class="max-w-2xl mx-auto space-y-4">
                    <div class="rounded-xl bg-rose-50 dark:bg-rose-500/10 ring-1 ring-rose-200 dark:ring-rose-500/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                        Masa coba 7 hari sudah habis. Data tidak hilang, tapi halaman Laporan & tambah kos/kamar dikunci.
                        <a href="<?php echo e(route('langganan.plans')); ?>" wire:navigate class="font-bold hover:underline">Upgrade ke PRO</a>
                    </div>
                    <?php if (isset($component)) { $__componentOriginalc325dfb0584ae0cb231e66721d030938 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc325dfb0584ae0cb231e66721d030938 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.premium-lock','data' => ['requiredPlan' => 'pro','title' => 'Laporan Premium','message' => 'Tersedia di paket PRO.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('premium-lock'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['requiredPlan' => 'pro','title' => 'Laporan Premium','message' => 'Tersedia di paket PRO.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc325dfb0584ae0cb231e66721d030938)): ?>
<?php $attributes = $__attributesOriginalc325dfb0584ae0cb231e66721d030938; ?>
<?php unset($__attributesOriginalc325dfb0584ae0cb231e66721d030938); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc325dfb0584ae0cb231e66721d030938)): ?>
<?php $component = $__componentOriginalc325dfb0584ae0cb231e66721d030938; ?>
<?php unset($__componentOriginalc325dfb0584ae0cb231e66721d030938); ?>
<?php endif; ?>
                </div>
            <?php else: ?>
            <div class="max-w-2xl mx-auto">
                <?php if (isset($component)) { $__componentOriginalc325dfb0584ae0cb231e66721d030938 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc325dfb0584ae0cb231e66721d030938 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.premium-lock','data' => ['requiredPlan' => 'pro','title' => 'Laporan Premium','message' => 'Tersedia di paket PRO.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('premium-lock'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['requiredPlan' => 'pro','title' => 'Laporan Premium','message' => 'Tersedia di paket PRO.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc325dfb0584ae0cb231e66721d030938)): ?>
<?php $attributes = $__attributesOriginalc325dfb0584ae0cb231e66721d030938; ?>
<?php unset($__attributesOriginalc325dfb0584ae0cb231e66721d030938); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc325dfb0584ae0cb231e66721d030938)): ?>
<?php $component = $__componentOriginalc325dfb0584ae0cb231e66721d030938; ?>
<?php unset($__componentOriginalc325dfb0584ae0cb231e66721d030938); ?>
<?php endif; ?>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php elseif(! $data): ?>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Pendapatan ('.e($data['periode']).')','value' => 'Rp' . number_format($data['ringkasan']['pendapatan'], 0, ',', '.'),'tone' => 'emerald','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Pendapatan ('.e($data['periode']).')','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Rp' . number_format($data['ringkasan']['pendapatan'], 0, ',', '.')),'tone' => 'emerald','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Pengeluaran ('.e($data['periode']).')','value' => 'Rp' . number_format($data['ringkasan']['pengeluaran'], 0, ',', '.'),'tone' => 'rose','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Pengeluaran ('.e($data['periode']).')','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Rp' . number_format($data['ringkasan']['pengeluaran'], 0, ',', '.')),'tone' => 'rose','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Untung Bersih','value' => 'Rp' . number_format($data['ringkasan']['laba_bersih'], 0, ',', '.'),'tone' => 'teal','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181m8.818 3.181l-3.182-5.511m0 0l-5.511 3.181M4.5 3.75v15m6.75 0v-4.5m3-7.5h.008v.008H14.25V5.25z" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Untung Bersih','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Rp' . number_format($data['ringkasan']['laba_bersih'], 0, ',', '.')),'tone' => 'teal','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181m8.818 3.181l-3.182-5.511m0 0l-5.511 3.181M4.5 3.75v15m6.75 0v-4.5m3-7.5h.008v.008H14.25V5.25z" /></svg>']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Transaksi Terverifikasi','value' => $data['ringkasan']['jumlah_transaksi'],'tone' => 'cyan','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Transaksi Terverifikasi','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($data['ringkasan']['jumlah_transaksi']),'tone' => 'cyan','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Ringkasan Bulan <?php echo e($data['periode']); ?></h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($data['ringkasan']['total_properti']); ?> properti · <?php echo e($data['ringkasan']['total_kamar']); ?> kamar · <?php echo e($data['ringkasan']['kamar_terisi']); ?> terisi · <?php echo e($data['ringkasan']['penyewaan_aktif']); ?> penyewaan aktif</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Ekspor Laporan Premium</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Unduh laporan lengkap dalam PDF atau Excel. Nama file memuat tier paket. Paket BUSINESS mendapat 4 bagian tambahan: rincian tiap kos, transaksi detail, pertumbuhan bulanan, serta metode &amp; top penyewa.</p>
                </div>
                <form method="GET" class="flex flex-wrap items-center gap-2" target="_blank" rel="noopener">
                    <input type="month" name="bulan" value="<?php echo e($data['bulan']); ?>"
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                    <select name="periode" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                        <option value="3" <?php if((int) $periode === 3): echo 'selected'; endif; ?>>3 bulan</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($maxPeriode ?? 12) >= 6): ?><option value="6" <?php if((int) $periode === 6): echo 'selected'; endif; ?>>6 bulan</option><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($maxPeriode ?? 12) >= 12): ?><option value="12" <?php if((int) $periode === 12): echo 'selected'; endif; ?>>12 bulan</option><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($maxPeriode ?? 12) >= 24): ?><option value="24" <?php if((int) $periode === 24): echo 'selected'; endif; ?>>24 bulan</option><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($propertiId): ?>
                        <input type="hidden" name="properti_id" value="<?php echo e($propertiId); ?>">
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <button type="submit" formaction="<?php echo e(route('pemilik.laporan.pdf')); ?>"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                        PDF
                    </button>
                    <button type="submit" formaction="<?php echo e(route('pemilik.laporan.excel')); ?>"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                        Excel
                    </button>
                </form>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Tagihan Belum Bayar</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Nilai tagihan yang belum dibayar</p>
                    </div>
                    <div class="p-5 grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-gray-50 dark:bg-gray-700/40 p-4">
                            <p class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Belum waktunya bayar</p>
                            <p class="mt-1 text-base font-extrabold text-gray-900 dark:text-gray-100">Rp<?php echo e(number_format($data['aging']['belum_jatuh_tempo'], 0, ',', '.')); ?></p>
                        </div>
                        <div class="rounded-xl bg-amber-50 dark:bg-amber-500/10 p-4">
                            <p class="text-[11px] font-semibold text-amber-600 dark:text-amber-400 uppercase">Baru telat, di bawah seminggu</p>
                            <p class="mt-1 text-base font-extrabold text-amber-700 dark:text-amber-300">Rp<?php echo e(number_format($data['aging']['telat_1_7'], 0, ',', '.')); ?></p>
                        </div>
                        <div class="rounded-xl bg-orange-50 dark:bg-orange-500/10 p-4">
                            <p class="text-[11px] font-semibold text-orange-600 dark:text-orange-400 uppercase">Telat sampai sebulan</p>
                            <p class="mt-1 text-base font-extrabold text-orange-700 dark:text-orange-300">Rp<?php echo e(number_format($data['aging']['telat_8_30'], 0, ',', '.')); ?></p>
                        </div>
                        <div class="rounded-xl bg-rose-50 dark:bg-rose-500/10 p-4">
                            <p class="text-[11px] font-semibold text-rose-600 dark:text-rose-400 uppercase">Telat lebih dari sebulan, segera tagih</p>
                            <p class="mt-1 text-base font-extrabold text-rose-700 dark:text-rose-300">Rp<?php echo e(number_format($data['aging']['telat_lebih_30'], 0, ',', '.')); ?></p>
                        </div>
                    </div>
                    <div class="px-5 pb-5">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">Siapa yang belum bayar (maks 50)</p>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-72 overflow-y-auto">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $data['tagihan_belum']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="py-2.5 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate"><?php echo e($t['anak_kos_nama']); ?> · <?php echo e($t['kamar_nama']); ?></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($t['periode']); ?> · tempo <?php echo e($t['jatuh_tempo']); ?></p>
                                    </div>
                                    <p class="shrink-0 text-sm font-bold text-rose-600 dark:text-rose-400">Rp<?php echo e(number_format($t['jumlah'] + $t['denda'], 0, ',', '.')); ?></p>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <p class="py-8 text-center text-sm text-gray-400">Semua tagihan lunas. Bagus!</p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Kos Pemasukan Terbesar</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Rentang bulan <?php echo e($data['periode_trend']); ?></p>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $data['top_properti']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="px-5 py-3.5 flex items-center gap-3">
                                <span class="shrink-0 h-8 w-8 rounded-lg bg-violet-50 dark:bg-violet-500/10 text-violet-700 dark:text-violet-300 text-sm font-extrabold flex items-center justify-center"><?php echo e($i + 1); ?></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate"><?php echo e($p['nama']); ?></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($p['kamar_terisi']); ?>/<?php echo e($p['total_kamar']); ?> terisi</p>
                                </div>
                                <p class="shrink-0 text-sm font-bold text-emerald-600 dark:text-emerald-400">Rp<?php echo e(number_format($p['pendapatan'], 0, ',', '.')); ?></p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="px-5 py-10 text-center text-sm text-gray-400">Belum ada data.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Naik Turun Tiap Bulan</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($data['periode_trend']); ?> · uang masuk, uang keluar, untung bersih, dan kamar terisi</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bulan</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pendapatan</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pengeluaran</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Untung Bersih</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kamar Terisi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $data['trend']['labels']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $labaNilai = $data['trend']['laba'][$i];
                                ?>
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100"><?php echo e($label); ?></td>
                                    <td class="px-4 py-3 text-sm text-right text-emerald-600 dark:text-emerald-400">Rp<?php echo e(number_format($data['trend']['pendapatan'][$i], 0, ',', '.')); ?></td>
                                    <td class="px-4 py-3 text-sm text-right text-rose-600 dark:text-rose-400">Rp<?php echo e(number_format($data['trend']['pengeluaran'][$i], 0, ',', '.')); ?></td>
                                    <td class="px-4 py-3 text-sm text-right <?php echo e($labaNilai >= 0 ? 'text-teal-600 dark:text-teal-400' : 'text-rose-600 dark:text-rose-400'); ?>">Rp<?php echo e(number_format($labaNilai, 0, ',', '.')); ?></td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-700 dark:text-gray-300"><?php echo e($data['trend']['okupansi'][$i]); ?>%</td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($data['tier'] ?? 'pro') === 'business'): ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Pertumbuhan Bulan ke Bulan</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Khusus BUSINESS · persen naik/turun vs bulan sebelumnya</p>
                        </div>
                        <span class="rounded-full bg-violet-600 px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-white">Business</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bulan</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pendapatan</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">±</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Untung</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">±</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $data['pertumbuhan'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100"><?php echo e($p['bulan']); ?></td>
                                        <td class="px-4 py-3 text-sm text-right text-emerald-600 dark:text-emerald-400">Rp<?php echo e(number_format($p['pendapatan'], 0, ',', '.')); ?></td>
                                        <td class="px-4 py-3 text-sm text-right font-bold <?php echo e($p['pendapatan_pct'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'); ?>"><?php echo e($p['pendapatan_pct']); ?>%</td>
                                        <td class="px-4 py-3 text-sm text-right text-teal-600 dark:text-teal-400">Rp<?php echo e(number_format($p['laba'], 0, ',', '.')); ?></td>
                                        <td class="px-4 py-3 text-sm text-right font-bold <?php echo e($p['laba_pct'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'); ?>"><?php echo e($p['laba_pct']); ?>%</td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">Belum ada data.</td></tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Metode Pembayaran</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Khusus BUSINESS · transaksi terverifikasi</p>
                            </div>
                            <span class="rounded-full bg-violet-600 px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-white">Business</span>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $data['metode_pembayaran'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="px-5 py-3.5 flex items-center justify-between gap-3">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100"><?php echo e($m['label']); ?> <span class="font-normal text-xs text-gray-500">· <?php echo e($m['jumlah_transaksi']); ?> transaksi</span></p>
                                    <p class="shrink-0 text-sm font-bold text-emerald-600 dark:text-emerald-400">Rp<?php echo e(number_format($m['total'], 0, ',', '.')); ?></p>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <p class="px-5 py-10 text-center text-sm text-gray-400">Belum ada data.</p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Top 10 Penyewa</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Khusus BUSINESS · pembayaran terbesar</p>
                            </div>
                            <span class="rounded-full bg-violet-600 px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-white">Business</span>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $data['top_penyewa'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="px-5 py-3 flex items-center gap-3">
                                    <span class="shrink-0 h-7 w-7 rounded-lg bg-violet-50 dark:bg-violet-500/10 text-violet-700 dark:text-violet-300 text-xs font-extrabold flex items-center justify-center"><?php echo e($i + 1); ?></span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate"><?php echo e($p['nama']); ?></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($p['jumlah_transaksi']); ?> transaksi</p>
                                    </div>
                                    <p class="shrink-0 text-sm font-bold text-emerald-600 dark:text-emerald-400">Rp<?php echo e(number_format($p['total'], 0, ',', '.')); ?></p>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <p class="px-5 py-10 text-center text-sm text-gray-400">Belum ada data.</p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Rincian Tiap Kos</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Pendapatan, pengeluaran & untung bersih per kos</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kos</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Terisi</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Masuk</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Keluar</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Untung</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $data['rincian_tiap_kos'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100"><?php echo e($r['nama']); ?></td>
                                            <td class="px-4 py-3 text-sm text-right text-gray-700 dark:text-gray-300"><?php echo e($r['kamar_terisi']); ?>/<?php echo e($r['total_kamar']); ?> (<?php echo e($r['tingkat_terisi']); ?>%)</td>
                                            <td class="px-4 py-3 text-sm text-right text-emerald-600 dark:text-emerald-400">Rp<?php echo e(number_format($r['pendapatan'], 0, ',', '.')); ?></td>
                                            <td class="px-4 py-3 text-sm text-right text-rose-600 dark:text-rose-400">Rp<?php echo e(number_format($r['pengeluaran'], 0, ',', '.')); ?></td>
                                            <td class="px-4 py-3 text-sm text-right text-teal-600 dark:text-teal-400">Rp<?php echo e(number_format($r['untung_bersih'], 0, ',', '.')); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">Belum ada data.</td></tr>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Daftar Transaksi Detail</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">200 transaksi terakhir yang terverifikasi</p>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-96 overflow-y-auto">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $data['transaksi_detail'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="px-5 py-2.5 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate"><?php echo e($t['penyewa']); ?> · <?php echo e($t['kamar']); ?></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($t['kos']); ?> · <?php echo e($t['periode']); ?> · <?php echo e($t['tanggal']); ?></p>
                                    </div>
                                    <p class="shrink-0 text-sm font-bold text-emerald-600 dark:text-emerald-400">Rp<?php echo e(number_format($t['jumlah'], 0, ',', '.')); ?></p>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <p class="px-5 py-10 text-center text-sm text-gray-400">Belum ada data.</p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div><?php /**PATH C:\laragon\www\Ngekos.in\resources\views\livewire/pages/pemilik/laporan-premium.blade.php ENDPATH**/ ?>