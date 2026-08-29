<?php

use App\Models\Properti;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.publik')] class extends Component
{
    public string $cari = '';

    public string $kota = '';

    public ?int $hargaMax = null;

    public int $kapasitas = 1;

    public function with(): array
    {
        $query = Properti::query()
            ->where('status', 'aktif')
            ->withCount(['kamars as total_kamar', 'kamars as kamar_tersedia' => fn ($q) => $q->where('status', 'tersedia')])
            ->withMin(['kamars as harga_termurah' => fn ($q) => $q->where('status', 'tersedia')], 'harga_sewa_bulanan');

        if ($this->cari) {
            $query->where(fn ($q) => $q
                ->where('nama', 'like', "%{$this->cari}%")
                ->orWhere('kota', 'like', "%{$this->cari}%")
                ->orWhere('alamat', 'like', "%{$this->cari}%"));
        }

        if ($this->kota) {
            $query->where('kota', $this->kota);
        }

        if ($this->hargaMax) {
            $query->whereHas('kamars', fn ($q) => $q->where('status', 'tersedia')->where('harga_sewa_bulanan', '<=', $this->hargaMax));
        }

        if ($this->kapasitas > 1) {
            $query->whereHas('kamars', fn ($q) => $q->where('status', 'tersedia')->where('kapasitas', '>=', $this->kapasitas));
        }

        return [
            'propertis' => $query->orderBy('nama')->get(),
            'daftarKota' => Properti::where('status', 'aktif')->whereNotNull('kota')->distinct()->orderBy('kota')->pluck('kota'),
        ];
    }
}; ?>

