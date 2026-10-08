<?php

use App\Models\Pembayaran;
use App\Models\Tagihan;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $cari = '';

    public string $filter = 'belum';

    public ?string $pesan = null;

    public ?string $galat = null;

    public function with(): array
    {
        $user = auth()->user();
        $lihatSemua = $user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super_admin']);
        $id = auth()->id();

        $query = Tagihan::query()
            ->when(! $lihatSemua, fn ($q) => $q->whereHas('penyewaan.properti', fn ($w) => $w->where('pemilik_id', $id)))
            ->select(['id', 'penyewaan_id', 'periode', 'jumlah', 'denda', 'jatuh_tempo', 'status'])
            ->with(['penyewaan.anakKos:id,nama', 'penyewaan.kamar:id,nama', 'penyewaan.properti:id,nama'])
            ->when($this->filter === 'belum', fn ($q) => $q->where('status', '!=', 'lunas'))
            ->when($this->filter === 'telat', fn ($q) => $q->where('status', '!=', 'lunas')->where('jatuh_tempo', '<', today()->toDateString()))
            ->when($this->filter === 'lunas', fn ($q) => $q->where('status', 'lunas'))
            ->when($this->cari, fn ($q) => $q->where(function ($w) {
                $w->where('periode', 'like', "%{$this->cari}%")
                    ->orWhereHas('penyewaan.anakKos', fn ($x) => $x->where('nama', 'like', "%{$this->cari}%"))
                    ->orWhereHas('penyewaan.kamar', fn ($x) => $x->where('nama', 'like', "%{$this->cari}%"))
                    ->orWhereHas('penyewaan.properti', fn ($x) => $x->where('nama', 'like', "%{$this->cari}%"));
            }))
            ->orderBy('jatuh_tempo');

        $daftar = $query->limit(50)->get();
        $total = (int) $daftar->sum(fn ($t) => $t->jumlah + $t->denda);

        $pembayaranMenunggu = Pembayaran::query()
            ->when(! $lihatSemua, fn ($q) => $q->whereHas('tagihan.penyewaan.properti', fn ($w) => $w->where('pemilik_id', $id)))
            ->where('status', 'menunggu_verifikasi')
            ->with(['anakKos:id,nama', 'tagihan.penyewaan.kamar:id,nama', 'tagihan.penyewaan.properti:id,nama'])
            ->orderBy('created_at')
            ->limit(50)
            ->get();

        return ['tagihans' => $daftar, 'totalNominal' => $total, 'pembayaranMenunggu' => $pembayaranMenunggu];
    }

    public function verifikasiPembayaran(int $id): void
    {
        $this->reset(['pesan', 'galat']);

        $user = auth()->user();
        $lihatSemua = $user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super_admin']);

        $pembayaran = Pembayaran::query()
            ->when(! $lihatSemua, fn ($q) => $q->whereHas('tagihan.penyewaan.properti', fn ($w) => $w->where('pemilik_id', auth()->id())))
            ->where('id', $id)
            ->with('anakKos', 'tagihan')
            ->first();

        if (! $pembayaran || $pembayaran->status !== 'menunggu_verifikasi') {
            $this->galat = 'Pembayaran tidak ditemukan atau sudah diproses.';

            return;
        }

        try {
            $hasil = \App\Services\PembayaranService::verifikasi($pembayaran, auth()->id(), 'diverifikasi');
        } catch (DomainException $e) {
            $this->galat = $e->getMessage();

            return;
        }

        $this->pesan = 'Pembayaran ' . ($pembayaran->anakKos?->nama ?? '-') . ' sebesar Rp'
            . number_format($pembayaran->jumlah, 0, ',', '.') . ' diverifikasi.'
            . ($hasil['kwitansi_url'] ? " Kwitansi {$hasil['pembayaran']->nomor_kwitansi} otomatis terkirim." : '');
    }
}; ?>

