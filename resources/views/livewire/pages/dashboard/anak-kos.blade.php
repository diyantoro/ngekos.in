<?php

use App\Models\Pembayaran;
use App\Models\Penyewaan;
use App\Models\Tagihan;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public string $tab = 'sewaan';

    public ?string $pesan = null;

    public ?string $galat = null;

    public ?int $modalBayarId = null;

    public $bukti = null;

    public function with(): array
    {
        $id = auth()->id();

        return [
            'penyewaanAktif' => Penyewaan::where('anak_kos_id', $id)->where('status', 'aktif')->count(),
            'tagihanBelumBayar' => Tagihan::where('status', '!=', 'lunas')
                ->whereHas('penyewaan', fn ($q) => $q->where('anak_kos_id', $id))
                ->count(),
            'totalBayar' => (int) Pembayaran::where('anak_kos_id', $id)->where('status', 'diverifikasi')->sum('jumlah'),
            'tagihans' => Tagihan::whereHas('penyewaan', fn ($q) => $q->where('anak_kos_id', $id))
                ->with(['penyewaan.kamar', 'pembayarans'])
                ->orderByDesc('jatuh_tempo')
                ->get(),
            'pembayarans' => Pembayaran::where('anak_kos_id', $id)
                ->with(['tagihan', 'verifikator'])
                ->latest()
                ->get(),
            'sewaans' => Penyewaan::where('anak_kos_id', $id)
                ->with(['kamar.properti', 'tagihans'])
                ->orderByDesc('status')
                ->latest()
                ->get(),
        ];
    }

    public function checkOut(int $sewaanId): void
    {
        $sewaan = Penyewaan::where('id', $sewaanId)
            ->where('anak_kos_id', auth()->id())
            ->where('status', 'aktif')
            ->with(['kamar', 'tagihans'])
            ->first();

        if (! $sewaan) {
            return;
        }

        $belumLunas = $sewaan->tagihans->where('status', '!=', 'lunas')->count();

        DB::transaction(function () use ($sewaan) {
            $sewaan->update([
                'tanggal_keluar' => now()->toDateString(),
                'status' => 'selesai',
            ]);

            optional($sewaan->kamar)->update(['status' => 'tersedia']);
        });

        $catatan = $belumLunas > 0
            ? " Perhatian: masih ada {$belumLunas} tagihan belum lunas."
            : '';

        $this->pesan = "Check-out dari kamar {$sewaan->kamar?->nama} berhasil. Kamar kembali tersedia.{$catatan}";
    }

    public function bayarTagihan(int $tagihanId): void
    {
        $this->resetValidation();
        $this->pesan = null;
        $this->galat = null;
        $this->bukti = null;

        $tagihan = Tagihan::where('id', $tagihanId)
            ->whereHas('penyewaan', fn ($q) => $q->where('anak_kos_id', auth()->id()))
            ->first();

        if (! $tagihan || $tagihan->status === 'lunas') {
            $this->galat = 'Tagihan tidak ditemukan atau sudah lunas.';

            return;
        }

        $sudahAda = $tagihan->pembayarans()->where('status', 'menunggu_verifikasi')->exists();

        if ($sudahAda) {
            $this->galat = 'Pembayaran untuk tagihan ini masih menunggu verifikasi admin.';

            return;
        }

        $this->modalBayarId = $tagihanId;
    }

    public function tutupModalBayar(): void
    {
        $this->modalBayarId = null;
        $this->bukti = null;
        $this->resetValidation();
    }

    public function konfirmasiBayar(): void
    {
        $tagihan = Tagihan::where('id', $this->modalBayarId)
            ->whereHas('penyewaan', fn ($q) => $q->where('anak_kos_id', auth()->id()))
            ->first();

        if (! $tagihan || $tagihan->status === 'lunas') {
            $this->tutupModalBayar();
            $this->galat = 'Tagihan tidak ditemukan atau sudah lunas.';

            return;
        }

        $sudahAda = $tagihan->pembayarans()->where('status', 'menunggu_verifikasi')->exists();

        if ($sudahAda) {
            $this->tutupModalBayar();
            $this->galat = 'Pembayaran untuk tagihan ini masih menunggu verifikasi admin.';

            return;
        }

        $this->validate([
            'bukti' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:2048'],
        ], [
            'bukti.required' => 'Lampirkan bukti transfer terlebih dahulu.',
            'bukti.mimes' => 'Bukti transfer harus berupa gambar (JPG, PNG, WEBP) atau PDF.',
            'bukti.max' => 'Ukuran bukti transfer maksimal 2MB.',
        ]);

        Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'anak_kos_id' => auth()->id(),
            'metode' => 'transfer',
            'jumlah' => $tagihan->jumlah + $tagihan->denda,
            'bukti' => $this->bukti->store('bukti-pembayaran', 'public'),
            'status' => 'menunggu_verifikasi',
        ]);

        $this->tutupModalBayar();

        $this->pesan = 'Pembayaran beserta bukti transfer terkirim dan sedang menunggu verifikasi admin.';
    }
}; ?>

