<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'pesan',
    'judul' => 'Berhasil!',
    'properti' => 'pesan',
]));

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

foreach (array_filter(([
    'pesan',
    'judul' => 'Berhasil!',
    'properti' => 'pesan',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pesan): ?>
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="alert" aria-live="assertive">
        <button type="button" wire:click="$set('<?php echo e($properti); ?>', null)"
            class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm cursor-default" tabindex="-1" aria-label="Tutup"></button>

        <div x-data="{ tampil: false }" x-init="$nextTick(() => tampil = true)" x-show="tampil"
            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            class="relative w-full max-w-sm bg-white dark:bg-gray-800 rounded-2xl shadow-2xl ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">

            <div class="h-1.5 bg-gradient-to-r from-teal-500 via-emerald-500 to-green-500"></div>

            <div class="px-6 py-7 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>

                <h3 class="mt-4 text-lg font-extrabold text-gray-900 dark:text-gray-100"><?php echo e($judul); ?></h3>
                <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-300"><?php echo e($pesan); ?></p>

                <button type="button" wire:click="$set('<?php echo e($properti); ?>', null)"
                    class="mt-6 w-full inline-flex items-center justify-center rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-500 transition">
                    Mengerti
                </button>
            </div>
        </div>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\Ngekos.in\resources\views\components\notifikasi-popup.blade.php ENDPATH**/ ?>