<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['bulanan' => null, 'mingguan' => null, 'harian' => null, 'asli' => null, 'varian' => 'kartu']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['bulanan' => null, 'mingguan' => null, 'harian' => null, 'asli' => null, 'varian' => 'kartu']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $asliAngka = $asli !== null && !is_array($asli) ? (float) $asli : null;

    $opsi = [
        'bulanan' => ['nilai' => $bulanan, 'satuan' => 'bln'],
        'mingguan' => ['nilai' => $mingguan, 'satuan' => 'mgg'],
        'harian' => ['nilai' => $harian, 'satuan' => 'hari'],
    ];

    $tersedia = collect($opsi)->filter(fn ($o) => $o['nilai'] !== null && (float) $o['nilai'] > 0);

    $utamaArray = $tersedia->sortBy('nilai')->values()->first();
    $utama = $utamaArray && isset($utamaArray['nilai']) ? (float) $utamaArray['nilai'] : null;
    $satuanUtama = $utamaArray ? $tersedia->search($utamaArray) : null;
    $satuanLabel = $satuanUtama && isset($opsi[$satuanUtama]) ? ($opsi[$satuanUtama]['satuan'] ?? 'bln') : 'bln';

    $lainnya = [];
    if ($utama) {
        $lainnya = $tersedia->except([$satuanUtama])->map(fn ($o) => 'Rp'.number_format((float) $o['nilai'], 0, ',', '.').'/'.$o['satuan'])->values()->all();
    }
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($varian === 'baris'): ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($utama): ?>
        <span class="text-sm sm:text-base font-extrabold text-teal-600 dark:text-teal-400">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($asliAngka && $asliAngka > $utama): ?>
                <span class="block text-xs font-semibold text-gray-400 dark:text-gray-500 line-through">Rp<?php echo e(number_format($asliAngka, 0, ',', '.')); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            Rp<?php echo e(number_format($utama, 0, ',', '.')); ?><span class="text-[10px] font-medium text-gray-400 dark:text-gray-500">/<?php echo e($satuanLabel); ?></span>
        </span>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lainnya !== []): ?>
            <span class="block text-[10px] font-medium text-gray-400 dark:text-gray-500">juga: <?php echo e(implode(' · ', $lainnya)); ?></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php else: ?>
        <span class="text-xs font-medium text-gray-400 dark:text-gray-500">Harga belum diisi</span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php elseif($varian === 'rincian'): ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($utama): ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($asliAngka && $asliAngka > $utama): ?>
            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 line-through">Rp<?php echo e(number_format($asliAngka, 0, ',', '.')); ?></p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <p class="text-base sm:text-lg font-extrabold text-teal-600 dark:text-teal-400">
            Rp<?php echo e(number_format($utama, 0, ',', '.')); ?>

            <span class="text-[10px] font-medium text-gray-400 dark:text-gray-500">/<?php echo e($satuanLabel); ?></span>
        </p>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lainnya !== []): ?>
            <p class="text-[10px] font-medium text-gray-400 dark:text-gray-500">juga: <?php echo e(implode(' · ', $lainnya)); ?></p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php else: ?>
        <p class="text-xs font-medium text-gray-400 dark:text-gray-500">Harga belum diisi</p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php else: ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($utama): ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($asliAngka && $asliAngka > $utama): ?>
            <span class="block text-xs font-semibold text-gray-400 dark:text-gray-500 line-through">Rp<?php echo e(number_format($asliAngka, 0, ',', '.')); ?></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <span class="text-sm sm:text-base font-extrabold text-teal-600 dark:text-teal-400">
            Rp<?php echo e(number_format($utama, 0, ',', '.')); ?><span class="text-[10px] font-medium text-gray-400 dark:text-gray-500">/<?php echo e($satuanLabel); ?></span>
        </span>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lainnya !== []): ?>
            <span class="block text-[10px] text-gray-400 dark:text-gray-500">juga: <?php echo e(implode(' · ', $lainnya)); ?></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php else: ?>
        <span class="text-xs font-medium text-gray-400 dark:text-gray-500">Harga belum diisi</span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH C:\laragon\www\Ngekos.in\resources\views\components\harga-tiga-periode.blade.php ENDPATH**/ ?>