<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <a href="{{ route('dashboard') }}" wire:navigate class="text-sm font-medium text-teal-600 dark:text-teal-400 hover:text-teal-500 dark:hover:text-teal-300 inline-flex items-center gap-1">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Kembali ke dashboard
            </a>
            <h1 class="mt-2 text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">Tagihan Penyewa</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $tagihans->count() }} tagihan · total <span class="font-bold text-gray-800 dark:text-gray-200">Rp{{ number_format($totalNominal, 0, ',', '.') }}</span>
            </p>
        </div>

        @if ($pesan)
            <x-notifikasi-popup :pesan="$pesan" judul="Berhasil!" />
        @endif

        @if ($galat)
            <div class="flex items-center justify-between gap-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 ring-1 ring-rose-200 dark:ring-rose-500/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                <span>{{ $galat }}</span>
                <button wire:click="$set('galat', null)" class="text-rose-500 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 font-bold">&times;</button>
            </div>
        @endif

        @if ($pembayaranMenunggu->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Pembayaran Menunggu Verifikasi</h3>
                    <span class="text-xs font-medium text-amber-600 dark:text-amber-400">{{ $pembayaranMenunggu->count() }} item</span>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($pembayaranMenunggu as $pembayaran)
                        <div class="px-5 py-3.5 flex items-center gap-3">
                            <div class="h-9 w-9 shrink-0 rounded-full bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-300 flex items-center justify-center">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $pembayaran->anakKos?->nama ?? 'Penyewa' }} · Kamar {{ $pembayaran->tagihan?->penyewaan?->kamar?->nama ?? '-' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $pembayaran->tagihan?->penyewaan?->properti?->nama ?? '-' }} · {{ $pembayaran->tagihan?->periode ?? '-' }} · {{ $pembayaran->labelMetode() }}</p>
                                @if ($pembayaran->bukti)
                                    <a href="{{ Storage::url($pembayaran->bukti) }}" target="_blank" rel="noopener"
                                        class="mt-1 inline-flex items-center gap-1 text-xs font-semibold text-teal-600 hover:text-teal-700 hover:underline dark:text-teal-400 dark:hover:text-teal-300">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        Lihat Bukti
                                    </a>
                                @else
                                    <p class="mt-1 text-xs italic text-gray-400 dark:text-gray-500">Tunai · tanpa bukti, pastikan uang sudah diterima</p>
                                @endif
                            </div>
                            <div class="text-end shrink-0">
                                <p class="text-sm font-bold text-gray-900 dark:text-gray-100">Rp{{ number_format($pembayaran->jumlah, 0, ',', '.') }}</p>
                                <button wire:click="verifikasiPembayaran({{ $pembayaran->id }})" wire:confirm="Sudah periksa bukti transfernya? Verifikasi pembayaran ini?" wire:loading.attr="disabled"
                                    class="mt-1 inline-flex items-center gap-1 rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-500 transition disabled:opacity-50">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    Verifikasi
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto_auto] gap-2 items-center">
            <input type="text" wire:model.live.debounce.300ms="cari" placeholder="Cari penyewa, kamar, kos, atau periode..."
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-brand-500 focus:border-brand-500">
            <select wire:model.live="filter" aria-label="Filter status"
                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-brand-500 focus:border-brand-500">
                <option value="belum">Belum lunas</option>
                <option value="telat">Telat</option>
                <option value="lunas">Lunas</option>
                <option value="semua">Semua</option>
            </select>
            @if ($cari !== '' || $filter !== 'belum')
                <button wire:click="$set('cari', ''); $set('filter', 'belum')"
                    class="justify-self-start inline-flex items-center gap-1 rounded-lg px-2 py-2 text-xs font-semibold text-rose-500 hover:text-rose-600 dark:text-rose-400 dark:hover:text-rose-300 transition active:scale-95 whitespace-nowrap">
                    Reset
                </button>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($tagihans as $tagihan)
                <div class="group bg-white dark:bg-gray-800 rounded-xl border border-stone-200 dark:border-gray-700 overflow-hidden hover:shadow-card-hover hover:border-brand-200 transition">
                    <div class="flex p-3 sm:p-4 gap-3">
                        <div class="relative h-20 w-20 shrink-0 rounded-lg {{ $tagihan->status === 'lunas' ? 'bg-emerald-100 dark:bg-emerald-500/20' : 'bg-rose-100 dark:bg-rose-500/20' }} flex items-center justify-center">
                            <svg class="h-8 w-8 {{ $tagihan->status === 'lunas' ? 'text-emerald-500 dark:text-emerald-400' : 'text-rose-400 dark:text-rose-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="flex-1 min-w-0 flex flex-col justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-gray-100 line-clamp-1">{{ $tagihan->penyewaan?->anakKos?->nama ?? 'Penyewa' }}</h3>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 truncate">
                                    {{ $tagihan->penyewaan?->properti?->nama ?? '-' }} · Kamar {{ $tagihan->penyewaan?->kamar?->nama ?? '-' }}
                                </p>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 truncate">
                                    {{ $tagihan->periode }} · jatuh tempo {{ $tagihan->jatuh_tempo?->translatedFormat('d M Y') }}
                                </p>
                            </div>
                            <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between gap-2">
                                <div>
                                    <p class="text-sm font-extrabold {{ $tagihan->status === 'lunas' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">Rp{{ number_format($tagihan->jumlah + $tagihan->denda, 0, ',', '.') }}</p>
                                    @if ($tagihan->denda > 0)
                                        <p class="text-[11px] text-rose-500 dark:text-rose-400">termasuk denda Rp{{ number_format($tagihan->denda, 0, ',', '.') }}</p>
                                    @endif
                                </div>
                                <x-status-badge :status="$tagihan->status" />
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center">
                    <p class="text-gray-500 dark:text-gray-400 font-medium text-sm">Tidak ada tagihan pada filter ini.</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Ubah filter atau kata kunci pencarian.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
