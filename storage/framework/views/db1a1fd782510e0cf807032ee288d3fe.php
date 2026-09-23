<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'plan' => 'free',
    'propertyUsed' => 0,
    'propertyLimit' => null,
    'roomUsed' => 0,
    'roomLimit' => null,
    'expiresAt' => null,
    'status' => null,
    'sisaTrial' => null,
    'trialHabis' => null,
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
    'plan' => 'free',
    'propertyUsed' => 0,
    'propertyLimit' => null,
    'roomUsed' => 0,
    'roomLimit' => null,
    'expiresAt' => null,
    'status' => null,
    'sisaTrial' => null,
    'trialHabis' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    use App\Services\SubscriptionService;

    $plan = strtolower($plan ?? 'free');
    if (! in_array($plan, ['free', 'pro', 'business'], true)) {
        $plan = 'free';
    }

    $plans = config('plans', []);
    $planName = strtoupper(data_get($plans, "{$plan}.name", $plan));

    $user = auth()->user();
    if ($status === null && $user) {
        $status = SubscriptionService::getSubscription($user)?->status;
    }
    if ($sisaTrial === null && $user) {
        $sisaTrial = SubscriptionService::sisaTrialHari($user);
    }
    if ($trialHabis === null && $user) {
        $trialHabis = SubscriptionService::trialExpired($user);
    }

    $isFree = $plan === 'free';
    $isPro = $plan === 'pro';
    $isBusiness = $plan === 'business';
    $isExpired = in_array($status, ['expired', 'cancelled'], true);

    $deskripsi = match ($plan) {
        'pro' => 'Fitur lengkap untuk mengembangkan bisnis kos Anda.',
        'business' => 'Solusi maksimal untuk skala bisnis kos Anda.',
        default => 'Nikmati fitur dasar untuk mengelola kos Anda dengan mudah.',
    };

    $badgeLabel = $isExpired ? 'Expired' : match ($plan) {
        'pro' => 'PRO Aktif',
        'business' => 'BUSINESS Aktif',
        default => 'Gratis',
    };

    $featureLabels = [
        'basic_dashboard' => 'Dashboard dasar',
        'basic_property' => 'Kelola 1 properti',
        'basic_room' => 'Kelola kamar',
        'basic_tenant' => 'Kelola penyewa',
        'basic_billing' => 'Tagihan bulanan',
        'basic_report' => 'Laporan dasar',
        'export_pdf_basic' => 'Unduh PDF dasar',
        'advanced_analytics' => 'Advanced Analytics',
        'advanced_report' => 'Laporan Premium',
        'export_report' => 'Export PDF/Excel',
        'automatic_invoice' => 'Invoice otomatis',
        'automatic_fine' => 'Denda otomatis',
        'broadcast' => 'Broadcast pengumuman',
        'maintenance' => 'Manajemen perawatan',
        'multi_property' => 'Multi properti',
        'multi_user' => 'Multi pengguna',
        'unlimited_property' => 'Properti tanpa batas',
        'unlimited_room' => 'Kamar tanpa batas',
        'laporan_24_bulan' => 'Laporan 24 bulan',
        'excel_7_sheet' => 'Excel 7 sheet lengkap',
    ];

    $planFeatures = data_get($plans, "{$plan}.features", []);
    $fitur = collect($planFeatures)->map(fn ($f) => $featureLabels[$f] ?? ucfirst(str_replace('_', ' ', $f)))->take(4)->values()->all();

    $ctaPrimerLabel = $isFree ? 'Upgrade ke Pro' : 'Kelola Paket';
    $ctaPrimerRoute = $isFree ? route('langganan.plans') : route('langganan.subscription');

    $propText = $propertyLimit === null ? $propertyUsed.' / Unlimited' : $propertyUsed.' / '.$propertyLimit;
    $roomText = $roomLimit === null ? $roomUsed.' / Unlimited' : $roomUsed.' / '.$roomLimit;
?>

