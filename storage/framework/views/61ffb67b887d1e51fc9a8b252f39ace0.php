<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['label', 'value', 'icon', 'tone' => 'teal', 'hint' => null, 'href' => null]));

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

foreach (array_filter((['label', 'value', 'icon', 'tone' => 'teal', 'hint' => null, 'href' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    // Disederhanakan: hanya 3 rumpun nada agar dashboard terlihat dikerjakan
    // manusia, bukan template AI warna-warni.
    $iconTones = [
        'teal' => 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300',
        'cyan' => 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300',
        'emerald' => 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300',
        'sky' => 'bg-stone-100 text-stone-600 dark:bg-gray-700 dark:text-gray-300',
        'amber' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
        'rose' => 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-300',
    ];
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($href): ?>
    <a href="<?php echo e($href); ?>" wire:navigate class="block card group p-5 flex items-center gap-4 transition hover:shadow-card-hover">
<?php else: ?>
    <div class="card p-5 flex items-center gap-4">
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <div class="shrink-0 h-11 w-11 rounded-lg flex items-center justify-center <?php echo e($iconTones[$tone] ?? $iconTones['teal']); ?>">
        <?php echo $icon; ?>

    </div>
    <div class="min-w-0 flex-1">
        <p class="truncate text-xs font-medium text-slate-500 dark:text-gray-400"><?php echo e($label); ?></p>
        <p class="mt-0.5 text-xl font-bold text-slate-900 dark:text-gray-100 break-words leading-snug tracking-tight"><?php echo e($value); ?></p>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hint): ?>
            <p class="mt-1 flex items-center gap-1 text-xs text-slate-500 dark:text-gray-400"><?php echo $hint; ?></p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($href): ?>
        <svg class="ml-auto h-5 w-5 shrink-0 text-slate-300 dark:text-gray-600 group-hover:text-brand-600 transition" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($href): ?>
    </a>
<?php else: ?>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\Ngekos.in\resources\views\components\stat-card.blade.php ENDPATH**/ ?>