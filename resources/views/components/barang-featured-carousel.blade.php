@props(['barangs', 'interval' => 3000])

@if ($barangs->isNotEmpty())
    <section
        class="overflow-hidden rounded-3xl border border-amber-200 bg-white shadow-sm dark:border-amber-900/50 dark:bg-gray-900"
        x-data="{
            active: 0,
            total: {{ $barangs->count() }},
            touchStart: null,
            timer: null,
            init() {
                if (this.total > 1) {
                    this.timer = window.setInterval(() => this.next(), {{ max(1000, (int) $interval) }});
                }
            },
            next() {
                this.active = (this.active + 1) % this.total;
            },
            previous() {
                this.active = (this.active - 1 + this.total) % this.total;
            },
            goTo(index) {
                this.active = index;
            },
            destroy() {
                if (this.timer) window.clearInterval(this.timer);
            }
        }"
        aria-label="Barang unggulan"
    >
        <div class="flex items-center justify-between gap-3 border-b border-stone-100 px-3 py-3 dark:border-gray-800 sm:px-6 sm:py-4">
            <div>
                <p class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">Pilihan Admin</p>
                <h2 class="mt-0.5 text-base font-extrabold text-slate-900 dark:text-white sm:mt-1 sm:text-xl">Barang Unggulan</h2>
            </div>
            @if ($barangs->count() > 1)
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <button type="button" @click="previous()" aria-label="Barang sebelumnya"
                        class="flex h-8 w-8 sm:h-9 sm:w-9 items-center justify-center rounded-full border border-stone-200 text-slate-700 transition hover:bg-stone-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                        <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </button>
                    <button type="button" @click="next()" aria-label="Barang berikutnya"
                        class="flex h-8 w-8 sm:h-9 sm:w-9 items-center justify-center rounded-full border border-stone-200 text-slate-700 transition hover:bg-stone-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                        <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </button>
                </div>
            @endif
        </div>

        <div class="overflow-hidden">
            <div
                @touchstart="touchStart = $event.changedTouches[0].screenX"
                @touchend="const delta = $event.changedTouches[0].screenX - touchStart; if (Math.abs(delta) > 40) delta < 0 ? next() : previous()"
                class="flex transition-transform duration-500 ease-out"
                :style="`transform: translateX(-${active * 100}%);`"
            >
                @foreach ($barangs as $barang)
                    <article class="w-full shrink-0 p-3 sm:p-6">
                        <div class="grid grid-cols-[88px_minmax(0,1fr)] items-center gap-3 sm:grid-cols-[minmax(0,240px)_1fr] sm:gap-5">
                            <a href="{{ route('barang.detail', $barang) }}" wire:navigate class="block overflow-hidden rounded-xl sm:rounded-2xl bg-stone-100 dark:bg-gray-800">
                                <img src="{{ $barang->fotoUrl() }}" alt="{{ $barang->nama }}" class="aspect-square w-full object-cover">
                            </a>
                            <div class="min-w-0">
                                <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-brand-700 dark:text-brand-300">{{ $barang->kategori?->label() ?? 'Lainnya' }}</p>
                                <a href="{{ route('barang.detail', $barang) }}" wire:navigate class="mt-0.5 block text-sm font-extrabold leading-tight text-slate-900 hover:text-brand-700 dark:text-white dark:hover:text-brand-300 sm:mt-1 sm:text-2xl">
                                    {{ $barang->nama }}
                                </a>
                                <p class="mt-1 truncate text-[11px] text-slate-500 dark:text-gray-400 sm:mt-2 sm:text-sm">
                                    {{ $barang->user?->nama ?? 'Penjual' }} · {{ $barang->kota }}
                                </p>
                                <p class="mt-1.5 text-base font-extrabold text-slate-900 dark:text-white sm:mt-3 sm:text-xl">{{ $barang->formattedHarga() }}</p>
                                <a href="{{ route('barang.detail', $barang) }}" wire:navigate class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-brand-700 px-2.5 py-1.5 text-[11px] font-bold text-white transition hover:bg-brand-800 sm:mt-4 sm:gap-2 sm:rounded-xl sm:px-4 sm:py-2.5 sm:text-sm">
                                    Lihat barang
                                    <svg class="h-3 w-3 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7-7l7 7-7 7" /></svg>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        @if ($barangs->count() > 1)
            <div class="flex justify-center gap-2 pb-3 sm:pb-4">
                @foreach ($barangs as $index => $barang)
                    <button type="button" @click="goTo({{ $index }})" aria-label="Tampilkan barang {{ $index + 1 }}"
                        :aria-current="active === {{ $index }} ? 'true' : 'false'"
                        :class="active === {{ $index }} ? 'w-6 bg-brand-700' : 'w-2 bg-stone-300 dark:bg-gray-600'"
                        class="h-2 rounded-full transition-all"></button>
                @endforeach
            </div>
        @endif
    </section>
@endif
