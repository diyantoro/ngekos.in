<?php

use App\Models\Properti;
use App\Support\Koordinat;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

?>

<div x-data="{ openFilter: false, tampilkanPeta: false }">
    <!-- Search Header -->
    <section class="bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl border-b border-gray-100/80 dark:border-gray-800 sticky top-14 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3">
            <!-- Search Bar -->
            <div class="flex items-center gap-2">
                <div class="flex-1 flex items-center gap-2 bg-gray-100/80 dark:bg-gray-800/80 rounded-xl px-3 py-2.5 transition-all duration-200 focus-within:bg-white dark:focus-within:bg-gray-800 focus-within:ring-2 focus-within:ring-teal-500/20 focus-within:shadow-sm">
                    <svg class="h-4 w-4 text-gray-400 dark:text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    <input type="text" wire:model.live.debounce.300ms="cari" placeholder="Cari nama kos, kota, atau alamat..."
                        class="w-full bg-transparent text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 border-0 focus:ring-0 focus:outline-none p-0">
                </div>
                <button @click="openFilter = !openFilter"
                    class="shrink-0 flex items-center justify-center h-10 w-10 rounded-xl transition-all duration-200 relative <?php echo e(($kota || $hargaMax || $kapasitas > 1) ? 'bg-teal-50 text-teal-600 ring-1 ring-teal-200 dark:bg-teal-500/10 dark:text-teal-400 dark:ring-teal-500/30' : 'bg-gray-100 text-gray-500 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400 dark:hover:bg-gray-600'); ?>">
                    <svg class="h-5 w-5 transition-transform duration-200" :class="openFilter ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" /></svg>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($kota || $hargaMax || $kapasitas > 1): ?>
                        <span class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-teal-600 text-[9px] font-bold text-white flex items-center justify-center shadow-sm">
                            <?php echo e(collect([$kota, $hargaMax, $kapasitas > 1 ? 1 : null])->filter()->count()); ?>

                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </button>
            </div>

            <!-- Filter Panel (toggle) -->
            <button @click="openFilter = !openFilter" class="mt-2 flex items-center gap-1 text-xs font-medium text-gray-500 hover:text-teal-600 dark:text-gray-400 dark:hover:text-teal-400 transition-all duration-200 active:scale-95">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" /></svg>
                Filter
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($kota || $hargaMax || $kapasitas > 1): ?>
                    <span class="inline-flex items-center rounded-full bg-teal-100 dark:bg-teal-500/10 px-1.5 py-0.5 text-[9px] font-bold text-teal-700 dark:text-teal-300">Aktif</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <svg class="h-3 w-3 transition-transform duration-200" :class="openFilter ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
            </button>

            <div x-show="openFilter" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="mt-3 bg-gray-50/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-xl p-3 space-y-3 ring-1 ring-gray-100 dark:ring-gray-700">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Kota</label>
                        <select wire:model.live="kota" class="w-full rounded-lg border-gray-200 dark:border-gray-600 text-xs focus:ring-teal-500 focus:border-teal-500 bg-white dark:bg-gray-800 dark:text-gray-100 transition-colors">
                            <option value="">Semua kota</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $daftarKota; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($k); ?>"><?php echo e($k); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Harga Maks</label>
                        <select wire:model.live="hargaMax" class="w-full rounded-lg border-gray-200 dark:border-gray-600 text-xs focus:ring-teal-500 focus:border-teal-500 bg-white dark:bg-gray-800 dark:text-gray-100 transition-colors">
                            <option value="">Semua harga</option>
                            <option value="500000"><= Rp500rb</option>
                            <option value="750000"><= Rp750rb</option>
                            <option value="1000000"><= Rp1 juta</option>
                            <option value="1500000"><= Rp1,5 juta</option>
                            <option value="2000000"><= Rp2 juta</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Kapasitas Minimal</label>
                    <select wire:model.live="kapasitas" class="w-full rounded-lg border-gray-200 dark:border-gray-600 text-xs focus:ring-teal-500 focus:border-teal-500 bg-white dark:bg-gray-800 dark:text-gray-100 transition-colors">
                        <option value="1">1 orang</option>
                        <option value="2">2 orang</option>
                        <option value="3">3 orang</option>
                    </select>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($kota || $hargaMax || $kapasitas > 1): ?>
                    <button wire:click="$set('kota', ''); $set('hargaMax', null); $set('kapasitas', 1)"
                        class="text-xs font-semibold text-rose-500 hover:text-rose-600 dark:text-rose-400 dark:hover:text-rose-300 transition-colors active:scale-95">
                        Reset Filter
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Iklan Partner -->
    <div class="max-w-7xl mx-auto px-4 pt-4">
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

    <div class="max-w-7xl mx-auto px-4 py-4">
        <!-- Result Count + Toggle Peta -->
        <div class="flex items-center justify-between gap-3 mb-3">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Menampilkan <span class="font-semibold text-gray-800 dark:text-gray-200"><?php echo e($propertis->count()); ?></span> kos
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($kota): ?> di <span class="font-semibold text-teal-600 dark:text-teal-400"><?php echo e($kota); ?></span> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </p>
            <button @click="tampilkanPeta = !tampilkanPeta; $nextTick(() => togglePetaNgekos(tampilkanPeta))"
                class="shrink-0 inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-xs font-semibold transition-all duration-200 active:scale-95 <?php echo e(count($markers) ? 'bg-teal-600 text-white hover:bg-teal-500 shadow-sm' : 'bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500'); ?>"
                <?php if(! count($markers)): ?> disabled title="Belum ada koordinat" <?php endif; ?>>
                <svg x-show="!tampilkanPeta" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                <svg x-show="tampilkanPeta" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M9 21V9h6v12" /></svg>
                <span x-text="tampilkanPeta ? 'Lihat Daftar' : 'Lihat Peta'"></span>
            </button>
        </div>

        <!-- Peta Semua Kos -->
        <div x-show="tampilkanPeta" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="mb-5">
            <div class="rounded-2xl overflow-hidden shadow-card ring-1 ring-gray-100 dark:ring-gray-700">
                <div id="peta-kos" class="h-96 w-full bg-gray-100 dark:bg-gray-800"></div>
            </div>
            <p class="mt-2 text-[10px] text-gray-400 dark:text-gray-500 text-center">Peta menggunakan koordinat properti; jika belum diisi, titik diambil dari pusat kota.</p>
        </div>

        <!-- Cards - Mobile-first list layout -->
        <div wire:loading.remove class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $propertis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $properti): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e(route('kos.detail', $properti)); ?>" wire:navigate
                   class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden hover:shadow-md hover:ring-teal-200 dark:hover:ring-teal-800 transition-all duration-200">
                    <div class="flex sm:block">
                        <!-- Image -->
                        <div class="relative h-32 sm:h-44 w-28 sm:w-full shrink-0 bg-gradient-to-br from-teal-100 via-emerald-100 to-cyan-100 dark:from-teal-500/20 dark:via-emerald-500/20 dark:to-cyan-500/20">
                            <?php $coverKos = $properti->fotoCover(); ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($coverKos): ?>
                                <img src="<?php echo e($coverKos); ?>" alt="<?php echo e($properti->nama); ?>" loading="lazy" decoding="async"
                                     class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
                            <?php else: ?>
                                <div class="h-full w-full flex items-center justify-center">
                                    <svg class="h-10 w-10 sm:h-14 sm:w-14 text-teal-300 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($properti->galeriUrls()) > 1): ?>
                                <span class="absolute bottom-2 left-2 inline-flex items-center gap-1 rounded-full bg-black/50 px-1.5 py-0.5 text-[9px] font-bold text-white backdrop-blur-sm">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                                    <?php echo e(count($properti->galeriUrls())); ?>

                                </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($properti->kamar_tersedia > 0): ?>
                                <span class="absolute top-2 right-2 inline-flex items-center rounded-full bg-emerald-600 px-1.5 py-0.5 text-[9px] font-bold text-white shadow-sm">
                                    <?php echo e($properti->kamar_tersedia); ?> Kamar
                                </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 p-3 sm:p-4 flex flex-col justify-between">
                            <div>
                                <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-gray-100 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition line-clamp-1"><?php echo e($properti->nama); ?></h3>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($properti->total_ulasan > 0): ?>
                                    <p class="mt-0.5 flex items-center gap-1 text-xs">
                                        <?php if (isset($component)) { $__componentOriginalfa87e49ca3cdf62358bbc468aaf3394b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfa87e49ca3cdf62358bbc468aaf3394b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.star-rating','data' => ['rating' => round($properti->rating_ulasan),'size' => 'h-3 w-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('star-rating'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['rating' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(round($properti->rating_ulasan)),'size' => 'h-3 w-3']); ?>
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
                                        <span class="font-semibold text-gray-700 dark:text-gray-300"><?php echo e(number_format($properti->rating_ulasan, 1, ',', '.')); ?></span>
                                        <span class="text-gray-400 dark:text-gray-500">(<?php echo e($properti->total_ulasan); ?>)</span>
                                    </p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                                    <?php echo e($properti->kota ?? $properti->alamat ?? 'Lokasi belum diisi'); ?>

                                </p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($properti->fasilitas): ?>
                                    <div class="mt-1.5 flex flex-wrap gap-1">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = array_slice(array_filter(array_map('trim', explode(',', $properti->fasilitas))), 0, 3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span class="inline-flex items-center rounded bg-teal-50 dark:bg-teal-500/10 px-1.5 py-0.5 text-[9px] font-medium text-teal-700 dark:text-teal-300"><?php echo e($f); ?></span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count(array_filter(array_map('trim', explode(',', $properti->fasilitas)))) > 3): ?>
                                            <span class="inline-flex items-center rounded bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 text-[9px] font-medium text-gray-500 dark:text-gray-400">+<?php echo e(count(array_filter(array_map('trim', explode(',', $properti->fasilitas)))) - 3); ?></span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700 flex items-end justify-between gap-2">
                                <span class="text-[10px] text-gray-400 dark:text-gray-500 font-medium">Mulai dari</span>
                                <div class="text-end">
                                    <?php
                                        $hargaTampil = $properti->harga ?? $properti->harga_termurah;
                                        $adaDiskon = $properti->harga_asli && $hargaTampil && $properti->harga_asli > $hargaTampil;
                                    ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hargaTampil): ?>
                                        <?php if (isset($component)) { $__componentOriginal9b7fa0d7349cf7f9eb91e881f4695d51 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9b7fa0d7349cf7f9eb91e881f4695d51 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.harga-tiga-periode','data' => ['bulanan' => $hargaTampil,'mingguan' => $properti->harga_mingguan,'harian' => $properti->harga_harian,'asli' => $adaDiskon ? $properti->harga_asli : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('harga-tiga-periode'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['bulanan' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hargaTampil),'mingguan' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($properti->harga_mingguan),'harian' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($properti->harga_harian),'asli' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($adaDiskon ? $properti->harga_asli : null)]); ?>
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
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-span-full py-16 text-center">
                    <div class="mx-auto h-16 w-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-4">
                        <svg class="h-8 w-8 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 font-medium text-sm">Tidak menemukan kos?</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500 max-w-sm mx-auto">Coba ubah kata kunci, pilih kota lain, atau perbesar budget pencarian Anda.</p>
                    <button wire:click="$set('cari', ''); $set('kota', ''); $set('hargaMax', null); $set('kapasitas', 1)"
                        class="mt-4 inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2 text-xs font-semibold text-white hover:bg-teal-500 transition shadow-sm">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" /></svg>
                        Reset Semua Filter
                    </button>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($propertis->hasPages()): ?>
            <div wire:loading.remove class="mt-6">
                <?php echo e($propertis->links()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if (isset($component)) { $__componentOriginal31de594fa8b31c89482b92f93a0b9eeb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal31de594fa8b31c89482b92f93a0b9eeb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.skeleton','data' => ['type' => 'card','count' => '6','target' => 'cari, kota, hargaMax, kapasitas','class' => 'sm:grid-cols-2 lg:grid-cols-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('skeleton'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'card','count' => '6','target' => 'cari, kota, hargaMax, kapasitas','class' => 'sm:grid-cols-2 lg:grid-cols-3']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal31de594fa8b31c89482b92f93a0b9eeb)): ?>
<?php $attributes = $__attributesOriginal31de594fa8b31c89482b92f93a0b9eeb; ?>
<?php unset($__attributesOriginal31de594fa8b31c89482b92f93a0b9eeb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal31de594fa8b31c89482b92f93a0b9eeb)): ?>
<?php $component = $__componentOriginal31de594fa8b31c89482b92f93a0b9eeb; ?>
<?php unset($__componentOriginal31de594fa8b31c89482b92f93a0b9eeb); ?>
<?php endif; ?>

        <!-- CTA untuk pemilik -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->guest()): ?>
            <div class="mt-10 bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-600 rounded-2xl p-6 sm:p-10 text-center">
                <h2 class="text-lg sm:text-2xl font-extrabold text-white">Punya Kos? Daftar Sekarang</h2>
                <p class="mt-2 text-teal-100 text-xs sm:text-sm max-w-md mx-auto">
                    Daftarkan kos Anda gratis, kelola kamar, dan balas chat pencari kos.
                </p>
                <a href="<?php echo e(route('register')); ?>" wire:navigate
                   class="mt-4 inline-flex items-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-teal-600 hover:bg-teal-50 transition shadow-lg">
                    Daftar sebagai Pemilik Kos
                </a>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            let ngekosMap = null;
            let ngekosBounds = null;
            const dataPetaKos = <?php echo json_encode($markers, 15, 512) ?>;

            function fallbackPetaKos() {
                const el = document.getElementById('peta-kos');
                if (!el || !dataPetaKos.length) return;
                if (typeof window.pasangOsmEmbed === 'function') {
                    const sum = dataPetaKos.reduce((a, m) => ({ lat: a.lat + m.lat, lng: a.lng + m.lng }), { lat: 0, lng: 0 });
                    window.pasangOsmEmbed(el, sum.lat / dataPetaKos.length, sum.lng / dataPetaKos.length, 10, dataPetaKos);
                } else if (typeof window.pasangGoogleEmbed === 'function') {
                    const sum = dataPetaKos.reduce((a, m) => ({ lat: a.lat + m.lat, lng: a.lng + m.lng }), { lat: 0, lng: 0 });
                    window.pasangGoogleEmbed(el, sum.lat / dataPetaKos.length, sum.lng / dataPetaKos.length, dataPetaKos.length <= 1 ? 14 : 10);
                } else {
                    el.innerHTML = '<div class="h-full w-full flex items-center justify-center p-4 text-center text-sm text-gray-400">' +
                        'Peta tidak dapat dimuat saat ini.</div>';
                }
            }

            function inisialisasiPetaKos(percobaan) {
                const el = document.getElementById('peta-kos');
                if (!el || !dataPetaKos.length) return;
                if (el.offsetWidth === 0) {
                    if ((percobaan || 0) < 10) requestAnimationFrame(() => inisialisasiPetaKos((percobaan || 0) + 1));
                    return;
                }
                if (typeof google === 'undefined' || !google.maps) {
                    fallbackPetaKos();
                    return;
                }
                if (ngekosMap) {
                    requestAnimationFrame(() => {
                        google.maps.event.trigger(ngekosMap, 'resize');
                        if (ngekosBounds) ngekosMap.fitBounds(ngekosBounds);
                    });
                    return;
                }

                ngekosBounds = new google.maps.LatLngBounds();
                dataPetaKos.forEach((m) => ngekosBounds.extend({ lat: m.lat, lng: m.lng }));

                ngekosMap = new google.maps.Map(el, { mapTypeId: 'roadmap' });
                if (dataPetaKos.length === 1) {
                    ngekosMap.setCenter(ngekosBounds.getCenter());
                    ngekosMap.setZoom(14);
                } else {
                    ngekosMap.fitBounds(ngekosBounds);
                }

                const markers = dataPetaKos.map(function (m) {
                    const pemuat = new google.maps.Marker({ position: { lat: m.lat, lng: m.lng }, map: ngekosMap, title: m.nama });
                    const info = new google.maps.InfoWindow();
                    pemuat.addListener('click', () => {
                        const isi = '<strong>' + String(m.nama || '').replace(/</g, '&lt;') + '</strong><br>' +
                            (m.alamat ? String(m.alamat).replace(/</g, '&lt;') + ', ' : '') +
                            (m.kota ? String(m.kota).replace(/</g, '&lt;') : '') +
                            (m.id ? '<br><a href="/kos/' + m.id + '">Lihat detail</a>' : '');
                        info.setContent(isi);
                        info.open({ map: ngekosMap, anchor: pemuat });
                    });
                    return pemuat;
                });
                if (typeof window.pasangCluster === 'function') window.pasangCluster(markers, ngekosMap);
            }

            window.togglePetaNgekos = function (show) {
                const el = document.getElementById('peta-kos');
                if (!el || !show) return;
                requestAnimationFrame(() => {
                    inisialisasiPetaKos();
                    if (typeof window.loadNgekosMaps === 'function') window.loadNgekosMaps(inisialisasiPetaKos);
                });
            };

            // Didaftarkan sekali saja agar tidak menumpuk tiap navigasi SPA.
            if (!window.__kosPetaNavOn) {
                window.__kosPetaNavOn = true;
                document.addEventListener('livewire:navigated', () => {
                    ngekosMap = null;
                    ngekosBounds = null;
                });
            }
            if (typeof window.loadNgekosMaps === 'function') window.loadNgekosMaps(inisialisasiPetaKos);
        </script>
    <?php $__env->stopPush(); ?>
</div><?php /**PATH C:\laragon\www\Ngekos.in\resources\views\livewire\pages\katalog\kos.blade.php ENDPATH**/ ?>