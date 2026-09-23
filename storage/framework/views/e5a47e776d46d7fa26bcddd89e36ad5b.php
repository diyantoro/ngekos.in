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
    $tones = [
        'teal' => 'from-teal-500 to-emerald-500',
        'cyan' => 'from-cyan-500 to-sky-500',
        'emerald' => 'from-emerald-500 to-green-500',
        'sky' => 'from-sky-500 to-blue-500',
        'amber' => 'from-amber-500 to-orange-500',
        'rose' => 'from-rose-500 to-pink-500',
    ];
    $shadows = [
        'teal' => 'shadow-teal-500/20',
        'cyan' => 'shadow-cyan-500/20',
        'sky' => 'shadow-sky-500/20',
        'emerald' => 'shadow-emerald-500/20',
        'amber' => 'shadow-amber-500/20',
        'rose' => 'shadow-rose-500/20',
    ];
    $bgTones = [
        'teal' => 'bg-teal-50 text-teal-600 dark:bg-teal-900/40 dark:text-teal-300',
        'cyan' => 'bg-cyan-50 text-cyan-600 dark:bg-cyan-900/40 dark:text-cyan-300',
        'emerald' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-300',
        'sky' => 'bg-sky-50 text-sky-600 dark:bg-sky-900/40 dark:text-sky-300',
        'amber' => 'bg-amber-50 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300',
        'rose' => 'bg-rose-50 text-rose-600 dark:bg-rose-900/40 dark:text-rose-300',
    ];
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($href): ?>
    <a href="<?php echo e($href); ?>" wire:navigate class="block card group hover:shadow-card-hover hover:-translate-y-0.5 p-5 flex items-center gap-4 transition cursor-pointer">
<?php else: ?>
    <div class="card group hover:shadow-card-hover hover:-translate-y-0.5 p-5 flex items-center gap-4">
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <div class="shrink-0 h-12 w-12 rounded-xl flex items-center justify-center bg-gradient-to-br <?php echo e($tones[$tone]); ?> text-white shadow-lg <?php echo e($shadows[$tone] ?? 'shadow-teal-500/20'); ?> transition-transform duration-300 group-hover:scale-110">
        <?php echo $icon; ?>

    </div>
    <div class="min-w-0 flex-1">
        <p class="truncate text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"><?php echo e($label); ?></p>
        <p class="mt-0.5 text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-gray-100 break-words leading-snug"><?php echo e($value); ?></p>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hint): ?>
            <p class="mt-0.5 flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400"><?php echo $hint; ?></p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($href): ?>
        <svg class="ml-auto h-5 w-5 shrink-0 text-gray-300 dark:text-gray-600 group-hover:text-teal-500 transition" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($href): ?>
    </a>
<?php else: ?>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH C:\laragon\www\Ngekos.in\resources\views/components/stat-card.blade.php ENDPATH**/ ?>