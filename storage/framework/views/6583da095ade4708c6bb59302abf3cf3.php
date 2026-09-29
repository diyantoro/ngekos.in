<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        <meta name="theme-color" content="#0f5450">
        <meta name="gmaps-key" content="<?php echo e(config('services.google_maps.key')); ?>">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

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
            // Fallback darurat: toggle tema tetap jalan walau bundle Vite gagal dimuat.
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
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

        <style>
            .pb-safe { padding-bottom: env(safe-area-inset-bottom, 0px); }
            .pt-safe { padding-top: env(safe-area-inset-top, 0px); }
        </style>
    </head>
    <body class="font-sans text-slate-800 antialiased bg-paper dark:bg-gray-950 dark:text-gray-100">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
            
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('layout.navigation', []);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1231132325-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            <div class="min-h-screen pt-14 lg:pl-64 lg:pt-0">
                <main class="pb-20 lg:pb-0">
                    <?php echo e($slot); ?>

                </main>
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
            </div>
        <?php else: ?>
        <div class="min-h-screen flex flex-col">

            <!-- Top Header (Desktop + Mobile simplified) -->
            <header x-data="{ open: false }" class="sticky top-0 z-40 bg-white dark:bg-gray-900 border-b border-stone-200 dark:border-gray-800 pt-safe">
                <div class="max-w-7xl mx-auto px-4 sm:px-6">
                    <div class="flex items-center justify-between h-16">
                        <a href="<?php echo e(route('home')); ?>" wire:navigate class="flex items-center gap-2.5 shrink-0 group">
                            <?php if (isset($component)) { $__componentOriginal8892e718f3d0d7a916180885c6f012e7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8892e718f3d0d7a916180885c6f012e7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.application-logo','data' => ['class' => 'h-8 w-8']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('application-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-8 w-8']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $attributes = $__attributesOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $component = $__componentOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__componentOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
                            <span class="text-lg font-bold tracking-tight text-slate-900 dark:text-gray-100">Ngekos<span class="text-brand-700 dark:text-brand-300">.in</span></span>
                        </a>

                        <!-- Desktop nav -->
                        <nav class="hidden sm:flex items-center gap-1">
                            <a href="<?php echo e(route('kos.index')); ?>" wire:navigate
                               class="px-3 py-2 text-sm font-medium rounded-lg transition <?php echo e(request()->routeIs('kos.*') ? 'text-brand-800 bg-brand-50 dark:bg-brand-500/10 dark:text-brand-200' : 'text-slate-600 dark:text-gray-300 hover:text-slate-900 dark:hover:text-white hover:bg-stone-100 dark:hover:bg-gray-800'); ?>">
                                Cari Kos
                            </a>
                            <a href="<?php echo e(route('bantuan')); ?>" wire:navigate
                               class="px-3 py-2 text-sm font-medium rounded-lg transition <?php echo e(request()->routeIs('bantuan') ? 'text-brand-800 bg-brand-50 dark:bg-brand-500/10 dark:text-brand-200' : 'text-slate-600 dark:text-gray-300 hover:text-slate-900 dark:hover:text-white hover:bg-stone-100 dark:hover:bg-gray-800'); ?>">
                                Bantuan
                            </a>
                            <a href="<?php echo e(route('register')); ?>" wire:navigate
                               class="px-3 py-2 text-sm font-medium rounded-lg transition text-slate-600 dark:text-gray-300 hover:text-slate-900 dark:hover:text-white hover:bg-stone-100 dark:hover:bg-gray-800">
                                Promosikan Kos
                            </a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasAnyRole(['anak_kos', 'pemilik'])): ?>
                                <?php $unread = auth()->user()->pesanBelumDibaca(); ?>
                                <a href="<?php echo e(route('chat.index')); ?>" wire:navigate
                                   class="relative px-3 py-2 text-sm font-medium rounded-lg transition <?php echo e(request()->routeIs('chat.*') ? 'text-brand-800 bg-brand-50 dark:bg-brand-500/10 dark:text-brand-200' : 'text-slate-600 dark:text-gray-300 hover:text-slate-900 dark:hover:text-white hover:bg-stone-100 dark:hover:bg-gray-800'); ?>">
                                    Pesan
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unread > 0): ?>
                                        <span class="absolute -top-0.5 -right-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white">
                                            <?php echo e($unread > 9 ? '9+' : $unread); ?>

                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('pemilik')): ?>
                                    <a href="<?php echo e(route('pemilik.properti')); ?>" wire:navigate
                                       class="px-3 py-2 text-sm font-medium rounded-lg transition <?php echo e(request()->routeIs('pemilik.properti*') ? 'text-brand-800 bg-brand-50 dark:bg-brand-500/10 dark:text-brand-200' : 'text-slate-600 dark:text-gray-300 hover:text-slate-900 dark:hover:text-white hover:bg-stone-100 dark:hover:bg-gray-800'); ?>">
                                        Kelola Kos
                                    </a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <div class="ms-2 flex items-center gap-2">
                                    <a href="<?php echo e(route('dashboard')); ?>" wire:navigate
                                       class="btn-primary !px-4 !py-2 !text-sm">
                                        Dashboard
                                    </a>
                                    <?php if (isset($component)) { $__componentOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.user-avatar','data' => ['size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('user-avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'sm']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e)): ?>
<?php $attributes = $__attributesOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e; ?>
<?php unset($__attributesOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e)): ?>
<?php $component = $__componentOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e; ?>
<?php unset($__componentOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e); ?>
<?php endif; ?>
                                </div>
                            <?php else: ?>
                                <a href="<?php echo e(route('login')); ?>" wire:navigate
                                   class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-gray-300 hover:text-slate-900 dark:hover:text-white rounded-lg hover:bg-stone-100 dark:hover:bg-gray-800 transition">
                                    Masuk
                                </a>
                                <a href="<?php echo e(route('register')); ?>" wire:navigate
                                   class="btn-primary !px-4 !py-2 !text-sm">
                                    Daftar
                                </a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <button @click="toggleNgekosTheme()" type="button" aria-label="Ganti tema"
                                class="ms-1 inline-flex items-center gap-1.5 rounded-lg border border-stone-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-semibold text-slate-600 dark:text-gray-300 hover:bg-stone-50 dark:hover:bg-gray-700 transition">
                                <svg class="h-4 w-4 dark:hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" /></svg>
                                <svg class="hidden h-4 w-4 text-amber-300 dark:block" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                                <span class="dark:hidden">Gelap</span><span class="hidden dark:inline">Terang</span>
                            </button>
                        </nav>

                        <!-- Mobile hamburger -->
                        <div class="flex items-center sm:hidden gap-1">
                            <button @click="toggleNgekosTheme()" type="button" aria-label="Ganti tema"
                                class="flex items-center justify-center h-10 w-10 rounded-lg text-slate-500 dark:text-gray-300 hover:bg-stone-100 dark:hover:bg-gray-800 transition">
                                <svg class="h-5 w-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" /></svg>
                                <svg class="hidden h-5 w-5 text-amber-500 dark:block" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                            </button>
                            <button type="button" @click="open = !open"
                                class="flex items-center justify-center h-10 w-10 rounded-lg text-slate-500 dark:text-gray-300 hover:bg-stone-100 dark:hover:bg-gray-800 transition">
                                <svg class="h-5 w-5 transition-transform duration-200" :class="open ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path :class="{'hidden': open}" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                    <path :class="{'hidden': !open}" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mobile dropdown -->
                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                     class="sm:hidden border-t border-stone-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                    <div class="px-4 py-3 space-y-1">
                        <a href="<?php echo e(route('kos.index')); ?>" wire:navigate @click="open = false"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition <?php echo e(request()->routeIs('kos.*') ? 'text-brand-800 bg-brand-50 dark:bg-brand-500/10 dark:text-brand-200' : 'text-slate-600 dark:text-gray-300 hover:bg-stone-100 dark:hover:bg-gray-800'); ?>">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                            Cari Kos
                        </a>
                        <a href="<?php echo e(route('bantuan')); ?>" wire:navigate @click="open = false"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition <?php echo e(request()->routeIs('bantuan') ? 'text-brand-800 bg-brand-50 dark:bg-brand-500/10 dark:text-brand-200' : 'text-slate-600 dark:text-gray-300 hover:bg-stone-100 dark:hover:bg-gray-800'); ?>">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" /></svg>
                            Bantuan
                        </a>
                        <a href="<?php echo e(route('register')); ?>" wire:navigate @click="open = false"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition text-slate-600 dark:text-gray-300 hover:bg-stone-100 dark:hover:bg-gray-800">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                            Promosikan Kos
                        </a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasAnyRole(['anak_kos', 'pemilik'])): ?>
                            <a href="<?php echo e(route('chat.index')); ?>" wire:navigate @click="open = false"
                               class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition <?php echo e(request()->routeIs('chat.*') ? 'text-brand-800 bg-brand-50 dark:bg-brand-500/10 dark:text-brand-200' : 'text-slate-600 dark:text-gray-300 hover:bg-stone-100 dark:hover:bg-gray-800'); ?>">
                                <span class="flex items-center gap-3">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                                    Pesan
                                </span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->pesanBelumDibaca() > 0): ?>
                                    <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1.5 text-[10px] font-bold text-white">
                                        <?php echo e(auth()->user()->pesanBelumDibaca() > 9 ? '9+' : auth()->user()->pesanBelumDibaca()); ?>

                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('pemilik')): ?>
                                <a href="<?php echo e(route('pemilik.properti')); ?>" wire:navigate @click="open = false"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition <?php echo e(request()->routeIs('pemilik.*') ? 'text-brand-800 bg-brand-50 dark:bg-brand-500/10 dark:text-brand-200' : 'text-slate-600 dark:text-gray-300 hover:bg-stone-100 dark:hover:bg-gray-800'); ?>">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
                                    Kelola Kos
                                </a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="px-4 pb-4 pt-2 border-t border-stone-200 space-y-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                            <a href="<?php echo e(route('dashboard')); ?>" wire:navigate @click="open = false"
                               class="flex items-center justify-center gap-2 btn-primary w-full">
                                <?php if (isset($component)) { $__componentOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.user-avatar','data' => ['size' => 'xs']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('user-avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'xs']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e)): ?>