<div>
    <!-- Search Header -->
    <section class="bg-white border-b border-gray-100 sticky top-14 z-30">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <!-- Search Bar -->
            <div class="flex items-center gap-2">
                <div class="flex-1 flex items-center gap-2 bg-gray-100 rounded-xl px-3 py-2.5">
                    <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    <input type="text" wire:model.live.debounce.300ms="cari" placeholder="Cari nama kos, kota, atau alamat..."
                        class="w-full bg-transparent text-sm text-gray-900 placeholder-gray-400 border-0 focus:ring-0 focus:outline-none p-0">
                </div>
                <button x-data="{ show: false }" @click="show = !show"
                    class="shrink-0 flex items-center justify-center h-10 w-10 rounded-xl bg-gray-100 text-gray-500 hover:bg-gray-200 transition relative">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" /></svg>
                    @if ($kota || $hargaMax || $kapasitas > 1)
                        <span class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-teal-600 text-[9px] font-bold text-white flex items-center justify-center">
                            {{ collect([$kota, $hargaMax, $kapasitas > 1 ? 1 : null])->filter()->count() }}
                        </span>
                    @endif
                </button>
            </div>

            <!-- Filter Panel (toggle) -->
            <div x-data="{ open: false }">
                <button @click="open = !open" class="mt-2 flex items-center gap-1 text-xs font-medium text-gray-500 hover:text-teal-600 transition">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" /></svg>
                    Filter
                    <svg class="h-3 w-3 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                </button>

                <div x-show="open" x-cloak x-transition class="mt-3 bg-gray-50 rounded-xl p-3 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Kota</label>
                            <select wire:model.live="kota" class="w-full rounded-lg border-gray-200 text-xs focus:ring-teal-500 focus:border-teal-500 bg-white">
                                <option value="">Semua kota</option>
                                @foreach ($daftarKota as $k)
                                    <option value="{{ $k }}">{{ $k }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Harga Maks</label>
                            <select wire:model.live="hargaMax" class="w-full rounded-lg border-gray-200 text-xs focus:ring-teal-500 focus:border-teal-500 bg-white">
                                <option value="">Semua harga</option>
                                <option value="500000"><= Rp500rb</option>
                                <option value="750000"><= Rp750rb</option>
                                <option value="1000000"><= Rp1 juta</option>
                                <option value="1500000"><= Rp1,5 juta</option>
                                <option value="2000000"><= Rp2 juta</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Kapasitas Minimal</label>
                        <select wire:model.live="kapasitas" class="w-full rounded-lg border-gray-200 text-xs focus:ring-teal-500 focus:border-teal-500 bg-white">
                            <option value="1">1 orang</option>
                            <option value="2">2 orang</option>
                            <option value="3">3 orang</option>
                        </select>
                    </div>
                    @if ($kota || $hargaMax || $kapasitas > 1)
                        <button wire:click="$set('kota', ''); $set('hargaMax', null); $set('kapasitas', 1)"
                            class="text-xs font-medium text-rose-500 hover:text-rose-600">
                            Reset Filter
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Iklan Partner -->
    <div class="max-w-7xl mx-auto px-4 pt-4">
        <x-promo-ads />
    </div>

    <div class="max-w-7xl mx-auto px-4 py-4">
        <!-- Result Count -->
        <p class="text-xs text-gray-500 mb-3">
            Menampilkan <span class="font-semibold text-gray-800">{{ $propertis->count() }}</span> kos
            @if ($kota) di <span class="font-semibold text-teal-600">{{ $kota }}</span> @endif
        </p>

        <!-- Cards - Mobile-first list layout -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($propertis as $properti)
                <a href="{{ route('kos.detail', $properti) }}" wire:navigate
                   class="group bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 overflow-hidden hover:shadow-md hover:ring-teal-200 transition-all duration-200">
                    <div class="flex sm:block">
                        <!-- Image -->
                        <div class="relative h-32 sm:h-44 w-28 sm:w-full shrink-0 bg-gradient-to-br from-teal-100 via-emerald-100 to-cyan-100">
                            @if ($properti->foto)
                                <img src="{{ asset('storage/' . $properti->foto) }}" alt="{{ $properti->nama }}"
                                     class="h-full w-full object-cover group-hover:scale-105 transition duration-300" loading="lazy">
                            @else
                                <div class="h-full w-full flex items-center justify-center">
                                    <svg class="h-10 w-10 sm:h-14 sm:w-14 text-teal-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
                                </div>
                            @endif
                            @if ($properti->kamar_tersedia > 0)
                                <span class="absolute top-2 right-2 inline-flex items-center rounded-full bg-emerald-600 px-1.5 py-0.5 text-[9px] font-bold text-white shadow-sm">
                                    {{ $properti->kamar_tersedia }} Kamar
                                </span>
                            @endif
                        </div>

                        <!-- Info -->
                        <div class="flex-1 p-3 sm:p-4 flex flex-col justify-between">
                            <div>
                                <h3 class="text-sm sm:text-base font-bold text-gray-900 group-hover:text-teal-600 transition line-clamp-1">{{ $properti->nama }}</h3>
                                <p class="mt-0.5 text-xs text-gray-500 flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                                    {{ $properti->kota ?? $properti->alamat ?? 'Lokasi belum diisi' }}
                                </p>
                                @if ($properti->fasilitas)
                                    <div class="mt-1.5 flex flex-wrap gap-1">
                                        @foreach (array_slice(array_filter(array_map('trim', explode(',', $properti->fasilitas))), 0, 3) as $f)
                                            <span class="inline-flex items-center rounded bg-teal-50 px-1.5 py-0.5 text-[9px] font-medium text-teal-700">{{ $f }}</span>
                                        @endforeach
                                        @if (count(array_filter(array_map('trim', explode(',', $properti->fasilitas)))) > 3)
                                            <span class="inline-flex items-center rounded bg-gray-100 px-1.5 py-0.5 text-[9px] font-medium text-gray-500">+{{ count(array_filter(array_map('trim', explode(',', $properti->fasilitas)))) - 3 }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="mt-2 pt-2 border-t border-gray-100 flex items-end justify-between">
                                <span class="text-[10px] text-gray-400 font-medium">Mulai dari</span>
                                @php
                                    $hargaTampil = $properti->harga ?? $properti->harga_termurah;
                                    $periode = $properti->jenis_harga ?? 'bulanan';
                                @endphp
                                <span class="text-sm sm:text-base font-extrabold text-teal-600">
                                    @if ($hargaTampil)
                                        Rp{{ number_format($hargaTampil, 0, ',', '.') }}<span class="text-[10px] font-medium text-gray-400">/{{ $periode === 'harian' ? 'hari' : 'bln' }}</span>
                                    @else
                                        <span class="text-xs font-medium text-gray-400">Penuh</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-16 text-center">
                    <div class="mx-auto h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                        <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    </div>
                    <p class="text-gray-500 font-medium text-sm">Belum ada kos yang cocok.</p>
                    <p class="mt-1 text-xs text-gray-400">Coba ubah kata kunci atau filter pencarian.</p>
                </div>
            @endforelse
        </div>

        <!-- CTA untuk pemilik -->
        @guest
            <div class="mt-10 bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-600 rounded-2xl p-6 sm:p-10 text-center">
                <h2 class="text-lg sm:text-2xl font-extrabold text-white">Punya Kos? Daftar Sekarang</h2>
                <p class="mt-2 text-teal-100 text-xs sm:text-sm max-w-md mx-auto">
                    Daftarkan kos Anda gratis, kelola kamar, dan balas chat pencari kos.
                </p>
                <a href="{{ route('register') }}" wire:navigate
                   class="mt-4 inline-flex items-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-teal-600 hover:bg-teal-50 transition shadow-lg">
                    Daftar sebagai Pemilik Kos
                </a>
            </div>
        @endguest
    </div>
</div>