<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <x-dashboard-greeting
            roleLabel="Anak Kos"
            description="Pantau penyewaan, tagihan, dan riwayat pembayaranmu di sini."
            icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>'
        />

        <x-promo-ads />

        @if ($pesan)
            <x-notifikasi-popup :pesan="$pesan" judul="Berhasil!" />
        @endif

        @if ($galat)
            <div class="flex items-center justify-between gap-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 ring-1 ring-rose-200 dark:ring-rose-500/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                <span>{{ $galat }}</span>
                <button wire:click="$set('galat', null)" class="text-rose-500 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 font-bold">&times;</button>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-stat-card label="Penyewaan Aktif" :value="$penyewaanAktif" tone="emerald"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />
            <x-stat-card label="Tagihan Belum Bayar" :value="$tagihanBelumBayar" tone="amber"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />
            <x-stat-card label="Total Sudah Dibayar" :value="'Rp' . number_format($totalBayar, 0, ',', '.')" tone="cyan"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>' />
        </div>

        <x-kos-trending />

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
            <div class="px-4 sm:px-6 pt-4 pb-3 border-b border-gray-100 dark:border-gray-700">
                <div class="flex gap-2 overflow-x-auto scrollbar-hide pb-1 -mb-1">
                    <button wire:click="$set('tab', 'sewaan')"
                        class="flex-shrink-0 whitespace-nowrap px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'sewaan' ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                        Sewa Saya
                    </button>
                    <button wire:click="$set('tab', 'tagihan')"
                        class="flex-shrink-0 whitespace-nowrap px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'tagihan' ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                        Tagihan Saya
                    </button>
                    <button wire:click="$set('tab', 'pembayaran')"
                        class="flex-shrink-0 whitespace-nowrap px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'pembayaran' ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                        Pembayaran Saya
                    </button>
                </div>
            </div>

            <div class="p-4 sm:p-6">
                @if ($tab === 'sewaan')
                    <div class="space-y-4">
                        @forelse ($sewaans as $sewaan)
                            @php
                                $belumLunas = $sewaan->tagihans->where('status', '!=', 'lunas');
                                $sisa = $belumLunas->sum(fn ($t) => $t->jumlah + $t->denda);
                            @endphp
                            <div class="rounded-xl ring-1 {{ $sewaan->status === 'aktif' ? 'ring-teal-100 dark:ring-teal-500/30' : 'ring-gray-100 dark:ring-gray-700 opacity-75' }} p-4 sm:p-5">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                            Kamar {{ $sewaan->kamar?->nama }} &middot; {{ $sewaan->kamar?->properti?->nama }}
                                        </p>
                                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                            Masuk: <span class="font-medium text-gray-700 dark:text-gray-200">{{ $sewaan->tanggal_masuk?->translatedFormat('d M Y') ?? '-' }}</span>
                                            @if ($sewaan->tanggal_keluar)
                                                &middot; Keluar: <span class="font-medium text-gray-700 dark:text-gray-200">{{ $sewaan->tanggal_keluar->translatedFormat('d M Y') }}</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <x-status-badge :status="$sewaan->status" />
                                    </div>
                                </div>

                                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        @if ($belumLunas->isEmpty())
                                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">Semua tagihan lunas</span>
                                        @else
                                            <span class="font-semibold text-rose-600 dark:text-rose-400">Sisa tagihan Rp{{ number_format($sisa, 0, ',', '.') }}</span> ({{ $belumLunas->count() }} tagihan) &mdash; bayar lewat tab Tagihan Saya
                                        @endif
                                    </p>
                                    @if ($sewaan->status === 'aktif')
                                        <button wire:click="checkOut({{ $sewaan->id }})" wire:loading.attr="disabled"
                                            wire:confirm="Check-out dari kamar {{ $sewaan->kamar?->nama }}? Kamar akan kembali tersedia."
                                            class="shrink-0 inline-flex items-center rounded-lg border border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/10 px-4 py-2 text-xs font-semibold text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-500/20 transition">
                                            Check-out
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-10 text-center">
                                <p class="text-sm text-gray-400 dark:text-gray-500">Belum ada penyewaan aktif.</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Cari kos di halaman <a href="{{ route('kos.index') }}" wire:navigate class="text-teal-600 dark:text-teal-400 hover:underline font-medium">Cari Kos</a> untuk mulai menyewa.</p>
                            </div>
                        @endforelse
                    </div>
                @elseif ($tab === 'tagihan')
                    <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Periode</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kamar</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jumlah</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jatuh Tempo</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse ($tagihans as $tagihan)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $tagihan->periode }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $tagihan->penyewaan?->kamar?->nama ?? '-' }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">Rp{{ number_format($tagihan->jumlah + $tagihan->denda, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $tagihan->jatuh_tempo?->translatedFormat('d M Y') }}</td>
                                    <td class="px-4 py-4"><x-status-badge :status="$tagihan->status" /></td>
                                    <td class="px-4 py-4">
                                        @if ($tagihan->status !== 'lunas')
                                            <div class="flex justify-end">
                                                <button wire:click="bayarTagihan({{ $tagihan->id }})" wire:loading.attr="disabled"
                                                    class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500 transition">
                                                    Bayar Sekarang
                                                </button>
                                            </div>
                                        @else
                                            <span class="block text-right text-xs text-gray-400 dark:text-gray-500">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-400 dark:text-gray-500">Tidak ada tagihan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                @else
                    <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Periode</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jumlah</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Metode</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bukti</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Diverifikasi Oleh</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse ($pembayarans as $pembayaran)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $pembayaran->tagihan?->periode ?? '-' }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">Rp{{ number_format($pembayaran->jumlah, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $pembayaran->metode }}</td>
                                    <td class="px-4 py-4 text-sm">
                                        @if ($pembayaran->bukti)
                                            <a href="{{ Storage::url($pembayaran->bukti) }}" target="_blank" rel="noopener"
                                                class="inline-flex items-center gap-1 text-xs font-semibold text-teal-600 dark:text-teal-400 hover:text-teal-700 dark:hover:text-teal-300 hover:underline">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                                Lihat
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-400 dark:text-gray-500 italic">Tidak ada</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $pembayaran->verifikator?->nama ?? '-' }}</td>
                                    <td class="px-4 py-4"><x-status-badge :status="$pembayaran->status" /></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-400 dark:text-gray-500">Belum ada pembayaran.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if ($modalBayarId)
    @php
        $tagihanModal = $tagihans->firstWhere('id', $modalBayarId);
        $totalTagihan = ($tagihanModal?->jumlah ?? 0) + ($tagihanModal?->denda ?? 0);
    @endphp
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
        <button type="button" wire:click="tutupModalBayar" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm cursor-default" tabindex="-1" aria-label="Tutup"></button>
        <div class="relative min-h-full flex items-end sm:items-center justify-center p-4">
            <div class="w-full sm:max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-xl ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate">Bayar Tagihan {{ $tagihanModal?->periode }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Total: Rp{{ number_format($totalTagihan, 0, ',', '.') }}</p>
                    </div>
                    <button type="button" wire:click="tutupModalBayar"
                        class="shrink-0 h-8 w-8 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-500 dark:text-gray-400 flex items-center justify-center transition">&times;</button>
                </div>

                <form wire:submit="konfirmasiBayar" class="p-5 space-y-4">
                    <p class="text-xs text-gray-400 dark:text-gray-500">Transfer tepat sesuai jumlah tagihan di atas, lalu unggah bukti transfer. Admin akan memverifikasi pembayaranmu.</p>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Bukti Transfer (JPG/PNG/WEBP/PDF, maks 2MB)</label>
                        <input type="file" wire:model="bukti" accept=".jpg,.jpeg,.png,.webp,.pdf"
                            class="w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-teal-50 dark:file:bg-teal-500/10 file:px-4 file:py-2 file:text-teal-700 dark:file:text-teal-300 file:font-semibold hover:file:bg-teal-100 dark:hover:file:bg-teal-500/20">
                        @error('bukti') <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                        <div wire:loading wire:target="bukti" class="mt-2 flex items-center gap-1.5 text-xs font-medium text-teal-600">
                            <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Mengunggah bukti...
                        </div>
                    </div>
                    <div class="flex flex-col-reverse sm:flex-row gap-2 pt-1">
                        <button type="button" wire:click="tutupModalBayar" wire:loading.attr="disabled"
                            class="flex-1 inline-flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="konfirmasiBayar"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500 transition disabled:opacity-50">
                            Kirim Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
