<?php

use App\Models\Properti;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

?>

<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
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

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?php echo e($kelolaSemua ? 'Kelola Semua Kos' : 'Kelola Kos Saya'); ?></h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($kelolaSemua): ?>
                        Sebagai admin, Anda dapat mengelola seluruh kos yang terdaftar di Ngekos.in.
                    <?php else: ?>
                        Kos dengan status <span class="font-medium text-emerald-600 dark:text-emerald-400">Aktif</span> akan tampil di halaman Cari Kos untuk dipromosikan.
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </p>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bolehTambah ?? true): ?>
                <a href="<?php echo e(route('pemilik.properti.buat')); ?>" wire:navigate
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-500 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Tambah Kos Baru
                </a>
            <?php else: ?>
                <a href="<?php echo e(route('langganan.plans')); ?>" wire:navigate
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-orange-500/30 hover:brightness-105 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Upgrade untuk Tambah Kos
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! ($bolehTambah ?? true)): ?>
            <div class="rounded-xl bg-rose-50 dark:bg-rose-500/10 ring-1 ring-rose-200 dark:ring-rose-500/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                <?php echo e($kunciTambah); ?>

                <a href="<?php echo e(route('langganan.plans')); ?>" wire:navigate class="font-bold hover:underline">Upgrade ke PRO</a>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $propertis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $properti): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                <div class="p-5 sm:p-6">
                    <div class="flex flex-col sm:flex-row gap-5">
                        <div class="h-36 sm:h-32 sm:w-48 shrink-0 rounded-xl bg-gradient-to-br from-teal-100 via-emerald-100 to-cyan-100 dark:from-teal-500/20 dark:via-emerald-500/20 dark:to-cyan-500/20 overflow-hidden relative">
                            <?php $coverKelola = $properti->fotoCover(); ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($coverKelola): ?>
                                <img src="<?php echo e($coverKelola); ?>" alt="<?php echo e($properti->nama); ?>" class="h-full w-full object-cover">
                            <?php else: ?>
                                <div class="h-full w-full flex items-center justify-center">
                                    <svg class="h-10 w-10 text-teal-300 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18-8.25V21m-1.5-8.25v-3.75a2.25 2.25 0 00-2.25-2.25h-1.5m-1.5 0V3.545c0-.621-.504-1.125-1.125-1.125H8.25c-.621 0-1.125.504-1.125 1.125v7.5" /></svg>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($properti->galeriUrls()) > 1): ?>
                                <span class="absolute bottom-2 left-2 rounded-full bg-black/50 px-1.5 py-0.5 text-[9px] font-bold text-white backdrop-blur-sm"><?php echo e(count($properti->galeriUrls())); ?> foto</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100"><?php echo e($properti->nama); ?></h2>
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
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 truncate"><?php echo e($properti->alamat ?? $properti->kota ?? 'Lokasi belum diisi'); ?></p>
                            <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">
                                <?php echo e($properti->total_kamar); ?> kamar &middot; <?php echo e($properti->kamar_terisi); ?> terisi
                            </p>
                        </div>

                        <div class="flex sm:flex-col items-center sm:items-stretch gap-2 sm:gap-2 shrink-0">
                            <a href="<?php echo e(route('pemilik.kamar', $properti)); ?>" wire:navigate
                               class="inline-flex items-center justify-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500 transition">
                                Kelola Kamar
                            </a>
                            <a href="<?php echo e(route('pemilik.properti.ubah', $properti)); ?>" wire:navigate
                               class="inline-flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Ubah
                            </a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($properti->status === 'aktif'): ?>
                                <button wire:click="ubahStatus(<?php echo e($properti->id); ?>, 'nonaktif')"
                                    class="inline-flex items-center justify-center rounded-lg border border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 px-4 py-2 text-sm font-medium text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-500/20 transition">
                                    Sembunyikan
                                </button>
                            <?php else: ?>
                                <button wire:click="ubahStatus(<?php echo e($properti->id); ?>, 'aktif')"
                                    class="inline-flex items-center justify-center rounded-lg border border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10 px-4 py-2 text-sm font-medium text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition">
                                    Tampilkan
                                </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <button wire:click="hapusProperti(<?php echo e($properti->id); ?>)"
                                    wire:confirm="Hapus kos ini beserta datanya?"
                                class="inline-flex items-center justify-center rounded-lg border border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/10 px-4 py-2 text-sm font-medium text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-500/20 transition">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 py-16 text-center">
                <div class="mx-auto h-16 w-16 rounded-full bg-teal-50 dark:bg-teal-500/10 flex items-center justify-center mb-4">
                    <svg class="h-8 w-8 text-teal-400 dark:text-teal-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18-8.25V21m-1.5-8.25v-3.75a2.25 2.25 0 00-2.25-2.25h-1.5m-1.5 0V3.545c0-.621-.504-1.125-1.125-1.125H8.25c-.621 0-1.125.504-1.125 1.125v7.5" /></svg>
                </div>
                <p class="text-gray-500 dark:text-gray-400 font-medium">Belum ada kos terdaftar.</p>
                <p class="mt-1 text-sm text-gray-400 dark:text-gray-500">Tambahkan kos pertamamu agar mulai dipromosikan di Ngekos.in.</p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bolehTambah ?? true): ?>
                    <a href="<?php echo e(route('pemilik.properti.buat')); ?>" wire:navigate
                       class="mt-5 inline-flex items-center rounded-lg bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-500 transition">
                        Tambah Kos Baru
                    </a>
                <?php else: ?>
                    <a href="<?php echo e(route('langganan.plans')); ?>" wire:navigate
                       class="mt-5 inline-flex items-center rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-orange-500/30 hover:brightness-105 transition">
                        Upgrade untuk Tambah Kos
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div><?php /**PATH C:\laragon\www\Ngekos.in\resources\views\livewire\pages\pemilik\properti.blade.php ENDPATH**/ ?>