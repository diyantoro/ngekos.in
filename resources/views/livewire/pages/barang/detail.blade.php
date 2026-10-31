<?php

use App\Enums\BarangStatus;
use App\Models\BarangKos;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.publik')] class extends Component
{
    public BarangKos $barang;

    public string $fotoAktif = '';

    public ?string $pesan = null;

    public function mount(BarangKos $barang): void
    {
        $this->barang = $barang->loadMissing(['user', 'properti']);
        // Increment jumlah dilihat secara ringan
        $barang->increment('dilihat');

        $semua = $barang->semuaFoto();
        $this->fotoAktif = $semua[0] ?? '';
    }

    public function pilihFoto(string $url): void
    {
        $this->fotoAktif = $url;
    }

    public function toggleStatus(): void
    {
        $user = auth()->user();
        if (! $this->barang->isMilik($user)) {
            abort(403);
        }

        $baru = $this->barang->status === BarangStatus::Tersedia
            ? BarangStatus::Terjual
            : BarangStatus::Tersedia;

        $this->barang->update(['status' => $baru]);
        $this->pesan = 'Status barang berhasil diubah menjadi: '.$baru->label();
    }

    public function hapusBarang(): void
    {
        $user = auth()->user();
        if (! $this->barang->isMilik($user)) {
            abort(403);
        }

        $this->barang->delete();
        $this->redirect(route('barang.kelola'), navigate: true);
    }

    public function with(): array
    {
        $semuaFoto = $this->barang->semuaFoto();
        $isMilik = $this->barang->isMilik(auth()->user());

        $rekomendasi = BarangKos::where('id', '!=', $this->barang->id)
            ->where('status', BarangStatus::Tersedia->value)
            ->where(function ($q) {
                $q->where('kategori', $this->barang->kategori->value)
                    ->orWhere('kota', $this->barang->kota);
            })
            ->with(['user'])
            ->limit(4)
            ->get();

        return [
            'semuaFoto' => $semuaFoto,
            'isMilik' => $isMilik,
            'rekomendasi' => $rekomendasi,
        ];
    }
}; ?>

