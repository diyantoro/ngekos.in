<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['fotos' => [], 'nama' => 'Foto kos', 'kelas' => 'h-56 sm:h-72', 'ringkas' => false]));

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

foreach (array_filter((['fotos' => [], 'nama' => 'Foto kos', 'kelas' => 'h-56 sm:h-72', 'ringkas' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $daftar = collect($fotos)->filter()->values()->all();
?>

<div x-data="{ aktif: 0, total: <?php echo e(count($daftar)); ?>, ticking: false }" class="relative <?php echo e($kelas); ?> bg-gradient-to-br from-teal-100 via-emerald-100 to-cyan-100 dark:from-teal-500/20 dark:via-emerald-500/20 dark:to-cyan-500/20 overflow-hidden group">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($daftar) > 0): ?>
        <div class="flex h-full w-full overflow-x-auto snap-x snap-mandatory scrollbar-hide overscroll-x-contain scroll-smooth"
            x-ref="track"
            @scroll="if (!ticking) { ticking = true; requestAnimationFrame(() => { aktif = Math.round($el.scrollLeft / $el.clientWidth); ticking = false; }); }"
            @scrollend="aktif = Math.round($el.scrollLeft / $el.clientWidth)">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $daftar; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $foto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="h-full w-full shrink-0 snap-center">
                    <img src="<?php echo e($foto); ?>" alt="<?php echo e($nama); ?>" class="h-full w-full object-cover" loading="lazy" decoding="async">
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($daftar) > 1): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $ringkas): ?>
            <button type="button" @click="$refs.track.scrollBy({ left: -$refs.track.clientWidth, behavior: 'smooth' })"
                class="absolute left-2 top-1/2 -translate-y-1/2 h-8 w-8 rounded-full bg-black/40 text-white backdrop-blur-sm hover:bg-black/60 transition flex items-center justify-center sm:opacity-0 sm:group-hover:opacity-100" aria-label="Sebelumnya">
                <svg class="h-4 w-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </button>
            <button type="button" @click="$refs.track.scrollBy({ left: $refs.track.clientWidth, behavior: 'smooth' })"
                class="absolute right-2 top-1/2 -translate-y-1/2 h-8 w-8 rounded-full bg-black/40 text-white backdrop-blur-sm hover:bg-black/60 transition flex items-center justify-center sm:opacity-0 sm:group-hover:opacity-100" aria-label="Berikutnya">
                <svg class="h-4 w-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
            </button>

            <div class="absolute bottom-2 inset-x-0 flex items-center justify-center gap-1.5">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $daftar; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $foto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button type="button" @click="$refs.track.scrollTo({ left: <?php echo e($i); ?> * $refs.track.clientWidth, behavior: 'smooth' })"
                        :class="aktif === <?php echo e($i); ?> ? 'w-5 bg-white' : 'w-1.5 bg-white/60'"
                        class="h-1.5 rounded-full transition-all" aria-label="Foto <?php echo e($i + 1); ?>"></button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <span class="absolute top-2 right-2 rounded-full bg-black/50 px-2 py-0.5 text-[10px] font-bold text-white backdrop-blur-sm">
                <span x-text="aktif + 1"></span>/<?php echo e(count($daftar)); ?>

            </span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php else: ?>
        <div class="h-full w-full flex items-center justify-center">
            <svg class="h-20 w-20 text-teal-300 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php echo e($slot); ?>

</div>
<?php /**PATH C:\laragon\www\Ngekos.in\resources\views\components\galeri-kos.blade.php ENDPATH**/ ?>