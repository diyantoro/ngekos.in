<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['active', 'color' => 'teal']));

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

foreach (array_filter((['active', 'color' => 'teal']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
// Sidebar terang yang tenang: teks slate, aktif = latar brand-50 + teks brand-800.
// Parameter $color dipertahankan agar pemanggil lama tidak error, tapi tidak lagi
// dipakai untuk gradasi warna-warni.
$isActive = (bool) ($active ?? false);

$activeClasses = $isActive
    ? 'inline-flex w-full items-center gap-3 rounded-lg bg-brand-50 px-3 py-2.5 text-sm font-semibold text-brand-800 ring-1 ring-inset ring-brand-100 dark:bg-brand-500/10 dark:text-brand-200 dark:ring-brand-500/20 focus:outline-none transition relative'
    : 'inline-flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 dark:text-gray-300 hover:bg-stone-100 dark:hover:bg-gray-800 hover:text-slate-900 dark:hover:text-white focus:outline-none transition relative';
?>

<a <?php echo e($attributes->merge(['class' => $activeClasses])); ?> wire:navigate>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isActive): ?>
        <span class="absolute left-0 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r-full bg-brand-700 dark:bg-brand-400"></span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <span class="relative shrink-0 <?php echo e($isActive ? 'text-brand-700 dark:text-brand-300' : 'text-slate-400 dark:text-gray-500'); ?>"><?php echo e($slot); ?></span>
    <span class="relative flex-1 truncate"><?php echo e($label ?? ''); ?></span>
</a>
<?php /**PATH C:\laragon\www\Ngekos.in\resources\views\components\sidebar-link.blade.php ENDPATH**/ ?>