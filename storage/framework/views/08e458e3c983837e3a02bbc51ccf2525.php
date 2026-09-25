<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['ads' => [], 'variant' => 'default']));

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

foreach (array_filter((['ads' => [], 'variant' => 'default']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $defaultAds = [
        [
            'brand' => 'Biznet',
            'tagline' => 'Internet Cepat & Stabil',
            'desc' => 'Pasang Biznet Home, kuliah online dan streaming di kos makin lancar.',
            'gradient' => 'from-blue-700 via-sky-600 to-sky-500',
            'accent' => 'text-sky-200',
            'icon' => 'wifi',
        ],
        [
            'brand' => 'Shopee',
            'tagline' => 'Belanja Online Murah',
            'desc' => 'Voucher gratis ongkir dan cashback untuk kebutuhan kos kamu.',
            'gradient' => 'from-orange-600 via-orange-500 to-amber-400',
            'accent' => 'text-amber-200',
            'icon' => 'bag',
        ],
        [
            'brand' => 'GoFood',
            'tagline' => 'Lapar di Kos?',
            'desc' => 'Pesan makanan favorit, GoFood antar sampai depan kosmu.',
            'gradient' => 'from-green-600 via-emerald-500 to-teal-500',
            'accent' => 'text-emerald-200',
            'icon' => 'scooter',
        ],
        [
            'brand' => 'DANA',
            'tagline' => 'Bayar Praktis',
            'desc' => 'Top up DANA untuk bayar tagihan kos dan jajan harian.',
            'gradient' => 'from-sky-600 via-blue-500 to-indigo-500',
            'accent' => 'text-blue-200',
            'icon' => 'wallet',
        ],
        [
            'brand' => 'IndiHome',
            'tagline' => 'Internet + TV di Kos',
            'desc' => 'Pasang IndiHome, nonton dan internetan bareng teman kos.',
            'gradient' => 'from-red-700 via-rose-600 to-pink-500',
            'accent' => 'text-rose-200',
            'icon' => 'tv',
        ],
        [
            'brand' => 'IKEA',
            'tagline' => 'Furnitur Kamar Kos',
            'desc' => 'Perabot IKEA harga bersahabat, kamar kos makin nyaman.',
            'gradient' => 'from-blue-800 via-blue-600 to-cyan-500',
            'accent' => 'text-cyan-200',
            'icon' => 'chair',
        ],
    ];

    $ads = filled($ads) ? $ads : $defaultAds;
    $total = count($ads);
    $icons = [
        'wifi' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z" />',
        'bag' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />',
        'scooter' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />',
        'wallet' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3" />',
        'tv' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 20.25h12m-7.5-3v3m3-3v3m-10.125-3h17.25c.621 0 1.125-.504 1.125-1.125V4.875c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125z" />',
        'chair' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75v-1.5A2.25 2.25 0 015.25 6h13.5A2.25 2.25 0 0121 8.25v1.5M3 9.75a.75.75 0 01.75.75v3a.75.75 0 00.75.75h15a.75.75 0 00.75-.75v-3a.75.75 0 01.75-.75M3 9.75L2.25 21M21 9.75l.75 11.25" />',
    ];
?>

<div class="relative group/promo" data-promo role="region" aria-roledescription="carousel" aria-label="Iklan partner" tabindex="0">
    <div class="relative overflow-hidden <?php echo e($variant === 'hero' ? '' : 'rounded-3xl shadow-xl shadow-teal-950/10 ring-1 ring-white/15'); ?>">
        <div class="promo-track" data-promo-track>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $ads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="promo-slide <?php echo e($loop->first ? 'promo-active' : ''); ?> relative overflow-hidden bg-gradient-to-tr <?php echo e($ad['gradient']); ?> <?php echo e($variant === 'hero' ? 'px-6 py-4 sm:px-9 sm:py-5 min-h-[220px] sm:min-h-[300px] flex items-center' : 'px-6 py-6 sm:px-9 sm:py-7'); ?>"
                    data-promo-slide aria-hidden="<?php echo e($loop->first ? 'false' : 'true'); ?>">
                    <div class="absolute inset-0"
                         style="background-image:url('data:image/svg+xml,%3Csvg width%3D%2240%22 height%3D%2240%22 viewBox%3D%220%200%2040%2040%22 xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Ccircle cx%3D%222%22 cy%3D%222%22 r%3D%221.2%22 fill%3D%22white%22 fill-opacity%3D%220.09%22%2F%3E%3C%2Fsvg%3E')"></div>
                    <div class="absolute -right-12 -top-14 h-44 w-44 rounded-full bg-white/25 blur-xl sm:blur-3xl"></div>
                    <div class="absolute -left-10 bottom-0 h-28 w-40 rounded-full bg-white/10 blur-xl sm:blur-3xl"></div>
                    <div class="absolute top-6 right-1/4 h-8 w-8 rounded-full border border-white/25"></div>
                    <div class="absolute -bottom-6 right-1/3 h-12 w-12 rounded-full border border-white/20"></div>

                    <div class="relative flex items-center justify-between gap-4 w-full <?php echo e($variant === 'hero' ? 'max-w-7xl mx-auto' : ''); ?>">
                        <div class="min-w-0 max-w-xl" data-promo-anim>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white ring-1 ring-white/25 backdrop-blur">
                                <svg class="h-3 w-3 <?php echo e($ad['accent']); ?>" fill="currentColor" viewBox="0 0 24 24"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" /></svg>
                                Iklan Partner
                            </span>
                            <p class="mt-2 <?php echo e($variant === 'hero' ? 'text-xl sm:text-2xl' : 'text-xl sm:text-2xl'); ?> font-extrabold tracking-tight text-white drop-shadow-sm"><?php echo e($ad['brand']); ?></p>
                            <p class="mt-0.5 <?php echo e($variant === 'hero' ? 'text-sm sm:text-base' : 'text-sm'); ?> font-bold text-white/95"><?php echo e($ad['tagline']); ?></p>
                            <p class="mt-1 <?php echo e($variant === 'hero' ? 'text-xs sm:text-sm' : 'text-xs'); ?> leading-snug text-white/80 line-clamp-2 max-w-md"><?php echo e($ad['desc']); ?></p>
                        </div>
                        <div class="shrink-0 relative" data-promo-anim>
                            <div class="flex items-center justify-center <?php echo e($variant === 'hero' ? 'h-12 w-12 sm:h-14 sm:w-14' : 'h-12 w-12 sm:h-14 sm:w-14'); ?> rounded-2xl border border-white/30 bg-white/20 shadow-lg shadow-black/10 backdrop-blur">
                                <svg class="h-6 w-6 sm:h-7 sm:w-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><?php echo $icons[$ad['icon']] ?? $icons['wifi']; ?></svg>
                            </div>
                            <div class="absolute -inset-1 -z-10 rounded-2xl bg-white/25 blur-md"></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($total > 1): ?>
        <button type="button" data-promo-prev aria-label="Promo sebelumnya"
            class="absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 flex h-9 w-9 items-center justify-center rounded-full bg-black/25 text-white backdrop-blur transition hover:bg-black/45 active:scale-95 sm:opacity-0 sm:group-hover/promo:opacity-100">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
        </button>
        <button type="button" data-promo-next aria-label="Promo berikutnya"
            class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 flex h-9 w-9 items-center justify-center rounded-full bg-black/25 text-white backdrop-blur transition hover:bg-black/45 active:scale-95 sm:opacity-0 sm:group-hover/promo:opacity-100">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
        </button>

        <div class="absolute bottom-3 inset-x-0 flex items-center justify-center gap-1.5" data-promo-dots></div>

        <span class="absolute top-3 right-3 sm:right-4 rounded-full bg-black/25 px-2 py-0.5 text-[10px] font-bold text-white backdrop-blur" data-promo-count>1 / <?php echo e($total); ?></span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\Ngekos.in\resources\views\components\promo-ads.blade.php ENDPATH**/ ?>