<?php

use App\Models\Kamar;
use App\Models\Properti;
use App\Models\Ulasan;
use App\Services\PenyewaanService;
use App\Support\Koordinat;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

?>

<div>
    <!-- Toast sukses booking -->
    <div x-data="{ buka: false, isi: '' }"
         x-on:booking-sukses.window="isi = $event.detail.pesan; buka = true; clearTimeout(window.__toastBooking); window.__toastBooking = setTimeout(() => buka = false, 5000)"
         class="fixed top-4 inset-x-0 z-[60] flex justify-center px-4 pointer-events-none"
         role="alert">
        <div x-show="buka"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-3"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0 -translate-y-3"
             class="pointer-events-auto flex max-w-md items-start gap-3 rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-xl shadow-emerald-900/30 ring-1 ring-white/20">
            <svg class="h-5 w-5 shrink-0 mt-0.5 text-emerald-100" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <div class="min-w-0">
                <p class="font-extrabold">Kamar Berhasil Dipesan</p>
                <p class="mt-0.5 text-emerald-50 text-xs leading-relaxed" x-text="isi"></p>
            </div>
            <button type="button" @click="buka = false" class="shrink-0 -m-1 rounded-lg p-1 text-emerald-100 hover:bg-emerald-500 transition" aria-label="Tutup">&times;</button>
        </div>
    </div>

    <!-- Cover Image -->
    <?php if (isset($component)) { $__componentOriginaleebe84d570c4b6ef634b920d4227baca = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaleebe84d570c4b6ef634b920d4227baca = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.galeri-kos','data' => ['fotos' => $properti->galeriUrls(),'nama' => $properti->nama,'kelas' => 'relative h-56 sm:h-72']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('galeri-kos'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['fotos' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($properti->galeriUrls()),'nama' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($properti->nama),'kelas' => 'relative h-56 sm:h-72']); ?>
        <a href="<?php echo e(route('kos.index')); ?>" wire:navigate
           class="absolute top-4 left-4 inline-flex items-center gap-1.5 rounded-xl bg-black/40 backdrop-blur-sm px-3 py-2 text-sm font-medium text-white hover:bg-black/60 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
            Kembali
        </a>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
            <a href="<?php echo e(route('chat.room', ['properti' => $properti->id])); ?>" wire:navigate
               class="absolute top-4 right-4 inline-flex items-center gap-1.5 rounded-xl bg-black/40 backdrop-blur-sm px-3 py-2 text-sm font-medium text-white hover:bg-black/60 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                Chat
            </a>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaleebe84d570c4b6ef634b920d4227baca)): ?>
<?php $attributes = $__attributesOriginaleebe84d570c4b6ef634b920d4227baca; ?>
<?php unset($__attributesOriginaleebe84d570c4b6ef634b920d4227baca); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaleebe84d570c4b6ef634b920d4227baca)): ?>
<?php $component = $__componentOriginaleebe84d570c4b6ef634b920d4227baca; ?>
<?php unset($__componentOriginaleebe84d570c4b6ef634b920d4227baca); ?>
<?php endif; ?>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($galat): ?>
            <div class="mb-4 flex items-center justify-between gap-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 ring-1 ring-rose-200 dark:ring-rose-500/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                <span><?php echo e($galat); ?></span>
                <button wire:click="$set('galat', null)" class="text-rose-500 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 font-bold">&times;</button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pesan): ?>
            <div class="mb-4 flex items-center justify-between gap-3 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 ring-1 ring-emerald-200 dark:ring-emerald-500/30 px-4 py-3 text-sm text-emerald-800 dark:text-emerald-200">
                <span><?php echo e($pesan); ?></span>
                <button wire:click="$set('pesan', null)" class="text-emerald-500 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 font-bold">&times;</button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Info Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-6 -mt-8 relative z-10">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-gray-100"><?php echo e($properti->nama); ?></h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                        <?php echo e($properti->alamat ?? $properti->kota ?? 'Lokasi belum diisi'); ?>

                    </p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($titik): ?>
                        <div class="mt-3 overflow-hidden rounded-xl ring-1 ring-gray-100 dark:ring-gray-700">
                            <div id="peta-properti-detail"
                                wire:ignore
                                class="h-48 sm:h-56 w-full z-0"
                                role="region"
                                aria-label="Peta lokasi <?php echo e($properti->nama); ?>"
                                data-lat="<?php echo e($titik[0]); ?>"
                                data-lng="<?php echo e($titik[1]); ?>"
                                data-nama="<?php echo e($properti->nama); ?>"
                                data-alamat="<?php echo e($properti->alamat); ?>"></div>
                            <a id="peta-buka-osm" href="#" target="_blank" rel="noopener nofollow"
                               class="flex items-center justify-center gap-1 border-t border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-medium text-teal-600 hover:text-teal-500 dark:text-teal-400 dark:hover:text-teal-300">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" /></svg>
                                Buka di Google Maps
                            </a>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="toggleFavorit"
                        class="<?php echo \Illuminate\Support\Arr::toCssClasses(['shrink-0 inline-flex items-center justify-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition h-10 w-10 border',
                                'border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-500/20' => $favorit,
                                'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-400 dark:text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-rose-500' => !$favorit]); ?>"
                        aria-label="<?php echo e($favorit ? 'Hapus dari favorit' : 'Tambah ke favorit'); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($favorit): ?>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                        <?php else: ?>
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </button>
                    <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $properti->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($properti->status)]); ?>
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
                    <span class="inline-flex items-center rounded-full bg-teal-50 dark:bg-teal-500/10 px-2.5 py-0.5 text-xs font-medium text-teal-700 dark:text-teal-300 ring-1 ring-inset ring-teal-200 dark:ring-teal-500/30">
                        <?php echo e($properti->kamar_tersedia); ?>/<?php echo e($properti->total_kamar); ?> tersedia
                    </span>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($statistikUlasan && $statistikUlasan['total'] > 0): ?>
                    <div class="mt-2 flex items-center gap-1.5">
                        <?php if (isset($component)) { $__componentOriginalfa87e49ca3cdf62358bbc468aaf3394b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfa87e49ca3cdf62358bbc468aaf3394b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.star-rating','data' => ['rating' => round($statistikUlasan['rata']),'size' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('star-rating'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['rating' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(round($statistikUlasan['rata'])),'size' => 'h-3.5 w-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfa87e49ca3cdf62358bbc468aaf3394b)): ?>
