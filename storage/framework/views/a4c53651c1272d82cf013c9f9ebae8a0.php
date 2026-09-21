<?php
    $isHome = request()->routeIs('home') || request()->routeIs('dashboard*');
?>
<div class="fixed bottom-0 left-0 right-0 z-50 sm:hidden safe-bottom">
    
    <div class="absolute inset-0 bg-gradient-to-t from-white/80 via-white/60 to-white/40 dark:from-gray-900/80 dark:via-gray-900/60 dark:to-gray-900/40 backdrop-blur-2xl border-t border-white/50 dark:border-gray-700/50 shadow-2xl shadow-black/10 dark:shadow-black/30">
        
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-teal-500/30 via-emerald-500/20 to-violet-500/30"></div>
        
        <div class="absolute top-2 left-1/4 w-1/2 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent dark:via-white/10"></div>
    </div>

    <nav class="relative flex items-center justify-around h-16 px-2">
        
        <div x-data="{
            activeIndex: <?php echo \Illuminate\Support\Js::from(array_search(true, [
                $isHome,
                request()->routeIs('kos.*'),
                auth()->check() && request()->routeIs('chat.*'),
                auth()->check() && auth()->user()->hasAnyRole(['super_admin', 'admin']) && request()->routeIs('bantuan.masuk'),
                auth()->check() && auth()->user()->hasAnyRole(['pemilik', 'admin', 'super_admin']) && request()->routeIs('pemilik.*'),
                auth()->check() && auth()->user()->hasRole('pemilik') && request()->routeIs('pemilik.grafik'),
                auth()->check() && request()->routeIs('pengaturan'),
            ]) ?: 0
        )->toHtml() ?> }"
            x-init="
                const items = $refs.nav.querySelectorAll('[data-nav-item]');
                const indicator = $refs.indicator;
                function updateIndicator(index) {
                    if (items[index]) {
                        const rect = items[index].getBoundingClientRect();
                        const navRect = $refs.nav.getBoundingClientRect();
                        indicator.style.width = rect.width + 'px';
                        indicator.style.left = (rect.left - navRect.left) + 'px';
                        indicator.style.opacity = '1';
                    }
                }
                setTimeout(() => updateIndicator(activeIndex), 50);
                window.addEventListener('resize', () => updateIndicator(activeIndex));
            "
            class="relative">
            
            <div x-ref="indicator"
                class="absolute top-1 left-0 h-12 rounded-2xl bg-gradient-to-br from-teal-500/20 via-emerald-500/10 to-violet-500/20 dark:from-teal-500/30 dark:via-emerald-500/15 dark:to-violet-500/30 backdrop-blur-2xl border border-white/30 dark:border-white/20 shadow-lg shadow-teal-500/10 dark:shadow-teal-500/20 transition-all duration-500 ease-out transform-gpu opacity-0"
                style="box-shadow:
                    0 8px 32px -4px rgba(16, 185, 129, 0.15),
                    0 4px 16px -2px rgba(16, 185, 129, 0.1),
                    inset 0 1px 0 rgba(255, 255, 255, 0.3),
                    inset 0 -1px 0 rgba(255, 255, 255, 0.1);">
                
                <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-teal-500/10 via-transparent to-violet-500/10 opacity-50"></div>
            </div>

            
            <div x-ref="nav" class="relative flex items-center justify-around w-full gap-1" role="navigation">

                
                <a href="<?php echo e(route('home')); ?>" wire:navigate
                    data-nav-item
                    aria-current="<?php echo e($isHome ? 'page' : 'false'); ?>"
                    x-on:click="activeIndex = 0"
                    x-on:mousedown="event.target.style.transform = 'scale(0.92)'"
                    x-on:mouseup="event.target.style.transform = ''"
                    x-on:mouseleave="event.target.style.transform = ''"
                    class="relative flex flex-col items-center justify-center gap-0.5 w-16 py-1 transition-all duration-300 ease-out
                        <?php echo e($isHome ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400 dark:text-gray-500'); ?>

                        active:scale-95
                        focus:outline-none">
                    <svg class="h-6 w-6 transition-all duration-300 <?php echo e($isHome ? 'scale-110 drop-shadow-[0_4px_8px_rgba(16,185,129,0.3)]' : ''); ?>"
                        fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    <span class="text-[10px] font-semibold transition-colors duration-200"><?php echo e(__('Beranda')); ?></span>
                </a>

                
                <a href="<?php echo e(route('kos.index')); ?>" wire:navigate
                    data-nav-item
                    aria-current="<?php echo e(request()->routeIs('kos.*') ? 'page' : 'false'); ?>"
                    x-on:click="activeIndex = 1"
                    x-on:mousedown="event.target.style.transform = 'scale(0.92)'"
                    x-on:mouseup="event.target.style.transform = ''"
                    x-on:mouseleave="event.target.style.transform = ''"
                    class="relative flex flex-col items-center justify-center gap-0.5 w-16 py-1 transition-all duration-300 ease-out
                        <?php echo e(request()->routeIs('kos.*') ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400 dark:text-gray-500'); ?>

                        active:scale-95
                        focus:outline-none">
                    <svg class="h-6 w-6 transition-all duration-300 <?php echo e(request()->routeIs('kos.*') ? 'scale-110 drop-shadow-[0_4px_8px_rgba(16,185,129,0.3)]' : ''); ?>"
                        fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <span class="text-[10px] font-semibold transition-colors duration-200"><?php echo e(__('Cari Kos')); ?></span>
                </a>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                    
                    <a href="<?php echo e(route('chat.index')); ?>" wire:navigate
                        data-nav-item
                        aria-current="<?php echo e(request()->routeIs('chat.*') ? 'page' : 'false'); ?>"
                        x-on:click="activeIndex = 2"
                        x-on:mousedown="event.target.style.transform = 'scale(0.92)'"
                        x-on:mouseup="event.target.style.transform = ''"
                        x-on:mouseleave="event.target.style.transform = ''"
                        class="relative flex flex-col items-center justify-center gap-0.5 w-16 py-1 transition-all duration-300 ease-out
                            <?php echo e(request()->routeIs('chat.*') ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400 dark:text-gray-500'); ?>

                            active:scale-95
                            focus:outline-none">
                        <svg class="h-6 w-6 transition-all duration-300 <?php echo e(request()->routeIs('chat.*') ? 'scale-110 drop-shadow-[0_4px_8px_rgba(16,185,129,0.3)]' : ''); ?>"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                        </svg>
                        <?php $unread = auth()->user()->pesanBelumDibaca(); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unread > 0): ?>
                            <span class="absolute top-0.5 right-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-gradient-to-r from-rose-500 to-pink-500 text-[9px] font-bold text-white shadow-sm shadow-rose-500/30 animate-bounce-gentle z-10"><?php echo e($unread > 9 ? '9+' : $unread); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <span class="text-[10px] font-semibold transition-colors duration-200"><?php echo e(__('Pesan')); ?></span>
                    </a>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasAnyRole(['super_admin', 'admin'])): ?>
                        
                        <a href="<?php echo e(route('bantuan.masuk')); ?>" wire:navigate
                            data-nav-item
                            aria-current="<?php echo e(request()->routeIs('bantuan.masuk') ? 'page' : 'false'); ?>"
                            x-on:click="activeIndex = 3"
                            x-on:mousedown="event.target.style.transform = 'scale(0.92)'"
                            x-on:mouseup="event.target.style.transform = ''"
                            x-on:mouseleave="event.target.style.transform = ''"
                            class="relative flex flex-col items-center justify-center gap-0.5 w-16 py-1 transition-all duration-300 ease-out
                                <?php echo e(request()->routeIs('bantuan.masuk') ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400 dark:text-gray-500'); ?>

                                active:scale-95
                                focus:outline-none">
                            <svg class="h-6 w-6 transition-all duration-300 <?php echo e(request()->routeIs('bantuan.masuk') ? 'scale-110 drop-shadow-[0_4px_8px_rgba(16,185,129,0.3)]' : ''); ?>"
                                fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z" />
                            </svg>
                            <?php $bantuanBaru = auth()->user()->bantuanMasukBelumDibaca(); ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bantuanBaru > 0): ?>
                                <span class="absolute top-0.5 right-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-gradient-to-r from-rose-500 to-pink-500 text-[9px] font-bold text-white shadow-sm shadow-rose-500/30 animate-bounce-gentle z-10"><?php echo e($bantuanBaru > 9 ? '9+' : $bantuanBaru); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <span class="text-[10px] font-semibold transition-colors duration-200"><?php echo e(__('Bantuan')); ?></span>
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasAnyRole(['pemilik', 'admin', 'super_admin'])): ?>
                        
                        <div x-data="{ buka: false }" class="relative" data-nav-item>
                            <button @click="buka = !buka" type="button"
                                aria-label="Menu kelola"
                                x-on:mousedown="event.target.style.transform = 'scale(0.92)'"
                                x-on:mouseup="event.target.style.transform = ''"
                                x-on:mouseleave="event.target.style.transform = ''"
                                class="relative flex flex-col items-center justify-center gap-0.5 w-16 py-1 transition-all duration-300 ease-out
                                    <?php echo e(request()->routeIs('pemilik.*') ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400 dark:text-gray-500'); ?>

                                    active:scale-95
                                    focus:outline-none">
                                <svg class="h-6 w-6 transition-all duration-300 <?php echo e(request()->routeIs('pemilik.*') ? 'scale-110 drop-shadow-[0_4px_8px_rgba(16,185,129,0.3)]' : ''); ?>"
                                    fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .414.336.75.75.75z" />
                                </svg>
                                <span class="text-[10px] font-semibold transition-colors duration-200"><?php echo e(__('Kelola')); ?></span>
                            </button>
                            <div x-show="buka" x-cloak @click.outside="buka = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                                class="absolute bottom-full mb-3 left-1/2 -translate-x-1/2 w-48 rounded-2xl bg-white/90 dark:bg-gray-800/90 backdrop-blur-2xl shadow-2xl ring-1 ring-gray-200/60 dark:ring-gray-700/60 overflow-hidden z-50 border border-white/50 dark:border-gray-700/50">
                                <a href="<?php echo e(route('pemilik.properti')); ?>" wire:navigate @click="buka = false"
                                    class="flex items-center gap-3 px-4 py-3 text-sm font-semibold <?php echo e(request()->routeIs('pemilik.properti*') ? 'text-teal-600 dark:text-teal-400 bg-teal-500/10' : 'text-gray-700 dark:text-gray-200'); ?> hover:bg-white/50 dark:hover:bg-white/10 transition-all duration-200 rounded-xl mx-2 my-1">
                                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819" /></svg>
                                    <?php echo e(__('Kelola Kos')); ?>

                                </a>
                                <a href="<?php echo e(route('pemilik.pengeluaran')); ?>" wire:navigate @click="buka = false"
                                    class="flex items-center gap-3 px-4 py-3 text-sm font-semibold <?php echo e(request()->routeIs('pemilik.pengeluaran') ? 'text-teal-600 dark:text-teal-400 bg-teal-500/10' : 'text-gray-700 dark:text-gray-200'); ?> hover:bg-white/50 dark:hover:bg-white/10 transition-all duration-200 rounded-xl mx-2 my-1">
                                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2.25 2.25 0 002.25-2.25v-1.5a2.25 2.25 0 00-2.25-2.25H6a2.25 2.25 0 00-2.25 2.25v1.5A2.25 2.25 0 006 21zm12-8.25v-6.5A2.25 2.25 0 0015.75 4H8.25A2.25 2.25 0 006 6.25v6.5m18 0h-18" /></svg>
                                    <?php echo e(__('Pengeluaran')); ?>

                                </a>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('pemilik')): ?>
                        
                        <a href="<?php echo e(route('pemilik.grafik')); ?>" wire:navigate
                            data-nav-item
                            aria-label="Grafik"
                            x-on:mousedown="event.target.style.transform = 'scale(0.92)'"
                            x-on:mouseup="event.target.style.transform = ''"
                            x-on:mouseleave="event.target.style.transform = ''"
                            class="relative flex flex-col items-center justify-center gap-0.5 w-16 py-1 transition-all duration-300 ease-out
                                <?php echo e(request()->routeIs('pemilik.grafik') ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400 dark:text-gray-500'); ?>

                                active:scale-95
                                focus:outline-none">
                            <svg class="h-6 w-6 transition-all duration-300 <?php echo e(request()->routeIs('pemilik.grafik') ? 'scale-110 drop-shadow-[0_4px_8px_rgba(16,185,129,0.3)]' : ''); ?>"
                                fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                            <span class="text-[10px] font-semibold transition-colors duration-200"><?php echo e(__('Grafik')); ?></span>
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <a href="<?php echo e(route('pengaturan')); ?>" wire:navigate
                        data-nav-item
                        aria-current="<?php echo e(request()->routeIs('pengaturan') ? 'page' : 'false'); ?>"
                        x-on:click="activeIndex = <?php $idx = 4; if (auth()->check() && auth()->user()->hasAnyRole(['super_admin', 'admin'])) $idx++; if (auth()->check() && auth()->user()->hasAnyRole(['pemilik', 'admin', 'super_admin'])) $idx++; if (auth()->check() && auth()->user()->hasRole('pemilik')) $idx++; echo $idx; ?>"
                        x-on:mousedown="event.target.style.transform = 'scale(0.92)'"
                        x-on:mouseup="event.target.style.transform = ''"
                        x-on:mouseleave="event.target.style.transform = ''"
                        class="relative flex flex-col items-center justify-center gap-0.5 w-16 py-1 transition-all duration-300 ease-out
                            <?php echo e(request()->routeIs('pengaturan') ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400 dark:text-gray-500'); ?>

                            active:scale-95
                            focus:outline-none">
                        <svg class="h-6 w-6 transition-all duration-300 <?php echo e(request()->routeIs('pengaturan') ? 'scale-110 drop-shadow-[0_4px_8px_rgba(16,185,129,0.3)]' : ''); ?>"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.28z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->avatar_url): ?>
                            <img src="<?php echo e(auth()->user()->avatar_url); ?>" alt="<?php echo e(auth()->user()->nama); ?>" class="h-6 w-6 rounded-full object-cover ring-2 ring-white/50 dark:ring-gray-700/50 transition-all duration-300 <?php echo e(request()->routeIs('pengaturan') ? 'ring-teal-500/50 scale-110' : ''); ?>">
                        <?php else: ?>
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-br from-teal-500 to-emerald-500 text-[10px] font-bold text-white ring-2 ring-white/50 dark:ring-gray-700/50 transition-all duration-300 <?php echo e(request()->routeIs('pengaturan') ? 'ring-teal-500/50 scale-110' : ''); ?>"><?php echo e(auth()->user()->inisial); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <span class="text-[10px] font-semibold transition-colors duration-200"><?php echo e(__('Akun')); ?></span>
                    </a>
                <?php else: ?>
                    
                    <a href="<?php echo e(route('login')); ?>" wire:navigate
                        class="flex flex-col items-center justify-center gap-0.5 w-16 py-1 text-gray-400 dark:text-gray-500 active:scale-95 transition-all duration-300 ease-out focus:outline-none">
                        <svg class="h-6 w-6 transition-transform duration-300 active:scale-110" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
                        <span class="text-[10px] font-semibold"><?php echo e(__('Masuk')); ?></span>
                    </a>
                    
                    <a href="<?php echo e(route('register')); ?>" wire:navigate
                        class="flex flex-col items-center justify-center gap-0.5 w-16 py-1 text-gray-400 dark:text-gray-500 active:scale-95 transition-all duration-300 ease-out focus:outline-none">
                        <svg class="h-6 w-6 transition-transform duration-300 active:scale-110" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" /></svg>
                        <span class="text-[10px] font-semibold"><?php echo e(__('Daftar')); ?></span>
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </nav>
</div>

<style>
/* Liquid glass ripple effect */
@keyframes liquid-ripple {
    0% { transform: scale(0); opacity: 0.5; }
    100% { transform: scale(2.5); opacity: 0; }
}

.transform-gpu {
    transform: translateZ(0);
    will-change: transform, width, left, opacity;
}

/* Smooth scrollbar hide */
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

/* Reduce motion support */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
</style><?php /**PATH C:\laragon\www\Ngekos.in\resources\views/components/bottom-nav.blade.php ENDPATH**/ ?>