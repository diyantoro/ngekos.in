<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['properti']));

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

foreach (array_filter((['properti']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $p = $properti;
    $hargaTampil = $p->harga ?? $p->harga_termurah;
    $selisih = ($p->harga_asli && $hargaTampil && $p->harga_asli > $hargaTampil) ? $p->harga_asli - $hargaTampil : 0;
    $labelDiskon = $selisih >= 1000 ? 'Diskon '.rtrim(rtrim(number_format($selisih / 1000, 1, ',', '.'), '0'), ',').'rb' : null;
    $fasilitas = collect(explode(',', (string) ($p->fasilitas ?? '')))->map(fn ($f) => trim($f))->filter()->take(6);
    $rating = (float) ($p->rating_ulasan ?? 0);
    $totalUlasan = (int) ($p->total_ulasan ?? 0);
    $tipe = strtolower((string) ($p->tipe_hunian ?? 'campur'));
    $warnaTipe = $tipe === 'putri' ? 'bg-pink-600' : ($tipe === 'putra' ? 'bg-sky-600' : 'bg-emerald-600');
    $labelTipe = $tipe === 'putri' ? 'Putri' : ($tipe === 'putra' ? 'Putra' : 'Campur');
    $sisa = (int) ($p->kamar_tersedia ?? 0);
?>

<a href="<?php echo e(route('kos.detail', $p)); ?>" wire:navigate <?php echo e($attributes->merge(['class' => 'group bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 rounded-xl overflow-hidden hover:shadow-card-hover hover:border-brand-200 transition'])); ?>>
    <div class="relative h-36 bg-stone-200 dark:bg-gray-700 overflow-hidden">
        <?php $cover = $p->fotoCover(); ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cover): ?>
            <img src="<?php echo e($cover); ?>" alt="<?php echo e($p->nama); ?>" loading="lazy" decoding="async" class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
        <?php else: ?>
            <div class="h-full w-full flex items-center justify-center">
                <svg class="h-10 w-10 text-stone-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sisa > 0 && $sisa <= 3): ?>
            <span class="absolute top-2 right-2 inline-flex items-center rounded-full bg-red-600 px-2 py-0.5 text-[10px] font-bold text-white shadow-md shadow-red-600/40">
                Sisa <?php echo e($sisa); ?> kamar
            </span>
        <?php elseif($sisa > 3): ?>
            <span class="absolute top-2 right-2 inline-flex items-center rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-bold text-white shadow-md shadow-emerald-600/40">
                <?php echo e($sisa); ?> Kamar
            </span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <div class="p-3">
        <div class="flex items-center gap-1.5">
            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-bold text-white <?php echo e($warnaTipe); ?>"><?php echo e($labelTipe); ?></span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalUlasan > 0): ?>
                <span class="inline-flex items-center gap-0.5 text-[11px] font-semibold text-slate-700 dark:text-gray-200">
                    <svg class="h-3 w-3 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 1.5l2.6 5.3 5.9.9-4.2 4.1 1 5.8L10 14.9l-5.3 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z" /></svg>
                    <?php echo e(number_format($rating, 1, ',', '.')); ?>

                </span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <h3 class="mt-1.5 text-sm font-bold text-slate-900 dark:text-gray-100 truncate group-hover:text-brand-800 dark:group-hover:text-brand-200 transition"><?php echo e($p->nama); ?></h3>
        <p class="mt-0.5 text-xs text-slate-500 dark:text-gray-400 truncate"><?php echo e($p->kota ?? $p->alamat ?? 'Lokasi belum diisi'); ?></p>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($fasilitas->isNotEmpty()): ?>
            <p class="mt-1 text-[11px] text-slate-500 dark:text-gray-400 truncate"><?php echo e($fasilitas->join('·')); ?></p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="mt-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($labelDiskon): ?>
                <p class="text-[11px] font-bold text-green-600 dark:text-emerald-400"><?php echo e($labelDiskon); ?></p>
                <p class="text-xs text-slate-400 dark:text-gray-500 line-through">Rp<?php echo e(number_format($p->harga_asli, 0, ',', '.')); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hargaTampil): ?>
                <p class="text-base font-extrabold text-brand-700 dark:text-brand-200">Rp<?php echo e(number_format($hargaTampil, 0, ',', '.')); ?></p>
                <p class="text-[10px] text-slate-400 dark:text-gray-500"><?php echo e($labelDiskon ? '(Bulan pertama)' : '/bulan'); ?></p>
            <?php else: ?>
                <p class="text-xs font-medium text-slate-400">Penuh</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</a>
<?php /**PATH C:\laragon\www\Ngekos.in\resources\views\components\kartu-kos.blade.php ENDPATH**/ ?>