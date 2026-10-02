<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['status']));

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

foreach (array_filter((['status']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $colors = [
        'tersedia' => 'bg-emerald-50 text-emerald-700 ring-emerald-200/80 dark:bg-emerald-900/40 dark:text-emerald-300 dark:ring-emerald-800',
        'terisi' => 'bg-amber-50 text-amber-700 ring-amber-200/80 dark:bg-amber-900/40 dark:text-amber-300 dark:ring-amber-800',
        'perbaikan' => 'bg-sky-50 text-sky-700 ring-sky-200/80 dark:bg-sky-900/40 dark:text-sky-300 dark:ring-sky-800',
        'aktif' => 'bg-emerald-50 text-emerald-700 ring-emerald-200/80 dark:bg-emerald-900/40 dark:text-emerald-300 dark:ring-emerald-800',
        'nonaktif' => 'bg-rose-50 text-rose-700 ring-rose-200/80 dark:bg-rose-900/40 dark:text-rose-300 dark:ring-rose-800',
        'selesai' => 'bg-gray-100 text-gray-600 ring-gray-200/80 dark:bg-gray-700 dark:text-gray-300 dark:ring-gray-600',
        'menunggu' => 'bg-amber-50 text-amber-700 ring-amber-200/80 dark:bg-amber-900/40 dark:text-amber-300 dark:ring-amber-800',
        'menunggu_verifikasi' => 'bg-amber-50 text-amber-700 ring-amber-200/80 dark:bg-amber-900/40 dark:text-amber-300 dark:ring-amber-800',
        'disetujui' => 'bg-emerald-50 text-emerald-700 ring-emerald-200/80 dark:bg-emerald-900/40 dark:text-emerald-300 dark:ring-emerald-800',
        'check_in' => 'bg-sky-50 text-sky-700 ring-sky-200/80 dark:bg-sky-900/40 dark:text-sky-300 dark:ring-sky-800',
        'diverifikasi' => 'bg-emerald-50 text-emerald-700 ring-emerald-200/80 dark:bg-emerald-900/40 dark:text-emerald-300 dark:ring-emerald-800',
        'ditolak' => 'bg-rose-50 text-rose-700 ring-rose-200/80 dark:bg-rose-900/40 dark:text-rose-300 dark:ring-rose-800',
        'batal' => 'bg-gray-100 text-gray-600 ring-gray-200/80 dark:bg-gray-700 dark:text-gray-300 dark:ring-gray-600',
        'belum_bayar' => 'bg-rose-50 text-rose-700 ring-rose-200/80 dark:bg-rose-900/40 dark:text-rose-300 dark:ring-rose-800',
        'lunas' => 'bg-emerald-50 text-emerald-700 ring-emerald-200/80 dark:bg-emerald-900/40 dark:text-emerald-300 dark:ring-emerald-800',
        'terlambat' => 'bg-rose-50 text-rose-700 ring-rose-200/80 dark:bg-rose-900/40 dark:text-rose-300 dark:ring-rose-800',
        'patungan' => 'bg-sky-50 text-sky-700 ring-sky-200/80 dark:bg-sky-900/40 dark:text-sky-300 dark:ring-sky-800',
        'tunggal' => 'bg-gray-100 text-gray-600 ring-gray-200/80 dark:bg-gray-700 dark:text-gray-300 dark:ring-gray-600',
        'keluar' => 'bg-gray-100 text-gray-600 ring-gray-200/80 dark:bg-gray-700 dark:text-gray-300 dark:ring-gray-600',
        'expired' => 'bg-rose-50 text-rose-700 ring-rose-200/80 dark:bg-rose-900/40 dark:text-rose-300 dark:ring-rose-800',
        'cancelled' => 'bg-gray-100 text-gray-600 ring-gray-200/80 dark:bg-gray-700 dark:text-gray-300 dark:ring-gray-600',
    ];
    $label = str($status)->replace('_', ' ')->title();
?>

<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset transition-colors duration-200 <?php echo e($colors[$status] ?? 'bg-gray-100 text-gray-600 ring-gray-200/80'); ?>">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($status, ['tersedia', 'aktif', 'lunas', 'diverifikasi', 'disetujui'])): ?>
        <span class="h-1.5 w-1.5 rounded-full bg-current opacity-60"></span>
    <?php elseif(in_array($status, ['terlambat', 'ditolak', 'nonaktif', 'belum_bayar'])): ?>
        <span class="h-1.5 w-1.5 rounded-full bg-current opacity-60"></span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php echo e($label); ?>

</span>
<?php /**PATH C:\laragon\www\Ngekos.in\resources\views\components\status-badge.blade.php ENDPATH**/ ?>