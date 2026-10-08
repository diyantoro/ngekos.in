<?php

use App\Models\Kamar;
use App\Models\Pembayaran;
use App\Models\Penyewaan;
use App\Models\PesanBantuan;
use App\Models\Properti;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\SubscriptionService;
use App\Support\GrafikBulan;
use Livewire\Volt\Component;

?>

<div class="py-10" wire:poll.visible.120s>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <?php if (isset($component)) { $__componentOriginal41da67e197cd1dfc4360372319841e50 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal41da67e197cd1dfc4360372319841e50 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-greeting','data' => ['roleLabel' => 'Super Admin','description' => 'Akses penuh ke seluruh data lintas properti, pengguna, dan konfigurasi sistem.','icon' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-greeting'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['roleLabel' => 'Super Admin','description' => 'Akses penuh ke seluruh data lintas properti, pengguna, dan konfigurasi sistem.','icon' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal41da67e197cd1dfc4360372319841e50)): ?>
<?php $attributes = $__attributesOriginal41da67e197cd1dfc4360372319841e50; ?>
<?php unset($__attributesOriginal41da67e197cd1dfc4360372319841e50); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal41da67e197cd1dfc4360372319841e50)): ?>
<?php $component = $__componentOriginal41da67e197cd1dfc4360372319841e50; ?>
<?php unset($__componentOriginal41da67e197cd1dfc4360372319841e50); ?>
<?php endif; ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Total Pengguna','value' => $totalUser,'tone' => 'teal','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Total Pengguna','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($totalUser),'tone' => 'teal','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>']); ?>
                 <?php $__env->slot('hint', null, []); ?> 
                    <span class="text-gray-400 dark:text-gray-500"><?php echo e($totalPemilik); ?> pemilik Â· <?php echo e($totalAnakKos); ?> anak kos Â· <?php echo e($totalAdmin); ?> admin Â· <?php echo e($totalSuperAdmin); ?> super admin<?php echo e($totalTanpaPeran > 0 ? ' Â· ' . $totalTanpaPeran . ' tanpa peran' : ''); ?></span>
                 <?php $__env->endSlot(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Properti','value' => $totalProperti,'tone' => 'cyan','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Properti','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($totalProperti),'tone' => 'cyan','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" /></svg>']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Kamar','value' => $totalKamar . ' (' . $kamarTerisi . ' terisi)','tone' => 'sky','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Kamar','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($totalKamar . ' (' . $kamarTerisi . ' terisi)'),'tone' => 'sky','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" /></svg>']); ?>
                 <?php $__env->slot('hint', null, []); ?> 
                    <span class="text-gray-400 dark:text-gray-500"><?php echo e($terisiDidukungSewa); ?> didukung sewa aktif</span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($kamarTerisi - $terisiDidukungSewa) > 0): ?>
                        <span class="font-semibold text-amber-600 dark:text-amber-400">Â· <?php echo e($kamarTerisi - $terisiDidukungSewa); ?> tanpa sewa</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                 <?php $__env->endSlot(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Penyewaan Aktif','value' => $penyewaanAktif,'tone' => 'emerald','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Penyewaan Aktif','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($penyewaanAktif),'tone' => 'emerald','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Pendapatan Terkumpul','value' => 'Rp' . number_format($pendapatan, 0, ',', '.'),'tone' => 'rose','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Pendapatan Terkumpul','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Rp' . number_format($pendapatan, 0, ',', '.')),'tone' => 'rose','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>']); ?>
                 <?php $__env->slot('hint', null, []); ?> 
                    <span class="text-gray-400 dark:text-gray-500">kumulatif semua pembayaran terverifikasi</span>
                 <?php $__env->endSlot(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Pemilik Kos','value' => $totalPemilik,'tone' => 'emerald','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Pemilik Kos','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($totalPemilik),'tone' => 'emerald','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" /></svg>']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Anak Kos','value' => $totalAnakKos,'tone' => 'sky','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Anak Kos','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($totalAnakKos),'tone' => 'sky','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>']); ?>
                 <?php $__env->slot('hint', null, []); ?> 
                    <span class="text-gray-400 dark:text-gray-500"><?php echo e($anakMenyewa); ?> menyewa Â· <?php echo e($totalAnakKos - $anakMenyewa); ?> mencari</span>
                 <?php $__env->endSlot(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Admin','value' => $totalAdmin,'tone' => 'amber','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>','hint' => '<span class="text-gray-400 dark:text-gray-500">operasional properti</span>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Admin','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($totalAdmin),'tone' => 'amber','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>','hint' => '<span class="text-gray-400 dark:text-gray-500">operasional properti</span>']); ?>
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

        <div id="growth-data"
            data-labels='<?php echo json_encode($bulanLabels, 15, 512) ?>'
            data-growth-total='<?php echo json_encode($chartGrowthTotal, 15, 512) ?>'
            data-growth-anak='<?php echo json_encode($chartGrowthAnak, 15, 512) ?>'
            data-growth-pemilik='<?php echo json_encode($chartGrowthPemilik, 15, 512) ?>'
            data-growth-properti='<?php echo json_encode($chartGrowthProperti, 15, 512) ?>'
            data-growth-penyewaan='<?php echo json_encode($chartGrowthPenyewaan, 15, 512) ?>'
            class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Pertumbuhan Pengguna</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pendaftaran baru per bulan</p>
                    </div>
                    <select wire:model.live="periode"
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                        <option value="3">3 bulan</option>
                        <option value="6">6 bulan</option>
                        <option value="12">12 bulan</option>
                    </select>
                </div>
                <canvas id="chart-growth-pengguna" class="mt-4 max-h-64"></canvas>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-6">
                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Pertumbuhan Bisnis</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Properti &amp; penyewaan baru per bulan</p>
                <canvas id="chart-growth-bisnis" class="mt-4 max-h-64"></canvas>
            </div>
        </div>

        <div id="transaction-data"
            data-labels='<?php echo json_encode($bulanLabels, 15, 512) ?>'
            data-total-transaksi='<?php echo json_encode($chartTransaksiJumlah, 15, 512) ?>'
            data-nilai-transaksi='<?php echo json_encode($chartTransaksiNilai, 15, 512) ?>'
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Pertumbuhan Transaksi</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Jumlah &amp; nilai pembayaran terverifikasi per bulan</p>
                </div>
                <select wire:model.live="periode"
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                    <option value="3">3 bulan</option>
                    <option value="6">6 bulan</option>
                    <option value="12">12 bulan</option>
                </select>
            </div>
            <canvas id="chart-trend-transaksi" class="mt-4 max-h-72"></canvas>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-6">
                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Top Properti Berkinerja</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">5 properti dengan pendapatan terbesar <?php echo e($periodeBulan); ?> bulan terakhir; okupansi &amp; rating aktual</p>
                <div class="mt-4 space-y-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $topPropertis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-center gap-3">
                            <span class="shrink-0 h-7 w-7 rounded-lg bg-teal-50 dark:bg-teal-500/10 text-teal-700 dark:text-teal-300 text-sm font-bold flex items-center justify-center"><?php echo e($index + 1); ?></span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate"><?php echo e($p['nama']); ?></p>
                                    <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400 shrink-0">Rp<?php echo e(number_format($p['pendapatan'], 0, ',', '.')); ?></p>
                                </div>
                                <div class="mt-1 flex items-center justify-between gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span class="truncate"><?php echo e($p['pemilik_nama']); ?> Â· <?php echo e($p['kota']); ?></span>
                                    <span class="shrink-0">Okupansi <?php echo e($p['okupansi']); ?>%</span>
                                </div>
                                <div class="mt-1.5 h-1.5 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                    <div class="h-full rounded-full bg-gradient-to-r from-teal-500 to-emerald-500" style="width: <?php echo e($p['okupansi']); ?>%"></div>
                                </div>
                                <div class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                    <?php echo e($p['kamar_terisi']); ?>/<?php echo e($p['total_kamar']); ?> kamar terisi
                                    Â· <?php echo e($p['penyewaan']); ?> sewaan
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($p['rating'] !== null): ?> Â· <span class="text-amber-500">â˜… <?php echo e($p['rating']); ?></span> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="py-10 text-center text-sm text-gray-400 dark:text-gray-500">Belum ada transaksi terverifikasi, sehingga belum ada properti berkinerja.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-6">
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Platform Revenue</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total upgrade paket PRO/Business yang disetujui</p>
                    <p class="mt-3 text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">Rp<?php echo e(number_format($platformRevenue ?? 0, 0, ',', '.')); ?></p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400"><?php echo e($upgradeApproved ?? 0); ?> transaksi upgrade disetujui</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-6">
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Premium Conversion</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pemilik berlangganan PRO/Business aktif</p>
                    <?php $konversi = $konversiPremium ?? ['total_pemilik' => 0, 'premium_aktif' => 0, 'persen' => 0]; ?>
                    <p class="mt-3 text-3xl font-extrabold text-teal-600 dark:text-teal-400"><?php echo e($konversi['persen']); ?>%</p>
                    <div class="mt-2 h-2 w-full rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-teal-500 to-emerald-500" style="width: <?php echo e(min(100, max(0, (float) $konversi['persen']))); ?>%"></div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400"><?php echo e($konversi['premium_aktif']); ?> dari <?php echo e($konversi['total_pemilik']); ?> pemilik memakai paket premium</p>
                </div>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(array_sum($needAttention) > 0): ?>
            <div class="rounded-2xl bg-rose-50/60 dark:bg-rose-500/5 ring-1 ring-rose-200/60 dark:ring-rose-500/20 p-4 sm:p-5">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.008v.008H12v-.008z" /></svg>
                    </span>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Perlu Perhatian</h3>
                </div>
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($needAttention['pembayaranMenunggu'] > 0): ?>
                        <div class="rounded-xl bg-white dark:bg-gray-800 p-3.5 flex items-center gap-3">
                            <span class="shrink-0 h-9 w-9 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-lg font-extrabold text-gray-900 dark:text-gray-100"><?php echo e($needAttention['pembayaranMenunggu']); ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Pembayaran menunggu verifikasi</p>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($needAttention['tagihanTelat'] > 0): ?>
                        <div class="rounded-xl bg-white dark:bg-gray-800 p-3.5 flex items-center gap-3">
                            <span class="shrink-0 h-9 w-9 rounded-xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9.303 3.376c-.866 1.5.217 3.374 1.948 3.374H5.75c1.73 0 2.813-1.874 1.948-3.374L10.05 3.378c.866-1.5 3.032-1.5 3.898 0l8.354 12.748z" /></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-lg font-extrabold text-gray-900 dark:text-gray-100"><?php echo e($needAttention['tagihanTelat']); ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Tagihan telat (kena denda)</p>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($needAttention['propertiTanpaKamar'] > 0): ?>
                        <div class="rounded-xl bg-white dark:bg-gray-800 p-3.5 flex items-center gap-3">
                            <span class="shrink-0 h-9 w-9 rounded-xl bg-teal-50 dark:bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75" /></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-lg font-extrabold text-gray-900 dark:text-gray-100"><?php echo e($needAttention['propertiTanpaKamar']); ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Properti belum punya kamar</p>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($needAttention['bantuanBaru'] ?? 0) > 0): ?>
                        <a href="<?php echo e(route('bantuan.masuk')); ?>" wire:navigate
                            class="rounded-xl bg-white dark:bg-gray-800 p-3.5 flex items-center gap-3 hover:bg-teal-50/50 dark:hover:bg-gray-700/60 transition group">
                            <span class="shrink-0 h-9 w-9 rounded-xl bg-teal-50 dark:bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center group-hover:scale-105 transition">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z" /></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-lg font-extrabold text-gray-900 dark:text-gray-100"><?php echo e($needAttention['bantuanBaru']); ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Pesan bantuan baru</p>
                            </div>
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if (isset($component)) { $__componentOriginald8164573f98476b0d8f7ee47884e7cb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald8164573f98476b0d8f7ee47884e7cb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-funnel','data' => ['stages' => $funnelStages,'title' => 'Grafik Pipeline','subtitle' => 'Kunjungan â†’ Penyewa â†’ Tagihan â†’ Lunas, seluruh properti (keseluruhan)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-funnel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['stages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($funnelStages),'title' => 'Grafik Pipeline','subtitle' => 'Kunjungan â†’ Penyewa â†’ Tagihan â†’ Lunas, seluruh properti (keseluruhan)']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald8164573f98476b0d8f7ee47884e7cb9)): ?>
<?php $attributes = $__attributesOriginald8164573f98476b0d8f7ee47884e7cb9; ?>
<?php unset($__attributesOriginald8164573f98476b0d8f7ee47884e7cb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald8164573f98476b0d8f7ee47884e7cb9)): ?>
<?php $component = $__componentOriginald8164573f98476b0d8f7ee47884e7cb9; ?>
<?php unset($__componentOriginald8164573f98476b0d8f7ee47884e7cb9); ?>
<?php endif; ?>

        <div id="pendapatan-data-super"
            data-pendapatan='<?php echo json_encode($pendapatanPerBulan, 15, 512) ?>'
            data-tagihan='<?php echo json_encode($tagihanStatusPerBulan, 15, 512) ?>'
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Pendapatan &amp; Status Tagihan</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($periodeBulan); ?> bulan terakhir, seluruh properti</p>
                </div>
            </div>
            <canvas id="chart-pendapatan-super-admin" class="mt-4 max-h-72"></canvas>
        </div>

    </div>
</div>

<?php $__env->startPush('scripts'); ?>
    <script>
        // Baca dari atribut data-* di DOM (bukan data inline saat load) agar data selalu
        // segar setelah morph Livewire (poll / cari / ganti periode).
        function renderPendapatanSuperAdmin() {
            const wrap = document.getElementById('pendapatan-data-super');
            if (!wrap) return;
            let pendapatan = {}, tagihan = {};
            try { pendapatan = JSON.parse(wrap.dataset.pendapatan || '{}'); } catch (e) { pendapatan = {}; }
            try { tagihan = JSON.parse(wrap.dataset.tagihan || '{}'); } catch (e) { tagihan = {}; }
            window.renderPendapatanChart('chart-pendapatan-super-admin', pendapatan, tagihan);
        }

        function renderGrowthSuperAdmin() {
            const wrap = document.getElementById('growth-data');
            if (!wrap) return;

            const labels = JSON.parse(wrap.dataset.labels || '[]');
            const total = JSON.parse(wrap.dataset.growthTotal || '[]');
            const anak = JSON.parse(wrap.dataset.growthAnak || '[]');
            const pemilik = JSON.parse(wrap.dataset.growthPemilik || '[]');
            const properti = JSON.parse(wrap.dataset.growthProperti || '[]');
            const penyewaan = JSON.parse(wrap.dataset.growthPenyewaan || '[]');

            window.growthLineChart('chart-growth-pengguna', labels, [
                { label: 'Semua', data: total, borderColor: '#0d9488', backgroundColor: 'rgba(13,148,136,.1)', tension: .4 },
                { label: 'Anak Kos', data: anak, borderColor: '#0ea5e9', backgroundColor: 'rgba(14,165,233,.1)', tension: .4 },
                { label: 'Pemilik', data: pemilik, borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,.1)', tension: .4 },
            ]);
            window.growthBarChart('chart-growth-bisnis', labels, [
                { label: 'Properti', data: properti, backgroundColor: 'rgba(20,184,166,.85)', borderRadius: 6 },
                { label: 'Penyewaan', data: penyewaan, backgroundColor: 'rgba(99,102,241,.85)', borderRadius: 6 },
            ]);
        }

        function renderTransactionSuperAdmin() {
            const wrap = document.getElementById('transaction-data');
            if (!wrap) return;

            const labels = JSON.parse(wrap.dataset.labels || '[]');
            const jumlah = JSON.parse(wrap.dataset.totalTransaksi || '[]');
            const nilai = JSON.parse(wrap.dataset.nilaiTransaksi || '[]');

            window.transactionTrendChart('chart-trend-transaksi', labels, jumlah, nilai);
        }

        // Render ulang semua chart dashboard super admin. Dijaga dengan penanda
        // halaman agar listener basi dari navigasi SPA tidak merender halaman lain.
        window.__renderAllSuperAdmin = function () {
            if (!document.getElementById('chart-pendapatan-super-admin')) return;
            renderPendapatanSuperAdmin(); renderGrowthSuperAdmin(); renderTransactionSuperAdmin();
        };
        // Antrean debounce: ketikan pencarian / poll / ganti periode menyatu jadi 1 render.
        let __superAdminRenderTimer = null;
        window.__queueRenderAllSuperAdmin = function () {
            if (__superAdminRenderTimer) clearTimeout(__superAdminRenderTimer);
            __superAdminRenderTimer = setTimeout(() => { try { window.__renderAllSuperAdmin(); } catch (e) {} }, 250);
        };

        (function pasangListenerSuperAdmin() {
            const jalan = () => { try { window.__renderAllSuperAdmin && window.__renderAllSuperAdmin(); } catch (e) {} };
            const antre = () => { try { window.__queueRenderAllSuperAdmin && window.__queueRenderAllSuperAdmin(); } catch (e) {} };
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', jalan, { once: true });
            } else {
                jalan();
            }
            // Daftarkan sekali saja: navigasi SPA mengeksekusi ulang skrip ini,
            // tanpa penjagaan listener menumpuk dan chart dirender berkali-kali.
            if (!window.__superAdminDashListenerOn) {
                window.__superAdminDashListenerOn = true;
                document.addEventListener('livewire:navigated', jalan);
                try { if (window.Livewire && typeof window.Livewire.hook === 'function') window.Livewire.hook('morph.updated', antre); } catch (e) {}
                try { if (window.Livewire && typeof window.Livewire.on === 'function') window.Livewire.on('chart:data-updated', antre); } catch (e) {}
            }
        })();
    </script>
<?php $__env->stopPush(); ?><?php /**PATH C:\laragon\www\Ngekos.in\resources\views\livewire\pages\dashboard\super-admin.blade.php ENDPATH**/ ?>