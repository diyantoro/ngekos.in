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
    <!-- Hero -->
    <section class="bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20">
            <div class="max-w-2xl">
                <h1 class="text-3xl sm:text-4xl font-extrabold text-white leading-tight">
                    Temukan Kos Impianmu,
                    <span class="text-teal-200">Cepat &amp; Mudah</span>
                </h1>
                <p class="mt-3 text-teal-100">
                    Ribuan kamar kos dari berbagai pemilik, satu platform. Cari berdasarkan kota, harga, dan kapasitas.
                </p>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <!-- Filter Bar -->
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-4 sm:p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Cari Kos</label>
                    <input type="text" wire:model.live.debounce.300ms="cari" placeholder="Nama kos, kota, atau alamat..."
                        class="w-full rounded-lg border-gray-300 text-sm focus:ring-teal-500 focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Kota</label>
                    <select wire:model.live="kota" class="w-full rounded-lg border-gray-300 text-sm focus:ring-teal-500 focus:border-teal-500">
                        <option value="">Semua kota</option>
                        @foreach ($daftarKota as $k)
                            <option value="{{ $k }}">{{ $k }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Harga Maks / Bulan</label>
                    <select wire:model.live="hargaMax" class="w-full rounded-lg border-gray-300 text-sm focus:ring-teal-500 focus:border-teal-500">
                        <option value="">Semua harga</option>
                        <option value="500000">&le; Rp500rb</option>
                        <option value="750000">&le; Rp750rb</option>
                        <option value="1000000">&le; Rp1 juta</option>
                        <option value="1500000">&le; Rp1,5 juta</option>
                        <option value="2000000">&le; Rp2 juta</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Kapasitas Minimal</label>
                    <select wire:model.live="kapasitas" class="w-full rounded-lg border-gray-300 text-sm focus:ring-teal-500 focus:border-teal-500">
                        <option value="1">1 orang</option>
                        <option value="2">2 orang</option>
                        <option value="3">3 orang</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Result Count -->
        <p class="text-sm text-gray-500">
            Menampilkan <span class="font-semibold text-gray-800">{{ $propertis->count() }}</span> kos
        </p>

        <!-- Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse ($propertis as $properti)
                <a href="{{ route('kos.detail', $properti) }}" wire:navigate
                   class="group bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 overflow-hidden hover:shadow-md hover:ring-teal-200 transition">
                    <div class="relative h-44 bg-gradient-to-br from-teal-100 via-emerald-100 to-cyan-100">
                        @if ($properti->foto)
                            <img src="{{ asset('storage/' . $properti->foto) }}" alt="{{ $properti->nama }}"
                                 class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
                        @else
                            <div class="h-full w-full flex items-center justify-center">
                                <svg class="h-14 w-14 text-teal-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18-8.25V21m-1.5-8.25v-3.75a2.25 2.25 0 00-2.25-2.25h-1.5m-1.5 0V3.545c0-.621-.504-1.125-1.125-1.125H8.25c-.621 0-1.125.504-1.125 1.125v7.5" /></svg>
                            </div>
                        @endif
                        @if ($properti->kamar_tersedia > 0)
                            <span class="absolute top-3 right-3 inline-flex items-center rounded-full bg-emerald-600 px-2.5 py-0.5 text-xs font-semibold text-white">
                                {{ $properti->kamar_tersedia }} kamar tersedia
                            </span>
                        @endif
                    </div>
                    <div class="p-4 sm:p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-gray-900 truncate group-hover:text-teal-600 transition">{{ $properti->nama }}</h3>
                                <p class="mt-0.5 text-sm text-gray-500 flex items-center gap-1">
                                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                                    {{ $properti->kota ?? $properti->alamat ?? 'Lokasi belum diisi' }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                            @php
                                $hargaTampil = $properti->harga ?? $properti->harga_termurah;
                                $periode = $properti->jenis_harga ?? 'bulanan';
                            @endphp
                            <span class="text-sm text-gray-500">Mulai dari</span>
                            <span class="text-base font-extrabold text-teal-600">
                                @if ($hargaTampil)
                                    Rp{{ number_format($hargaTampil, 0, ',', '.') }}<span class="text-xs font-medium text-gray-400">/{{ $periode === 'harian' ? 'hari' : 'bulan' }}</span>
                                @else
                                    <span class="text-sm font-medium text-gray-400">Kamar terisi</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-16 text-center">
                    <div class="mx-auto h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                        <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    </div>
                    <p class="text-gray-500 font-medium">Belum ada kos yang cocok dengan pencarianmu.</p>
                    <p class="mt-1 text-sm text-gray-400">Coba ubah kata kunci atau filter pencarian.</p>
                </div>
            @endforelse
        </div>

        <!-- CTA untuk pemilik -->
        @guest
            <div class="mt-10 bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-600 rounded-2xl p-8 sm:p-10 text-center">
                <h2 class="text-2xl font-extrabold text-white">Punya Kos? Tambah Penghuni Lebih Cepat</h2>
                <p class="mt-2 text-teal-100 max-w-xl mx-auto">
                    Daftarkan kos Anda secara gratis, kelola kamar, dan terima booking langsung dari pencari kos.
                </p>
                <a href="{{ route('register') }}" wire:navigate
                   class="mt-5 inline-flex items-center rounded-lg bg-white px-6 py-3 text-sm font-bold text-teal-600 hover:bg-teal-50 transition">
                    Daftar sebagai Pemilik Kos
                </a>
            </div>
        @endguest
    </div>
</div>