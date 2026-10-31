<?php

use App\Enums\BarangStatus;
use App\Models\BarangKos;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string $cari = '';

    public string $filterStatus = 'semua';

    public ?string $pesan = null;

    public function updatedCari(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function toggleStatus(int $id): void
    {
        $user = auth()->user();
        $barang = BarangKos::findOrFail($id);

        if (! $barang->isMilik($user)) {
            abort(403);
        }

        $baru = $barang->status === BarangStatus::Tersedia
            ? BarangStatus::Terjual
            : BarangStatus::Tersedia;

        $barang->update(['status' => $baru]);
        $this->pesan = "\"{$barang->nama}\" ditandai sebagai {$baru->label()}.";
    }

    public function hapus(int $id): void
    {
        $user = auth()->user();
        $barang = BarangKos::findOrFail($id);

        if (! $barang->isMilik($user)) {
            abort(403);
        }

        $nama = $barang->nama;
        $barang->delete();
        $this->pesan = "Iklan \"{$nama}\" berhasil dihapus.";
    }

    public function with(): array
    {
        $user = auth()->user();
        $bisaKelolaSemua = $user->hasAnyRole(['admin', 'super_admin']);

        $baseQuery = BarangKos::query();
        if (! $bisaKelolaSemua) {
            $baseQuery->where('user_id', $user->id);
        }

        $totalSemua = (clone $baseQuery)->count();
        $totalTersedia = (clone $baseQuery)->where('status', BarangStatus::Tersedia->value)->count();
        $totalTerjual = (clone $baseQuery)->where('status', BarangStatus::Terjual->value)->count();

        $query = (clone $baseQuery)->with(['user', 'properti:id,nama']);

        if ($this->cari) {
            $keyword = trim($this->cari);
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%")
                    ->orWhere('deskripsi', 'like', "%{$keyword}%")
                    ->orWhere('kota', 'like', "%{$keyword}%");
            });
        }

        if ($this->filterStatus && $this->filterStatus !== 'semua') {
            $query->where('status', $this->filterStatus);
        }

        $query->orderByDesc('created_at');

        return [
            'barangs' => $query->paginate(10),
            'totalSemua' => $totalSemua,
            'totalTersedia' => $totalTersedia,
            'totalTerjual' => $totalTerjual,
        ];
    }
}; ?>

