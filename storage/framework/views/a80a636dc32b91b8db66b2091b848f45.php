<?php

use App\Models\ChatPesan;
use App\Models\Kamar;
use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use App\Models\Penyewaan;
use App\Models\Tagihan;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;

?>

<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <?php if (isset($component)) { $__componentOriginal41da67e197cd1dfc4360372319841e50 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal41da67e197cd1dfc4360372319841e50 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-greeting','data' => ['roleLabel' => 'Pemilik Kos','description' => 'Kamar yang kosong, tagihan yang telat, sama pemasukan bulan ini — semuanya kelihatan di sini.','icon' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .414.336.75.75.75z" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-greeting'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['roleLabel' => 'Pemilik Kos','description' => 'Kamar yang kosong, tagihan yang telat, sama pemasukan bulan ini — semuanya kelihatan di sini.','icon' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .414.336.75.75.75z" /></svg>']); ?>
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

        <div wire:ignore>
            <?php if (isset($component)) { $__componentOriginalf00e66bd4c416c4b5f17e7e6f5c37f9f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf00e66bd4c416c4b5f17e7e6f5c37f9f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.promo-ads','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('promo-ads'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf00e66bd4c416c4b5f17e7e6f5c37f9f)): ?>
<?php $attributes = $__attributesOriginalf00e66bd4c416c4b5f17e7e6f5c37f9f; ?>
<?php unset($__attributesOriginalf00e66bd4c416c4b5f17e7e6f5c37f9f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf00e66bd4c416c4b5f17e7e6f5c37f9f)): ?>
<?php $component = $__componentOriginalf00e66bd4c416c4b5f17e7e6f5c37f9f; ?>
<?php unset($__componentOriginalf00e66bd4c416c4b5f17e7e6f5c37f9f); ?>
<?php endif; ?>
        </div>

        <?php if (isset($component)) { $__componentOriginal8f70eb3b66f5f76832e54d270b05734b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f70eb3b66f5f76832e54d270b05734b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.subscription-card','data' => ['plan' => $langganan['plan'],'roomUsed' => $langganan['roomUsed'],'roomLimit' => $langganan['roomLimit'],'expiresAt' => $langganan['expiresAt'],'status' => $langganan['status'] ?? null,'sisaTrial' => $langganan['sisaTrial'] ?? null,'trialHabis' => $langganan['trialHabis'] ?? null,'bisaKlaim' => $langganan['bisaKlaim'] ?? false,'sisaLangganan' => $langganan['sisaLangganan'] ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('subscription-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['plan' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($langganan['plan']),'roomUsed' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($langganan['roomUsed']),'roomLimit' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($langganan['roomLimit']),'expiresAt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($langganan['expiresAt']),'status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($langganan['status'] ?? null),'sisaTrial' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($langganan['sisaTrial'] ?? null),'trialHabis' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($langganan['trialHabis'] ?? null),'bisaKlaim' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($langganan['bisaKlaim'] ?? false),'sisaLangganan' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($langganan['sisaLangganan'] ?? null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8f70eb3b66f5f76832e54d270b05734b)): ?>
<?php $attributes = $__attributesOriginal8f70eb3b66f5f76832e54d270b05734b; ?>
<?php unset($__attributesOriginal8f70eb3b66f5f76832e54d270b05734b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8f70eb3b66f5f76832e54d270b05734b)): ?>
<?php $component = $__componentOriginal8f70eb3b66f5f76832e54d270b05734b; ?>
<?php unset($__componentOriginal8f70eb3b66f5f76832e54d270b05734b); ?>
<?php endif; ?>

        <?php if (isset($component)) { $__componentOriginal86aed4c7aae687be9cbc3e767d87be21 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal86aed4c7aae687be9cbc3e767d87be21 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.promo-premium','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('promo-premium'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal86aed4c7aae687be9cbc3e767d87be21)): ?>
<?php $attributes = $__attributesOriginal86aed4c7aae687be9cbc3e767d87be21; ?>
<?php unset($__attributesOriginal86aed4c7aae687be9cbc3e767d87be21); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal86aed4c7aae687be9cbc3e767d87be21)): ?>
<?php $component = $__componentOriginal86aed4c7aae687be9cbc3e767d87be21; ?>
<?php unset($__componentOriginal86aed4c7aae687be9cbc3e767d87be21); ?>
<?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pesan): ?>
            <?php if (isset($component)) { $__componentOriginalfdcd7a5a16c9274b4b57c957ab5de77a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfdcd7a5a16c9274b4b57c957ab5de77a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.notifikasi-popup','data' => ['pesan' => $pesan,'judul' => 'Berhasil!']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('notifikasi-popup'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['pesan' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pesan),'judul' => 'Berhasil!']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfdcd7a5a16c9274b4b57c957ab5de77a)): ?>
<?php $attributes = $__attributesOriginalfdcd7a5a16c9274b4b57c957ab5de77a; ?>
<?php unset($__attributesOriginalfdcd7a5a16c9274b4b57c957ab5de77a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfdcd7a5a16c9274b4b57c957ab5de77a)): ?>
<?php $component = $__componentOriginalfdcd7a5a16c9274b4b57c957ab5de77a; ?>
<?php unset($__componentOriginalfdcd7a5a16c9274b4b57c957ab5de77a); ?>
<?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($galat): ?>
            <div class="flex items-center justify-between gap-3 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 px-4 py-3 text-sm text-red-800 dark:text-red-200">
                <span><?php echo e($galat); ?></span>
                <button wire:click="$set('galat', null)" class="text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-bold">&times;</button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-brand-700 dark:text-brand-300">Ringkasan bisnis</p>
            <h2 class="text-base sm:text-lg font-extrabold tracking-tight text-slate-900 dark:text-gray-100">Sekilas kos-kosanmu</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 !mt-3">
            <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Kamar Aktif','value' => $kamarAktif ?? ($ringkas['penyewaanAktif'] ?? $totalKamar ?? 0),'tone' => 'cyan','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Kamar Aktif','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kamarAktif ?? ($ringkas['penyewaanAktif'] ?? $totalKamar ?? 0)),'tone' => 'cyan','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" /></svg>']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Total Kamar','value' => $totalKamar,'tone' => 'sky','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Total Kamar','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($totalKamar),'tone' => 'sky','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" /></svg>']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Kamar Terisi','value' => $kamarTerisi,'tone' => 'emerald','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Kamar Terisi','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kamarTerisi),'tone' => 'emerald','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Pendapatan Bulan Ini','value' => 'Rp' . number_format($pendapatanBulanIni, 0, ',', '.'),'tone' => 'emerald','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Pendapatan Bulan Ini','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Rp' . number_format($pendapatanBulanIni, 0, ',', '.')),'tone' => 'emerald','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>']); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendapatanBulanLalu > 0): ?>
                    <?php $delta = round((($pendapatanBulanIni - $pendapatanBulanLalu) / $pendapatanBulanLalu) * 100); ?>
                     <?php $__env->slot('hint', null, []); ?> 
                        <span class="<?php echo e($delta >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'); ?> font-semibold"><?php echo e($delta >= 0 ? '▲' : '▼'); ?> <?php echo e(abs($delta)); ?>%</span>
                        <span class="text-gray-400 dark:text-gray-500">vs bulan lalu</span>
                     <?php $__env->endSlot(); ?>
                <?php else: ?>
                     <?php $__env->slot('hint', null, []); ?> 
                        <span class="text-gray-400 dark:text-gray-500">Bulan lalu belum ada pemasukan</span>
                     <?php $__env->endSlot(); ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

        <div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-brand-700 dark:text-brand-300">Keuangan bulan ini</p>
            <h2 class="text-base sm:text-lg font-extrabold tracking-tight text-slate-900 dark:text-gray-100">Uang masuk vs keluar</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 !mt-3">
            <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Tingkat Okupansi','value' => $okupansiSekarang . '%','tone' => 'teal','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Tingkat Okupansi','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($okupansiSekarang . '%'),'tone' => 'teal','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>']); ?>
                 <?php $__env->slot('hint', null, []); ?> 
                    <span class="text-gray-400 dark:text-gray-500"><?php echo e($kamarTerisi); ?>/<?php echo e($totalKamar); ?> kamar terisi</span>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Kamar Kosong','value' => $kamarTersedia,'tone' => 'sky','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Kamar Kosong','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kamarTersedia),'tone' => 'sky','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>']); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($kamarPerbaikan > 0): ?>
                     <?php $__env->slot('hint', null, []); ?> 
                        <span class="text-amber-600 dark:text-amber-400"><?php echo e($kamarPerbaikan); ?> kamar perbaikan</span>
                     <?php $__env->endSlot(); ?>
                <?php else: ?>
                     <?php $__env->slot('hint', null, []); ?> 
                        <span class="text-gray-400 dark:text-gray-500">Siap disewakan</span>
                     <?php $__env->endSlot(); ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Pengeluaran Bulan Ini','value' => 'Rp' . number_format($pengeluaranBulanIni, 0, ',', '.'),'tone' => 'rose','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Pengeluaran Bulan Ini','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Rp' . number_format($pengeluaranBulanIni, 0, ',', '.')),'tone' => 'rose','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>']); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pengeluaranBulanLalu > 0): ?>
                    <?php $deltaB = round((($pengeluaranBulanIni - $pengeluaranBulanLalu) / $pengeluaranBulanLalu) * 100); ?>
                     <?php $__env->slot('hint', null, []); ?> 
                        <span class="<?php echo e($deltaB <= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'); ?> font-semibold"><?php echo e($deltaB <= 0 ? '▲' : '▼'); ?> <?php echo e(abs($deltaB)); ?>%</span>
                        <span class="text-gray-400 dark:text-gray-500">vs bulan lalu</span>
                     <?php $__env->endSlot(); ?>
                <?php else: ?>
                     <?php $__env->slot('hint', null, []); ?> 
                        <span class="text-gray-400 dark:text-gray-500">Bulan lalu belum ada pengeluaran</span>
                     <?php $__env->endSlot(); ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Laba Bersih Bulan Ini','value' => 'Rp' . number_format($labaBersihBulanIni, 0, ',', '.'),'tone' => 'teal','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181m8.818 3.181l-3.182-5.511m0 0l-5.511 3.181M4.5 3.75v15m6.75 0v-4.5m3-7.5h.008v.008H14.25V5.25z" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Laba Bersih Bulan Ini','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Rp' . number_format($labaBersihBulanIni, 0, ',', '.')),'tone' => 'teal','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181m8.818 3.181l-3.182-5.511m0 0l-5.511 3.181M4.5 3.75v15m6.75 0v-4.5m3-7.5h.008v.008H14.25V5.25z" /></svg>']); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($labaBersihBulanLalu != 0): ?>
                    <?php $deltaL = $labaBersihBulanLalu > 0 ? round(($labaBersihBulanIni - $labaBersihBulanLalu) / $labaBersihBulanLalu * 100) : 0; ?>
                     <?php $__env->slot('hint', null, []); ?> 
                        <span class="<?php echo e($deltaL >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'); ?> font-semibold"><?php echo e($deltaL >= 0 ? '▲' : '▼'); ?> <?php echo e(abs($deltaL)); ?>%</span>
                        <span class="text-gray-400 dark:text-gray-500">vs bulan lalu</span>
                     <?php $__env->endSlot(); ?>
                <?php else: ?>
                     <?php $__env->slot('hint', null, []); ?> 
                        <span class="text-gray-400 dark:text-gray-500">Pendapatan - Pengeluaran</span>
                     <?php $__env->endSlot(); ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tagihanBelumCount > 0): ?>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-lg bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-300 flex items-center justify-center shrink-0">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-red-800 dark:text-red-200">Ada <?php echo e($tagihanBelumCount); ?> tagihan belum dibayar senilai <span class="font-bold">Rp<?php echo e(number_format($nilaiTagihanBelum, 0, ',', '.')); ?></span></p>
                        <p class="text-xs text-red-700/80 dark:text-red-300/80"><?php echo e($tagihanTelat > 0 ? $tagihanTelat . ' di antaranya sudah melewati jatuh tempo.' : 'Semuanya masih dalam batas jatuh tempo.'); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [['tersedia', 'Kamar Kosong', $kamarTersedia, 'bg-brand-50 dark:bg-brand-500/10 text-brand-800 dark:text-brand-200 border-brand-100 dark:border-brand-500/20'], ['terisi', 'Kamar Terisi', $kamarTerisi, 'bg-amber-50 dark:bg-amber-500/10 text-amber-800 dark:text-amber-200 border-amber-200 dark:border-amber-500/20'], ['perbaikan', 'Kamar Perbaikan', $kamarPerbaikan, 'bg-stone-100 dark:bg-gray-700/50 text-slate-600 dark:text-gray-300 border-stone-200 dark:border-gray-700']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$status, $label, $nilai, $warna]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="rounded-xl border <?php echo e($warna); ?> px-4 py-3 flex items-center justify-between">
                    <span class="text-sm font-medium"><?php echo e($label); ?></span>
                    <span class="text-xl font-bold"><?php echo e($nilai); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <a href="<?php echo e(route('pemilik.grafik')); ?>" wire:navigate
            class="group flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-xl bg-brand-900 p-5 sm:p-6 text-white transition hover:bg-brand-950">
            <div class="flex items-center gap-4">
                <span class="shrink-0 h-12 w-12 rounded-lg bg-white/10 text-white flex items-center justify-center border border-white/15">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                </span>
                <div>
                    <p class="text-base font-bold">Grafik & Analitik</p>
                    <p class="mt-0.5 text-xs text-brand-100">Keuangan, okupansi, piutang & performa tiap properti — pindah ke menu Grafik di sidebar.</p>
                </div>
            </div>
            <span class="shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-brand-900 group-hover:bg-brand-50 transition">
                Buka Grafik
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12l-7.5 7.5M21 12H3" /></svg>
            </span>
        </a>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pembayaranMenungguCount > 0): ?>
            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Pembayaran Menunggu Verifikasi</h3>
                    <span class="text-xs font-medium text-amber-600 dark:text-amber-400"><?php echo e($pembayaranMenungguCount); ?> item</span>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $pembayaranMenunggu; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pembayaran): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="px-5 py-3.5 flex items-center gap-3">
                            <div class="h-9 w-9 shrink-0 rounded-full bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-300 flex items-center justify-center">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate"><?php echo e($pembayaran->anakKos?->nama ?? 'Penyewa'); ?> · <?php echo e($pembayaran->tagihan->penyewaan->kamar?->nama); ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate"><?php echo e($pembayaran->tagihan->penyewaan->properti?->nama); ?> · <?php echo e($pembayaran->tagihan->periode); ?> · <?php echo e($pembayaran->labelMetode()); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pembayaran->bukti): ?>
                                    <a href="<?php echo e(route('pembayaran.bukti', $pembayaran->id)); ?>" target="_blank" rel="noopener"
                                        class="mt-1 inline-flex items-center gap-1 text-xs font-semibold text-teal-600 hover:text-teal-700 hover:underline dark:text-teal-400 dark:hover:text-teal-300">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        Lihat Bukti
                                    </a>
                                <?php else: ?>
                                    <p class="mt-1 text-xs italic text-gray-400 dark:text-gray-500">Tunai · tanpa bukti, pastikan uang sudah diterima</p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="text-end shrink-0">
                                <p class="text-sm font-bold text-gray-900 dark:text-gray-100">Rp<?php echo e(number_format($pembayaran->jumlah, 0, ',', '.')); ?></p>
                                <button wire:click="verifikasiPembayaran(<?php echo e($pembayaran->id); ?>)" wire:confirm="Sudah periksa bukti transfernya? Verifikasi pembayaran ini?"
                                    class="mt-1 inline-flex items-center gap-1 rounded-lg bg-brand-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-800 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    Verifikasi
                                </button>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500">Tidak ada pembayaran menunggu verifikasi.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Pembayaran Terbaru</h3>
                    <span class="text-xs text-gray-400 dark:text-gray-500">Terverifikasi</span>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $pembayaranTerbaru; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pembayaran): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="px-5 py-3.5 flex items-center gap-3">
                            <div class="h-9 w-9 shrink-0 rounded-full bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-300 flex items-center justify-center">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate"><?php echo e($pembayaran->tagihan->penyewaan->anakKos?->nama ?? 'Penyewa'); ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate"><?php echo e($pembayaran->tagihan->penyewaan->properti?->nama); ?> · <?php echo e($pembayaran->tagihan->periode); ?></p>
                            </div>
                            <div class="text-end shrink-0">
                                <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400">+Rp<?php echo e(number_format($pembayaran->jumlah, 0, ',', '.')); ?></p>
                                <p class="text-xs text-gray-400 dark:text-gray-500"><?php echo e($pembayaran->verified_at?->translatedFormat('d M Y')); ?></p>
                                <a href="<?php echo e(route('pembayaran.kwitansi', $pembayaran)); ?>" target="_blank" rel="noopener"
                                    class="mt-1 inline-flex items-center gap-1 rounded-lg bg-brand-700 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-brand-800 transition">
                                    Kwitansi<?php echo e($pembayaran->nomor_kwitansi ? ' '.$pembayaran->nomor_kwitansi : ''); ?>

                                </a>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500">Belum ada pembayaran terverifikasi.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Tagihan Belum Dibayar</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-rose-500 dark:text-rose-400"><?php echo e($tagihanBelumCount); ?> item</span>
                        <a href="<?php echo e(route('pemilik.tagihan')); ?>" wire:navigate class="text-xs font-semibold text-teal-600 dark:text-teal-400 hover:underline">Lihat semua →</a>
                    </div>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $tagihanBelumList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tagihan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="px-5 py-3.5 flex items-center gap-3">
                            <div class="h-9 w-9 shrink-0 rounded-full bg-rose-50 dark:bg-rose-900/40 text-rose-600 dark:text-rose-300 flex items-center justify-center">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate"><?php echo e($tagihan->penyewaan->anakKos?->nama ?? 'Penyewa'); ?> · <?php echo e($tagihan->penyewaan->kamar?->nama); ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate"><?php echo e($tagihan->periode); ?> · jatuh tempo <?php echo e($tagihan->jatuh_tempo?->translatedFormat('d M Y')); ?></p>
                            </div>
                            <div class="text-end shrink-0">
                                <p class="text-sm font-bold text-rose-600 dark:text-rose-400">Rp<?php echo e(number_format($tagihan->jumlah + $tagihan->denda, 0, ',', '.')); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tagihan->denda > 0): ?>
                                    <p class="text-xs text-rose-500 dark:text-rose-400">denda Rp<?php echo e(number_format($tagihan->denda, 0, ',', '.')); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500">Semua tagihan sudah lunas. Bagus!</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php /**PATH C:\laragon\www\Ngekos.in\resources\views\livewire/pages/dashboard/pemilik.blade.php ENDPATH**/ ?>