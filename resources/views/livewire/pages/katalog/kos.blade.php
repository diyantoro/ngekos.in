<?php

use App\Models\Properti;
use App\Support\Koordinat;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.publik')] class extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $cari = '';

    #[Url(history: true)]
    public string $kota = '';

    #[Url(history: true)]
    public $hargaMax = null;

    #[Url(history: true)]
    public int $kapasitas = 1;

    public function updatedCari(): void
    {
        $this->resetPage();
    }

    public function updatedKota(): void
    {
        $this->resetPage();
    }

    public function updatedHargaMax($value): void
    {
        $this->hargaMax = ($value === '' || $value === null) ? null : (int) $value;
        $this->resetPage();
    }

    public function updatedKapasitas(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $query = Properti::query()
            ->where('status', 'aktif')
            ->select(['id', 'nama', 'kota', 'alamat', 'fasilitas', 'foto', 'harga', 'harga_mingguan', 'harga_harian', 'harga_asli', 'tipe_hunian'])
            ->with(['fotos:id,properti_id,path,urutan'])
            ->withCount(['kamars as total_kamar', 'kamars as kamar_tersedia' => fn ($q) => $q->where('status', 'tersedia'), 'ulasans as total_ulasan'])
            ->withAvg('ulasans as rating_ulasan', 'rating')
            ->withMin(['kamars as harga_termurah' => fn ($q) => $q->where('status', 'tersedia')->where('harga_sewa_bulanan', '>', 0)], 'harga_sewa_bulanan');

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
            'propertis' => $query->orderBy('nama')->paginate(12),
            'daftarKota' => cache()->remember('katalog.daftarKota', 3600, fn () => Properti::where('status', 'aktif')
                ->whereNotNull('kota')
                ->distinct()
                ->orderBy('kota')
                ->pluck('kota')
                ->all()),
            'markers' => cache()->remember('katalog.markers', 3600, fn () => Properti::where('status', 'aktif')
                ->get(['id', 'nama', 'kota', 'alamat', 'latitude', 'longitude'])
                ->map(function ($p) {
                    $titik = Koordinat::titik($p->kota, $p->latitude, $p->longitude);

                    return $titik ? [
                        'id' => $p->id,
                        'nama' => $p->nama,
                        'kota' => $p->kota,
                        'alamat' => $p->alamat,
                        'lat' => $titik[0],
                        'lng' => $titik[1],
                    ] : null;
                })
                ->filter()
                ->values()
                ->all()),
        ];
    }
}; ?>