<?php $attributes = $__attributesOriginalfa87e49ca3cdf62358bbc468aaf3394b; ?>
<?php unset($__attributesOriginalfa87e49ca3cdf62358bbc468aaf3394b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfa87e49ca3cdf62358bbc468aaf3394b)): ?>
<?php $component = $__componentOriginalfa87e49ca3cdf62358bbc468aaf3394b; ?>
<?php unset($__componentOriginalfa87e49ca3cdf62358bbc468aaf3394b); ?>
<?php endif; ?>
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300"><?php echo e(number_format($statistikUlasan['rata'], 1, ',', '.')); ?></span>
                        <span class="text-xs text-gray-400 dark:text-gray-500">(<?php echo e($statistikUlasan['total']); ?> ulasan)</span>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="mt-4 grid grid-cols-3 gap-3">
                <div class="rounded-xl bg-gray-50 dark:bg-gray-700/50 p-3 text-center">
                    <p class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Harga Mulai</p>
                    <p class="mt-1">
                        <?php
                            $termurahBulan = $termurah['bulan'] ?? null;
                            $termurahMinggu = $termurah['minggu'] ?? null;
                            $termurahHari = $termurah['hari'] ?? null;
                            $adaDiskonDetail = $properti->harga_asli && $termurahBulan && $properti->harga_asli > $termurahBulan;
                        ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($termurahBulan): ?>
                            <?php if (isset($component)) { $__componentOriginal9b7fa0d7349cf7f9eb91e881f4695d51 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9b7fa0d7349cf7f9eb91e881f4695d51 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.harga-tiga-periode','data' => ['bulanan' => $termurahBulan,'mingguan' => $termurahMinggu,'harian' => $termurahHari,'asli' => $adaDiskonDetail ? $properti->harga_asli : null,'varian' => 'baris']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('harga-tiga-periode'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['bulanan' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($termurahBulan),'mingguan' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($termurahMinggu),'harian' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($termurahHari),'asli' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($adaDiskonDetail ? $properti->harga_asli : null),'varian' => 'baris']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9b7fa0d7349cf7f9eb91e881f4695d51)): ?>
<?php $attributes = $__attributesOriginal9b7fa0d7349cf7f9eb91e881f4695d51; ?>
<?php unset($__attributesOriginal9b7fa0d7349cf7f9eb91e881f4695d51); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9b7fa0d7349cf7f9eb91e881f4695d51)): ?>
<?php $component = $__componentOriginal9b7fa0d7349cf7f9eb91e881f4695d51; ?>
<?php unset($__componentOriginal9b7fa0d7349cf7f9eb91e881f4695d51); ?>
<?php endif; ?>
                        <?php else: ?>
                            <span class="text-xs font-medium text-gray-400 dark:text-gray-500">Penuh</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </p>
                </div>
                <div class="rounded-xl bg-gray-50 dark:bg-gray-700/50 p-3 text-center">
                    <p class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Denda</p>
                    <p class="mt-1 text-sm font-bold text-gray-900 dark:text-gray-100">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($properti->denda_per_hari): ?>
                            Rp<?php echo e(number_format($properti->denda_per_hari, 0, ',', '.')); ?><span class="text-[10px] text-gray-400 dark:text-gray-500">/hr</span>
                        <?php else: ?>
                            <span class="text-xs text-gray-400 dark:text-gray-500">Tidak ada</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </p>
                </div>
                <div class="rounded-xl bg-gray-50 dark:bg-gray-700/50 p-3 text-center">
                    <p class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Kontak</p>
                    <p class="mt-1 text-sm font-bold text-gray-900 dark:text-gray-100 truncate"><?php echo e($properti->pemilik?->no_hp ?? '-'); ?></p>
                </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! auth()->check() || auth()->user()->hasRole('anak_kos')): ?>
                <div class="mt-4 rounded-xl bg-teal-50 dark:bg-teal-500/10 ring-1 ring-teal-100 dark:ring-teal-500/30 p-3 sm:p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Punya pertanyaan?</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Tanya langsung pemiliknya lewat chat.</p>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->check()): ?>
                        <a href="<?php echo e(route('chat.room', ['properti' => $properti->id])); ?>" wire:navigate
                           class="shrink-0 inline-flex items-center justify-center gap-1.5 rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                            Tanya Pemilik
                        </a>
                    <?php else: ?>
                        <a href="<?php echo e(route('login')); ?>" wire:navigate
                           class="shrink-0 inline-flex items-center justify-center rounded-lg border border-teal-200 dark:border-teal-500/30 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-semibold text-teal-600 dark:text-teal-400 hover:bg-teal-100 dark:hover:bg-teal-500/10 transition">
                            Masuk untuk Chat
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($properti->deskripsi): ?>
                <div class="mt-4">
                    <h2 class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wide">Deskripsi</h2>
                    <p class="mt-1.5 text-sm text-gray-600 dark:text-gray-300 leading-relaxed"><?php echo e($properti->deskripsi); ?></p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($properti->fasilitas): ?>
                <div class="mt-4">
                    <h2 class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wide">Fasilitas</h2>
                    <div class="mt-2">
                        <?php if (isset($component)) { $__componentOriginalb22b9ef51f0fdeb575f9479da907b656 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb22b9ef51f0fdeb575f9479da907b656 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.facility-icons','data' => ['fasilitas' => $properti->fasilitas]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('facility-icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['fasilitas' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($properti->fasilitas)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb22b9ef51f0fdeb575f9479da907b656)): ?>
