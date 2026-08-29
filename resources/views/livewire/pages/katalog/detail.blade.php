<?php

use App\Models\Kamar;
use App\Models\Properti;
use App\Services\PenyewaanService;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.publik')] class extends Component
{
    public Properti $properti;

    public ?string $galat = null;

    public ?string $pesan = null;

    public ?int $modalKamarId = null;

    public string $tanggalMasuk = '';

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
        if (! auth()->user()?->hasRole('anak_kos')) {
            $this->galat = 'Hanya akun pencari kos (anak kos) yang dapat menyewa kamar.';
            $this->pesan = null;

            return;
        }

        $kamar = Kamar::where('id', $kamarId)->where('properti_id', $this->properti->id)->first();

        if (! $kamar || $kamar->status !== 'tersedia') {
            $this->galat = 'Kamar tidak tersedia saat ini.';
            $this->pesan = null;

            return;
        }

        $this->galat = null;
        $this->pesan = null;
        $this->modalKamarId = $kamarId;
        $this->tanggalMasuk = today()->toDateString();
    }

    public function tutupModalSewa(): void
    {
        $this->modalKamarId = null;
        $this->tanggalMasuk = '';
        $this->resetValidation();
    }

    public function konfirmasiSewa(): void
    {
        if (! auth()->user()?->hasRole('anak_kos')) {
            $this->resetForm();
            $this->galat = 'Hanya akun pencari kos (anak kos) yang dapat menyewa kamar.';

            return;
        }

        $this->validate([
            'tanggalMasuk' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:' . today()->addMonths(3)->toDateString()],
        ], [
            'tanggalMasuk.required' => 'Pilih tanggal masuk terlebih dahulu.',
            'tanggalMasuk.date' => 'Tanggal masuk tidak valid.',
            'tanggalMasuk.after_or_equal' => 'Tanggal masuk tidak boleh mundur dari hari ini.',
            'tanggalMasuk.before_or_equal' => 'Tanggal masuk maksimal 3 bulan ke depan.',
        ]);

        $kamar = Kamar::with('properti')
            ->where('id', $this->modalKamarId)
            ->where('properti_id', $this->properti->id)
            ->first();

        if (! $kamar) {
            $this->resetForm();
            $this->galat = 'Kamar tidak ditemukan.';

            return;
        }

        try {
            app(PenyewaanService::class)->sewaKamar(auth()->user(), $kamar, $this->tanggalMasuk);
        } catch (DomainException $e) {
            $this->resetForm();
            $this->galat = $e->getMessage();

            return;
        }

        $this->pesan = 'Kamar '.$kamar->nama.' berhasil dipesan. Rencana masuk: '
            .Carbon::parse($this->tanggalMasuk)->locale('id')->translatedFormat('d F Y')
            .'. Kamar langsung terkunci untukmu.';
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->modalKamarId = null;
        $this->tanggalMasuk = '';
        $this->resetValidation();
    }
}; ?>