<div class="py-6 sm:py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumbs --}}
        <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-gray-400 mb-6 overflow-x-auto whitespace-nowrap">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-brand-700 dark:hover:text-brand-300">Beranda</a>
            <span>/</span>
            <a href="{{ route('barang.index') }}" wire:navigate class="hover:text-brand-700 dark:hover:text-brand-300">Pasar Kos</a>
            <span>/</span>
            <span class="text-slate-800 dark:text-gray-200 font-semibold truncate max-w-xs">{{ $barang->nama }}</span>
        </nav>

        @if ($pesan)
            <div class="mb-6 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 p-4 text-sm text-emerald-800 dark:text-emerald-300 flex items-center justify-between">
                <span>{{ $pesan }}</span>
                <button type="button" wire:click="$set('pesan', null)" class="text-emerald-700 hover:text-emerald-900 font-bold">✕</button>
            </div>
        @endif

        {{-- Owner Control Alert --}}
        @if ($isMilik)
            <div class="mb-6 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-400 text-brand-950">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.67 2.67 0 0021 17.25l-5.83-5.83m0 0a5.333 5.333 0 10-7.54-7.54 5.333 5.333 0 007.54 7.54z" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold text-amber-900 dark:text-amber-200 uppercase tracking-wider">Iklan Anda</p>
                        <p class="text-xs text-amber-800 dark:text-amber-300">Anda adalah pemilik iklan barang kos ini.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="toggleStatus"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $barang->status === \App\Enums\BarangStatus::Tersedia ? 'bg-amber-400 hover:bg-amber-300 text-brand-950' : 'bg-emerald-600 hover:bg-emerald-500 text-white' }}">
                        {{ $barang->status === \App\Enums\BarangStatus::Tersedia ? 'Tandai Sudah Terjual' : 'Aktifkan Kembali' }}
                    </button>
                    <a href="{{ route('barang.ubah', $barang) }}" wire:navigate
                       class="px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 border border-stone-300 dark:border-gray-700 text-xs font-bold text-slate-800 dark:text-gray-200 hover:bg-stone-50 transition">
                        Ubah Iklan
                    </a>
                    <button type="button" wire:click="hapusBarang"
                            wire:confirm="Yakin ingin menghapus iklan barang ini secara permanen?"
                            class="px-3 py-1.5 rounded-xl bg-red-100 hover:bg-red-200 dark:bg-red-950/50 text-xs font-bold text-red-700 dark:text-red-400 transition">
                        Hapus
                    </button>
                </div>
            </div>
        @endif

        {{-- Detail Grid: Galeri Foto (Kiri) & Info Barang (Kanan) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">

            {{-- Kolom Kiri: Galeri Foto --}}
            <div class="lg:col-span-7 space-y-4">
                {{-- Foto Utama --}}
                <div class="relative aspect-4/3 sm:aspect-16/10 w-full rounded-3xl overflow-hidden bg-stone-100 dark:bg-gray-800 border border-stone-200 dark:border-gray-800 shadow-sm">
                    <img src="{{ $fotoAktif ?: $barang->fotoUrl() }}"
                         alt="{{ $barang->nama }}"
                         class="w-full h-full object-cover transition duration-300">

                    @if ($barang->status === \App\Enums\BarangStatus::Terjual)
                        <div class="absolute inset-0 bg-black/60 backdrop-blur-[2px] flex items-center justify-center">
                            <span class="px-4 py-2 rounded-full bg-red-600 text-white font-extrabold text-sm uppercase tracking-wider shadow-xl">
                                Barang Ini Sudah Terjual
                            </span>
                        </div>
                    @endif

                    <div class="absolute top-4 left-4 flex gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-brand-950/80 backdrop-blur text-white shadow-md">
                            {{ $barang->kondisi?->label() ?? 'Bekas' }}
                        </span>
                        @if ($barang->bisa_nego)
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-400 text-brand-950 shadow-md">
                                Bisa Nego
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Thumbnails jika lebih dari 1 foto --}}
                @if (count($semuaFoto) > 1)
                    <div class="flex items-center gap-3 overflow-x-auto pb-2 scrollbar-hide">
                        @foreach ($semuaFoto as $foto)
                            <button type="button"
                                    wire:click="pilihFoto('{{ $foto }}')"
                                    class="relative h-20 w-20 sm:h-24 sm:w-24 shrink-0 rounded-2xl overflow-hidden border-2 transition {{ $fotoAktif === $foto ? 'border-brand-600 ring-2 ring-brand-500/30' : 'border-stone-200 dark:border-gray-800 hover:border-stone-400 opacity-70 hover:opacity-100' }}">
                                <img src="{{ $foto }}" alt="Thumbnail" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif

                {{-- Deskripsi Barang --}}
                <div class="bg-white dark:bg-gray-900 border border-stone-200 dark:border-gray-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-gray-100">Deskripsi Barang</h2>
                        <div class="mt-3 text-sm text-slate-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                            {{ $barang->deskripsi }}
                        </div>
                    </div>

                    {{-- Catatan Minus (Jika Ada) --}}
                    @if ($barang->minus)
                        <div class="rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 p-4 sm:p-5">
                            <div class="flex items-start gap-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-400 text-brand-950 font-bold text-xs mt-0.5">!</span>
                                <div>
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-amber-900 dark:text-amber-200">Catatan Minus / Kekurangan:</h3>
                                    <p class="mt-1 text-sm text-amber-950 dark:text-amber-300 leading-relaxed whitespace-pre-line">
                                        {{ $barang->minus }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Informasi Detail Spesifikasi --}}
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-gray-100 mb-3">Spesifikasi</h2>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                            <div class="bg-stone-50 dark:bg-gray-800/50 p-3 rounded-xl border border-stone-200/60 dark:border-gray-800">
                                <span class="text-slate-400">Kategori</span>
                                <p class="mt-1 font-semibold text-slate-800 dark:text-gray-200">{{ $barang->kategori?->label() }}</p>
                            </div>
                            <div class="bg-stone-50 dark:bg-gray-800/50 p-3 rounded-xl border border-stone-200/60 dark:border-gray-800">
                                <span class="text-slate-400">Kondisi</span>
                                <p class="mt-1 font-semibold text-slate-800 dark:text-gray-200">{{ $barang->kondisi?->label() }}</p>
                            </div>
                            <div class="bg-stone-50 dark:bg-gray-800/50 p-3 rounded-xl border border-stone-200/60 dark:border-gray-800">
                                <span class="text-slate-400">Negosiasi</span>
                                <p class="mt-1 font-semibold text-slate-800 dark:text-gray-200">{{ $barang->bisa_nego ? 'Bisa Nego' : 'Harga Pas' }}</p>
                            </div>
                            <div class="bg-stone-50 dark:bg-gray-800/50 p-3 rounded-xl border border-stone-200/60 dark:border-gray-800">
                                <span class="text-slate-400">Lokasi Kota</span>
                                <p class="mt-1 font-semibold text-slate-800 dark:text-gray-200">{{ $barang->kota }}</p>
                            </div>
                            <div class="bg-stone-50 dark:bg-gray-800/50 p-3 rounded-xl border border-stone-200/60 dark:border-gray-800">
                                <span class="text-slate-400">Diiklankan Pada</span>
                                <p class="mt-1 font-semibold text-slate-800 dark:text-gray-200">{{ $barang->created_at->translatedFormat('d M Y') }}</p>
                            </div>
                            <div class="bg-stone-50 dark:bg-gray-800/50 p-3 rounded-xl border border-stone-200/60 dark:border-gray-800">
                                <span class="text-slate-400">Dilihat</span>
                                <p class="mt-1 font-semibold text-slate-800 dark:text-gray-200">{{ number_format($barang->dilihat) }} kali</p>
                            </div>
                        </div>
                    </div>

                    {{-- Properti Kos Terkait --}}
                    @if ($barang->properti)
                        <div class="pt-4 border-t border-stone-100 dark:border-gray-800 flex items-center justify-between gap-4">
                            <div>
                                <span class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Terkait dengan Kos</span>
                                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $barang->properti->nama }}</p>
                                <p class="text-xs text-slate-500">{{ $barang->properti->kota }}</p>
                            </div>
                            <a href="{{ route('kos.detail', $barang->properti) }}" wire:navigate
                               class="px-3 py-1.5 rounded-xl border border-brand-300 dark:border-brand-700 text-xs font-semibold text-brand-700 dark:text-brand-300 hover:bg-brand-50 dark:hover:bg-brand-900/30 transition shrink-0">
                                Lihat Kos Ini →
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Kolom Kanan: Info Harga, Penjual & Tombol Kontak --}}
            <div class="lg:col-span-5 space-y-6">

                {{-- Box Harga & Judul --}}
                <div class="bg-white dark:bg-gray-900 border border-stone-200 dark:border-gray-800 rounded-3xl p-6 sm:p-7 shadow-sm space-y-5">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $barang->status === \App\Enums\BarangStatus::Tersedia ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300' : 'bg-red-100 dark:bg-red-950/60 text-red-800 dark:text-red-300' }}">
                                <span class="h-2 w-2 rounded-full {{ $barang->status === \App\Enums\BarangStatus::Tersedia ? 'bg-emerald-500 animate-pulse' : 'bg-red-500' }}"></span>
                                {{ $barang->status?->label() }}
                            </span>
                            <span class="text-xs text-slate-400">
                                Diposting {{ $barang->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white leading-snug">
                            {{ $barang->nama }}
                        </h1>

                        <div class="mt-4 flex items-baseline gap-3">
                            <span class="text-3xl font-extrabold text-brand-800 dark:text-emerald-400">
                                {{ $barang->formattedHarga() }}
                            </span>
                            @if ($barang->bisa_nego)
                                <span class="text-xs font-bold text-amber-700 dark:text-amber-300 bg-amber-100 dark:bg-amber-950/50 px-2 py-0.5 rounded-md">
                                    Bisa Nego Tipis
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Lokasi & COD --}}
                    <div class="p-4 rounded-2xl bg-stone-50 dark:bg-gray-800/60 border border-stone-200/70 dark:border-gray-800 space-y-2">
                        <div class="flex items-start gap-2 text-xs text-slate-600 dark:text-gray-300">
                            <svg class="w-4 h-4 text-brand-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <div>
                                <strong class="text-slate-800 dark:text-gray-100 font-semibold">Lokasi Barang & COD:</strong>
                                <p class="mt-0.5">{{ $barang->kota }}</p>
                                @if ($barang->lokasi_detail)
                                    <p class="mt-0.5 text-slate-500 dark:text-gray-400">{{ $barang->lokasi_detail }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Hubungi WhatsApp --}}
                    @if ($barang->status === \App\Enums\BarangStatus::Tersedia)
                        <div class="space-y-3 pt-2">
                            <a href="{{ $barang->waLink() }}" target="_blank" rel="noopener noreferrer"
                               class="w-full flex items-center justify-center gap-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 py-3.5 px-6 text-sm font-bold text-white transition shadow-lg shadow-emerald-600/25 active:scale-[0.98]">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.746.953 3.71 1.456 5.711 1.457h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                </svg>
                                Chat WhatsApp Penjual
                            </a>

                            <p class="text-[11px] text-center text-slate-400">
                                Tip aman: Selalu periksa fisik barang saat COD sebelum melakukan pembayaran tunai / transfer.
                            </p>
                        </div>
                    @else
                        <div class="p-4 rounded-2xl bg-stone-100 dark:bg-gray-800 text-center">
                            <p class="text-sm font-semibold text-slate-600 dark:text-gray-300">Barang ini sudah laku terjual.</p>
                            <p class="text-xs text-slate-400 mt-1">Silakan cari barang serupa lainnya di Pasar Kos.</p>
                        </div>
                    @endif
                </div>

                {{-- Box Profil Penjual --}}
                <div class="bg-white dark:bg-gray-900 border border-stone-200 dark:border-gray-800 rounded-3xl p-6 shadow-sm">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Informasi Penjual</p>
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 rounded-2xl bg-brand-100 dark:bg-brand-900/60 text-brand-700 dark:text-brand-300 font-extrabold text-base flex items-center justify-center shrink-0">
                            {{ strtoupper(substr($barang->user?->nama ?? 'U', 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate">
                                {{ $barang->user?->nama ?? 'Pengguna Ngekos.in' }}
                            </h3>
                            <div class="mt-0.5 flex items-center gap-2">
                                @if (($barang->user?->roleValue() ?? '') === 'pemilik')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/50 px-2 py-0.5 rounded-md">
                                        Pemilik Kos
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-brand-700 dark:text-brand-300 bg-brand-50 dark:bg-brand-950/50 px-2 py-0.5 rounded-md">
                                        Anak Kos
                                    </span>
                                @endif
                                <span class="text-xs text-slate-400">Bergabung {{ $barang->user?->created_at?->translatedFormat('M Y') ?? '2026' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        {{-- Rekomendasi Barang Lainnya --}}
        @if ($rekomendasi->count() > 0)
            <div class="mt-14 pt-8 border-t border-stone-200 dark:border-gray-800">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white">Barang Kos Lainnya</h2>
                        <p class="text-xs text-slate-500">Mungkin kamu juga membutuhkan barang-barang ini</p>
                    </div>
                    <a href="{{ route('barang.index') }}" wire:navigate class="text-xs font-semibold text-brand-700 dark:text-brand-300 hover:underline">
                        Lihat Semua Pasar Kos →
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-5">
                    @foreach ($rekomendasi as $rek)
                        <a href="{{ route('barang.detail', $rek) }}" wire:navigate
                           class="group bg-white dark:bg-gray-900 border border-stone-200 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm hover:shadow-md hover:-translate-y-1 transition duration-200 flex flex-col">
                            <div class="aspect-square bg-stone-100 dark:bg-gray-800 overflow-hidden relative">
                                <img src="{{ $rek->fotoUrl() }}" alt="{{ $rek->nama }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-brand-950/80 text-white">
                                    {{ $rek->kondisi?->label() }}
                                </span>
                            </div>
                            <div class="p-3.5 flex-1 flex flex-col justify-between">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-gray-100 line-clamp-2">{{ $rek->nama }}</h4>
                                    <p class="mt-1.5 text-sm font-extrabold text-brand-800 dark:text-brand-300">{{ $rek->formattedHarga() }}</p>
                                </div>
                                <p class="mt-2 text-[11px] text-slate-400 truncate">{{ $rek->kota }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>