<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- Header & CTA --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    {{ auth()->user()->hasRole('pemilik') ? 'Kelola Barang Jualan' : 'Barang Jualan Saya' }}
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                    Iklankan dan kelola perlengkapan kamar kos yang ingin Anda jual atau oper ke penghuni lain.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('barang.index') }}" wire:navigate
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-stone-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-bold text-slate-700 dark:text-gray-200 hover:bg-stone-50 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Lihat Pasar Kos
                </a>
                <a href="{{ route('barang.pasang') }}" wire:navigate
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-xs font-bold text-white transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Pasang Iklan Baru
                </a>
            </div>
        </div>

        @if ($pesan)
            <div class="rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 p-4 text-sm text-emerald-800 dark:text-emerald-300 flex items-center justify-between">
                <span>{{ $pesan }}</span>
                <button type="button" wire:click="$set('pesan', null)" class="text-emerald-700 hover:text-emerald-900 font-bold">✕</button>
            </div>
        @endif

        {{-- Ringkasan Kartu Statistik --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-900 border border-stone-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Iklan</p>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $totalSemua }}</span>
                    <span class="text-xs text-slate-500">barang</span>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 border border-stone-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Sedang Dijual</p>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $totalTersedia }}</span>
                    <span class="text-xs text-slate-500">tersedia</span>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 border border-stone-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Laku Terjual</p>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-2xl font-extrabold text-slate-800 dark:text-gray-200">{{ $totalTerjual }}</span>
                    <span class="text-xs text-slate-500">terjual</span>
                </div>
            </div>
        </div>

        {{-- Filter & Daftar --}}
        <div class="bg-white dark:bg-gray-900 border border-stone-200 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-stone-100 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                {{-- Tabs Status --}}
                <div class="flex items-center gap-1.5 overflow-x-auto text-xs">
                    <button type="button" wire:click="$set('filterStatus', 'semua')"
                            class="px-3.5 py-1.5 rounded-lg font-bold transition {{ $filterStatus === 'semua' ? 'bg-brand-700 text-white' : 'text-slate-600 dark:text-gray-300 hover:bg-stone-100 dark:hover:bg-gray-800' }}">
                        Semua ({{ $totalSemua }})
                    </button>
                    <button type="button" wire:click="$set('filterStatus', 'tersedia')"
                            class="px-3.5 py-1.5 rounded-lg font-bold transition {{ $filterStatus === 'tersedia' ? 'bg-brand-700 text-white' : 'text-slate-600 dark:text-gray-300 hover:bg-stone-100 dark:hover:bg-gray-800' }}">
                        Tersedia ({{ $totalTersedia }})
                    </button>
                    <button type="button" wire:click="$set('filterStatus', 'terjual')"
                            class="px-3.5 py-1.5 rounded-lg font-bold transition {{ $filterStatus === 'terjual' ? 'bg-brand-700 text-white' : 'text-slate-600 dark:text-gray-300 hover:bg-stone-100 dark:hover:bg-gray-800' }}">
                        Terjual ({{ $totalTerjual }})
                    </button>
                </div>

                {{-- Input Cari --}}
                <div class="relative w-full sm:w-64">
                    <input type="search" wire:model.live.debounce.300ms="cari" placeholder="Cari nama barang..."
                           class="w-full py-1.5 pl-8 pr-3 text-xs rounded-xl border border-stone-300 dark:border-gray-700 bg-stone-50 dark:bg-gray-800 text-slate-800 dark:text-gray-100 focus:ring-1 focus:ring-brand-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            {{-- List Barang --}}
            @if ($barangs->count() > 0)
                <div class="divide-y divide-stone-100 dark:divide-gray-800">
                    @foreach ($barangs as $item)
                        @php
                            $isTerjual = $item->status === \App\Enums\BarangStatus::Terjual;
                        @endphp
                        <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition hover:bg-stone-50/50 dark:hover:bg-gray-800/40 {{ $isTerjual ? 'opacity-80' : '' }}">
                            <div class="flex items-start gap-4 min-w-0">
                                {{-- Thumbnail Foto --}}
                                <a href="{{ route('barang.detail', $item) }}" wire:navigate class="shrink-0 relative block">
                                    <div class="h-16 w-16 sm:h-20 sm:w-20 rounded-xl overflow-hidden bg-stone-100 dark:bg-gray-800 border border-stone-200 dark:border-gray-700">
                                        <img src="{{ $item->fotoUrl() }}" alt="{{ $item->nama }}" class="h-full w-full object-cover">
                                    </div>
                                    @if ($isTerjual)
                                        <span class="absolute -bottom-1 -right-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-red-600 text-white">
                                            Terjual
                                        </span>
                                    @endif
                                </a>

                                {{-- Info Barang --}}
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $isTerjual ? 'bg-red-100 dark:bg-red-950/60 text-red-700 dark:text-red-400' : 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300' }}">
                                            {{ $item->status?->label() }}
                                        </span>
                                        <span class="text-[11px] font-semibold text-slate-400">
                                            {{ $item->kategori?->label() }} • {{ $item->kondisi?->label() }}
                                        </span>
                                    </div>

                                    <a href="{{ route('barang.detail', $item) }}" wire:navigate class="block mt-1">
                                        <h3 class="text-sm font-bold text-slate-900 dark:text-white hover:text-brand-700 dark:hover:text-brand-300 transition truncate">
                                            {{ $item->nama }}
                                        </h3>
                                    </a>

                                    <div class="mt-1 flex flex-wrap items-center gap-3 text-xs">
                                        <strong class="font-extrabold text-brand-800 dark:text-emerald-400">
                                            {{ $item->formattedHarga() }}
                                        </strong>
                                        <span class="text-slate-400">•</span>
                                        <span class="text-slate-500 dark:text-gray-400">{{ $item->kota }}</span>
                                        <span class="text-slate-400">•</span>
                                        <span class="text-slate-400">{{ number_format($item->dilihat) }}x dilihat</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Aksi --}}
                            <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                                <button type="button" wire:click="toggleStatus({{ $item->id }})"
                                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $isTerjual ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-amber-50 text-amber-800 hover:bg-amber-100 dark:bg-amber-950/40 dark:text-amber-300' }}">
                                    {{ $isTerjual ? 'Tandai Tersedia' : 'Tandai Terjual' }}
                                </button>

                                <a href="{{ route('barang.ubah', $item) }}" wire:navigate
                                   class="px-3 py-1.5 rounded-xl bg-stone-100 hover:bg-stone-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-200 text-xs font-bold transition">
                                    Ubah
                                </a>

                                <a href="{{ route('barang.detail', $item) }}" wire:navigate
                                   class="px-3 py-1.5 rounded-xl bg-stone-100 hover:bg-stone-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-200 text-xs font-bold transition">
                                    Lihat
                                </a>

                                <button type="button" wire:click="hapus({{ $item->id }})"
                                        wire:confirm="Yakin ingin menghapus iklan '{{ $item->nama }}'?"
                                        class="p-1.5 rounded-xl text-red-600 hover:bg-red-50 dark:hover:bg-red-950/50 transition" title="Hapus Iklan">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="p-4 border-t border-stone-100 dark:border-gray-800">
                    {{ $barangs->links() }}
                </div>
            @else
                <div class="py-12 text-center p-6">
                    <div class="h-14 w-14 rounded-full bg-stone-100 dark:bg-gray-800 text-stone-400 mx-auto flex items-center justify-center mb-3">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white">Belum Ada Barang</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-gray-400 max-w-sm mx-auto">
                        Anda belum memiliki iklan barang kos. Mulai jual perlengkapan kos Anda sekarang!
                    </p>
                    <a href="{{ route('barang.pasang') }}" wire:navigate
                       class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand-700 hover:bg-brand-800 text-xs font-bold text-white transition shadow-sm">
                        + Pasang Iklan Barang
                    </a>
                </div>
            @endif
        </div>

    </div>
</div>
