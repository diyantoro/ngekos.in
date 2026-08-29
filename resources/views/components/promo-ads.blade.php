@props(['ads' => []])

@php
    $defaultAds = [
        [
            'brand' => 'Biznet',
            'tagline' => 'Internet Cepat & Stabil',
            'desc' => 'Pasang Biznet Home, kuliah online dan streaming di kos makin lancar.',
            'gradient' => 'from-blue-700 to-sky-500',
            'icon' => 'wifi',
        ],
        [
            'brand' => 'Shopee',
            'tagline' => 'Belanja Online Murah',
            'desc' => 'Voucher gratis ongkir dan cashback untuk kebutuhan kos kamu.',
            'gradient' => 'from-orange-600 to-orange-500',
            'icon' => 'bag',
        ],
        [
            'brand' => 'GoFood',
            'tagline' => 'Lapar di Kos?',
            'desc' => 'Pesan makanan favorit, GoFood antar sampai depan kosmu.',
            'gradient' => 'from-green-600 to-green-500',
            'icon' => 'scooter',
        ],
        [
            'brand' => 'DANA',
            'tagline' => 'Bayar Praktis',
            'desc' => 'Top up DANA untuk bayar tagihan kos dan jajan harian.',
            'gradient' => 'from-sky-600 to-blue-400',
            'icon' => 'wallet',
        ],
        [
            'brand' => 'IndiHome',
            'tagline' => 'Internet + TV di Kos',
            'desc' => 'Pasang IndiHome, nonton dan internetan bareng teman kos.',
            'gradient' => 'from-red-700 to-red-500',
            'icon' => 'tv',
        ],
        [
            'brand' => 'IKEA',
            'tagline' => 'Furnitur Kamar Kos',
            'desc' => 'Perabot IKEA harga bersahabat, kamar kos makin nyaman.',
            'gradient' => 'from-blue-800 to-blue-600',
            'icon' => 'chair',
        ],
    ];

    $ads = filled($ads) ? $ads : $defaultAds;
    $icons = [
        'wifi' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z" />',
        'bag' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />',
        'scooter' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />',
        'wallet' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3" />',
        'tv' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 20.25h12m-7.5-3v3m3-3v3m-10.125-3h17.25c.621 0 1.125-.504 1.125-1.125V4.875c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125z" />',
        'chair' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75v-1.5A2.25 2.25 0 015.25 6h13.5A2.25 2.25 0 0121 8.25v1.5M3 9.75a.75.75 0 01.75.75v3a.75.75 0 00.75.75h15a.75.75 0 00.75-.75v-3a.75.75 0 01.75-.75M3 9.75L2.25 21M21 9.75l.75 11.25" />',
    ];
@endphp

<div x-data="{ i: 0, n: {{ count($ads) }} }"
    x-init="setInterval(() => { i = (i + 1) % n }, 4000)">
    <div class="relative overflow-hidden rounded-2xl shadow-sm">
        <div class="flex transition-transform duration-500 ease-out" :style="`transform: translateX(-${i * 100}%)`">
            @foreach ($ads as $ad)
                <div class="w-full shrink-0 bg-gradient-to-r {{ $ad['gradient'] }} px-5 sm:px-7 py-4 sm:py-5">
                    <div class="relative flex items-center justify-between gap-4">
                        <div class="min-w-0 max-w-xl">
                            <div class="flex items-center gap-2">
                                <span class="text-white font-extrabold">{{ $ad['brand'] }}</span>
                                <span class="inline-flex rounded bg-white/20 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">Iklan</span>
                            </div>
                            <p class="mt-0.5 text-sm font-bold text-white">{{ $ad['tagline'] }}</p>
                            <p class="mt-0.5 text-xs leading-snug text-white/85 line-clamp-2">{{ $ad['desc'] }}</p>
                        </div>
                        <div class="shrink-0 flex items-center justify-center h-12 w-12 sm:h-14 sm:w-14 rounded-xl border border-white/30 bg-white/20">
                            <svg class="h-6 w-6 sm:h-7 sm:w-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">{!! $icons[$ad['icon']] ?? $icons['wifi'] !!}</svg>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-2.5 flex justify-center gap-1.5">
        @foreach ($ads as $key => $ad)
            <button type="button"
                :class="i === {{ $key }} ? 'w-5 bg-teal-600' : 'w-2 bg-teal-600/25'"
                class="h-1.5 rounded-full transition-all" @click="i = {{ $key }}"></button>
        @endforeach
    </div>
</div>