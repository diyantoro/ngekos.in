<?php

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Properti;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.publik')] class extends Component
{
    public Properti $properti;

    public ?string $pesan = null;

    public ?string $galat = null;

    public ?string $rencanaMasuk = null;

    public array $durasiKamar = [];

    public function with(): array
    {
        $this->properti->loadCount([
            'kamars as total_kamar',
            'kamars as kamar_tersedia' => fn ($q) => $q->where('status', 'tersedia'),
        ]);

        return [
            'kamars' => Kamar::where('properti_id', $this->properti->id)->orderBy('nama')->get(),
        ];
    }

    public function pesanKamar(int $kamarId): void
    {
        if (! auth()->check() || ! auth()->user()->hasRole('anak_kos')) {
            $this->galat = 'Silakan masuk sebagai Anak Kos untuk memesan kamar.';

            return;
        }

        // Rencana masuk opsional — boleh diatur belakangan.
        $rencana = trim((string) $this->rencanaMasuk);

        if ($rencana !== '') {
            try {
                $masuk = \Illuminate\Support\Carbon::parse($rencana)->startOfDay();
            } catch (\Throwable) {
                $this->galat = 'Format tanggal masuk tidak valid.';

                return;
            }

            if ($masuk->lessThan(\Illuminate\Support\Carbon::today())) {
                $this->galat = 'Tanggal masuk tidak boleh di masa lalu.';

                return;
            }
        }

        $kamar = Kamar::where('id', $kamarId)->where('properti_id', $this->properti->id)->where('status', 'tersedia')->first();

        if (! $kamar) {
            $this->galat = 'Kamar tidak tersedia lagi.';

            return;
        }

        $sudahAda = Booking::where('anak_kos_id', auth()->id())
            ->where('kamar_id', $kamarId)
            ->whereIn('status', ['menunggu', 'disetujui'])
            ->exists();

        if ($sudahAda) {
            $this->galat = 'Kamu sudah memiliki booking aktif untuk kamar ini.';

            return;
        }

        $harian = $kamar->jenis_harga === 'harian';
        $durasi = max(1, (int) ($this->durasiKamar[$kamarId] ?? 1));
        $durasi = min($durasi, $harian ? 30 : 12);

        Booking::create([
            'anak_kos_id' => auth()->id(),
            'kamar_id' => $kamarId,
            'tanggal_booking' => now()->toDateString(),
            'tanggal_masuk' => $rencana !== '' ? $rencana : null,
            'durasi_bulan' => $harian ? null : $durasi,
            'durasi_hari' => $harian ? $durasi : null,
            'status' => 'menunggu',
            'catatan' => null,
        ]);

        $teksDurasi = $harian ? "{$durasi} hari" : "{$durasi} bulan";
        $teksMasuk = $rencana !== ''
            ? 'untuk masuk '.\Illuminate\Support\Carbon::parse($rencana)->translatedFormat('d M Y')
            : '(tanggal masuk bisa diatur nanti dari dashboard)';

        $this->pesan = "Booking kamar {$kamar->nama} di {$this->properti->nama} berhasil diajukan ({$teksDurasi}) {$teksMasuk}. Menunggu persetujuan pemilik kos.";
    }
}; ?>