<div>
    <!-- Cover Image -->
    <div class="relative h-56 sm:h-72 bg-gradient-to-br from-teal-100 via-emerald-100 to-cyan-100">
        @if ($properti->foto)
            <img src="{{ asset('storage/' . $properti->foto) }}" alt="{{ $properti->nama }}" class="h-full w-full object-cover" loading="lazy">
        @else
            <div class="h-full w-full flex items-center justify-center">
                <svg class="h-20 w-20 text-teal-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
            </div>
        @endif
        <a href="{{ route('kos.index') }}" wire:navigate
           class="absolute top-4 left-4 inline-flex items-center gap-1.5 rounded-xl bg-black/40 backdrop-blur-sm px-3 py-2 text-sm font-medium text-white hover:bg-black/60 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
            Kembali
        </a>
        @auth
            <a href="{{ route('chat.room', ['properti' => $properti->id]) }}" wire:navigate
               class="absolute top-4 right-4 inline-flex items-center gap-1.5 rounded-xl bg-black/40 backdrop-blur-sm px-3 py-2 text-sm font-medium text-white hover:bg-black/60 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                Chat
            </a>
        @endauth
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if ($galat)
            <div class="mb-4 flex items-center justify-between gap-3 rounded-xl bg-rose-50 ring-1 ring-rose-200 px-4 py-3 text-sm text-rose-800">
                <span>{{ $galat }}</span>
                <button wire:click="$set('galat', null)" class="text-rose-500 hover:text-rose-700 font-bold">&times;</button>
            </div>
        @endif

        @if ($pesan)
            <div class="mb-4 flex items-center justify-between gap-3 rounded-xl bg-emerald-50 ring-1 ring-emerald-200 px-4 py-3 text-sm text-emerald-800">
                <span>{{ $pesan }}</span>
                <button wire:click="$set('pesan', null)" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
            </div>
        @endif

        <!-- Info Card -->
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-4 sm:p-6 -mt-8 relative z-10">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900">{{ $properti->nama }}</h1>
                    <p class="mt-1 text-sm text-gray-500 flex items-center gap-1">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                        {{ $properti->alamat ?? $properti->kota ?? 'Lokasi belum diisi' }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <x-status-badge :status="$properti->status" />
                    <span class="inline-flex items-center rounded-full bg-teal-50 px-2.5 py-0.5 text-xs font-medium text-teal-700 ring-1 ring-inset ring-teal-200">
                        {{ $properti->kamar_tersedia }}/{{ $properti->total_kamar }} tersedia
                    </span>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-3 gap-3">
                @php
                    $termurah = $properti->kamars()->where('status', 'tersedia')->min('harga_sewa_bulanan');
                    $periode = 'bulanan';
                @endphp
                <div class="rounded-xl bg-gray-50 p-3 text-center">
                    <p class="text-[10px] font-semibold text-gray-500 uppercase">Harga Mulai</p>
                    <p class="mt-1 text-sm sm:text-base font-extrabold text-teal-600">
                        @if ($termurah)
                            Rp{{ number_format($termurah, 0, ',', '.') }}<span class="text-[10px] font-medium text-gray-400">/{{ $periode === 'harian' ? 'hari' : 'bln' }}</span>
                        @else
                            <span class="text-xs font-medium text-gray-400">Penuh</span>
                        @endif
                    </p>
                </div>
                <div class="rounded-xl bg-gray-50 p-3 text-center">
                    <p class="text-[10px] font-semibold text-gray-500 uppercase">Denda</p>
                    <p class="mt-1 text-sm font-bold text-gray-900">
                        @if ($properti->denda_per_hari)
                            Rp{{ number_format($properti->denda_per_hari, 0, ',', '.') }}<span class="text-[10px] text-gray-400">/hr</span>
                        @else
                            <span class="text-xs text-gray-400">Tidak ada</span>
                        @endif
                    </p>
                </div>
                <div class="rounded-xl bg-gray-50 p-3 text-center">
                    <p class="text-[10px] font-semibold text-gray-500 uppercase">Kontak</p>
                    <p class="mt-1 text-sm font-bold text-gray-900 truncate">{{ $properti->pemilik?->no_hp ?? '-' }}</p>
                </div>
            </div>

            @if (! auth()->check() || auth()->user()->hasRole('anak_kos'))
                <div class="mt-4 rounded-xl bg-teal-50 ring-1 ring-teal-100 p-3 sm:p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Punya pertanyaan?</p>
                        <p class="text-xs text-gray-500">Tanya langsung pemiliknya lewat chat.</p>
                    </div>
                    @if (auth()->check())
                        <a href="{{ route('chat.room', ['properti' => $properti->id]) }}" wire:navigate
                           class="shrink-0 inline-flex items-center justify-center gap-1.5 rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                            Tanya Pemilik
                        </a>
                    @else
                        <a href="{{ route('login') }}" wire:navigate
                           class="shrink-0 inline-flex items-center justify-center rounded-lg border border-teal-200 bg-white px-4 py-2 text-sm font-semibold text-teal-600 hover:bg-teal-100 transition">
                            Masuk untuk Chat
                        </a>
                    @endif
                </div>
            @endif

            @if ($properti->deskripsi)
                <div class="mt-4">
                    <h2 class="text-xs font-bold text-gray-900 uppercase tracking-wide">Deskripsi</h2>
                    <p class="mt-1.5 text-sm text-gray-600 leading-relaxed">{{ $properti->deskripsi }}</p>
                </div>
            @endif

            @if ($properti->fasilitas)
                <div class="mt-4">
                    <h2 class="text-xs font-bold text-gray-900 uppercase tracking-wide">Fasilitas</h2>
                    <div class="mt-2">
                        <x-facility-icons :fasilitas="$properti->fasilitas" />
                    </div>
                </div>
            @endif

            @if ($properti->aturan)
                <div class="mt-4">
                    <h2 class="text-xs font-bold text-gray-900 uppercase tracking-wide">Aturan Kos</h2>
                    <p class="mt-1.5 text-sm text-gray-600 leading-relaxed">{{ $properti->aturan }}</p>
                </div>
            @endif
        </div>

        <!-- Iklan Partner -->
        <div class="mt-6">
            <x-promo-ads />
        </div>

        <!-- Daftar Kamar -->
        <div class="mt-6">
            <h2 class="text-base sm:text-lg font-bold text-gray-900">Daftar Kamar</h2>
            <p class="text-xs sm:text-sm text-gray-500">Pilih kamar yang tersedia dan tanya pemiliknya lewat chat.</p>

            <div class="mt-3 space-y-3">
                @forelse ($kamars as $kamar)
                    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 overflow-hidden">
                        <div class="flex">
                            <div class="h-28 sm:h-36 w-24 sm:w-36 shrink-0 bg-gradient-to-br from-gray-100 to-gray-50">
                                @if ($kamar->foto)
                                    <img src="{{ asset('storage/' . $kamar->foto) }}" alt="Kamar {{ $kamar->nama }}" class="h-full w-full object-cover" loading="lazy">
                                @else
                                    <div class="h-full w-full flex items-center justify-center">
                                        <svg class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" /></svg>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 p-3 sm:p-4 flex flex-col justify-between">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <h3 class="text-sm sm:text-base font-bold text-gray-900">{{ $kamar->nama }}</h3>
                                        <p class="text-xs text-gray-500">{{ $kamar->kapasitas }} orang</p>
                                    </div>
                                    <x-status-badge :status="$kamar->status" />
                                </div>

                                <div class="mt-2 flex items-end justify-between">
                                    <p class="text-base sm:text-lg font-extrabold text-teal-600">
                                        Rp{{ number_format($kamar->harga_sewa_bulanan, 0, ',', '.') }}
                                        <span class="text-[10px] font-medium text-gray-400">/{{ $kamar->jenis_harga === 'harian' ? 'hari' : 'bulan' }}</span>
                                    </p>

                                    @if ($kamar->status === 'tersedia')
                                        @auth
                                            @if (auth()->user()->hasRole('anak_kos'))
                                                <div class="flex items-center gap-2">
                                                    <button wire:click="pesanKamar({{ $kamar->id }})" wire:loading.attr="disabled"
                                                        class="shrink-0 inline-flex items-center justify-center gap-1.5 rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-500 transition disabled:opacity-50">
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                                                        Sewa
                                                    </button>
                                                    <a href="{{ route('chat.room', ['properti' => $properti->id]) }}" wire:navigate
                                                        class="shrink-0 inline-flex items-center justify-center gap-1.5 rounded-lg border border-teal-200 bg-teal-50 px-3 py-1.5 text-xs font-semibold text-teal-600 hover:bg-teal-100 transition">
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                                                        Chat
                                                    </a>
                                                </div>
                                            @else
                                                <a href="{{ route('dashboard') }}" wire:navigate
                                                    class="shrink-0 inline-flex items-center justify-center rounded-lg border border-teal-200 bg-teal-50 px-3 py-1.5 text-xs font-semibold text-teal-600 hover:bg-teal-100 transition">
                                                    Kelola
                                                </a>
                                            @endif
                                        @else
                                            <a href="{{ route('login') }}" wire:navigate
                                                class="shrink-0 inline-flex items-center justify-center rounded-lg border border-teal-200 bg-teal-50 px-3 py-1.5 text-xs font-semibold text-teal-600 hover:bg-teal-100 transition">
                                                Masuk
                                            </a>
                                        @endauth
                                    @else
                                        <span class="text-xs font-medium text-gray-400">Terisi</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center">
                        <div class="mx-auto h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                        </div>
                        <p class="text-sm text-gray-400">Belum ada kamar terdaftar.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @if ($modalKamarId)
    @php
        $kamarModal = $kamars->firstWhere('id', $modalKamarId);
    @endphp
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
        <button type="button" wire:click="tutupModalSewa" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm cursor-default" tabindex="-1" aria-label="Tutup"></button>
        <div class="relative min-h-full flex items-end sm:items-center justify-center p-4">
            <div class="w-full sm:max-w-md bg-white rounded-2xl shadow-xl ring-1 ring-gray-100 overflow-hidden">
                <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-gray-100">
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-gray-900 truncate">Sewa Kamar {{ $kamarModal?->nama }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $properti->nama }} &middot; Rp{{ number_format($kamarModal?->harga_sewa_bulanan ?? 0, 0, ',', '.') }}/bulan</p>
                    </div>
                    <button type="button" wire:click="tutupModalSewa"
                        class="shrink-0 h-8 w-8 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center transition">&times;</button>
                </div>

                <form wire:submit="konfirmasiSewa" class="p-5 space-y-4">
                    <p class="text-xs text-gray-400">Kamar yang tersedia akan langsung terkunci untukmu — tanpa menunggu konfirmasi. Pilih tanggal kamu berencana masuk (maksimal 3 bulan ke depan).</p>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal Masuk</label>
                        <input type="date" wire:model="tanggalMasuk" min="{{ today()->toDateString() }}" max="{{ today()->addMonths(3)->toDateString() }}"
                            class="w-full rounded-lg border-gray-300 text-sm text-gray-700">
                        @error('tanggalMasuk') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex flex-col-reverse sm:flex-row gap-2 pt-1">
                        <button type="button" wire:click="tutupModalSewa" wire:loading.attr="disabled"
                            class="flex-1 inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="konfirmasiSewa"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-500 transition disabled:opacity-50">
                            Sewa Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
