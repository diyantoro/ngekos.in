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
$colorMap = [
    'teal' => ['bg' => 'from-white/25 via-white/10 to-emerald-300/25 dark:from-teal-400/25 dark:via-teal-400/10 dark:to-emerald-400/25', 'text' => 'text-white dark:text-teal-100', 'ring' => 'ring-white/50 dark:ring-teal-400/30', 'icon' => 'text-white dark:text-teal-200'],
    'sky' => ['bg' => 'from-white/25 via-white/10 to-cyan-300/25 dark:from-teal-400/25 dark:via-teal-400/10 dark:to-cyan-400/25', 'text' => 'text-white dark:text-teal-100', 'ring' => 'ring-white/50 dark:ring-teal-400/30', 'icon' => 'text-white dark:text-teal-200'],
    'amber' => ['bg' => 'from-white/25 via-white/10 to-amber-300/25 dark:from-teal-400/25 dark:via-teal-400/10 dark:to-emerald-400/25', 'text' => 'text-white dark:text-teal-100', 'ring' => 'ring-white/50 dark:ring-teal-400/30', 'icon' => 'text-white dark:text-teal-200'],
    'rose' => ['bg' => 'from-white/25 via-white/10 to-rose-300/25 dark:from-teal-400/25 dark:via-teal-400/10 dark:to-emerald-400/25', 'text' => 'text-white dark:text-teal-100', 'ring' => 'ring-white/50 dark:ring-teal-400/30', 'icon' => 'text-white dark:text-teal-200'],
    'emerald' => ['bg' => 'from-white/25 via-white/10 to-emerald-300/25 dark:from-teal-400/25 dark:via-teal-400/10 dark:to-emerald-400/25', 'text' => 'text-white dark:text-teal-100', 'ring' => 'ring-white/50 dark:ring-teal-400/30', 'icon' => 'text-white dark:text-teal-200'],
    'cyan' => ['bg' => 'from-white/25 via-white/10 to-cyan-300/25 dark:from-teal-400/25 dark:via-teal-400/10 dark:to-cyan-400/25', 'text' => 'text-white dark:text-teal-100', 'ring' => 'ring-white/50 dark:ring-teal-400/30', 'icon' => 'text-white dark:text-teal-200'],
    'indigo' => ['bg' => 'from-white/25 via-white/10 to-indigo-300/25 dark:from-teal-400/25 dark:via-teal-400/10 dark:to-emerald-400/25', 'text' => 'text-white dark:text-teal-100', 'ring' => 'ring-white/50 dark:ring-teal-400/30', 'icon' => 'text-white dark:text-teal-200'],
    'purple' => ['bg' => 'from-white/25 via-white/10 to-purple-300/25 dark:from-teal-400/25 dark:via-teal-400/10 dark:to-emerald-400/25', 'text' => 'text-white dark:text-teal-100', 'ring' => 'ring-white/50 dark:ring-teal-400/30', 'icon' => 'text-white dark:text-teal-200'],
    'violet' => ['bg' => 'from-white/25 via-white/10 to-violet-300/25 dark:from-teal-400/25 dark:via-teal-400/10 dark:to-emerald-400/25', 'text' => 'text-white dark:text-teal-100', 'ring' => 'ring-white/50 dark:ring-teal-400/30', 'icon' => 'text-white dark:text-teal-200'],
    'orange' => ['bg' => 'from-white/25 via-white/10 to-orange-300/25 dark:from-teal-400/25 dark:via-teal-400/10 dark:to-emerald-400/25', 'text' => 'text-white dark:text-teal-100', 'ring' => 'ring-white/50 dark:ring-teal-400/30', 'icon' => 'text-white dark:text-teal-200'],
    'gray' => ['bg' => 'from-white/25 via-white/10 to-gray-300/25 dark:from-teal-400/25 dark:via-teal-400/10 dark:to-emerald-400/25', 'text' => 'text-white dark:text-teal-100', 'ring' => 'ring-white/50 dark:ring-teal-400/30', 'icon' => 'text-white dark:text-teal-200'],
];

$c = $colorMap[$color] ?? $colorMap['teal'];

// Active state: soft white bg over teal sidebar, white text/icon, rounded-2xl
$activeClasses = ($active ?? false)
    ? 'inline-flex w-full items-center gap-3 rounded-2xl bg-gradient-to-r ' . $c['bg'] . ' px-3 py-2.5 text-sm font-semibold ' . $c['text'] . ' ring-1 ring-inset ' . $c['ring'] . ' border border-white/25 dark:border-teal-400/30 shadow-sm shadow-teal-900/20 dark:shadow-teal-500/20 focus:outline-none transition-all duration-200 relative'
    // Inactive state: translucent white text, hover = white bg
    : 'inline-flex w-full items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-medium text-white/85 dark:text-teal-100 hover:bg-white/10 dark:hover:bg-white/10 hover:text-white dark:hover:text-white focus:outline-none transition-all duration-200 active:scale-[0.98] relative';

$hoverOverlay = 'absolute inset-0 bg-gradient-to-r from-white/10 via-transparent to-white/10 dark:from-teal-400/10 dark:via-transparent dark:to-teal-400/10 opacity-0 transition-opacity duration-200 pointer-events-none';
?>

<a <?php echo e($attributes->merge(['class' => $activeClasses])); ?> wire:navigate>
    
    <div class="<?php echo e($hoverOverlay); ?> group-hover:opacity-100"></div>
    
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($active ?? false): ?>
        <div class="absolute left-0 top-1/2 -translate-y-1/2 h-6 w-0.5 rounded-r-full bg-white dark:bg-teal-300 opacity-90"></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    
    <span class="relative shrink-0 transition-all duration-200 <?php echo e($active ? $c['icon'] . ' scale-110' : 'text-white/70 dark:text-teal-300 group-hover:text-white dark:group-hover:text-teal-200 group-hover:scale-110'); ?>"><?php echo e($slot); ?></span>
    <span class="relative flex-1 truncate transition-colors duration-200"><?php echo e($label ?? ''); ?></span>
</a><?php /**PATH C:\laragon\www\Ngekos.in\resources\views\components\sidebar-link.blade.php ENDPATH**/ ?>