<?php $attributes = $__attributesOriginalb22b9ef51f0fdeb575f9479da907b656; ?>
<?php unset($__attributesOriginalb22b9ef51f0fdeb575f9479da907b656); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb22b9ef51f0fdeb575f9479da907b656)): ?>
<?php $component = $__componentOriginalb22b9ef51f0fdeb575f9479da907b656; ?>
<?php unset($__componentOriginalb22b9ef51f0fdeb575f9479da907b656); ?>
<?php endif; ?>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($properti->aturan): ?>
                <div class="mt-4">
                    <h2 class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wide">Aturan Kos</h2>
                    <p class="mt-1.5 text-sm text-gray-600 dark:text-gray-300 leading-relaxed"><?php echo e($properti->aturan); ?></p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Iklan Partner -->
        <div class="mt-6">
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

        <!-- Informasi Pemilik -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($properti->pemilik): ?>
            <div class="mt-6 rounded-2xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-5">
                <h2 class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wide">Informasi Pemilik</h2>
                <div class="mt-3 flex items-center gap-3">
                    <?php if (isset($component)) { $__componentOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.user-avatar','data' => ['user' => $properti->pemilik,'size' => 'md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('user-avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($properti->pemilik),'size' => 'md']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e)): ?>
<?php $attributes = $__attributesOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e; ?>
<?php unset($__attributesOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e)): ?>
<?php $component = $__componentOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e; ?>
<?php unset($__componentOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e); ?>
<?php endif; ?>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate"><?php echo e($properti->pemilik->nama); ?></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($properti->pemilik->no_hp): ?>
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" /></svg>
                                <?php echo e($properti->pemilik->no_hp); ?>

                            <?php else: ?>
                                Kontak belum diisi
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </p>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->id !== $properti->pemilik_id): ?>
                            <a href="<?php echo e(route('chat.room', ['properti' => $properti->id])); ?>" wire:navigate
                               class="shrink-0 inline-flex items-center gap-1.5 rounded-xl bg-teal-50 dark:bg-teal-500/10 ring-1 ring-teal-200 dark:ring-teal-500/30 px-3.5 py-2 text-xs font-semibold text-teal-700 dark:text-teal-300 hover:bg-teal-100 dark:hover:bg-teal-500/20 transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                                Chat
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <!-- Daftar Kamar -->
        <div class="mt-6">
            <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-gray-100">Daftar Kamar</h2>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Pilih kamar yang tersedia dan tanya pemiliknya lewat chat.</p>

            <div class="mt-3 space-y-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $kamars; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kamar): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                        <div class="flex">
                            <?php if (isset($component)) { $__componentOriginaleebe84d570c4b6ef634b920d4227baca = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaleebe84d570c4b6ef634b920d4227baca = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.galeri-kos','data' => ['fotos' => $kamar->galeriUrls(),'nama' => 'Kamar ' . $kamar->nama,'kelas' => 'h-28 sm:h-36 w-24 sm:w-36 shrink-0','ringkas' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('galeri-kos'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['fotos' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kamar->galeriUrls()),'nama' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Kamar ' . $kamar->nama),'kelas' => 'h-28 sm:h-36 w-24 sm:w-36 shrink-0','ringkas' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaleebe84d570c4b6ef634b920d4227baca)): ?>