<?php $attributes = $__attributesOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e; ?>
<?php unset($__attributesOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e)): ?>
<?php $component = $__componentOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e; ?>
<?php unset($__componentOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e); ?>
<?php endif; ?>
                                Dashboard
                            </a>
                        <?php else: ?>
                            <a href="<?php echo e(route('login')); ?>" wire:navigate @click="open = false"
                               class="flex items-center justify-center btn-secondary w-full">
                                Masuk
                            </a>
                            <a href="<?php echo e(route('register')); ?>" wire:navigate @click="open = false"
                               class="flex items-center justify-center btn-primary w-full">
                                Daftar Sekarang
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1">
                <?php echo e($slot); ?>

            </main>

            <!-- Footer -->
            <footer class="bg-white dark:bg-gray-900 border-t border-stone-200 dark:border-gray-800 mt-10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-8 border-b border-stone-200 dark:border-gray-800">
                        <div class="flex items-center gap-3">
                            <?php if (isset($component)) { $__componentOriginal8892e718f3d0d7a916180885c6f012e7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8892e718f3d0d7a916180885c6f012e7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.application-logo','data' => ['class' => 'h-9 w-9']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('application-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-9 w-9']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $attributes = $__attributesOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $component = $__componentOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__componentOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
                            <div>
                                <p class="text-sm font-bold text-slate-900 dark:text-gray-100">Dapatkan info kos murah hanya di <?php echo e(\App\Models\Pengaturan::namaSitus()); ?>.</p>
                                <p class="text-xs text-slate-500 dark:text-gray-400">Mau sewa kos murah? Cari, chat pemilik, dan bayar dalam satu aplikasi.</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <a href="<?php echo e(route('login')); ?>" wire:navigate class="btn-secondary !text-xs !px-4">Masuk</a>
                            <a href="<?php echo e(route('register')); ?>" wire:navigate class="btn-primary !text-xs !px-4">Daftar Gratis</a>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 py-8">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-gray-500"><?php echo e(\App\Models\Pengaturan::namaSitus()); ?></p>
                            <ul class="mt-3 space-y-2 text-sm">
                                <li><a href="<?php echo e(route('kos.index')); ?>" wire:navigate class="text-slate-600 dark:text-gray-300 hover:text-brand-700">Cari Kos</a></li>
                                <li><a href="<?php echo e(route('register')); ?>" wire:navigate class="text-slate-600 dark:text-gray-300 hover:text-brand-700">Promosikan Kos Anda</a></li>
                                <li><a href="<?php echo e(route('bantuan')); ?>" wire:navigate class="text-slate-600 dark:text-gray-300 hover:text-brand-700">Pusat Bantuan</a></li>
                            </ul>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-gray-500">Jelajah</p>
                            <ul class="mt-3 space-y-2 text-sm">
                                <li><a href="<?php echo e(route('kos.index')); ?>" wire:navigate class="text-slate-600 dark:text-gray-300 hover:text-brand-700">Semua Kos</a></li>
                                <li><a href="<?php echo e(route('login')); ?>" wire:navigate class="text-slate-600 dark:text-gray-300 hover:text-brand-700">Masuk</a></li>
                                <li><a href="<?php echo e(route('register')); ?>" wire:navigate class="text-slate-600 dark:text-gray-300 hover:text-brand-700">Daftar</a></li>
                            </ul>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-gray-500">Kebijakan</p>
                            <ul class="mt-3 space-y-2 text-sm">
                                <li><a href="<?php echo e(route('bantuan')); ?>" wire:navigate class="text-slate-600 dark:text-gray-300 hover:text-brand-700">Syarat dan Ketentuan</a></li>
                                <li><a href="<?php echo e(route('bantuan')); ?>" wire:navigate class="text-slate-600 dark:text-gray-300 hover:text-brand-700">Kebijakan Privasi</a></li>
                            </ul>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-gray-500">Hubungi kami</p>
                            <?php
                                $emailKontak = \App\Models\Pengaturan::ambil('situs.email');
                                $teleponKontak = \App\Models\Pengaturan::ambil('situs.telepon');
                                $alamatKontak = \App\Models\Pengaturan::ambil('situs.alamat');
                            ?>
                            <ul class="mt-3 space-y-2 text-sm text-slate-600 dark:text-gray-300">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($emailKontak): ?><li><a href="mailto:<?php echo e($emailKontak); ?>" class="hover:text-brand-700"><?php echo e($emailKontak); ?></a></li><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($teleponKontak): ?><li><?php echo e($teleponKontak); ?></li><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($alamatKontak): ?><li><?php echo e($alamatKontak); ?></li><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-6 border-t border-stone-200 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <?php if (isset($component)) { $__componentOriginal8892e718f3d0d7a916180885c6f012e7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8892e718f3d0d7a916180885c6f012e7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.application-logo','data' => ['class' => 'h-5 w-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('application-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $attributes = $__attributesOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $component = $__componentOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__componentOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
                            <p class="text-xs text-slate-500 dark:text-gray-400">&copy; <?php echo e(date('Y')); ?> <?php echo e(\App\Models\Pengaturan::namaSitus()); ?>. All rights reserved.</p>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                            <a href="<?php echo e(route('dashboard')); ?>" wire:navigate class="text-xs font-medium text-brand-700 hover:text-brand-800 dark:text-brand-300 transition">Masuk ke dashboard</a>
                        <?php else: ?>
                            <a href="<?php echo e(route('register')); ?>" wire:navigate class="text-xs font-medium text-brand-700 hover:text-brand-800 dark:text-brand-300 transition">Daftarkan kos Anda</a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </footer>

            <!-- Bottom Navigation (Mobile only) -->
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
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('chatbot', []);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1231132325-1', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

        <?php echo $__env->yieldPushContent('scripts'); ?>
    </body>
</html>
<?php /**PATH C:\laragon\www\Ngekos.in\resources\views/layouts/publik.blade.php ENDPATH**/ ?>