<div>
    <!-- Cover -->
    <div class="relative h-56 sm:h-72 bg-gradient-to-br from-teal-100 via-emerald-100 to-cyan-100">
        @if ($properti->foto)
            <img src="{{ asset('storage/' . $properti->foto) }}" alt="{{ $properti->nama }}" class="h-full w-full object-cover">
        @else
            <div class="h-full w-full flex items-center justify-center">
                <svg class="h-20 w-20 text-teal-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18-8.25V21m-1.5-8.25v-3.75a2.25 2.25 0 00-2.25-2.25h-1.5m-1.5 0V3.545c0-.621-.504-1.125-1.125-1.125H8.25c-.621 0-1.125.504-1.125 1.125v7.5" /></svg>
            </div>
        @endif
        <a href="{{ route('kos.index') }}" wire:navigate
           class="absolute top-4 left-4 inline-flex items-center gap-1.5 rounded-lg bg-white/90 backdrop-blur px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-white shadow-sm">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
            Kembali
        </a>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if ($pesan)
            <x-notifikasi-popup :pesan="$pesan" judul="Berhasil!" />
        @endif

        @if ($galat)
            <div class="mb-4 flex items-center justify-between gap-3 rounded-xl bg-rose-50 ring-1 ring-rose-200 px-4 py-3 text-sm text-rose-800">
                <span>{{ $galat }}</span>
                <button wire:click="$set('galat', null)" class="text-rose-500 hover:text-rose-700 font-bold">&times;</button>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6 sm:p-8 -mt-10 relative">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900">{{ $properti->nama }}</h1>
                    <p class="mt-1 text-sm text-gray-500 flex items-center gap-1">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                        {{ $properti->alamat ?? $properti->kota ?? 'Lokasi belum diisi' }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <x-status-badge :status="$properti->status" />
                    <span class="inline-flex items-center rounded-full bg-teal-50 px-2.5 py-0.5 text-xs font-medium text-teal-700 ring-1 ring-inset ring-teal-200">
                        {{ $properti->kamar_tersedia }} dari {{ $properti->total_kamar }} kamar tersedia
                    </span>
                </div>
            </div>

            <dl class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <div class="rounded-xl bg-gray-50 p-4">
                    <dt class="text-xs font-semibold text-gray-500 uppercase">Harga Mulai</dt>
                    <dd class="mt-1 text-lg font-extrabold text-teal-600">
                        @php
                            $termurah = $properti->harga ?? $properti->kamars()->where('status', 'tersedia')->min('harga_sewa_bulanan');
                            $periode = $properti->jenis_harga ?? 'bulanan';
                        @endphp
                        @if ($termurah)
                            Rp{{ number_format($termurah, 0, ',', '.') }}<span class="text-xs font-medium text-gray-400">/{{ $periode === 'harian' ? 'hari' : 'bulan' }}</span>
                        @else
                            <span class="text-sm font-medium text-gray-400">Semua terisi</span>
                        @endif
                    </dd>
                </div>
                <div class="rounded-xl bg-gray-50 p-4">
                    <dt class="text-xs font-semibold text-gray-500 uppercase">Denda Terlambat</dt>
                    <dd class="mt-1 text-base font-bold text-gray-900">
                        @if ($properti->denda_per_hari)
                            Rp{{ number_format($properti->denda_per_hari, 0, ',', '.') }}<span class="text-xs font-medium text-gray-400">/hari</span>
                        @else
                            <span class="text-sm font-medium text-gray-400">Tidak ada</span>
                        @endif
                    </dd>
                </div>
                <div class="rounded-xl bg-gray-50 p-4">
                    <dt class="text-xs font-semibold text-gray-500 uppercase">Kontak Pemilik</dt>
                    <dd class="mt-1 text-base font-bold text-gray-900">{{ $properti->pemilik?->no_hp ?? '-' }}</dd>
                </div>
            </dl>

            @if (! auth()->check() || auth()->user()->hasRole('anak_kos'))
                <div class="mt-6 rounded-xl bg-teal-50 ring-1 ring-teal-100 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Ada pertanyaan soal kos ini?</p>
                        <p class="text-xs text-gray-500">Tanya langsung pemiliknya lewat pesan &mdash; tanya fasilitas, aturan, atau ketersediaan kamar.</p>
                    </div>
                    @if (auth()->check())
                        <a href="{{ route('chat.room', ['properti' => $properti->id]) }}" wire:navigate
                           class="shrink-0 inline-flex items-center justify-center gap-1.5 rounded-lg bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-500 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                            Tanya Pemilik
                        </a>
                    @else
                        <a href="{{ route('login') }}" wire:navigate
                           class="shrink-0 inline-flex items-center justify-center rounded-lg border border-teal-200 bg-white px-5 py-2.5 text-sm font-semibold text-teal-600 hover:bg-teal-100 transition">
                            Masuk untuk Bertanya
                        </a>
                    @endif
                </div>
            @endif

            @if ($properti->deskripsi)
                <div class="mt-6">
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Deskripsi</h2>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ $properti->deskripsi }}</p>
                </div>
            @endif

            @if ($properti->fasilitas)
                <div class="mt-6">
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Fasilitas</h2>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach (array_filter(array_map('trim', explode(',', $properti->fasilitas))) as $fasilitas)
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                {{ $fasilitas }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($properti->aturan)
                <div class="mt-6">
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Aturan Kos</h2>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ $properti->aturan }}</p>
                </div>
            @endif
        </div>

        <!-- Kamar List -->
        <div class="mt-8">
            <h2 class="text-lg font-bold text-gray-900">Daftar Kamar</h2>
            <p class="text-sm text-gray-500">Tentukan rencana tinggalmu, pilih kamar yang tersedia, lalu ajukan booking.</p>

            @if (! auth()->check() || auth()->user()->hasRole('anak_kos'))
                <div class="mt-4 bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-4 sm:p-5">
                    <p class="text-sm font-semibold text-gray-900">Rencana Tinggal</p>
                    <p class="mt-0.5 text-xs text-gray-400">Tanggal masuk boleh dikosongkan dulu &mdash; bisa diatur kapan saja dari dashboard setelah booking dibuat.</p>
                    <div class="mt-3">
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal Masuk (opsional)</label>
                        <input type="date" wire:model="rencanaMasuk" min="{{ now()->toDateString() }}"
                            max="{{ now()->addMonthsNoOverflow(6)->toDateString() }}"
                            class="w-full sm:w-64 rounded-lg border-gray-300 text-sm focus:ring-teal-500 focus:border-teal-500">
                        @error('rencanaMasuk') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            @endif

            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse ($kamars as $kamar)
                    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 overflow-hidden flex flex-col">
                        <div class="h-36 bg-gradient-to-br from-gray-100 to-gray-50">
                            @if ($kamar->foto)
                                <img src="{{ asset('storage/' . $kamar->foto) }}" alt="Kamar {{ $kamar->nama }}" class="h-full w-full object-cover">
                            @else
                                <div class="h-full w-full flex items-center justify-center">
                                    <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" /></svg>
                                </div>
                            @endif
                        </div>
                        <div class="p-4 flex-1 flex flex-col">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="text-base font-bold text-gray-900">Kamar {{ $kamar->nama }}</h3>
                                <x-status-badge :status="$kamar->status" />
                            </div>
                            <p class="mt-1 text-sm text-gray-500">{{ $kamar->kapasitas }} orang</p>
                            <p class="mt-2 text-lg font-extrabold text-teal-600">
                                Rp{{ number_format($kamar->harga_sewa_bulanan, 0, ',', '.') }}
                                <span class="text-xs font-medium text-gray-400">/{{ $kamar->jenis_harga === 'harian' ? 'hari' : 'bulan' }}</span>
                            </p>
                            <div class="mt-auto pt-4">
                                @if ($kamar->status === 'tersedia')
                                    @auth
                                        @if (auth()->user()->hasRole('anak_kos'))
                                            <div class="mb-2">
                                                <label class="block text-xs font-semibold text-gray-500 mb-1">Durasi</label>
                                                <select wire:model="durasiKamar.{{ $kamar->id }}"
                                                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-teal-500 focus:border-teal-500">
                                                    @foreach (range(1, $kamar->jenis_harga === 'harian' ? 30 : 12) as $n)
                                                        <option value="{{ $n }}">{{ $n }} {{ $kamar->jenis_harga === 'harian' ? 'hari' : 'bulan' }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button wire:click="pesanKamar({{ $kamar->id }})" wire:loading.attr="disabled"
                                                class="w-full inline-flex items-center justify-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500 transition">
                                                Pesan Kamar
                                            </button>
                                        @else
                                            <a href="{{ route('dashboard') }}" wire:navigate
                                               class="w-full inline-flex items-center justify-center rounded-lg border border-teal-200 bg-teal-50 px-4 py-2 text-sm font-semibold text-teal-600 hover:bg-teal-100 transition">
                                                Kelola dari Dashboard
                                            </a>
                                        @endif
                                    @else
                                        <a href="{{ route('login') }}" wire:navigate
                                           class="w-full inline-flex items-center justify-center rounded-lg border border-teal-200 bg-teal-50 px-4 py-2 text-sm font-semibold text-teal-600 hover:bg-teal-100 transition">
                                            Masuk untuk Pesan
                                        </a>
                                    @endauth
                                @else
                                    <span class="block w-full text-center rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-400">Tidak tersedia</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="col-span-full py-10 text-center text-sm text-gray-400">Belum ada kamar terdaftar di kos ini.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>