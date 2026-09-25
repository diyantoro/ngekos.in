<?php

use App\Models\Kamar;
use App\Models\Properti;
use App\Support\Koordinat;
use App\Support\NormalisasiKota;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

?>

<div>
<!-- Iklan Partner sebagai hero full-bleed -->
    <section class="relative overflow-hidden">
        <?php if (isset($component)) { $__componentOriginalf00e66bd4c416c4b5f17e7e6f5c37f9f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf00e66bd4c416c4b5f17e7e6f5c37f9f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.promo-ads','data' => ['variant' => 'hero']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('promo-ads'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'hero']); ?>
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
    </section>

<!-- Pencarian Kos -->
    <section class="relative bg-white dark:bg-gray-900 overflow-hidden">
        <div class="relative max-w-7xl mx-auto px-4 py-8 sm:py-10">
            <div class="text-center max-w-2xl mx-auto">
                <span class="fade-up inline-flex items-center gap-1.5 rounded-full bg-teal-50 dark:bg-teal-500/10 ring-1 ring-teal-200 dark:ring-teal-500/30 px-3 py-1 text-xs font-semibold text-teal-700 dark:text-teal-300">
                    <span class="h-1.5 w-1.5 rounded-full bg-teal-500 animate-pulse"></span>
                    <?php echo e($totalKamar); ?> kamar tersedia saat ini
                </span>
                <h1 class="fade-up stagger-1 mt-4 text-2xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 dark:text-gray-100 leading-tight">
                    Cari Kos<br class="sm:hidden"> <span class="gradient-text">Gak Pake Ribet</span>
                </h1>
                <p class="fade-up stagger-2 mt-3 text-sm sm:text-base text-gray-500 dark:text-gray-400 leading-relaxed">
                    Temukan kamar kos impianmu, tanya pemilik langsung lewat chat, dan kelola semua dalam satu aplikasi.
                </p>
            </div>

            <!-- Search Bar - Prominent like Mamikos -->
            <div class="fade-up stagger-3 mt-6 max-w-2xl mx-auto">
                <form action="<?php echo e(route('kos.index')); ?>" method="GET" wire:navigate
                      class="animate-shine bg-white dark:bg-gray-800 rounded-2xl shadow-xl ring-1 ring-gray-100 dark:ring-gray-700 p-2 flex items-center gap-2">
                    <div class="flex-1 flex items-center gap-2 px-3">
                        <svg class="h-5 w-5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        <input type="text" name="cari" value="<?php echo e($cari); ?>" placeholder="Ketik nama kos, kota, atau lokasi..."
                            class="w-full border-0 bg-transparent text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-0 focus:outline-none py-2.5">
                    </div>
                    <button type="submit"
                            class="shrink-0 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-2.5 text-sm font-bold text-white hover:from-emerald-500 hover:to-teal-500 transition shadow-sm">
                        Cari Kos
                    </button>
                </form>

                <!-- Quick filter chips -->
                <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                    <a href="<?php echo e(route('kos.index')); ?>?cari=" wire:navigate
                       class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-gray-700 px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-teal-50 hover:text-teal-700 dark:hover:bg-teal-500/10 dark:hover:text-teal-300 transition">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                        Semua Kos
                    </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $daftarKota->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kota): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('kos.index')); ?>?kota=<?php echo e($kota); ?>" wire:navigate
                           class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-gray-700 px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-teal-50 hover:text-teal-700 dark:hover:bg-teal-500/10 dark:hover:text-teal-300 transition">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                            <?php echo e($kota); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats + CTA Strip -->
    <section class="max-w-7xl mx-auto px-4 mt-6 relative z-10">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            <a href="<?php echo e(route('register')); ?>" wire:navigate
               class="reveal group relative overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="absolute -right-6 -top-8 h-20 w-20 rounded-full bg-emerald-100/60 dark:bg-emerald-500/10 blur-2xl"></div>
                <div class="relative flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-sm">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" /></svg>
                    </span>
                    <div class="min-w-0">
                        <h3 class="text-sm font-extrabold text-gray-900 dark:text-gray-100">Mulai Cari Kos Hari Ini</h3>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Tanpa biaya, langsung chat pemilik kos.</p>
                        <span class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-teal-600 dark:text-teal-400">
                            Daftar Gratis
                            <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                        </span>
                    </div>
                </div>
            </a>

            <a href="<?php echo e(route('register')); ?>" wire:navigate
               class="reveal group relative overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="absolute -right-6 -top-8 h-20 w-20 rounded-full bg-violet-100/60 dark:bg-violet-500/10 blur-2xl"></div>
                <div class="relative flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white shadow-sm">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                    </span>
                    <div class="min-w-0">
                        <h3 class="text-sm font-extrabold text-gray-900 dark:text-gray-100">Promosikan Kos Anda</h3>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Kelola kamar dan balas chat pencari kos.</p>
                        <span class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-violet-600 dark:text-violet-400">
                            Mulai Gratis
                            <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                        </span>
                    </div>
                </div>
            </a>

            <div class="reveal rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                <div class="flex items-center justify-center gap-6 py-1">
                    <div class="text-center">
                        <p class="text-3xl font-extrabold text-teal-600 dark:text-teal-400" x-data="statCounter(<?php echo \Illuminate\Support\Js::from($totalProperti)->toHtml() ?>)" x-text="display">0</p>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Kos Aktif</p>
                    </div>
                    <div class="h-10 w-px bg-gray-200 dark:bg-gray-700"></div>
                    <div class="text-center">
                        <p class="text-3xl font-extrabold text-cyan-600 dark:text-cyan-400" x-data="statCounter(<?php echo \Illuminate\Support\Js::from($totalKamar)->toHtml() ?>)" x-text="display">0</p>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Kamar Tersedia</p>
                    </div>
                </div>
                <p class="text-center text-[11px] text-gray-400 dark:text-gray-500 mt-3">Selalu ada kamar baru setiap minggu</p>
            </div>
        </div>
    </section>

    <!-- Daftar Kos Terbaru -->
    <section class="max-w-7xl mx-auto px-4 py-8 sm:py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg sm:text-xl font-extrabold text-gray-900 dark:text-gray-100">Kos Terbaru</h2>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Kos yang baru ditambahkan pemilik</p>
            </div>
            <a href="<?php echo e(route('kos.index')); ?>" wire:navigate
               class="text-xs sm:text-sm font-semibold text-teal-600 hover:text-teal-500 dark:text-teal-400 dark:hover:text-teal-300 flex items-center gap-1">
                Lihat Semua
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $propertiList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $properti): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e(route('kos.detail', $properti)); ?>" wire:navigate
                   class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden hover:shadow-md hover:ring-teal-200 dark:hover:ring-teal-800 transition-all duration-200">
                    <div class="relative h-40 sm:h-44 bg-gradient-to-br from-teal-100 via-emerald-100 to-cyan-100">
                        <?php $coverBeranda = $properti->fotoCover(); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($coverBeranda): ?>
                            <img src="<?php echo e($coverBeranda); ?>" alt="<?php echo e($properti->nama); ?>" loading="lazy" decoding="async"
                                 class="h-full w-full object-cover aspect-[16/10] group-hover:scale-105 transition duration-300">
                        <?php else: ?>
                            <div class="h-full w-full flex items-center justify-center">
                                <svg class="h-14 w-14 text-teal-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($properti->galeriUrls()) > 1): ?>
                            <span class="absolute bottom-2 left-2 inline-flex items-center gap-1 rounded-full bg-black/50 px-1.5 py-0.5 text-[9px] font-bold text-white backdrop-blur-sm">
                                <?php echo e(count($properti->galeriUrls())); ?> foto
                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($properti->kamar_tersedia > 0): ?>
                            <span class="absolute top-2.5 right-2.5 inline-flex items-center rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-bold text-white shadow-sm">
                                <?php echo e($properti->kamar_tersedia); ?> Kamar
                            </span>
                        <?php else: ?>
                            <span class="absolute top-2.5 right-2.5 inline-flex items-center rounded-full bg-gray-800 px-2 py-0.5 text-[10px] font-bold text-white shadow-sm">
                                Penuh
                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="p-3.5 sm:p-4">
                        <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-gray-100 truncate group-hover:text-teal-600 dark:group-hover:text-teal-400 transition"><?php echo e($properti->nama); ?></h3>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                            <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                            <?php echo e($properti->kota ?? $properti->alamat ?? 'Lokasi belum diisi'); ?>

                        </p>
                        <div class="mt-2.5 pt-2.5 border-t border-gray-100 dark:border-gray-700 flex items-end justify-between gap-2">
                            <span class="text-[10px] text-gray-400 dark:text-gray-500 font-medium">Mulai dari</span>
                            <div class="text-end">
                                <?php
                                    $hargaTampil = $properti->harga ?? $properti->harga_termurah;
                                    $adaDiskon = $properti->harga_asli && $hargaTampil && $properti->harga_asli > $hargaTampil;
                                ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hargaTampil): ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($adaDiskon): ?>
                                        <span class="block text-xs font-semibold text-gray-400 dark:text-gray-500 line-through">Rp<?php echo e(number_format($properti->harga_asli, 0, ',', '.')); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <span class="text-base font-extrabold text-teal-600 dark:text-teal-400">
                                        Rp<?php echo e(number_format($hargaTampil, 0, ',', '.')); ?><span class="text-[10px] font-medium text-gray-400 dark:text-gray-500">/bln</span>
                                    </span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($properti->harga_harian): ?>
                                        <span class="block text-[10px] font-medium text-gray-400 dark:text-gray-500">Rp<?php echo e(number_format($properti->harga_harian, 0, ',', '.')); ?>/hari</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php else: ?>
                                    <span class="text-xs font-medium text-gray-400 dark:text-gray-500">Penuh</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-span-full py-12 text-center">
                    <div class="mx-auto h-14 w-14 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                        <svg class="h-7 w-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Belum ada kos terdaftar.</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </section>

    <!-- Persebaran Kos -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($kotaStatistik->isNotEmpty()): ?>
    <section x-data="{
                semua: false,
                buka: null,
                cariKota: '',
                muat: 15,
                data: <?php echo \Illuminate\Support\Js::from($kotaStatistik)->toHtml() ?>,
                get totalKota() { return this.data.length; },
                get sisa() { return this.data.slice(6); },
                get kotaLain() {
                    const s = this.sisa;
                    if (!s.length) return null;
                    return { nama: 'Kota Lainnya', jumlah: s.reduce((a, k) => a + k.jumlah, 0), daerah: s };
                },
                get awal() {
                    const rows = this.data.slice(0, 6);
                    const lain = this.kotaLain;
                    return lain ? rows.concat([lain]) : rows;
                },
                get hasilFilter() {
                    const q = this.cariKota.trim().toLowerCase();
                    if (!q) return this.data;
                    return this.data.filter((k) => k.nama.toLowerCase().includes(q));
                },
                get tampil() {
                    if (!this.semua) return this.awal;
                    return this.hasilFilter.slice(0, this.muat);
                },
                get sisaMuat() { return this.hasilFilter.length - this.muat; },
                get kosong() { return this.semua && this.hasilFilter.length === 0; },
                get adaDaerahTampil() {
                    return this.tampil.filter((k) => k.nama !== 'Kota Lainnya').some((kota) => kota.daerah.length);
                },
                kotakBuka() {
                    if (!this.buka) return null;
                    const k = this.tampil.find((kota) => kota.nama === this.buka);
                    return k && k.daerah.length ? k : null;
                },
                bukaModeSemua() {
                    this.buka = null;
                    this.cariKota = '';
                    this.muat = 15;
                    this.semua = true;
                },
                kembaliTeratas() {
                    this.semua = false;
                    this.buka = null;
                    this.cariKota = '';
                    this.muat = 15;
                },
                muatLagi() { this.muat += 15; },
                renderGrafik() {
                    const el = document.getElementById('chart-persebaran-kos');
                    if (!el || !this.tampil.length || typeof window.kosPerKotaChart !== 'function') return;
                    const labels = this.tampil.map((kota) => kota.nama);
                    const values = this.tampil.map((kota) => kota.jumlah);
                    window.kosPerKotaChart('chart-persebaran-kos', labels, values, {
                        onBarClick: (i) => {
                            const k = this.tampil[i];
                            if (!k) return;
                            if (k.nama === 'Kota Lainnya') { this.bukaModeSemua(); return; }
                            this.buka = k.daerah.length ? (this.buka === k.nama ? null : k.nama) : this.buka;
                        },
                    });
                },
                // Listener dokumen didaftarkan sekali saja (navigasi SPA membuat
                // init() jalan ulang; tanpa penjagaan handler menumpuk).
                __dengarTemaBeranda() {
                    if (window.__berandaTemaOn) return;
                    window.__berandaTemaOn = true;
                    document.addEventListener('ngekos:theme-changed', () => {
                        try { window.__berandaGrafikAktif && window.__berandaGrafikAktif(); } catch (e) {}
                    });
                },
                __dengarNavigasiBeranda() {
                    if (window.__berandaNavOn) return;
                    window.__berandaNavOn = true;
                    document.addEventListener('livewire:navigated', () => {
                        if (!document.getElementById('chart-persebaran-kos')) return;
                        try { window.__berandaGrafikAktif && window.__berandaGrafikAktif(); } catch (e) {}
                    });
                },
                init() {
                    this.$nextTick(() => {
                        this.renderGrafik();
                        this.$watch('semua', () => this.$nextTick(() => this.renderGrafik()));
                        this.$watch('cariKota', () => {
                            this.muat = 15;
                            this.buka = null;
                            this.$nextTick(() => this.renderGrafik());
                        });
                        this.$watch('muat', () => this.$nextTick(() => this.renderGrafik()));
                        try {
                            if (this.$store && this.$store.theme) {
                                this.$watch(() => this.$store.theme.dark, () => this.$nextTick(() => this.renderGrafik()));
                            } else {
                                window.__berandaGrafikAktif = () => this.$nextTick(() => this.renderGrafik());
                                this.__dengarTemaBeranda();
                            }
                        } catch (e) {
                            window.__berandaGrafikAktif = () => this.$nextTick(() => this.renderGrafik());
                            this.__dengarTemaBeranda();
                        }
                        window.__berandaGrafikAktif = () => this.$nextTick(() => this.renderGrafik());
                        this.__dengarNavigasiBeranda();
                    });
                },
            }" class="max-w-7xl mx-auto px-4 pb-8 sm:pb-10">
        <div class="reveal relative overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 sm:p-6 shadow-sm">
            <div class="absolute -right-10 -top-12 h-32 w-32 rounded-full bg-teal-100/50 dark:bg-teal-500/10 blur-3xl"></div>
            <div class="relative flex items-center gap-3 mb-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-teal-500 to-cyan-500 text-white shadow-sm">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z"/></svg>
                </span>
                <div>
                    <h2 class="text-base font-extrabold text-gray-900 dark:text-gray-100">Persebaran Kos</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Jumlah kos aktif per kota</p>
                </div>
            </div>
            <div class="relative">
                <!-- Pencarian (mode semua) -->
                <div x-show="semua" x-cloak class="mb-3">
                    <div class="flex items-center gap-2 bg-gray-100/80 dark:bg-gray-800/80 rounded-xl px-3 py-2 focus-within:ring-2 focus-within:ring-teal-500/20 transition-all">
                        <svg class="h-4 w-4 text-gray-400 dark:text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        <input type="text" x-model="cariKota" placeholder="Cari kota atau daerah..."
                            class="w-full bg-transparent text-xs sm:text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 border-0 focus:ring-0 focus:outline-none p-0">
                        <template x-if="cariKota">
                            <button type="button" @click="cariKota = ''" class="rounded-lg p-1 text-gray-400 hover:text-teal-600 dark:hover:text-teal-400 transition-colors" title="Bersihkan pencarian">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </template>
                    </div>
                </div>

                <div x-show="!kosong" class="relative transition-all duration-200" :style="'height:' + Math.max(tampil.length * 32, 150) + 'px'" :class="semua ? 'max-h-80 overflow-y-auto pr-1' : ''">
                    <canvas id="chart-persebaran-kos" class="cursor-pointer"></canvas>
                </div>

                <!-- Hasil pencarian kosong -->
                <div x-show="kosong" x-cloak class="py-10 text-center">
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        Kota "<span class="font-semibold text-gray-600 dark:text-gray-300" x-text="cariKota"></span>" tidak ditemukan.
                    </p>
                </div>

                <!-- Counter mode semua -->
                <p x-show="semua && !kosong" class="mt-2 text-center text-[10px] text-gray-400 dark:text-gray-500">
                    Menampilkan <span class="font-bold text-gray-600 dark:text-gray-300" x-text="Math.min(muat, hasilFilter.length).toLocaleString('id-ID')"></span> dari <span class="font-bold text-gray-600 dark:text-gray-300" x-text="hasilFilter.length.toLocaleString('id-ID')"></span> kota
                </p>

                <!-- Muat lebih banyak -->
                <template x-if="semua && sisaMuat > 0">
                    <button type="button" @click="muatLagi()"
                        class="mt-3 w-full inline-flex items-center justify-center gap-1.5 rounded-xl border border-teal-200 dark:border-teal-500/30 px-3 py-2 text-xs font-semibold text-teal-600 dark:text-teal-400 transition-all duration-200 hover:bg-teal-50 dark:hover:bg-teal-500/10 active:scale-95">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Muat lebih banyak (<span x-text="sisaMuat"></span>)
                    </button>
                </template>

                <template x-if="totalKota > 6">
                    <button type="button" @click="semua ? kembaliTeratas() : bukaModeSemua()"
                        class="mt-3 inline-flex items-center gap-1.5 rounded-xl border border-teal-200 dark:border-teal-500/30 px-3 py-2 text-xs font-semibold text-teal-600 dark:text-teal-400 transition-all duration-200 hover:bg-teal-50 dark:hover:bg-teal-500/10 active:scale-95">
                        <svg x-show="!semua" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        <svg x-show="semua" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
                        <span x-text="semua ? 'Tampilkan teratas' : 'Lihat semua kota (' + totalKota + ')'"></span>
                    </button>
                </template>

                <template x-if="kotakBuka()">
                    <div class="mt-4 rounded-xl border border-teal-100 dark:border-teal-500/20 bg-teal-50/50 dark:bg-teal-500/5">
                        <div class="flex items-center justify-between gap-2 border-b border-teal-100 dark:border-teal-500/20 px-3 py-2.5">
                            <p class="text-xs font-bold text-teal-700 dark:text-teal-300">
                                <span x-text="buka"></span> — pecahan daerah
                            </p>
                            <button type="button" @click="buka = null" class="rounded-lg p-1 text-teal-500 transition-colors hover:bg-teal-100 dark:hover:bg-teal-500/10" title="Tutup">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <div class="divide-y divide-teal-100/70 dark:divide-teal-500/10 px-3 py-1">
                            <template x-for="d in kotakBuka().daerah" :key="d.nama">
                                <div class="flex items-center justify-between gap-3 py-2">
                                    <span class="truncate text-xs font-semibold text-gray-700 dark:text-gray-200" x-text="d.nama"></span>
                                    <span class="shrink-0 text-xs font-extrabold text-teal-600 dark:text-teal-400" x-text="d.jumlah.toLocaleString('id-ID') + ' kos'"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <p x-show="adaDaerahTampil && !buka" class="mt-4 text-center text-[10px] text-gray-400 dark:text-gray-500">
                    Klik bar kota untuk melihat pecahan daerahnya.
                </p>
            </div>
        </div>
    </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Peta Semua Kos -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($markers->isNotEmpty()): ?>
    <section class="max-w-7xl mx-auto px-4 pb-8 sm:pb-10" x-data="{ tampilkanPeta: false }">
        <div class="reveal relative overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 sm:p-6 shadow-sm">
            <div class="absolute -left-10 -top-12 h-32 w-32 rounded-full bg-cyan-100/50 dark:bg-cyan-500/10 blur-3xl"></div>
            <div class="relative flex items-center justify-between gap-3 mb-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-teal-500 to-cyan-500 text-white shadow-sm">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" /></svg>
                    </span>
                    <div>
                        <h2 class="text-base font-extrabold text-gray-900 dark:text-gray-100">Peta Kos</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($markers->count()); ?> titik lokasi kos aktif</p>
                    </div>
                </div>
                <button @click="tampilkanPeta = !tampilkanPeta; $nextTick(() => { if (tampilkanPeta) initPetaBeranda(); else if (typeof resetPetaBeranda === 'function') resetPetaBeranda(); })"
                    class="shrink-0 inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-xs font-semibold transition-all duration-200 active:scale-95 <?php echo e($markers->count() ? 'bg-teal-600 text-white hover:bg-teal-500 shadow-sm' : 'bg-gray-100 text-gray-400'); ?>">
                    <svg x-show="!tampilkanPeta" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                    <svg x-show="tampilkanPeta" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M9 21V9h6v12" /></svg>
                    <span x-text="tampilkanPeta ? 'Tutup Peta' : 'Lihat Peta'"></span>
                </button>
            </div>
            <div x-show="tampilkanPeta" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="relative">
                <div id="peta-kos-beranda" class="h-80 sm:h-96 w-full rounded-xl bg-gray-100 dark:bg-gray-800"></div>
            </div>
        </div>
    </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Kenapa Ngekos.in -->
    <section class="bg-white dark:bg-gray-800 border-y border-gray-100 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 py-10 sm:py-14">
            <div class="reveal text-center max-w-xl mx-auto mb-8">
                <h2 class="text-lg sm:text-2xl font-extrabold text-gray-900 dark:text-gray-100">Kenapa Pilih Ngekos.in?</h2>
                <p class="mt-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400">Solusi praktis untuk pencari kos dan pemilik kos</p>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                    ['Cari Mudah', 'Filter berdasarkan lokasi, harga, dan fasilitas yang kamu mau.', 'M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z'],
                    ['Chat Langsung', 'Tanya pemilik kos langsung dari HP tanpa harus datang.', 'M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155'],
                    ['Bayar Praktis', 'Tagihan bulanan otomatis, bayar lewat transfer.', 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z'],
                    ['Kelola Mudah', 'Pemilik kelola kamar, chat, dan pembayaran dari dashboard.', 'M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$judul, $deskripsi, $ikon]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="reveal bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-4 sm:p-5 hover:bg-teal-50 dark:hover:bg-teal-500/10 transition-colors duration-200">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-teal-500 to-emerald-500 text-white shadow-sm">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($ikon); ?>" /></svg>
                        </span>
                        <h3 class="mt-3 text-sm sm:text-base font-bold text-gray-900 dark:text-gray-100"><?php echo e($judul); ?></h3>
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 leading-relaxed"><?php echo e($deskripsi); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="max-w-7xl mx-auto px-4 py-10 sm:py-14">
        <div class="reveal bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-600 rounded-2xl p-6 sm:p-10 text-center relative overflow-hidden">
            <div class="absolute -top-16 left-1/4 h-48 w-48 rounded-full bg-emerald-300/20 blur-3xl"></div>
            <div class="absolute -bottom-20 -right-8 h-48 w-48 rounded-full bg-cyan-200/20 blur-3xl"></div>
            <div class="relative">
                <h2 class="text-lg sm:text-2xl font-extrabold text-white">Siap Cari atau Punya Kos?</h2>
                <p class="mt-2 text-xs sm:text-sm text-teal-100 max-w-md mx-auto">Buat akun gratis sekarang dan mulai cari kos impianmu.</p>
                <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
                    <a href="<?php echo e(route('register')); ?>" wire:navigate
                       class="inline-flex items-center rounded-xl bg-white px-5 sm:px-6 py-2.5 sm:py-3 text-sm font-bold text-teal-700 shadow-lg hover:bg-teal-50 transition">
                        Daftar Gratis
                    </a>
                    <a href="<?php echo e(route('kos.index')); ?>" wire:navigate
                       class="inline-flex items-center rounded-xl bg-white/15 ring-1 ring-white/30 px-5 sm:px-6 py-2.5 sm:py-3 text-sm font-bold text-white hover:bg-white/25 transition backdrop-blur">
                        Lihat Semua Kos
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php $__env->startPush('scripts'); ?>
        <script>
            window._mapBeranda = window._mapBeranda || null;
            window._boundsBeranda = window._boundsBeranda || null;
            window.dataPetaBeranda = <?php echo \Illuminate\Support\Js::from($markers)->toHtml() ?>;

            function berandaFallback() {
                const el = document.getElementById('peta-kos-beranda');
                const data = window.dataPetaBeranda || [];
                if (!el || !data.length) return;
                if (typeof window.pasangOsmEmbed === 'function') {
                    const sum = dataPetaBeranda.reduce((a, m) => ({ lat: a.lat + m.lat, lng: a.lng + m.lng }), { lat: 0, lng: 0 });
                    window.pasangOsmEmbed(el, sum.lat / dataPetaBeranda.length, sum.lng / dataPetaBeranda.length, 10, dataPetaBeranda);
                } else if (typeof window.pasangGoogleEmbed === 'function') {
                    const sum = dataPetaBeranda.reduce((a, m) => ({ lat: a.lat + m.lat, lng: a.lng + m.lng }), { lat: 0, lng: 0 });
                    window.pasangGoogleEmbed(el, sum.lat / dataPetaBeranda.length, sum.lng / dataPetaBeranda.length, dataPetaBeranda.length <= 1 ? 14 : 10);
                } else {
                    el.innerHTML = '<div class="h-full w-full flex items-center justify-center p-4 text-center text-xs text-gray-400">Peta tidak dapat dimuat saat ini.</div>';
                }
            }

            function berandaBuatPeta(percobaan) {
                const el = document.getElementById('peta-kos-beranda');
                const data = window.dataPetaBeranda || [];
                if (!el || !data.length) return;
                if (el.offsetWidth === 0) {
                    if ((percobaan || 0) < 20) setTimeout(() => berandaBuatPeta((percobaan || 0) + 1), 120);
                    else berandaFallback();
                    return;
                }
                if (typeof google === 'undefined' || !google.maps) {
                    berandaFallback();
                    return;
                }
                if (window._mapBeranda) {
                    try {
                        google.maps.event.trigger(window._mapBeranda, 'resize');
                        if (window._boundsBeranda) window._mapBeranda.fitBounds(window._boundsBeranda);
                    } catch (e) {}
                    return;
                }

                const bounds = new google.maps.LatLngBounds();
                data.forEach((m) => bounds.extend({ lat: m.lat, lng: m.lng }));
                window._boundsBeranda = bounds;

                window._mapBeranda = new google.maps.Map(el, { mapTypeId: 'roadmap', disableDefaultUI: false });
                if (data.length === 1) {
                    window._mapBeranda.setCenter(bounds.getCenter());
                    window._mapBeranda.setZoom(14);
                } else {
                    window._mapBeranda.fitBounds(bounds);
                }

                const markers = data.map((m) => {
                    const pemuat = new google.maps.Marker({ position: { lat: m.lat, lng: m.lng }, map: window._mapBeranda, title: m.nama });
                    const info = new google.maps.InfoWindow();
                    pemuat.addListener('click', () => {
                        const isi = '<strong>' + String(m.nama || '').replace(/</g, '&lt;') + '</strong><br>' +
                            (m.alamat ? String(m.alamat).replace(/</g, '&lt;') + ', ' : '') +
                            (m.kota ? String(m.kota).replace(/</g, '&lt;') : '') +
                            (m.id ? '<br><a href="/kos/' + m.id + '">Lihat detail</a>' : '');
                        info.setContent(isi);
                        info.open({ map: window._mapBeranda, anchor: pemuat });
                    });
                    return pemuat;
                });
                if (typeof window.pasangCluster === 'function') { try { window.pasangCluster(markers, window._mapBeranda); } catch (e) {} }
            }

            window.initPetaBeranda = function () {
                const el = document.getElementById('peta-kos-beranda');
                if (!el) return;
                requestAnimationFrame(() => {
                    berandaBuatPeta();
                    if (typeof window.loadNgekosMaps === 'function') window.loadNgekosMaps(berandaBuatPeta);
                });
            };

            window.resetPetaBeranda = function () {
                const el = document.getElementById('peta-kos-beranda');
                if (el) el.innerHTML = '';
                window._mapBeranda = null;
                window._boundsBeranda = null;
            };

            (() => {
                // Didaftarkan sekali saja agar tidak menumpuk tiap navigasi SPA.
                if (!window.__berandaPetaNavOn) {
                    window.__berandaPetaNavOn = true;
                    document.addEventListener('livewire:navigated', () => {
                        window._mapBeranda = null;
                        window._boundsBeranda = null;
                    });
                }
                if (typeof window.loadNgekosMaps === 'function') { try { window.loadNgekosMaps(window.berandaBuatPeta || berandaBuatPeta); } catch (e) {} }
                window.berandaBuatPeta = berandaBuatPeta;
                window.berandaFallback = berandaFallback;
            })();
        </script>
    <?php $__env->stopPush(); ?>
</div><?php /**PATH C:\laragon\www\Ngekos.in\resources\views\livewire\pages\beranda.blade.php ENDPATH**/ ?>