<?php $attributes = $__attributesOriginaleebe84d570c4b6ef634b920d4227baca; ?>
<?php unset($__attributesOriginaleebe84d570c4b6ef634b920d4227baca); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaleebe84d570c4b6ef634b920d4227baca)): ?>
<?php $component = $__componentOriginaleebe84d570c4b6ef634b920d4227baca; ?>
<?php unset($__componentOriginaleebe84d570c4b6ef634b920d4227baca); ?>
<?php endif; ?>

                            <div class="flex-1 p-3 sm:p-4 flex flex-col justify-between">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-gray-100"><?php echo e($kamar->nama); ?></h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($kamar->kapasitas); ?> orang</p>
                                    </div>
                                    <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $kamar->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kamar->status)]); ?>
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

                                <div class="mt-2 flex items-end justify-between">
                                    <div>
                                        <?php if (isset($component)) { $__componentOriginal9b7fa0d7349cf7f9eb91e881f4695d51 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9b7fa0d7349cf7f9eb91e881f4695d51 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.harga-tiga-periode','data' => ['bulanan' => $kamar->harga_sewa_bulanan,'mingguan' => $kamar->harga_sewa_mingguan,'harian' => $kamar->harga_sewa_harian,'asli' => ($kamar->harga_asli && $kamar->harga_asli > $kamar->harga_sewa_bulanan) ? $kamar->harga_asli : null,'varian' => 'rincian']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('harga-tiga-periode'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['bulanan' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kamar->harga_sewa_bulanan),'mingguan' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kamar->harga_sewa_mingguan),'harian' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kamar->harga_sewa_harian),'asli' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($kamar->harga_asli && $kamar->harga_asli > $kamar->harga_sewa_bulanan) ? $kamar->harga_asli : null),'varian' => 'rincian']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9b7fa0d7349cf7f9eb91e881f4695d51)): ?>
<?php $attributes = $__attributesOriginal9b7fa0d7349cf7f9eb91e881f4695d51; ?>
<?php unset($__attributesOriginal9b7fa0d7349cf7f9eb91e881f4695d51); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9b7fa0d7349cf7f9eb91e881f4695d51)): ?>
<?php $component = $__componentOriginal9b7fa0d7349cf7f9eb91e881f4695d51; ?>
<?php unset($__componentOriginal9b7fa0d7349cf7f9eb91e881f4695d51); ?>
<?php endif; ?>
                                    </div>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($kamar->status === 'tersedia'): ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('anak_kos')): ?>
                                                <div class="flex items-center gap-2">
                                                    <button wire:click="pesanKamar(<?php echo e($kamar->id); ?>)" wire:loading.attr="disabled"
                                                        class="shrink-0 inline-flex items-center justify-center gap-1.5 rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-500 transition disabled:opacity-50">
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                                                        Sewa
                                                    </button>
                                                    <a href="<?php echo e(route('chat.room', ['properti' => $properti->id])); ?>" wire:navigate
                                                        class="shrink-0 inline-flex items-center justify-center gap-1.5 rounded-lg border border-teal-200 dark:border-teal-500/30 bg-teal-50 dark:bg-teal-500/10 px-3 py-1.5 text-xs font-semibold text-teal-600 dark:text-teal-400 hover:bg-teal-100 dark:hover:bg-teal-500/20 transition">
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                                                        Chat
                                                    </a>
                                                </div>
                                            <?php else: ?>
                                                <a href="<?php echo e(route('dashboard')); ?>" wire:navigate
                                                    class="shrink-0 inline-flex items-center justify-center rounded-lg border border-teal-200 bg-teal-50 px-3 py-1.5 text-xs font-semibold text-teal-600 dark:border-teal-500/30 dark:bg-teal-500/10 dark:text-teal-300 hover:bg-teal-100 dark:hover:bg-teal-500/20 transition">
                                                    Kelola
                                                </a>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php else: ?>
                                            <a href="<?php echo e(route('login')); ?>" wire:navigate
                                                class="shrink-0 inline-flex items-center justify-center rounded-lg border border-teal-200 bg-teal-50 px-3 py-1.5 text-xs font-semibold text-teal-600 dark:border-teal-500/30 dark:bg-teal-500/10 dark:text-teal-300 hover:bg-teal-100 dark:hover:bg-teal-500/20 transition">
                                                Masuk
                                            </a>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-xs font-medium text-gray-400 dark:text-gray-500">Terisi</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="py-10 text-center">
                        <div class="mx-auto h-12 w-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                            <svg class="h-6 w-6 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                        </div>
                        <p class="text-sm text-gray-400 dark:text-gray-500">Belum ada kamar terdaftar.</p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <!-- Ulasan & Rating -->
        <div class="mt-6">
            <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-gray-100">Ulasan &amp; Rating</h2>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Penilaian dari penghuni kos.</p>

            <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4">
                
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-5">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($statistikUlasan && $statistikUlasan['total'] > 0): ?>
                        <div class="flex items-center gap-3">
                            <span class="text-3xl sm:text-4xl font-extrabold text-gray-900 dark:text-gray-100"><?php echo e(number_format($statistikUlasan['rata'], 1, ',', '.')); ?></span>
                            <div>
                                <?php if (isset($component)) { $__componentOriginalfa87e49ca3cdf62358bbc468aaf3394b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfa87e49ca3cdf62358bbc468aaf3394b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.star-rating','data' => ['rating' => round($statistikUlasan['rata']),'size' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('star-rating'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['rating' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(round($statistikUlasan['rata'])),'size' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfa87e49ca3cdf62358bbc468aaf3394b)): ?>
<?php $attributes = $__attributesOriginalfa87e49ca3cdf62358bbc468aaf3394b; ?>
<?php unset($__attributesOriginalfa87e49ca3cdf62358bbc468aaf3394b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfa87e49ca3cdf62358bbc468aaf3394b)): ?>
<?php $component = $__componentOriginalfa87e49ca3cdf62358bbc468aaf3394b; ?>
<?php unset($__componentOriginalfa87e49ca3cdf62358bbc468aaf3394b); ?>
<?php endif; ?>
                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500"><?php echo e($statistikUlasan['total']); ?> ulasan</p>
                            </div>
                        </div>
                        <div class="mt-4 space-y-1.5">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($bintang = 5; $bintang >= 1; $bintang--): ?>
                                <?php
                                    $jumlah = $statistikUlasan['distribusi'][$bintang] ?? 0;
                                    $persen = $statistikUlasan['total'] > 0 ? round(($jumlah / $statistikUlasan['total']) * 100) : 0;
                                ?>
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="w-8 font-semibold text-gray-500 dark:text-gray-400 shrink-0"><?php echo e($bintang); ?>★</span>
                                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                        <div class="h-full rounded-full bg-amber-400" style="width: <?php echo e($persen); ?>%"></div>
                                    </div>
                                    <span class="w-8 text-right text-gray-400 dark:text-gray-500"><?php echo e($jumlah); ?></span>
                                </div>
                            <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada ulasan untuk kos ini. Jadilah yang pertama memberi penilaian.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-5 md:col-span-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('anak_kos')): ?>
                            <div x-data="{ nilai: <?php echo e($ratingUlasan); ?>, hover: 0 }">
                                <p class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wide">Berikan Ulasan</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Klik bintang lalu tekan Kirim untuk menyimpan ulasanmu.</p>
                                <div class="mt-2 flex items-center gap-1">
                                    <template x-for="i in 5" :key="i">
                                        <button type="button" x-on:mouseenter="hover = i" x-on:mouseleave="hover = 0"
                                            x-on:click="nilai = i; $wire.set('ratingUlasan', i)"
                                            class="p-0.5 transition-transform hover:scale-110 focus:outline-none"
                                            :aria-label="'Beri rating ' + i + ' dari 5'">
                                            <svg x-show="i <= (hover || nilai)" class="h-6 w-6 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.446a1 1 0 00-.363 1.118l1.286 3.958c.3.922-.755 1.688-1.539 1.118l-3.367-2.446a1 1 0 00-1.176 0l-3.367 2.446c-.784.57-1.838-.196-1.539-1.118l1.286-3.958a1 1 0 00-.363-1.118L2.126 9.385c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.958z"/></svg>
                                            <svg x-show="i > (hover || nilai)" class="h-6 w-6 text-gray-300 dark:text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.446a1 1 0 00-.363 1.118l1.286 3.958c.3.922-.755 1.688-1.539 1.118l-3.367-2.446a1 1 0 00-1.176 0l-3.367 2.446c-.784.57-1.838-.196-1.539-1.118l1.286-3.958a1 1 0 00-.363-1.118L2.126 9.385c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.958z"/></svg>
                                        </button>
                                    </template>
                                    <span x-show="nilai > 0" x-text="nilai + ' dari 5'" class="ml-2 text-xs font-semibold text-gray-700 dark:text-gray-300"></span>
                                </div>
                                <textarea wire:model="komentarUlasan" rows="3"
                                    class="mt-3 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm"
                                    placeholder="Bagikan pengalamanmu tinggal di kos ini (opsional)"></textarea>
                                <div class="mt-2 flex items-center justify-end gap-2">
                                    <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => ['wire:click' => 'simpanUlasan','wire:loading.attr' => 'disabled','xShow' => 'nilai > 0','class' => 'text-xs px-3 py-1.5 disabled:opacity-60']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'simpanUlasan','wire:loading.attr' => 'disabled','x-show' => 'nilai > 0','class' => 'text-xs px-3 py-1.5 disabled:opacity-60']); ?>
                                        <span wire:loading.remove wire:target="simpanUlasan">Kirim Ulasan</span>
                                        <span wire:loading wire:target="simpanUlasan" class="inline-flex items-center gap-1.5">
                                            <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                                            Mengirim...
                                        </span>
                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $attributes = $__attributesOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__attributesOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $component = $__componentOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__componentOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
                                    <span x-show="nilai == 0" class="text-xs text-gray-400 dark:text-gray-500">Pilih bintang dulu untuk mengirim</span>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Hanya pengguna dengan akun <strong>anak kos</strong> yang dapat memberikan ulasan.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Silakan <a href="<?php echo e(route('login')); ?>" wire:navigate class="font-semibold text-teal-600 dark:text-teal-400 hover:underline">masuk</a> untuk memberikan ulasan.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <div class="mt-4 divide-y divide-gray-100 dark:divide-gray-700">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $ulasans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ulasan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="py-3 first:pt-0 last:pb-0">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <?php if (isset($component)) { $__componentOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.user-avatar','data' => ['user' => $ulasan->user,'size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('user-avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ulasan->user),'size' => 'sm']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e)): ?>
<?php $attributes = $__attributesOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e; ?>
<?php unset($__attributesOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e)): ?>
<?php $component = $__componentOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e; ?>
<?php unset($__componentOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e); ?>
<?php endif; ?>
                                        <div>
                                            <p class="text-xs font-bold text-gray-900 dark:text-gray-100"><?php echo e($ulasan->user->nama); ?></p>
                                            <p class="text-[10px] text-gray-400 dark:text-gray-500"><?php echo e($ulasan->created_at->locale('id')->diffForHumans()); ?></p>
                                        </div>
                                    </div>
                                    <?php if (isset($component)) { $__componentOriginalfa87e49ca3cdf62358bbc468aaf3394b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfa87e49ca3cdf62358bbc468aaf3394b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.star-rating','data' => ['rating' => $ulasan->rating,'size' => 'h-3 w-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('star-rating'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['rating' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ulasan->rating),'size' => 'h-3 w-3']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfa87e49ca3cdf62358bbc468aaf3394b)): ?>