<div x-data="{ tampilkanPeta: false }">
    <!-- Search + Filter: satu baris kompak -->
    <section class="bg-white dark:bg-gray-900 border-b border-stone-200 dark:border-gray-800 sticky top-14 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-2.5">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-[1fr_170px_150px_140px_auto] gap-2 items-center">
                <div class="col-span-2 sm:col-span-3 lg:col-span-1 flex items-center gap-2 bg-stone-100 dark:bg-gray-800 rounded-lg px-3 py-2 transition focus-within:bg-white dark:focus-within:bg-gray-800 focus-within:ring-2 focus-within:ring-brand-500/30">
                    <svg class="h-4 w-4 text-slate-400 dark:text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    <input type="text" wire:model.live.debounce.300ms="cari" placeholder="Cari nama kos, kota, atau alamat..."
                        class="w-full bg-transparent text-sm text-slate-900 dark:text-gray-100 placeholder-slate-400 dark:placeholder-gray-500 border-0 focus:ring-0 focus:outline-none p-0">
                </div>
                <select wire:model.live="kota" aria-label="Kota" class="rounded-lg border-gray-200 dark:border-gray-600 text-xs focus:ring-teal-500 focus:border-teal-500 bg-white dark:bg-gray-800 dark:text-gray-100 transition-colors py-2">
                    <option value="">Semua kota</option>
                    @foreach ($daftarKota as $k)
                        <option value="{{ $k }}">{{ $k }}</option>
                    @endforeach
                </select>
                <select wire:model.live="hargaMax" aria-label="Harga maksimal" class="rounded-lg border-gray-200 dark:border-gray-600 text-xs focus:ring-teal-500 focus:border-teal-500 bg-white dark:bg-gray-800 dark:text-gray-100 transition-colors py-2">
                    <option value="">Semua harga</option>
                    <option value="500000"><= Rp500rb</option>
                    <option value="750000"><= Rp750rb</option>
                    <option value="1000000"><= Rp1 juta</option>
                    <option value="1500000"><= Rp1,5 juta</option>
                    <option value="2000000"><= Rp2 juta</option>
                </select>
                <select wire:model.live="kapasitas" aria-label="Kapasitas minimal" class="rounded-lg border-gray-200 dark:border-gray-600 text-xs focus:ring-teal-500 focus:border-teal-500 bg-white dark:bg-gray-800 dark:text-gray-100 transition-colors py-2">
                    <option value="1">1 orang+</option>
                    <option value="2">2 orang+</option>
                    <option value="3">3 orang+</option>
                </select>
                @if ($kota || $hargaMax || $kapasitas > 1)
                    <button wire:click="$set('kota', ''); $set('hargaMax', null); $set('kapasitas', 1)"
                        class="justify-self-start lg:justify-self-auto inline-flex items-center gap-1 rounded-lg px-2 py-2 text-xs font-semibold text-rose-500 hover:text-rose-600 dark:text-rose-400 dark:hover:text-rose-300 transition-colors active:scale-95 whitespace-nowrap">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" /></svg>
                        Reset
                    </button>
                @endif
            </div>
        </div>
    </section>

    <!-- Iklan Partner (wire:ignore: carousel jalan via JS, jangan di-morph Livewire) -->
    <div class="max-w-7xl mx-auto px-4 pt-4" wire:ignore>
        <x-promo-ads />
    </div>

    <div class="max-w-7xl mx-auto px-4 py-4">
        <!-- Result Count + Toggle Peta -->
        <div class="flex items-center justify-between gap-3 mb-3">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Menampilkan <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $propertis->count() }}</span> kos
                @if ($kota) di <span class="font-semibold text-teal-600 dark:text-teal-400">{{ $kota }}</span> @endif
            </p>
            <button @click="tampilkanPeta = !tampilkanPeta; $nextTick(() => togglePetaNgekos(tampilkanPeta))"
                class="shrink-0 inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-xs font-semibold transition-all duration-200 active:scale-95 {{ count($markers) ? 'bg-teal-600 text-white hover:bg-teal-500 shadow-sm' : 'bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500' }}"
                @if (! count($markers)) disabled title="Belum ada koordinat" @endif>
                <svg x-show="!tampilkanPeta" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                <svg x-show="tampilkanPeta" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M9 21V9h6v12" /></svg>
                <span x-text="tampilkanPeta ? 'Lihat Daftar' : 'Lihat Peta'"></span>
            </button>
        </div>

        <!-- Peta Semua Kos -->
        <div x-show="tampilkanPeta" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="mb-5">
            <div class="rounded-2xl overflow-hidden shadow-card ring-1 ring-gray-100 dark:ring-gray-700">
                <div id="peta-kos" class="h-96 sm:h-[30rem] w-full bg-gray-100 dark:bg-gray-800"></div>
            </div>
            <p class="mt-2 text-[10px] text-gray-400 dark:text-gray-500 text-center">Peta menggunakan koordinat properti; jika belum diisi, titik diambil dari pusat kota.</p>
        </div>

        <!-- Cards - Mobile-first list layout -->
        <div wire:loading.remove class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($propertis as $properti)
                <a href="{{ route('kos.detail', $properti) }}" wire:navigate
                   class="group bg-white dark:bg-gray-800 rounded-xl border border-stone-200 dark:border-gray-700 overflow-hidden hover:shadow-card-hover hover:border-brand-200 transition">
                    <div class="flex sm:block">
                        <!-- Image -->
                        <div class="relative h-32 sm:h-44 w-28 sm:w-full shrink-0 bg-stone-200 dark:bg-gray-800">
                            @php $coverKos = $properti->fotoCover(); @endphp
                            @if ($coverKos)
                                <img src="{{ $coverKos }}" alt="{{ $properti->nama }}" loading="lazy" decoding="async"
                                     class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
                            @else
                                <div class="h-full w-full flex items-center justify-center">
                                    <svg class="h-10 w-10 sm:h-14 sm:w-14 text-teal-300 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
                                </div>
                            @endif
                            @if (count($properti->galeriUrls()) > 1)
                                <span class="absolute bottom-2 left-2 inline-flex items-center gap-1 rounded-full bg-black/50 px-1.5 py-0.5 text-[9px] font-bold text-white backdrop-blur-sm">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                                    {{ count($properti->galeriUrls()) }}
                                </span>
                            @endif
                            @if ($properti->kamar_tersedia > 0)
                                <span class="absolute top-2 right-2 inline-flex items-center rounded-full bg-brand-700 px-1.5 py-0.5 text-[9px] font-bold text-white">
                                    Sisa {{ $properti->kamar_tersedia }} kamar
                                </span>
                            @endif
                            @if ($properti->tipe_hunian)
                                <span class="absolute top-2 left-2 inline-flex items-center rounded px-1.5 py-0.5 text-[9px] font-bold text-white {{ $properti->tipe_hunian === 'putri' ? 'bg-rose-700' : ($properti->tipe_hunian === 'putra' ? 'bg-slate-700' : 'bg-brand-800') }}">
                                    {{ ucfirst($properti->tipe_hunian) }}
                                </span>
                            @endif
                        </div>

                        <!-- Info -->
                        <div class="flex-1 p-3 sm:p-4 flex flex-col justify-between">
                            <div>
                                <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-gray-100 group-hover:text-brand-800 dark:group-hover:text-brand-200 transition line-clamp-1">{{ $properti->nama }}</h3>
                                @if ($properti->total_ulasan > 0)
                                    <p class="mt-0.5 flex items-center gap-1 text-xs">
                                        <x-star-rating :rating="round($properti->rating_ulasan)" size="h-3 w-3" />
                                        <span class="font-semibold text-gray-700 dark:text-gray-300">{{ number_format($properti->rating_ulasan, 1, ',', '.') }}</span>
                                        <span class="text-gray-400 dark:text-gray-500">({{ $properti->total_ulasan }})</span>
                                    </p>
                                @endif
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                                    {{ $properti->kota ?? $properti->alamat ?? 'Lokasi belum diisi' }}
                                </p>
                                @if ($properti->fasilitas)
                                    <div class="mt-1.5 flex flex-wrap gap-1">
                                        @foreach (array_slice(array_filter(array_map('trim', explode(',', $properti->fasilitas))), 0, 3) as $f)
                                            <span class="inline-flex items-center rounded bg-brand-50 dark:bg-brand-500/10 px-1.5 py-0.5 text-[9px] font-medium text-brand-800 dark:text-brand-200">{{ $f }}</span>
                                        @endforeach
                                        @if (count(array_filter(array_map('trim', explode(',', $properti->fasilitas)))) > 3)
                                            <span class="inline-flex items-center rounded bg-stone-100 dark:bg-gray-700 px-1.5 py-0.5 text-[9px] font-medium text-slate-500 dark:text-gray-400">+{{ count(array_filter(array_map('trim', explode(',', $properti->fasilitas)))) - 3 }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700 flex items-end justify-between gap-2">
                                <span class="text-[10px] text-gray-400 dark:text-gray-500 font-medium">Mulai dari</span>
                                <div class="text-end">
                                    @php
                                        $hargaTampil = $properti->harga ?? $properti->harga_termurah;
                                        $adaDiskon = $properti->harga_asli && (float) $hargaTampil > 0 && $properti->harga_asli > $hargaTampil;
                                    @endphp
                                    @if ((float) $hargaTampil > 0)
                                        <x-harga-tiga-periode :bulanan="$hargaTampil" :mingguan="$properti->harga_mingguan" :harian="$properti->harga_harian" :asli="$adaDiskon ? $properti->harga_asli : null" />
                                    @else
                                        <span class="text-xs font-medium text-gray-400 dark:text-gray-500">Penuh</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-16 text-center">
                    <div class="mx-auto h-16 w-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-4">
                        <svg class="h-8 w-8 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 font-medium text-sm">Tidak menemukan kos?</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500 max-w-sm mx-auto">Coba ubah kata kunci, pilih kota lain, atau perbesar budget pencarian Anda.</p>
                    <button wire:click="$set('cari', ''); $set('kota', ''); $set('hargaMax', null); $set('kapasitas', 1)"
                        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-brand-700 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-800 transition">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" /></svg>
                        Reset Semua Filter
                    </button>
                </div>
            @endforelse
        </div>

        @if ($propertis->hasPages())
            <div wire:loading.remove class="mt-6">
                {{ $propertis->links() }}
            </div>
        @endif

        <x-skeleton type="card" count="6" target="cari, kota, hargaMax, kapasitas" class="sm:grid-cols-2 lg:grid-cols-3" />

        <!-- CTA untuk pemilik -->
        @guest
            <div class="mt-10 bg-brand-900 rounded-xl p-6 sm:p-10 text-center">
                <p class="text-xs font-semibold uppercase tracking-widest text-brand-200">Untuk pemilik kos</p>
                <h2 class="mt-2 text-lg sm:text-2xl font-bold tracking-tight text-white">Punya Kos? Daftarkan Sekarang</h2>
                <p class="mt-2 text-brand-100 text-xs sm:text-sm max-w-md mx-auto">
                    Daftarkan kos Anda gratis, kelola kamar, dan balas chat pencari kos.
                </p>
                <a href="{{ route('register') }}" wire:navigate
                   class="mt-4 inline-flex items-center rounded-lg bg-white px-5 py-2.5 text-sm font-semibold text-brand-900 hover:bg-brand-50 transition">
                    Daftar sebagai Pemilik Kos
                </a>
            </div>
        @endguest
    </div>

    @push('scripts')
        <script>
            let ngekosMap = null;
            let ngekosBounds = null;
            const dataPetaKos = @json($markers);

            async function fallbackPetaKos() {
                const el = document.getElementById('peta-kos');
                if (!el || !dataPetaKos.length) return;
                if (typeof window.buatPetaDaftarOsm === 'function') {
                    try {
                        await window.buatPetaDaftarOsm(el, dataPetaKos);
                        return;
                    } catch (e) {}
                }
                if (typeof window.pasangOsmEmbed === 'function') {
                    const sum = dataPetaKos.reduce((a, m) => ({ lat: a.lat + m.lat, lng: a.lng + m.lng }), { lat: 0, lng: 0 });
                    window.pasangOsmEmbed(el, sum.lat / dataPetaKos.length, sum.lng / dataPetaKos.length, 10, dataPetaKos);
                } else if (typeof window.pasangGoogleEmbed === 'function') {
                    const sum = dataPetaKos.reduce((a, m) => ({ lat: a.lat + m.lat, lng: a.lng + m.lng }), { lat: 0, lng: 0 });
                    window.pasangGoogleEmbed(el, sum.lat / dataPetaKos.length, sum.lng / dataPetaKos.length, dataPetaKos.length <= 1 ? 14 : 10);
                } else {
                    el.innerHTML = '<div class="h-full w-full flex items-center justify-center p-4 text-center text-sm text-gray-400">' +
                        'Peta tidak dapat dimuat saat ini.</div>';
                }
            }

            function inisialisasiPetaKos(percobaan) {
                const el = document.getElementById('peta-kos');
                if (!el || !dataPetaKos.length) return;
                if (el.offsetWidth === 0) {
                    if ((percobaan || 0) < 10) requestAnimationFrame(() => inisialisasiPetaKos((percobaan || 0) + 1));
                    return;
                }
                if (el._petaLeaflet) {
                    try { el._petaLeaflet.invalidateSize(); } catch (e) {}
                    return;
                }
                if (typeof google === 'undefined' || !google.maps) {
                    try { const r = fallbackPetaKos(); if (r && typeof r.catch === 'function') r.catch(() => {}); } catch (e) {}
                    return;
                }
                if (ngekosMap) {
                    requestAnimationFrame(() => {
                        google.maps.event.trigger(ngekosMap, 'resize');
                        if (ngekosBounds) ngekosMap.fitBounds(ngekosBounds);
                    });
                    return;
                }

                ngekosBounds = new google.maps.LatLngBounds();
                dataPetaKos.forEach((m) => ngekosBounds.extend({ lat: m.lat, lng: m.lng }));

                ngekosMap = new google.maps.Map(el, { mapTypeId: 'roadmap' });
                if (dataPetaKos.length === 1) {
                    ngekosMap.setCenter(ngekosBounds.getCenter());
                    ngekosMap.setZoom(14);
                } else {
                    ngekosMap.fitBounds(ngekosBounds);
                }

                const markers = dataPetaKos.map(function (m) {
                    const pemuat = new google.maps.Marker({ position: { lat: m.lat, lng: m.lng }, map: ngekosMap, title: m.nama });
                    const info = new google.maps.InfoWindow();
                    pemuat.addListener('click', () => {
                        const isi = '<strong>' + String(m.nama || '').replace(/</g, '&lt;') + '</strong><br>' +
                            (m.alamat ? String(m.alamat).replace(/</g, '&lt;') + ', ' : '') +
                            (m.kota ? String(m.kota).replace(/</g, '&lt;') : '') +
                            (m.id ? '<br><a href="/kos/' + m.id + '">Lihat detail</a>' : '');
                        info.setContent(isi);
                        info.open({ map: ngekosMap, anchor: pemuat });
                    });
                    return pemuat;
                });
                if (typeof window.pasangCluster === 'function') window.pasangCluster(markers, ngekosMap);
            }

            window.togglePetaNgekos = function (show) {
                const el = document.getElementById('peta-kos');
                if (!el || !show) return;
                requestAnimationFrame(() => {
                    inisialisasiPetaKos();
                    if (typeof window.loadNgekosMaps === 'function') window.loadNgekosMaps(inisialisasiPetaKos);
                });
            };

            // Didaftarkan sekali saja agar tidak menumpuk tiap navigasi SPA.
            if (!window.__kosPetaNavOn) {
                window.__kosPetaNavOn = true;
                document.addEventListener('livewire:navigated', () => {
                    try {
                        const wadah = document.getElementById('peta-kos');
                        if (wadah && typeof window.bersihkanWadahLeaflet === 'function') window.bersihkanWadahLeaflet(wadah);
                    } catch (e) {}
                    ngekosMap = null;
                    ngekosBounds = null;
                });
            }
            if (typeof window.loadNgekosMaps === 'function') window.loadNgekosMaps(inisialisasiPetaKos);
        </script>
    @endpush
</div>
