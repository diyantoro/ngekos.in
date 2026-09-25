<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['disabled' => false, 'error' => null]));

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

foreach (array_filter((['disabled' => false, 'error' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$baseClasses = 'rounded-xl shadow-sm transition-colors dark:bg-gray-800 dark:text-gray-100 ';
$stateClasses = $error
    ? 'border-rose-500 focus:border-rose-500 focus:ring-rose-500 dark:border-rose-500'
    : 'border-gray-300 focus:border-teal-500 focus:ring-teal-500 dark:border-gray-600';
?>

<input <?php if($disabled): echo 'disabled'; endif; ?>
       <?php if($error): ?> aria-invalid="true" <?php endif; ?>
       <?php echo e($attributes->merge(['class' => $baseClasses . $stateClasses])); ?>>
<?php /**PATH C:\laragon\www\Ngekos.in\resources\views\components\text-input.blade.php ENDPATH**/ ?>