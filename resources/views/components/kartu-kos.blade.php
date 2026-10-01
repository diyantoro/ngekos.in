@props(['properti'])

@php
    $p = $properti;
    $hargaTampil = $p->harga ?? $p->harga_termurah;
    $selisih = ($p->harga_asli && $hargaTampil && $p->harga_asli > $hargaTampil) ? $p->harga_asli - $hargaTampil : 0;
    $labelDiskon = $selisih >= 1000 ? 'Diskon '.rtrim(rtrim(number_format($selisih / 1000, 1, ',', '.'), '0'), ',').'rb' : null;
    $fasilitas = collect(explode(',', (string) ($p->fasilitas ?? '')))->map(fn ($f) => trim($f))->filter()->take(6);
    $rating = (float) ($p->rating_ulasan ?? 0);
    $totalUlasan = (int) ($p->total_ulasan ?? 0);
    $tipe = strtolower((string) ($p->tipe_hunian ?? 'campur'));
    $warnaTipe = $tipe === 'putri' ? 'bg-pink-600' : ($tipe === 'putra' ? 'bg-sky-600' : 'bg-emerald-600');
    $labelTipe = $tipe === 'putri' ? 'Putri' : ($tipe === 'putra' ? 'Putra' : 'Campur');
    $sisa = (int) ($p->kamar_tersedia ?? 0);
@endphp

<a href="{{ route('kos.detail', $p) }}" wire:navigate {{ $attributes->merge(['class' => 'group min-w-0 bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 rounded-xl overflow-hidden hover:shadow-card-hover hover:border-brand-200 transition']) }}>
    <div class="relative h-28 sm:h-36 bg-stone-200 dark:bg-gray-700 overflow-hidden">
        @php $cover = $p->fotoCover(); @endphp
        @if ($cover)
            <img src="{{ $cover }}" alt="{{ $p->nama }}" loading="lazy" decoding="async" class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
        @else
            <div class="h-full w-full flex items-center justify-center">
                <svg class="h-10 w-10 text-stone-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
            </div>
        @endif
        @if ($sisa > 0 && $sisa <= 3)
            <span class="absolute top-1.5 right-1.5 sm:top-2 sm:right-2 inline-flex items-center rounded-full bg-red-600 px-1.5 sm:px-2 py-0.5 text-[10px] font-bold text-white shadow-md shadow-red-600/40">
                Sisa {{ $sisa }} kamar
            </span>
        @elseif ($sisa > 3)
            <span class="absolute top-1.5 right-1.5 sm:top-2 sm:right-2 inline-flex items-center rounded-full bg-emerald-600 px-1.5 sm:px-2 py-0.5 text-[10px] font-bold text-white shadow-md shadow-emerald-600/40">
                {{ $sisa }} Kamar
            </span>
        @endif
    </div>
    <div class="p-2.5 sm:p-3">
        <div class="flex items-center gap-1.5">
            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-bold text-white {{ $warnaTipe }}">{{ $labelTipe }}</span>
            @if ($totalUlasan > 0)
                <span class="inline-flex items-center gap-0.5 text-[11px] font-semibold text-slate-700 dark:text-gray-200">
                    <svg class="h-3 w-3 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 1.5l2.6 5.3 5.9.9-4.2 4.1 1 5.8L10 14.9l-5.3 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z" /></svg>
                    {{ number_format($rating, 1, ',', '.') }}
                </span>
            @endif
        </div>
        <h3 class="mt-1.5 text-sm font-bold text-slate-900 dark:text-gray-100 truncate group-hover:text-brand-800 dark:group-hover:text-brand-200 transition">{{ $p->nama }}</h3>
        <p class="mt-0.5 text-xs text-slate-500 dark:text-gray-400 truncate">{{ $p->kota ?? $p->alamat ?? 'Lokasi belum diisi' }}</p>
        @if ($fasilitas->isNotEmpty())
            <p class="mt-1 text-[11px] text-slate-500 dark:text-gray-400 truncate">{{ $fasilitas->join('·') }}</p>
        @endif
        <div class="mt-2">
            @if ($labelDiskon)
                <p class="text-[11px] font-bold text-green-600 dark:text-emerald-400">{{ $labelDiskon }}</p>
                <p class="text-xs text-slate-400 dark:text-gray-500 line-through">Rp{{ number_format($p->harga_asli, 0, ',', '.') }}</p>
            @endif
            @if ($hargaTampil)
                <p class="text-sm sm:text-base font-extrabold text-brand-700 dark:text-brand-200 tabular-nums truncate">Rp{{ number_format($hargaTampil, 0, ',', '.') }}</p>
                <p class="text-[10px] text-slate-400 dark:text-gray-500">{{ $labelDiskon ? '(Bulan pertama)' : '/bulan' }}</p>
            @else
                <p class="text-xs font-medium text-slate-400">Penuh</p>
            @endif
        </div>
    </div>
</a>
