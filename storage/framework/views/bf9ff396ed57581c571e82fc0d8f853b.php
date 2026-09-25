<?php

use App\Models\PesanBantuan;
use Livewire\Volt\Component;

?>

<div x-data="{ terbuka: false, tetapBawah: true, scroll(paksa = false) { $nextTick(() => { const el = document.getElementById('chat-riwayat'); if (!el) return; if (paksa || this.tetapBawah) el.scrollTo({ top: el.scrollHeight, behavior: 'auto' }); }); } }"
     x-init="(() => { const pasang = () => { try { if ($wire && $wire.$watch && !window.__chatbotWatch) { window.__chatbotWatch = true; $wire.$watch('percakapan', () => scroll()); } } catch (e) {} }; pasang(); $watch('terbuka', (v) => { if (v) { tetapBawah = true; scroll(true); } }); if (window.Livewire) { document.addEventListener('livewire:initialized', pasang); } })()" @keydown.escape.window="terbuka = false" @click.away="terbuka = false" wire:ignore.self class="fixed right-4 sm:right-5 z-[60] bottom-24 sm:bottom-6 <?php echo e($tampil ? '' : 'hidden'); ?>">
    <button type="button" @click="terbuka = !terbuka"
            class="relative flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-teal-500 via-emerald-500 to-green-500 text-white shadow-lg shadow-emerald-300/60 ring-2 ring-white/60 hover:scale-105 hover:shadow-xl hover:shadow-emerald-300/70 transition"
            :aria-label="terbuka ? 'Tutup chatbot bantuan' : 'Buka chatbot bantuan'"
            :aria-expanded="terbuka.toString()">
        <svg x-show="!terbuka" class="h-7 w-7" viewBox="0 0 24 24">
            <path d="M12 2.75v2.4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <circle cx="12" cy="2.25" r="1.05" fill="currentColor"/>
            <rect x="3.5" y="7.5" width="17" height="10.5" rx="3.25" fill="none" stroke="currentColor" stroke-width="1.8"/>
            <circle cx="8.5" cy="12.25" r="1.15" fill="currentColor"/>
            <circle cx="15.5" cy="12.25" r="1.15" fill="currentColor"/>
            <path d="M9.75 15.75h4.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        <span class="absolute -top-0.5 -right-0.5 h-3.5 w-3.5 rounded-full bg-emerald-400 ring-2 ring-white" x-show="!terbuka"></span>
        <svg x-show="terbuka" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
    </button>

    <div x-show="terbuka" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-3 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute bottom-16 right-0 w-[calc(100vw-2rem)] max-w-sm overflow-hidden rounded-2xl bg-white dark:bg-gray-800 shadow-2xl ring-1 ring-gray-200 dark:ring-gray-600 flex flex-col"
         style="max-height: min(70vh, 30rem); height: 30rem;"
         role="dialog" aria-label="Chatbot bantuan Ngekos.in">
        <div class="flex items-center justify-between bg-gradient-to-r from-teal-600 to-emerald-600 px-4 py-3">
            <div class="flex items-center gap-2.5">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white/20">
                    <svg class="h-5 w-5 text-white" viewBox="0 0 24 24">
                        <path d="M12 3v2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <circle cx="12" cy="2.5" r="0.9" fill="currentColor"/>
                        <rect x="4" y="8" width="16" height="9.5" rx="3" fill="none" stroke="currentColor" stroke-width="1.8"/>
                        <circle cx="8.5" cy="12.5" r="1" fill="currentColor"/>
                        <circle cx="15.5" cy="12.5" r="1" fill="currentColor"/>
                        <path d="M9.75 15.5h4.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-white">Asisten Ngekos.in</p>
                    <p class="text-xs text-teal-100">Online — balasan otomatis</p>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <button type="button" wire:click="mulaiUlang" class="rounded-lg px-2 py-1 text-xs font-medium text-teal-100 hover:bg-white/10 hover:text-white transition" title="Mulai ulang percakapan">
                    Reset
                </button>
                <button type="button" @click="terbuka = false" class="rounded-lg p-1.5 text-teal-100 hover:bg-white/10 hover:text-white transition" aria-label="Tutup chat">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>

        <div id="chat-riwayat" class="flex-1 overflow-y-auto space-y-3 bg-gray-50 dark:bg-gray-900 px-4 py-4" aria-live="polite" @scroll="tetapBawah = ($event.target.scrollHeight - $event.target.scrollTop - $event.target.clientHeight) < 150">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $percakapan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $pesan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div wire:key="chat-<?php echo e($index); ?>"
                     class="flex <?php echo e($pesan['dari'] === 'user' ? 'justify-end' : 'justify-start'); ?>">
                    <div class="max-w-[80%] rounded-2xl px-3.5 py-2.5 text-sm leading-relaxed shadow-sm
                        <?php echo e($pesan['dari'] === 'user'
                            ? 'bg-teal-600 text-white rounded-br-sm'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 ring-1 ring-gray-100 dark:ring-gray-600 rounded-bl-sm'); ?>">
                        <?php echo e($pesan['teks']); ?>

                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div wire:loading wire:target="kirim" class="flex justify-start">
                <div class="flex items-center gap-1 rounded-2xl rounded-bl-sm bg-white dark:bg-gray-700 px-3.5 py-3 shadow-sm ring-1 ring-gray-100 dark:ring-gray-600" aria-label="Asisten sedang mengetik">
                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-gray-400"></span>
                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-gray-400" style="animation-delay: .15s"></span>
                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-gray-400" style="animation-delay: .3s"></span>
                </div>
            </div>
        </div>

        <div class="flex gap-2 overflow-x-auto scrollbar-hide px-4 py-2 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $saranCepat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $saran): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button type="button" wire:click="kirim('<?php echo e($saran); ?>')"
                    class="shrink-0 rounded-full bg-teal-50 dark:bg-teal-500/10 px-3 py-1 text-xs font-medium text-teal-700 dark:text-teal-300 ring-1 ring-inset ring-teal-100 dark:ring-teal-500/30 hover:bg-teal-100 dark:hover:bg-teal-500/20 transition">
                    <?php echo e($saran); ?>

                </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <form wire:submit="kirim" class="flex items-center gap-2 bg-white dark:bg-gray-800 px-3 py-2.5 border-t border-gray-100 dark:border-gray-700">
            <input type="text" wire:model="input" placeholder="Ketik pertanyaanmu..." maxlength="500" autocomplete="off"
                   class="flex-1 rounded-full border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:border-teal-500 focus:ring-teal-500">
            <button type="submit" wire:loading.attr="disabled" wire:target="kirim" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-teal-600 text-white hover:bg-teal-500 transition disabled:opacity-50" aria-label="Kirim">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" /></svg>
            </button>
        </form>
    </div>
</div><?php /**PATH C:\laragon\www\Ngekos.in\resources\views\livewire\chatbot.blade.php ENDPATH**/ ?>