<div class="group relative overflow-hidden rounded-[28px] border border-slate-100 bg-white shadow-[0_10px_40px_rgba(13,148,136,0.08)] transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_16px_48px_rgba(13,148,136,0.14)] dark:border-slate-700/60 dark:bg-slate-900">
    
    <div class="pointer-events-none absolute -right-24 -top-28 h-72 w-72 rounded-full bg-teal-100/50 blur-3xl dark:bg-teal-500/10" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -bottom-28 right-1/3 h-56 w-56 rounded-full bg-cyan-100/50 blur-3xl dark:bg-cyan-500/10" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -left-20 top-1/3 h-48 w-48 rounded-full bg-sky-100/40 blur-3xl dark:bg-sky-500/10" aria-hidden="true"></div>

    
    <span class="absolute right-5 top-5 z-10 inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-bold
        <?php echo e($isExpired
            ? 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300'
            : 'border-emerald-200/70 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300'); ?>">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isExpired): ?>
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
        <?php elseif($isPro || $isBusiness): ?>
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
        <?php else: ?>
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H4.5a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php echo e($badgeLabel); ?>

    </span>

    <div class="relative grid grid-cols-1 gap-6 p-6 sm:p-7 lg:grid-cols-12 lg:items-center lg:gap-6">
        
        <div class="min-w-0 lg:col-span-5">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-teal-700 ring-1 ring-teal-100 dark:bg-teal-500/10 dark:text-teal-300 dark:ring-teal-500/30">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H4.5a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                Paket Anda
            </span>

            <h2 class="mt-3 text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white"><?php echo e($planName); ?></h2>
            <p class="mt-1.5 max-w-md text-sm leading-relaxed text-slate-500 dark:text-slate-400"><?php echo e($deskripsi); ?></p>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isFree && $sisaTrial !== null): ?>
                <p class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-teal-50 px-2.5 py-1 text-xs font-semibold text-teal-700 ring-1 ring-teal-100 dark:bg-teal-500/10 dark:text-teal-300 dark:ring-teal-500/30">
                    Sisa masa coba <?php echo e($sisaTrial); ?> hari
                </p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isExpired): ?>
                <p class="mt-2 max-w-md text-xs leading-relaxed text-amber-600 dark:text-amber-400">Langganan berakhir<?php echo e($expiresAt ? ' pada '.$expiresAt : ''); ?>. Data tetap aman — upgrade untuk membuka kembali fitur premium.</p>
            <?php elseif($expiresAt && ! $isFree): ?>
                <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">Aktif sampai <?php echo e($expiresAt); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <div class="mt-5 flex items-center gap-6">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl text-blue-600 dark:text-blue-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                    </span>
                    <span>
                        <span class="block text-xs font-medium text-slate-400 dark:text-slate-500">Property</span>
                        <span class="block text-sm font-extrabold tabular-nums text-slate-900 dark:text-white"><?php echo e($propText); ?></span>
                    </span>
                </div>
                <span class="h-10 w-px bg-slate-200 dark:bg-slate-700" aria-hidden="true"></span>
                <div class="flex items-center gap-2.5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl text-blue-600 dark:text-blue-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12h5.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H2.25A2.25 2.25 0 010 18v-3.75A2.25 2.25 0 012.25 12zM21.75 12H18a2.25 2.25 0 00-2.25 2.25V18a2.25 2.25 0 002.25 2.25h3.75A2.25 2.25 0 0024 18v-3.75a2.25 2.25 0 00-2.25-2.25zM8.25 12h7.5M8.25 12V5.625c0-.621.504-1.125 1.125-1.125H18a2.25 2.25 0 012.25 2.25V12" /></svg>
                    </span>
                    <span>
                        <span class="block text-xs font-medium text-slate-400 dark:text-slate-500">Kamar</span>
                        <span class="block text-sm font-extrabold tabular-nums text-slate-900 dark:text-white"><?php echo e($roomText); ?></span>
                    </span>
                </div>
            </div>

            
            <div class="mt-6 flex flex-wrap items-center gap-3">
                <a href="<?php echo e(route('langganan.subscription')); ?>" wire:navigate
                    class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700">
                    Detail Langganan
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                </a>
                <a href="<?php echo e($ctaPrimerRoute); ?>" wire:navigate
                    class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-orange-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-xl hover:brightness-105">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8.25l3.5 7 3.5-4.5L13.5 15l2-3.5L21 18M4 4h16a1 1 0 011 1v4H3V5a1 1 0 011-1zm-1 6h18v3a4 4 0 01-4 4H6a4 4 0 01-4-4v-3z" /></svg>
                    <?php echo e($ctaPrimerLabel); ?>

                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                </a>
            </div>
        </div>

        
        <div class="min-w-0 md:col-span-6 lg:col-span-4">
            <div class="h-full rounded-2xl border border-teal-100/70 bg-cyan-50/60 p-5 transition-colors duration-200 hover:bg-cyan-50 dark:border-teal-500/20 dark:bg-teal-500/5">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/80 px-2.5 py-1 text-xs font-bold text-teal-700 ring-1 ring-teal-100 dark:bg-teal-500/10 dark:text-teal-300 dark:ring-teal-500/30">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" /></svg>
                    Fitur Paket <?php echo e($planName); ?>

                </span>
                <ul class="mt-4 space-y-3" role="list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $fitur; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="flex items-center gap-2.5 text-sm font-medium text-slate-600 dark:text-slate-300">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white" aria-hidden="true">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            </span>
                            <?php echo e($f); ?>

                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ul>
                <a href="<?php echo e(route('langganan.plans')); ?>" wire:navigate class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-teal-700 transition-all hover:gap-2 dark:text-teal-400" aria-label="Lihat semua fitur paket <?php echo e($planName); ?>">
                    Lihat semua fitur
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                </a>
            </div>
        </div>

        
        <div class="relative flex items-center justify-center md:col-span-6 lg:col-span-3">
            <div class="relative flex w-full max-w-[250px] items-center justify-center">
                <div class="absolute h-44 w-44 rounded-full bg-sky-100/70 blur-2xl dark:bg-sky-500/10" aria-hidden="true"></div>
                <div class="absolute -right-2 top-0 h-16 w-16 rounded-full bg-cyan-100/70 blur-xl dark:bg-cyan-500/10" aria-hidden="true"></div>
                <img src="<?php echo e(asset('images/login-owner.svg')); ?>" alt="Ilustrasi properti kos" width="220" height="165"
                    class="relative h-auto w-full opacity-95 drop-shadow-[0_10px_24px_rgba(13,148,136,0.15)] transition-transform duration-300 group-hover:scale-[1.02]">
                
                <div class="absolute -right-1 bottom-3 w-[104px] rounded-2xl border border-slate-100 bg-white/95 p-2.5 shadow-[0_10px_28px_rgba(15,23,42,0.14)] backdrop-blur dark:border-slate-700 dark:bg-slate-800/95" aria-hidden="true">
                    <svg viewBox="0 0 80 32" class="h-7 w-full" fill="none">
                        <path d="M4 26 L22 18 L34 21 L48 10 L62 13 L76 4" stroke="#10B981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="76" cy="4" r="3" fill="#10B981" />
                    </svg>
                    <p class="mt-1 text-center text-[10px] font-extrabold uppercase tracking-wide text-emerald-600 dark:text-emerald-400">+ Growth</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\laragon\www\Ngekos.in\resources\views/components/subscription-card.blade.php ENDPATH**/ ?>