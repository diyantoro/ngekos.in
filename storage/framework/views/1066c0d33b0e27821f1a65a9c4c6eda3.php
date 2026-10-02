<?php

use App\Models\Pengaturan;
use App\Services\LanggananNotifier;
use App\Services\SubscriptionService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

?>

<div class="py-10">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">Bayar <?php echo e($nama); ?></h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Scan QRIS sebesar nominal di bawah lalu unggah bukti pembayaran. Paket langsung aktif otomatis setelah terkirim.</p>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($galat): ?>
            <div class="flex items-center justify-between gap-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 ring-1 ring-rose-200 dark:ring-rose-500/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                <span><?php echo e($galat); ?></span>
                <button wire:click="$set('galat', null)" class="font-bold">&times;</button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="card p-6 text-center">
            <p class="text-3xl font-extrabold text-gray-900 dark:text-gray-100">Rp<?php echo e(number_format($harga, 0, ',', '.')); ?></p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Paket <?php echo e($nama); ?> · 1 bulan · via QRIS</p>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $qrisAktif): ?>
            <div class="flex items-start gap-3 rounded-xl bg-amber-50 dark:bg-amber-500/10 ring-1 ring-amber-200 dark:ring-amber-500/30 px-4 py-3 text-sm text-amber-800 dark:text-amber-200">
                <span>Admin belum mengatur pembayaran QRIS. Hubungi admin untuk mengaktifkannya di Pengaturan → Pembayaran.</span>
            </div>
        <?php else: ?>
            <div class="card p-6 space-y-4">
                <div class="flex flex-col items-center gap-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qrisImage): ?>
                        <img id="gambarQris" src="<?php echo e(asset('storage/'.$qrisImage)); ?>" alt="QRIS <?php echo e($nama); ?>"
                            class="h-52 w-52 rounded-xl object-contain ring-1 ring-gray-200 dark:ring-gray-700 bg-white p-2">
                    <?php else: ?>
                        <img id="gambarQris" src="https://api.qrserver.com/v1/create-qr-code/?size=240x240&margin=12&data=<?php echo e(urlencode($qris)); ?>"
                            alt="QRIS <?php echo e($nama); ?>"
                            class="h-52 w-52 rounded-xl ring-1 ring-gray-200 dark:ring-gray-700 bg-white p-2"
                            loading="lazy">
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <a id="unduhQrisBtn" href="<?php echo e(route('langganan.qris-unduh', ['plan' => $plan])); ?>" download
                        class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-teal-500 transition active:scale-[0.98] shadow">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        Unduh QRIS
                    </a>
                    <p class="text-xs text-gray-400">Bayar tepat <span class="font-bold text-gray-700 dark:text-gray-200">Rp<?php echo e(number_format($harga, 0, ',', '.')); ?></span> sesuai nominal di atas.</p>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($petunjuk): ?>
                    <p class="text-center text-sm text-gray-500 dark:text-gray-400 whitespace-pre-line"><?php echo e($petunjuk); ?></p>
                <?php else: ?>
                    <p class="text-center text-sm text-gray-500 dark:text-gray-400">Setelah membayar, unggah bukti di bawah ini.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <form wire:submit="bayar" class="card p-6 space-y-4">
                <div>
                    <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => 'bukti','value' => 'Bukti Pembayaran']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'bukti','value' => 'Bukti Pembayaran']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $attributes = $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $component = $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
                    <input type="file" id="bukti" wire:model="bukti" accept="image/*"
                        class="mt-2 block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-teal-600 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-teal-500">
                    <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('bukti'),'class' => 'mt-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('bukti')),'class' => 'mt-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $attributes = $__attributesOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__attributesOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $component = $__componentOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__componentOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
                    <div wire:loading wire:target="bukti" class="mt-2 text-xs text-gray-400">Mengunggah...</div>
                </div>
                <button type="submit" wire:loading.attr="disabled"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-teal-600 px-5 py-3 text-sm font-bold text-white hover:bg-teal-500 transition disabled:opacity-50 active:scale-[0.98]">
                    Kirim Pembayaran
                </button>
                <p class="text-center text-xs text-gray-400">Setelah terkirim, paket langsung aktif otomatis.</p>
            </form>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="text-center">
            <a href="<?php echo e(route('langganan.plans')); ?>" wire:navigate class="text-sm font-semibold text-teal-600 dark:text-teal-400 hover:underline">← Kembali ke daftar paket</a>
        </div>
    </div>
</div><?php /**PATH C:\laragon\www\Ngekos.in\resources\views\livewire\pages\langganan\bayar.blade.php ENDPATH**/ ?>