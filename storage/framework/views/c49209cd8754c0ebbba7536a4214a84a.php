<?php

use App\Models\Pengaturan;
use App\Models\Properti;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

?>

<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <a href="<?php echo e(route('dashboard')); ?>" wire:navigate class="text-sm font-medium text-teal-600 dark:text-teal-400 hover:text-teal-500 dark:hover:text-teal-300 inline-flex items-center gap-1">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Kembali ke dashboard
            </a>
            <h1 class="mt-2 text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">Kelola Landing</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Atur hero, Promo Ngebut, dan banner iklan yang tampil di halaman utama.</p>
        </div>

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
            <div class="flex items-center justify-between gap-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 ring-1 ring-rose-200 dark:ring-rose-500/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                <span><?php echo e($galat); ?></span>
                <button wire:click="$set('galat', null)" class="text-rose-500 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 font-bold">&times;</button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="flex flex-col sm:flex-row gap-2">
            <input type="text" wire:model.live.debounce.300ms="cari" placeholder="Cari kos berdasarkan nama atau kota..."
                class="w-full rounded-lg border-gray-300 text-sm focus:ring-teal-500 focus:border-teal-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-5">
                <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">Gambar kos mengambang (Hero)</h4>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Pin 1 kos agar foto hero di landing tidak ikut berubah saat ada kos baru. Kosongkan untuk kembali otomatis (kos terbaru).</p>
                <div class="mt-3 flex flex-col sm:flex-row gap-2">
                    <select wire:model="heroPropertiId"
                        class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                        <option value="">Otomatis (kos terbaru)</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($heroProperti && ! $landingPropertis->contains('id', $heroProperti->id)): ?>
                            <option value="<?php echo e($heroProperti->id); ?>"><?php echo e($heroProperti->nama); ?> — terpin saat ini</option>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $landingPropertis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($lp->id); ?>"><?php echo e($lp->nama); ?> — <?php echo e($lp->kota ?? '-'); ?> (<?php echo e($lp->status); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <div class="flex gap-2">
                        <button wire:click="simpanHero" wire:loading.attr="disabled"
                            class="inline-flex items-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500 transition disabled:opacity-50">
                            Simpan
                        </button>
                        <button wire:click="lepasHero" wire:loading.attr="disabled"
                            class="inline-flex items-center rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 transition disabled:opacity-50">
                            Otomatis
                        </button>
                    </div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['heroPropertiId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-rose-600 dark:text-rose-400"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($heroProperti): ?>
                    <div class="mt-3 flex items-center gap-3 rounded-xl bg-gray-50 dark:bg-gray-800/60 ring-1 ring-gray-100 dark:ring-gray-700 p-3">
                        <?php $heroCover = $heroProperti->fotoCover(); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($heroCover): ?>
                            <img src="<?php echo e($heroCover); ?>" alt="<?php echo e($heroProperti->nama); ?>" class="h-12 w-12 rounded-lg object-cover">
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate"><?php echo e($heroProperti->nama); ?></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate"><?php echo e($heroProperti->kota ?? $heroProperti->alamat ?? '-'); ?> · <?php echo e($heroProperti->pemilik?->nama ?? '-'); ?></p>
                        </div>
                        <span class="ml-auto shrink-0"><?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $heroProperti->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($heroProperti->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?></span>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($heroProperti->status !== 'aktif'): ?>
                        <p class="mt-2 text-xs text-amber-600 dark:text-amber-400">Kos ini tidak aktif — landing otomatis memakai kos terbaru sampai Anda ganti pin.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php else: ?>
                    <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">Mode otomatis aktif. Cari kos lewat kolom “Cari data...” di atas untuk mempersempit daftar.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">Promo Ngebut (<?php echo e(count($promoIds)); ?>/8)</h4>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Centang kos untuk dikunci di Promo Ngebut sesuai urutan. Kosongkan semua untuk kembali otomatis (kos diskon terbaru).</p>
                    </div>
                    <button wire:click="resetPromo" wire:loading.attr="disabled"
                        class="shrink-0 inline-flex items-center rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 transition disabled:opacity-50">
                        Kembali otomatis
                    </button>
                </div>
                <form wire:submit="simpanPromoBerakhir" class="mt-3 flex flex-wrap items-end gap-x-3 gap-y-2 rounded-xl bg-gray-50 dark:bg-gray-800/60 ring-1 ring-gray-100 dark:ring-gray-700 p-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Hitung mundur berakhir</label>
                        <input type="datetime-local" wire:model="promoBerakhirPada"
                            class="mt-1 block rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                    </div>
                    <button type="submit" class="inline-flex items-center rounded-lg bg-teal-600 px-3 py-2 text-xs font-semibold text-white hover:bg-teal-500 transition">Simpan waktu</button>
                    <button type="button" wire:click="resetPromoBerakhir" class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 transition">Otomatis (akhir bulan)</button>
                    <p class="w-full text-[11px] text-gray-400 dark:text-gray-500">Waktu hari/jam/menit/detik di landing. Kosongkan kembali = akhir bulan berjalan.</p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['promoBerakhirPada'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="w-full text-xs text-rose-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </form>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($promoTerpilih->isNotEmpty()): ?>
                    <div class="mt-3 space-y-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $promoTerpilih; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $urutan => $pp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center gap-2 rounded-xl bg-gray-50 dark:bg-gray-800/60 ring-1 ring-gray-100 dark:ring-gray-700 px-3 py-2">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-teal-600 text-[11px] font-extrabold text-white"><?php echo e($urutan + 1); ?></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-gray-900 dark:text-gray-100 truncate"><?php echo e($pp->nama); ?></p>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate"><?php echo e($pp->kota ?? '-'); ?> · Rp<?php echo e(number_format($pp->harga ?? 0, 0, ',', '.')); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pp->harga_asli && $pp->harga_asli > $pp->harga): ?> <span class="line-through">Rp<?php echo e(number_format($pp->harga_asli, 0, ',', '.')); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></p>
                                </div>
                                <div class="flex shrink-0 items-center gap-1">
                                    <button wire:click="naikPromo(<?php echo e($pp->id); ?>)" title="Naik" class="rounded-md px-1.5 py-1 text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">↑</button>
                                    <button wire:click="turunPromo(<?php echo e($pp->id); ?>)" title="Turun" class="rounded-md px-1.5 py-1 text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">↓</button>
                                    <button wire:click="togglePromo(<?php echo e($pp->id); ?>)" title="Lepas" class="rounded-md px-1.5 py-1 font-bold text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10">&times;</button>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="mt-3 divide-y divide-gray-100 dark:divide-gray-700 rounded-xl ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $landingPropertis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $terpilih = in_array((int) $lp->id, array_map('intval', (array) $promoIds), true); ?>
                        <div class="flex items-center gap-3 px-3 py-2.5 <?php echo e($terpilih ? 'bg-teal-50/60 dark:bg-teal-500/5' : ''); ?>">
                            <button wire:click="togglePromo(<?php echo e($lp->id); ?>)" wire:loading.attr="disabled"
                                class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md border <?php echo e($terpilih ? 'border-teal-600 bg-teal-600 text-white' : 'border-gray-300 dark:border-gray-600 text-transparent'); ?>">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            </button>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold text-gray-900 dark:text-gray-100 truncate"><?php echo e($lp->nama); ?></p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate"><?php echo e($lp->kota ?? $lp->alamat ?? '-'); ?> · <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $lp->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($lp->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?></p>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($terpilih): ?>
                                <span class="shrink-0 rounded-full bg-teal-600 px-2 py-0.5 text-[10px] font-bold text-white">#<?php echo e(array_search((int) $lp->id, array_map('intval', (array) $promoIds), true) + 1); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="px-3 py-6 text-center text-xs text-gray-400 dark:text-gray-500">Tidak ada kos yang cocok. Ubah kata kunci pencarian.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">Banner iklan partner (<?php echo e(count($banners)); ?>/8)</h4>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Banner paling atas di landing. Nonaktifkan untuk menyembunyikan tanpa menghapus.</p>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <button wire:click="baruBanner" class="inline-flex items-center rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-500 transition">+ Banner</button>
                        <button wire:click="kembalikanBannerDefault" class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 transition">Kembalikan bawaan</button>
                    </div>
                </div>
                <form wire:submit="simpanIntervalPromo" class="mt-3 flex flex-wrap items-end gap-x-3 gap-y-2 rounded-xl bg-gray-50 dark:bg-gray-800/60 ring-1 ring-gray-100 dark:ring-gray-700 p-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Jeda auto-slide promo (detik)</label>
                        <input type="number" wire:model="promoIntervalDetik" min="1" max="10" step="0.5"
                            class="mt-1 block w-28 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                    </div>
                    <button type="submit" class="inline-flex items-center rounded-lg bg-teal-600 px-3 py-2 text-xs font-semibold text-white hover:bg-teal-500 transition">Simpan jeda</button>
                    <p class="w-full text-[11px] text-gray-400 dark:text-gray-500">Berlaku untuk semua carousel promo (landing & halaman cari kos). Antara 1–10 detik.</p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['promoIntervalDetik'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="w-full text-xs text-rose-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </form>
                <div class="mt-3 space-y-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $banners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $bn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-center gap-3 rounded-xl ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden <?php echo e(($bn['aktif'] ?? true) ? '' : 'opacity-60'); ?>">
                            <div class="flex h-14 w-20 shrink-0 items-center justify-center bg-gradient-to-tr <?php echo e($bn['gradient']); ?>">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($bn['image'])): ?>
                                    <img src="<?php echo e(asset('storage/'.$bn['image'])); ?>" alt="<?php echo e($bn['brand']); ?>" class="h-full w-full object-cover">
                                <?php else: ?>
                                    <span class="text-sm font-extrabold text-white drop-shadow"><?php echo e(strtoupper(mb_substr($bn['brand'], 0, 1))); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="min-w-0 flex-1 py-2">
                                <p class="text-xs font-bold text-gray-900 dark:text-gray-100 truncate"><?php echo e($bn['brand']); ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! ($bn['aktif'] ?? true)): ?><span class="ml-1 rounded-full bg-gray-200 dark:bg-gray-700 px-1.5 py-0.5 text-[10px] font-semibold text-gray-500">nonaktif</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate"><?php echo e($bn['tagline']); ?></p>
                            </div>
                            <div class="flex shrink-0 items-center gap-1 pr-2">
                                <button wire:click="ubahBanner(<?php echo e($i); ?>)" title="Ubah" class="rounded-md px-1.5 py-1 text-xs font-semibold text-teal-600 hover:bg-teal-50 dark:hover:bg-teal-500/10">Ubah</button>
                                <button wire:click="toggleBanner(<?php echo e($i); ?>)" title="Aktif/nonaktif" class="rounded-md px-1.5 py-1 text-xs font-semibold text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700"><?php echo e(($bn['aktif'] ?? true) ? 'Matikan' : 'Nyalakan'); ?></button>
                                <button wire:click="naikBanner(<?php echo e($i); ?>)" title="Naik" class="rounded-md px-1 py-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">↑</button>
                                <button wire:click="turunBanner(<?php echo e($i); ?>)" title="Turun" class="rounded-md px-1 py-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">↓</button>
                                <button wire:click="hapusBanner(<?php echo e($i); ?>)" title="Hapus" class="rounded-md px-1.5 py-1 font-bold text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10">&times;</button>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="rounded-xl ring-1 ring-dashed ring-gray-300 dark:ring-gray-600 px-3 py-6 text-center text-xs text-gray-400 dark:text-gray-500">Memakai banner bawaan (Biznet, Shopee, GoFood, DANA, IndiHome, IKEA). Tambah banner untuk mengganti.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <form wire:submit="simpanBanner" class="mt-4 space-y-3 rounded-xl bg-gray-50 dark:bg-gray-800/60 ring-1 ring-gray-100 dark:ring-gray-700 p-3 sm:p-4">
                    <p class="text-xs font-bold text-gray-900 dark:text-gray-100"><?php echo e($bannerIndex !== null ? 'Ubah banner' : 'Tambah banner baru'); ?></p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Nama brand</label>
                            <input type="text" wire:model="bannerBrand" placeholder="cth: Biznet"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['bannerBrand'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-rose-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Tagline</label>
                            <input type="text" wire:model="bannerTagline" placeholder="cth: Internet Cepat & Stabil"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['bannerTagline'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-rose-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Deskripsi</label>
                        <input type="text" wire:model="bannerDesc" placeholder="cth: Pasang internet, kuliah online makin lancar."
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['bannerDesc'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-rose-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Gradasi warna</label>
                            <select wire:model="bannerGradient"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                                <option value="from-blue-700 via-sky-600 to-sky-500">Biru</option>
                                <option value="from-orange-600 via-orange-500 to-amber-400">Oranye</option>
                                <option value="from-green-600 via-emerald-500 to-teal-500">Hijau</option>
                                <option value="from-sky-600 via-blue-500 to-indigo-500">Biru DANA</option>
                                <option value="from-red-700 via-rose-600 to-pink-500">Merah</option>
                                <option value="from-blue-800 via-blue-600 to-cyan-500">Biru tua</option>
                                <option value="from-teal-700 via-emerald-600 to-lime-500">Teal</option>
                                <option value="from-violet-700 via-purple-600 to-fuchsia-500">Ungu</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Ikon</label>
                            <select wire:model="bannerIcon"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                                <option value="wifi">Wifi</option>
                                <option value="bag">Tas belanja</option>
                                <option value="scooter">Skuter</option>
                                <option value="wallet">Dompet</option>
                                <option value="tv">TV</option>
                                <option value="chair">Kursi</option>
                                <option value="tag">Tag promo</option>
                                <option value="gift">Kado</option>
                                <option value="star">Bintang</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Warna aksen</label>
                            <select wire:model="bannerAccent"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                                <option value="text-sky-200">Biru muda</option>
                                <option value="text-amber-200">Kuning</option>
                                <option value="text-emerald-200">Hijau muda</option>
                                <option value="text-blue-200">Biru</option>
                                <option value="text-rose-200">Merah muda</option>
                                <option value="text-cyan-200">Cyan</option>
                                <option value="text-white">Putih</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Gambar banner (opsional, maks 2MB)</label>
                        <input type="file" wire:model="bannerGambar" accept="image/*"
                            class="mt-1 block w-full text-xs text-gray-600 dark:text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-teal-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-teal-500">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['bannerGambar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-rose-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bannerGambar): ?>
                            <img src="<?php echo e($bannerGambar->temporaryUrl()); ?>" alt="Pratinjau banner" class="mt-2 h-20 w-auto rounded-lg object-cover ring-1 ring-gray-200 dark:ring-gray-700">
                        <?php elseif($bannerImageLama): ?>
                            <img src="<?php echo e(asset('storage/'.$bannerImageLama)); ?>" alt="Banner aktif" class="mt-2 h-20 w-auto rounded-lg object-cover ring-1 ring-gray-200 dark:ring-gray-700">
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-200">
                        <input type="checkbox" wire:model="bannerAktif" class="h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                        Tampilkan di landing
                    </label>
                    <div class="flex items-center gap-2 pt-1">
                        <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex items-center rounded-lg bg-teal-600 px-4 py-2 text-xs font-semibold text-white hover:bg-teal-500 transition disabled:opacity-50">
                            <?php echo e($bannerIndex !== null ? 'Simpan perubahan' : 'Tambah banner'); ?>

                        </button>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bannerIndex !== null): ?>
                            <button type="button" wire:click="baruBanner" class="inline-flex items-center rounded-lg bg-gray-200 dark:bg-gray-700 px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300">Batal</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <span wire:loading.delay wire:target="simpanBanner,bannerGambar" class="text-xs text-gray-400">Menyimpan...</span>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\laragon\www\Ngekos.in\resources\views\livewire\pages\super-admin\landing.blade.php ENDPATH**/ ?>