<?php $attributes = $__attributesOriginalfa87e49ca3cdf62358bbc468aaf3394b; ?>
<?php unset($__attributesOriginalfa87e49ca3cdf62358bbc468aaf3394b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfa87e49ca3cdf62358bbc468aaf3394b)): ?>
<?php $component = $__componentOriginalfa87e49ca3cdf62358bbc468aaf3394b; ?>
<?php unset($__componentOriginalfa87e49ca3cdf62358bbc468aaf3394b); ?>
<?php endif; ?>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ulasan->komentar): ?>
                                    <p class="mt-1.5 text-xs text-gray-600 dark:text-gray-300 leading-relaxed"><?php echo e($ulasan->komentar); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="py-3 text-xs text-gray-400 dark:text-gray-500 text-center">Belum ada ulasan yang ditulis.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($modalKamarId): ?>
    <?php
        $kamarModal = $kamars->firstWhere('id', $modalKamarId);
    ?>
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
        <button type="button" wire:click="tutupModalSewa" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm cursor-default" tabindex="-1" aria-label="Tutup"></button>
        <div class="relative min-h-full flex items-end sm:items-center justify-center p-4">
            <div class="w-full sm:max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-xl ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate">Sewa Kamar <?php echo e($kamarModal?->nama); ?></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate"><?php echo e($properti->nama); ?>

                            &middot; Rp<?php echo e(number_format($kamarModal?->harga_sewa_bulanan ?? 0, 0, ',', '.')); ?>/bulan
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($kamarModal?->harga_sewa_mingguan): ?> &middot; Rp<?php echo e(number_format($kamarModal->harga_sewa_mingguan, 0, ',', '.')); ?>/minggu <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($kamarModal?->harga_sewa_harian): ?> &middot; Rp<?php echo e(number_format($kamarModal->harga_sewa_harian, 0, ',', '.')); ?>/hari <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></p>
                    </div>
                    <button type="button" wire:click="tutupModalSewa"
                        class="shrink-0 h-8 w-8 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-500 dark:text-gray-400 flex items-center justify-center transition">&times;</button>
                </div>

                <form wire:submit="konfirmasiSewa" class="p-5 space-y-4">
                    
                    <div wire:key="langkah-1" <?php if($langkahSewa !== 1): ?> class="hidden" <?php endif; ?>>
                        <p class="text-xs text-gray-400 dark:text-gray-500">Kamar yang tersedia akan langsung terkunci untukmu — tanpa menunggu konfirmasi. Pilih tanggal kamu berencana masuk (maksimal 3 bulan ke depan) dan lama sewa. Tagihan dibuat otomatis untuk dibayar.</p>
                        <div class="mt-4">
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Tanggal Masuk</label>
                            <input type="date" wire:model="tanggalMasuk" min="<?php echo e(today()->toDateString()); ?>" max="<?php echo e(today()->addMonths(3)->toDateString()); ?>"
                                class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm text-gray-700">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['tanggalMasuk'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="mt-4">
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Periode Sewa</label>
                            <?php
                                $opsiPeriode = [
                                    'bulanan' => ['label' => 'Per Bulan', 'harga' => $kamarModal?->harga_sewa_bulanan],
                                    'mingguan' => ['label' => 'Per Minggu', 'harga' => $kamarModal?->harga_sewa_mingguan],
                                    'harian' => ['label' => 'Per Hari', 'harga' => $kamarModal?->harga_sewa_harian],
                                ];
                            ?>
                            <div class="grid grid-cols-3 gap-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $opsiPeriode; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nilai => $opsi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $tersedia = $opsi['harga'] !== null && (float) $opsi['harga'] > 0; ?>
                                    <button type="button" wire:click="$set('periodeSewa', '<?php echo e($nilai); ?>')"
                                        <?php if(! $tersedia): ?> disabled title="Pemilik tidak membuka sewa <?php echo e(strtolower($opsi['label'])); ?>" <?php endif; ?>
                                        class="<?php echo \Illuminate\Support\Arr::toCssClasses(['rounded-xl border px-2 py-2.5 text-sm font-semibold transition',
                                                'bg-teal-600 border-teal-600 text-white' => $periodeSewa === $nilai && $tersedia,
                                                'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' => $periodeSewa !== $nilai && $tersedia,
                                                'border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 text-gray-300 dark:text-gray-600 cursor-not-allowed' => ! $tersedia]); ?>">
                                        <?php echo e($opsi['label']); ?>

                                        <span class="block text-[10px] font-normal opacity-70">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tersedia): ?>
                                                Rp<?php echo e(number_format($opsi['harga'], 0, ',', '.')); ?>

                                            <?php else: ?>
                                                Tdk tersedia
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </span>
                                    </button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['periodeSewa'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="mt-4">
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">
                                <?php echo e($periodeSewa === 'harian' ? 'Lama Sewa (hari)' : ($periodeSewa === 'mingguan' ? 'Lama Sewa (minggu)' : 'Lama Sewa (bulan)')); ?>

                            </label>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($periodeSewa === 'harian'): ?>
                                <select wire:model="durasiHari"
                                    class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm text-gray-700">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = range(1, 90); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hari): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($hari); ?>"><?php echo e($hari); ?> hari</option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['durasiHari'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php elseif($periodeSewa === 'mingguan'): ?>
                                <select wire:model="durasiMinggu"
                                    class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm text-gray-700">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $minggu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($minggu); ?>"><?php echo e($minggu); ?> minggu</option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['durasiMinggu'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php else: ?>
                                <select wire:model="durasiBulan"
                                    class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm text-gray-700">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bulan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($bulan); ?>"><?php echo e($bulan); ?> bulan</option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['durasiBulan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="mt-4">
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Foto KTP <span class="text-rose-500">*</span></label>
                            <input type="file" wire:model="ktp" accept=".jpg,.jpeg,.png,.webp,.pdf"
                                class="w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-teal-50 dark:file:bg-teal-500/10 file:px-4 file:py-2 file:text-teal-700 dark:file:text-teal-300 file:font-semibold hover:file:bg-teal-100 dark:hover:file:bg-teal-500/20">
                            <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">Wajib. JPG/PNG/WEBP/PDF, maks 2MB. Data hanya untuk verifikasi pemilik.</p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['ktp'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div wire:loading wire:target="ktp" class="mt-2 flex items-center gap-1.5 text-xs font-medium text-teal-600">
                                <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Mengunggah KTP...
                            </div>
                        </div>
                        <div class="flex flex-col-reverse sm:flex-row gap-2 pt-3">
                            <button type="button" wire:click="tutupModalSewa" wire:loading.attr="disabled"
                                class="flex-1 inline-flex items-center justify-center rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Batal
                            </button>
                            <button type="button" wire:click="lanjutReview" wire:loading.attr="disabled" wire:target="lanjutReview"
                                class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-500 transition disabled:opacity-50">
                                Lanjut ke Review
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                            </button>
                        </div>
                    </div>

                    
                    <div wire:key="langkah-2" <?php if($langkahSewa !== 2): ?> class="hidden" <?php endif; ?>>
                        <div class="rounded-xl ring-1 ring-gray-100 dark:ring-gray-700 bg-gray-50 dark:bg-gray-700/30 divide-y divide-gray-100 dark:divide-gray-600 overflow-hidden">
                            <?php
                                $harga = $kamarModal?->hargaUntuk($periodeSewa) ?? $kamarModal?->harga_sewa_bulanan ?? 0;
                                $durasi = $periodeSewa === 'harian' ? $durasiHari : ($periodeSewa === 'mingguan' ? $durasiMinggu : $durasiBulan);
                                $periode = $periodeSewa === 'harian' ? 'hari' : ($periodeSewa === 'mingguan' ? 'minggu' : 'bulan');
                            ?>
                            <div class="flex justify-between gap-2 px-4 py-2.5 text-xs">
                                <span class="text-gray-500 dark:text-gray-400">Kos</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100 text-end"><?php echo e($properti->nama); ?></span>
                            </div>
                            <div class="flex justify-between gap-2 px-4 py-2.5 text-xs">
                                <span class="text-gray-500 dark:text-gray-400">Kamar</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100 text-end"><?php echo e($kamarModal?->nama); ?> &middot; <?php echo e($kamarModal?->kapasitas); ?> org</span>
                            </div>
                            <div class="flex justify-between gap-2 px-4 py-2.5 text-xs">
                                <span class="text-gray-500 dark:text-gray-400">Harga Sewa</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100 text-end">Rp<?php echo e(number_format($harga, 0, ',', '.')); ?>/<?php echo e($periode); ?></span>
                            </div>
                            <div class="flex justify-between gap-2 px-4 py-2.5 text-xs">
                                <span class="text-gray-500 dark:text-gray-400">Tanggal Masuk</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100 text-end"><?php echo e(Carbon::parse($tanggalMasuk)->locale('id')->translatedFormat('d F Y')); ?></span>
                            </div>
                            <div class="flex justify-between gap-2 px-4 py-2.5 text-xs">
                                <span class="text-gray-500 dark:text-gray-400">Lama Sewa</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100 text-end"><?php echo e($durasi); ?> <?php echo e($periode); ?></span>
                            </div>
                            <div class="flex justify-between gap-2 px-4 py-2.5 text-xs">
                                <span class="text-gray-500 dark:text-gray-400">Estimasi Total Tagihan</span>
                                <span class="font-semibold text-emerald-600 dark:text-emerald-400 text-end">Rp<?php echo e(number_format($harga * $durasi, 0, ',', '.')); ?></span>
                            </div>
                            <div class="flex justify-between gap-2 px-4 py-2.5 text-xs">
                                <span class="text-gray-500 dark:text-gray-400">Sistem Pembayaran</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100 text-end">Transfer</span>
                            </div>
                        </div>

                        <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">Dengan menekan tombol di bawah, kamar langsung terkunci untukmu. Kamu akan melihat tagihan sewa di dashboard.</p>

                        <div class="flex flex-col-reverse sm:flex-row gap-2 pt-3">
                            <button type="button" wire:click="kembaliKeTanggal" wire:loading.attr="disabled"
                                class="flex-1 inline-flex items-center justify-center rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Kembali
                            </button>
                            <button type="submit" wire:loading.attr="disabled" wire:target="konfirmasiSewa"
                                class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-500 transition disabled:opacity-50">
                                <span wire:loading.remove wire:target="konfirmasiSewa">Booking Sekarang</span>
                                <span wire:loading wire:target="konfirmasiSewa" class="inline-flex items-center gap-1.5">
                                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                                    Memproses...
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    window.initPetaDetail = window.initPetaDetail || (() => {
        const el = document.getElementById('peta-properti-detail');
        if (!el || el.dataset.ada === '1') return;

        const lat = parseFloat(el.dataset.lat);
        const lng = parseFloat(el.dataset.lng);
        if (Number.isNaN(lat) || Number.isNaN(lng)) return;

        el.dataset.ada = '1';

        const tautan = document.getElementById('peta-buka-osm');
        if (tautan) {
            tautan.href = 'https://www.google.com/maps/search/?api=1&query=' + lat + ',' + lng;
        }

        if (typeof google === 'undefined' || !google.maps) {
            if (typeof window.pasangOsmEmbed === 'function') {
                window.pasangOsmEmbed(el, lat, lng, 16);
            } else if (typeof window.pasangGoogleEmbed === 'function') {
                window.pasangGoogleEmbed(el, lat, lng, 16);
            } else {
                el.innerHTML = '<div class="h-full w-full flex items-center justify-center p-4 text-center text-xs text-gray-400">' +
                    'Peta tidak dapat dimuat saat ini.</div>';
            }
            return;
        }

        const map = new google.maps.Map(el, {
            center: { lat, lng },
            zoom: 16,
            mapTypeId: 'roadmap',
        });

        const pemuat = new google.maps.Marker({ position: { lat, lng }, map, title: el.dataset.nama || '' });

        const info = new google.maps.InfoWindow();
        const isi = '<div><strong>' + String(el.dataset.nama || '').replace(/</g, '&lt;') + '</strong>' +
            (el.dataset.alamat ? '<br>' + String(el.dataset.alamat).replace(/</g, '&lt;') : '') + '</div>';
        info.setContent(isi);
        info.open({ map, anchor: pemuat });
    });

    (() => {
        const init = () => setTimeout(window.initPetaDetail, 0);
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
        document.addEventListener('livewire:navigated', init);
        window.loadNgekosMaps(window.initPetaDetail);
    })();
</script>
<?php $__env->stopPush(); ?><?php /**PATH C:\laragon\www\Ngekos.in\resources\views\livewire\pages\katalog\detail.blade.php ENDPATH**/ ?>