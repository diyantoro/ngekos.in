<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['requiredPlan' => 'pro', 'title' => 'Fitur Premium', 'message' => null]));

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

foreach (array_filter((['requiredPlan' => 'pro', 'title' => 'Fitur Premium', 'message' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $planName = strtoupper($requiredPlan);
    $message = $message ?? "Fitur ini tersedia pada paket {$planName}.";
?>

<div class="rounded-2xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-6 text-center">
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-500">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
    </div>
    <h3 class="mt-3 text-base font-bold text-gray-900 dark:text-gray-100">🔒 <?php echo e($title); ?></h3>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400"><?php echo e($message); ?></p>
    <a href="<?php echo e(route('langganan.plans')); ?>" wire:navigate class="btn-primary mt-4 inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white">
        Upgrade ke <?php echo e($planName); ?>

    </a>
</div>
<?php /**PATH C:\laragon\www\Ngekos.in\resources\views\components\premium-lock.blade.php ENDPATH**/ ?>