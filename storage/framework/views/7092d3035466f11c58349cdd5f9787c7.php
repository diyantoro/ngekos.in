<?php

use App\Models\SubscriptionRequest;
use App\Services\SubscriptionService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

?>

<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">Pilih Paket</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Paket saat ini: <span class="font-bold text-gray-900 dark:text-gray-100"><?php echo e(strtoupper($paketAktif)); ?></span>. Bayar via QRIS lalu admin memverifikasi pembayaran Anda.</p>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isFree && $sisaTrial !== null): ?>
            <div class="rounded-xl bg-amber-50 dark:bg-amber-500/10 ring-1 ring-amber-200 dark:ring-amber-500/30 px-4 py-3 text-sm text-amber-800 dark:text-amber-200">
                Masa coba gratis tinggal <strong><?php echo e($sisaTrial); ?> hari</strong>. Upgrade ke PRO agar laporan & grafik lengkap tetap terbuka.
            </div>
        <?php elseif($isFree && $trialHabis): ?>
            <div class="rounded-xl bg-rose-50 dark:bg-rose-500/10 ring-1 ring-rose-200 dark:ring-rose-500/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                Masa coba 7 hari sudah habis. Data tidak hilang, tapi tambah kos/kamar, halaman Laporan & unduh Excel dikunci. Upgrade ke PRO untuk membuka lagi.
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $pakets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $paket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $planColors = [
                        'free' => [
                            'bg' => 'bg-gradient-to-br from-slate-50 via-slate-100/50 to-gray-50 dark:from-slate-900/60 dark:via-slate-800/50 dark:to-gray-900/60',
                            'border' => 'border-slate-200/60 dark:border-slate-700/60',
                            'ring' => 'ring-slate-400',
                            'title' => 'text-slate-600 dark:text-slate-400',
                            'price' => 'text-slate-900 dark:text-slate-100',
                            'accent' => 'text-slate-500',
                            'badge' => 'bg-slate-100/80 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 ring-slate-200/60 dark:ring-slate-600/60',
                            'btn' => 'btn-secondary',
                            'glow' => 'shadow-slate-400/20',
                            'hoverGlow' => 'shadow-slate-400/40',
                            'accentGradient' => 'from-slate-400 to-slate-500',
                        ],
                        'pro' => [
                            'bg' => 'bg-gradient-to-br from-teal-50 via-teal-100/30 to-emerald-50 dark:from-teal-900/40 dark:via-teal-800/30 dark:to-emerald-900/40',
                            'border' => 'border-teal-200/60 dark:border-teal-800/60',
                            'ring' => 'ring-teal-500',
                            'title' => 'text-teal-700 dark:text-teal-300',
                            'price' => 'text-teal-800 dark:text-teal-200',
                            'accent' => 'text-teal-600 dark:text-teal-400',
                            'badge' => 'bg-teal-100/80 dark:bg-teal-900/40 text-teal-700 dark:text-teal-300 ring-teal-200/60 dark:ring-teal-700/60',
                            'btn' => 'btn-primary',
                            'glow' => 'shadow-teal-400/20',
                            'hoverGlow' => 'shadow-teal-400/40',
                            'accentGradient' => 'from-teal-500 to-emerald-500',
                        ],
                        'business' => [
                            'bg' => 'bg-gradient-to-br from-violet-50 via-violet-100/30 to-purple-50 dark:from-violet-900/40 dark:via-violet-800/30 dark:to-purple-900/40',
                            'border' => 'border-violet-200/60 dark:border-violet-800/60',
                            'ring' => 'ring-violet-500',
                            'title' => 'text-violet-700 dark:text-violet-300',
                            'price' => 'text-violet-800 dark:text-violet-200',
                            'accent' => 'text-violet-600 dark:text-violet-400',
                            'badge' => 'bg-violet-100/80 dark:bg-violet-900/40 text-violet-700 dark:text-violet-300 ring-violet-200/60 dark:ring-violet-700/60',
                            'btn' => 'bg-gradient-to-r from-violet-600 to-purple-600 hover:from-violet-700 hover:to-purple-700 text-white',
                            'glow' => 'shadow-violet-400/20',
                            'hoverGlow' => 'shadow-violet-400/40',
                            'accentGradient' => 'from-violet-500 to-purple-500',
                        ],
                    ];
                    $c = $planColors[$key] ?? $planColors['free'];
                    $isActive = $paketAktif === $key;
                    $isPopular = $key === 'pro';
                    $glowHex = preg_replace('/shadow-/', '', $c['glow']);
                    $hoverGlowHex = preg_replace('/shadow-/', '', $c['hoverGlow']);
                    $transformOffset = $isActive ? '-4px' : '0';
                    $activeBoxShadow = $isActive ? '0 10px 40px -10px ' . $glowHex : 'none';
                ?>
                <div
                    x-data="{ hovered: false, pressed: false, offset: <?php echo \Illuminate\Support\Js::from($transformOffset)->toHtml() ?>, glow: <?php echo \Illuminate\Support\Js::from($glowHex)->toHtml() ?>, hoverGlow: <?php echo \Illuminate\Support\Js::from($hoverGlowHex)->toHtml() ?>, activeShadow: <?php echo \Illuminate\Support\Js::from($activeBoxShadow)->toHtml() ?> }"
                    @mouseenter="hovered = true"
                    @mouseleave="hovered = false"
                    @mousedown="pressed = true"
                    @mouseup="pressed = false"
                    @mouseleave="pressed = false"
                    class="relative group card p-6 overflow-hidden transition-all duration-300 ease-out
                        <?php echo e($c['bg']); ?> <?php echo e($c['border']); ?>

                        <?php echo e($isActive ? 'ring-2 ' . $c['ring'] . ' ' . $c['glow'] : ''); ?>

                        hover:-translate-y-1.5 hover:<?php echo e($c['hoverGlow']); ?> hover:shadow-xl
                        active:scale-[0.98] active:shadow-lg
                        backdrop-blur-xl
                        <?php echo e($isPopular ? 'relative' : ''); ?>"
                    style="transform: translateY(<?php echo e($isActive ? '-4px' : '0'); ?>);"
                    :style="`transform: translateY(${hovered ? '-8px' : (pressed ? '-2px' : offset)}) scale(${pressed ? 0.98 : 1}); box-shadow: ${hovered ? '0 25px 50px -12px ' + hoverGlow : (pressed ? '0 10px 20px -5px ' + glow : activeShadow)};`"
                >
                    
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r <?php echo e($c['accentGradient']); ?> opacity-90 group-hover:opacity-100 transition-opacity duration-300"></div>

                    
                    <div class="absolute inset-0 bg-gradient-to-br from-white/50 via-transparent to-transparent dark:from-white/10 dark:via-transparent dark:to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isPopular): ?>
                        <div class="absolute -top-3 right-4 z-10">
                            <span class="inline-flex items-center gap-1 rounded-full bg-gradient-to-r from-teal-500 to-emerald-500 px-3 py-1 text-xs font-bold text-white shadow-lg shadow-teal-500/30 animate-pulse-gentle">
                                <span class="relative flex h-1.5 w-1.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-teal-400"></span>
                                </span>
                                Populer
                            </span>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="relative z-10">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wider <?php echo e($c['badge']); ?> backdrop-blur-sm">
                                <?php echo e($paket['name']); ?>

                            </span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isActive): ?>
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100/80 dark:bg-emerald-900/40 px-2.5 py-1 text-[10px] font-bold text-emerald-700 dark:text-emerald-300 ring-1 ring-emerald-200/60 dark:ring-emerald-700/60 backdrop-blur-sm">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    Aktif
                                </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <p class="text-sm font-bold uppercase tracking-wider <?php echo e($c['title']); ?> transition-colors duration-200 group-hover:text-gray-900 dark:group-hover:text-gray-100"><?php echo e($paket['name']); ?></p>
                        <p class="mt-2 text-3xl font-extrabold <?php echo e($c['price']); ?> transition-all duration-300">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($paket['price'] ?? 0) > 0): ?>
                                Rp<?php echo e(number_format($paket['price'], 0, ',', '.')); ?><span class="text-sm font-medium text-gray-500 dark:text-gray-400">/bulan</span>
                            <?php else: ?>
                                <span class="<?php echo e($c['accent']); ?>">Gratis</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </p>

                        <div class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-5 w-5 items-center justify-center rounded-lg bg-gradient-to-br <?php echo e($c['accentGradient']); ?> text-white text-[10px] font-bold transition-transform duration-300 group-hover:scale-110">🏠</span>
                                <span>Property: <span class="font-semibold text-gray-900 dark:text-gray-100"><?php echo e($paket['limits']['properties'] ?? 'Unlimited'); ?></span></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-5 w-5 items-center justify-center rounded-lg bg-gradient-to-br <?php echo e($c['accentGradient']); ?> text-white text-[10px] font-bold transition-transform duration-300 group-hover:scale-110">🚪</span>
                                <span>Kamar: <span class="font-semibold text-gray-900 dark:text-gray-100"><?php echo e($paket['limits']['rooms'] ?? 'Unlimited'); ?></span></span>
                            </div>
                        </div>

                        <ul class="mt-5 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $paket['features'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fitur): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="flex items-start gap-2 group relative transition-colors duration-200 hover:text-gray-900 dark:hover:text-gray-100">
                                    <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded <?php echo e($key === 'free' ? 'bg-slate-400' : ($key === 'pro' ? 'bg-teal-500' : 'bg-violet-500')); ?> text-white text-[10px] font-bold transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">✓</span>
                                    <span><?php echo e(str($fitur)->replace('_', ' ')->title()); ?></span>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ul>

                        <div class="mt-6 pt-4 border-t border-gray-100/60 dark:border-gray-800/60">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isActive): ?>
                                <span class="inline-flex items-center gap-1 rounded-xl <?php echo e($c['badge']); ?> px-4 py-2.5 text-xs font-bold transition-all duration-200">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    Paket Aktif
                                </span>
                            <?php elseif($permintaan?->status === 'pending'): ?>
                                <span class="inline-flex items-center gap-2 rounded-xl bg-amber-100/80 dark:bg-amber-900/40 px-4 py-2.5 text-xs font-bold text-amber-700 dark:text-amber-300 ring-1 ring-amber-200/60 dark:ring-amber-700/60 animate-pulse-gentle">
                                    <span class="relative flex h-1.5 w-1.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-500 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-amber-500"></span>
                                    </span>
                                    Menunggu Persetujuan
                                </span>
                            <?php else: ?>
                                <a href="<?php echo e(route('langganan.bayar', $key)); ?>" wire:navigate
                                   class="inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-xs font-semibold <?php echo e($c['btn']); ?>

                                          transition-all duration-200
                                          hover:shadow-lg hover:shadow-teal-500/25
                                          active:scale-[0.97]
                                          focus:outline-none focus:ring-2 focus:ring-offset-2 <?php echo e($isActive ? 'focus:ring-teal-500' : 'focus:ring-violet-500'); ?>">
                                    <svg class="h-3.5 w-3.5 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                                    Upgrade ke <?php echo e($paket['name']); ?>

                                </a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>

<style>
.animate-pulse-gentle {
    animation: pulse-gentle 2s ease-in-out infinite;
}
@keyframes pulse-gentle {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
.animate-ping {
    animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
}
@keyframes ping {
    75%, 100% { transform: scale(2); opacity: 0; }
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style><?php /**PATH C:\laragon\www\Ngekos.in\resources\views\livewire/pages/langganan/plans.blade.php ENDPATH**/ ?>