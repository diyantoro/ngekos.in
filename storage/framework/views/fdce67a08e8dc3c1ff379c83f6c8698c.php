<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        <meta name="theme-color" content="#0f5450">
        <meta name="gmaps-key" content="<?php echo e(config('services.google_maps.key')); ?>">

        <title><?php echo e(\App\Models\Pengaturan::namaSitus()); ?></title>

        <link rel="icon" href="<?php echo e(asset('favicon.svg')); ?>" type="image/svg+xml">
        <link rel="icon" href="<?php echo e(asset('favicon.ico')); ?>" sizes="48x48">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        <script>
            (function () {
                var t = localStorage.getItem('theme');
                var gelap = t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', gelap);
            })();
            window.toggleNgekosTheme = window.toggleNgekosTheme || function () {
                var gelap = !document.documentElement.classList.contains('dark');
                document.documentElement.classList.toggle('dark', gelap);
                try { localStorage.setItem('theme', gelap ? 'dark' : 'light'); } catch (e) {}
                var meta = document.querySelector('meta[name="theme-color"]');
                if (meta) meta.setAttribute('content', gelap ? '#0f172a' : '#0d9488');
                try { if (window.Alpine && Alpine.store && Alpine.store('theme')) Alpine.store('theme').dark = gelap; } catch (e) {}
                window.dispatchEvent(new CustomEvent('ngekos:theme-changed', { detail: { dark: gelap } }));
            };
        </script>
        <script>
            (function () {
                try {
                    if (localStorage.getItem('ngekos:sidebar') === 'collapsed') document.documentElement.classList.add('sidebar-collapsed');
                } catch (e) {}
                window.toggleNgekosSidebar = window.toggleNgekosSidebar || function () {
                    var c = document.documentElement.classList.toggle('sidebar-collapsed');
                    try { localStorage.setItem('ngekos:sidebar', c ? 'collapsed' : 'open'); } catch (e) {}
                };
            })();
        </script>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

        <style>
            .pb-safe { padding-bottom: env(safe-area-inset-bottom, 0px); }
            .app-sidebar, .app-shell { transition: width .25s ease, padding .25s ease; }
            html.sidebar-collapsed .icon-collapse { display: none; }
            html:not(.sidebar-collapsed) .icon-expand { display: none; }
            @media (min-width: 1024px) {
                html.sidebar-collapsed .app-shell { padding-left: 5.5rem; }
                html.sidebar-collapsed .app-sidebar { width: 5.5rem; }
                html.sidebar-collapsed .app-sidebar .sidebar-label,
                html.sidebar-collapsed .app-sidebar .sidebar-badge,
                html.sidebar-collapsed .app-sidebar .sidebar-group,
                html.sidebar-collapsed .app-sidebar .sidebar-paket { display: none; }
                html.sidebar-collapsed .app-sidebar .sidebar-head,
                html.sidebar-collapsed .app-sidebar .sidebar-linkrow,
                html.sidebar-collapsed .app-sidebar .sidebar-profile { justify-content: center; padding-left: .5rem; padding-right: .5rem; }
                html.sidebar-collapsed .app-sidebar .sidebar-head { gap: .375rem; }
                html.sidebar-collapsed .app-sidebar .sidebar-head button { margin-left: 0; height: 1.75rem; width: 1.75rem; }
                html.sidebar-collapsed .app-sidebar .profile-pop {
                    left: calc(100% + .75rem) !important;
                    right: auto !important;
                    width: 13rem;
                    bottom: 0 !important;
                }
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-stone-100 text-slate-800 dark:bg-gray-950 dark:text-gray-100">
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('layout.navigation', []);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-920770237-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

        <div class="app-shell min-h-screen pt-14 lg:pl-64 lg:pt-0">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($header)): ?>
                <header class="bg-white dark:bg-gray-900 border-b border-stone-200 dark:border-gray-800">
                    <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                        <?php echo e($header); ?>

                    </div>
                </header>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <main class="pb-20 lg:pb-0">
                <?php echo e($slot); ?>

            </main>

            <!-- Bottom Navigation (Mobile) -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                <?php if (isset($component)) { $__componentOriginal91530f48093dbfe9d7d6cfefc4ce84c1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91530f48093dbfe9d7d6cfefc4ce84c1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.bottom-nav','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('bottom-nav'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91530f48093dbfe9d7d6cfefc4ce84c1)): ?>
<?php $attributes = $__attributesOriginal91530f48093dbfe9d7d6cfefc4ce84c1; ?>
<?php unset($__attributesOriginal91530f48093dbfe9d7d6cfefc4ce84c1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91530f48093dbfe9d7d6cfefc4ce84c1)): ?>
<?php $component = $__componentOriginal91530f48093dbfe9d7d6cfefc4ce84c1; ?>
<?php unset($__componentOriginal91530f48093dbfe9d7d6cfefc4ce84c1); ?>
<?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <?php echo $__env->yieldPushContent('scripts'); ?>
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('chatbot', []);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-920770237-1', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
    </body>
</html>
<?php /**PATH C:\laragon\www\Ngekos.in\resources\views\layouts\app.blade.php ENDPATH**/ ?>