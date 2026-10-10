<?php

use App\Models\Penyewaan;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $cari = '';

    public ?string $pesan = null;

    public ?string $galat = null;

    public ?string $modalKtpUrl = null;

    public ?string $modalKtpNama = null;

    public bool $modalKtpIsPdf = false;

    public function with(): array
    {
        $user = auth()->user();
        $lihatSemua = $user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super_admin']);
        $scope = fn ($q) => $lihatSemua ? $q : $q->where('pemilik_id', $user->id);

        return [
            'sewaans' => Penyewaan::query()
                ->whereHas('properti', $scope)
                ->select(['id', 'anak_kos_id', 'kamar_id', 'properti_id', 'tanggal_masuk', 'tanggal_keluar', 'status', 'ktp_path', 'mode_hunian', 'permintaan_keluar_pada'])
                ->with(['anakKos:id,nama', 'kamar:id,nama,properti_id', 'kamar.properti:id,nama', 'tagihans:id,penyewaan_id,periode,jumlah,denda,status,jatuh_tempo', 'anggotas.user:id,nama'])
                ->when($this->cari, fn ($q) => $q->whereHas('anakKos', fn ($q) => $q->where('nama', 'like', "%{$this->cari}%")))
                ->latest()
                ->limit(30)
                ->get(),
        ];
    }

    public function lihatKtp(int $sewaanId, ?int $userId = null): void
    {
        $this->galat = null;
        $user = auth()->user();
        $lihatSemua = $user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super_admin']);

        $sewaan = Penyewaan::where('id', $sewaanId)
            ->when(! $lihatSemua, fn ($q) => $q->whereHas('properti', fn ($w) => $w->where('pemilik_id', $user->id)))
            ->with(['anakKos:id,nama', 'anggotas.user:id,nama'])
            ->first();

        if (! $sewaan) {
            $this->galat = 'Penyewaan tidak ditemukan.';

            return;
        }

        $targetUserId = $userId ?? $sewaan->anak_kos_id;

        $path = $targetUserId === $sewaan->anak_kos_id
            ? $sewaan->ktp_path
            : $sewaan->anggotas->firstWhere('user_id', $targetUserId)?->ktp_path;

        if (! $path || ! \App\Services\KtpStorage::ada($path)) {
            $this->galat = 'File KTP belum tersedia.';
            $this->modalKtpUrl = null;
            $this->modalKtpNama = null;

            return;
        }

        $nama = $targetUserId === $sewaan->anak_kos_id
            ? ($sewaan->anakKos?->nama ?? 'Penyewa')
            : ($sewaan->anggotas->firstWhere('user_id', $targetUserId)?->user?->nama ?? 'Anggota');

        $params = ['sewaan' => $sewaan->id];
        if ($targetUserId !== $sewaan->anak_kos_id) {
            $params['user_id'] = $targetUserId;
        }

        $this->modalKtpUrl = route('penyewaan.ktp', $params);
        $this->modalKtpNama = $nama;
        $this->modalKtpIsPdf = strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf';
    }

    public function tutupModalKtp(): void
    {
        $this->modalKtpUrl = null;
        $this->modalKtpNama = null;
        $this->modalKtpIsPdf = false;
    }

    public function checkOut(int $sewaanId): void
    {
        $user = auth()->user();
        $lihatSemua = $user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super_admin']);

        $sewaan = Penyewaan::where('id', $sewaanId)
            ->where('status', 'aktif')
            ->when(! $lihatSemua, fn ($q) => $q->whereHas('properti', fn ($w) => $w->where('pemilik_id', $user->id)))
            ->with(['anakKos', 'kamar', 'tagihans', 'anggotas'])
            ->first();

        if (! $sewaan) {
            $this->galat = 'Penyewaan tidak ditemukan.';

            return;
        }

        try {
            $hasil = \App\Services\CheckoutService::checkoutPemilik($sewaan, auth()->id());
        } catch (DomainException $e) {
            $this->galat = $e->getMessage();

            return;
        }

        $catatan = $hasil['tagihan_belum_lunas'] > 0
            ? " Perhatian: masih ada {$hasil['tagihan_belum_lunas']} tagihan belum lunas milik penyewa ini."
            : '';

        $batal = ($hasil['dibatalkan'] ?? 0) > 0
            ? ' '.($hasil['dibatalkan']).' tagihan bulan depan dibatalkan.'
            : '';

        $tambahan = $hasil['kamar_tersedia'] ? ' Kamar kembali tersedia.' : ' Kamar tetap terisi karena masih ada anggota patungan yang stay.';

        $this->pesan = "Check-out {$sewaan->anakKos?->nama} dari kamar {$sewaan->kamar?->nama} berhasil.{$tambahan}{$catatan}{$batal}";
    }

    public function setujuiCheckout(int $sewaanId): void
    {
        $this->pesan = null;
        $this->galat = null;
        $user = auth()->user();
        $lihatSemua = $user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super_admin']);

        $sewaan = Penyewaan::where('id', $sewaanId)
            ->where('status', 'aktif')
            ->when(! $lihatSemua, fn ($q) => $q->whereHas('properti', fn ($w) => $w->where('pemilik_id', $user->id)))
            ->with(['anakKos', 'kamar', 'tagihans', 'anggotas', 'properti'])
            ->first();

        if (! $sewaan) {
            $this->galat = 'Penyewaan tidak ditemukan.';

            return;
        }

        try {
            $hasil = \App\Services\CheckoutService::setujuiCheckout($sewaan, $user->id, $lihatSemua);
        } catch (DomainException $e) {
            $this->galat = $e->getMessage();

            return;
        }

        $catatan = $hasil['tagihan_belum_lunas'] > 0
            ? " Perhatian: masih ada {$hasil['tagihan_belum_lunas']} tagihan belum lunas milik penyewa ini."
            : '';

        $batal = ($hasil['dibatalkan'] ?? 0) > 0
            ? ' '.($hasil['dibatalkan']).' tagihan bulan depan dibatalkan.'
            : '';

        $tambahan = $hasil['kamar_tersedia'] ? ' Kamar kembali tersedia.' : ' Kamar tetap terisi karena masih ada anggota patungan yang stay.';

        $this->pesan = "Pengajuan check-out {$sewaan->anakKos?->nama} dari kamar {$sewaan->kamar?->nama} disetujui.{$tambahan}{$catatan}{$batal}";
    }

    public function tolakCheckout(int $sewaanId): void
    {
        $this->pesan = null;
        $this->galat = null;
        $user = auth()->user();
        $lihatSemua = $user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super_admin']);

        $sewaan = Penyewaan::where('id', $sewaanId)
            ->where('status', 'aktif')
            ->when(! $lihatSemua, fn ($q) => $q->whereHas('properti', fn ($w) => $w->where('pemilik_id', $user->id)))
            ->with(['anakKos', 'kamar', 'properti'])
            ->first();

        if (! $sewaan) {
            $this->galat = 'Penyewaan tidak ditemukan.';

            return;
        }

        try {
            \App\Services\CheckoutService::tolakCheckout($sewaan, $user->id, $lihatSemua);
        } catch (DomainException $e) {
            $this->galat = $e->getMessage();

            return;
        }

        $this->pesan = "Pengajuan check-out {$sewaan->anakKos?->nama} dari kamar {$sewaan->kamar?->nama} ditolak. Sewa tetap berjalan aktif.";
    }
}; ?>

<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <a href="{{ route('dashboard') }}" wire:navigate class="text-sm font-medium text-teal-600 dark:text-teal-400 hover:text-teal-500 dark:hover:text-teal-300 inline-flex items-center gap-1">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Kembali ke dashboard
            </a>
            <h1 class="mt-2 text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">Penyewa Saya</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Siapa saja yang menyewa kamar di kos Anda, lengkap dengan tagihan &amp; statusnya.</p>
        </div>

        @if ($pesan)
            <x-notifikasi-popup :pesan="$pesan" judul="Berhasil!" />
        @endif

        @if ($galat)
            <div class="flex items-center justify-between gap-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 ring-1 ring-rose-200 dark:ring-rose-500/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                <span>{{ $galat }}</span>
                <button wire:click="$set('galat', null)" class="font-bold">&times;</button>
            </div>
        @endif

        <div class="card overflow-hidden">
            <div class="px-4 sm:px-6 pt-4 pb-3 border-b border-gray-200 dark:border-gray-700">
                <input type="text" wire:model.live.debounce.300ms="cari" placeholder="Cari nama penyewa..."
                    class="w-full sm:max-w-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Penyewa</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kamar</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Masuk</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tagihan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($sewaans as $sewaan)
                            @php
                                $belumLunas = $sewaan->tagihans->whereNotIn('status', ['lunas', 'batal']);
                                $sisa = $belumLunas->sum(fn ($t) => $t->jumlah + $t->denda);
                                $telat = $belumLunas->filter(fn ($t) => $t->denda > 0)->count();
                                $anggotaAktifRow = $sewaan->anggotas->where('status', 'aktif');
                                $isPatunganRow = ($sewaan->mode_hunian ?? 'tunggal') === 'patungan' || $anggotaAktifRow->isNotEmpty();
                                $kurangBulanIni = \App\Services\CheckoutService::tagihanWajibBelumLunas($sewaan);
                                $blokirCheckout = $kurangBulanIni->isNotEmpty();
                                $alasanBlokir = $blokirCheckout ? 'Ada '.$kurangBulanIni->count().' tagihan sampai bulan ini yang belum lunas.' : null;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $sewaan->anakKos?->nama ?? '-' }}
                                    <span class="block text-[11px] font-normal text-gray-400 dark:text-gray-500">
                                        @if ($sewaan->ktp_path)
                                            <button type="button" wire:click="lihatKtp({{ $sewaan->id }})" class="font-semibold text-brand-700 dark:text-brand-300 hover:underline">Lihat KTP utama</button>
                                        @else
                                            <span class="font-semibold text-amber-600 dark:text-amber-400">KTP utama belum ada</span>
                                        @endif
                                    </span>
                                    @foreach ($anggotaAktifRow as $ag)
                                        <span class="block text-xs font-normal text-gray-500 dark:text-gray-400">+ {{ $ag->user?->nama }} ({{ (int) $ag->porsi_persen }}%)
                                            @if ($ag->ktp_path)
                                                · <button type="button" wire:click="lihatKtp({{ $sewaan->id }}, {{ $ag->user_id }})" class="font-semibold text-brand-700 dark:text-brand-300 hover:underline">Lihat KTP</button>
                                            @else
                                                · <span class="font-semibold text-amber-600 dark:text-amber-400">KTP belum ada</span>
                                            @endif
                                        </span>
                                    @endforeach
                                    @if ($isPatunganRow)
                                        <span class="mt-1 inline-flex items-center rounded-full bg-sky-100 dark:bg-sky-500/10 px-2 py-0.5 text-[10px] font-bold text-sky-700 dark:text-sky-300">Patungan</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    {{ $sewaan->kamar?->nama ?? '-' }}
                                    <span class="block text-xs text-gray-400 dark:text-gray-500">{{ $sewaan->kamar?->properti?->nama }}</span>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $sewaan->tanggal_masuk?->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-4 text-sm">
                                    <span class="font-semibold text-gray-900 dark:text-gray-100">Rp{{ number_format($sisa, 0, ',', '.') }}</span>
                                    <span class="block text-xs {{ $belumLunas->isNotEmpty() ? 'text-rose-500 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                        {{ $belumLunas->isEmpty() ? 'Semua lunas' : $belumLunas->count() . ' tagihan belum lunas' . ($telat > 0 ? " ({$telat} telat)" : '') }}
                                    </span>
                                </td>
                                <td class="px-4 py-4"><x-status-badge :status="$sewaan->status" /></td>
                                <td class="px-4 py-4">
                                    @if ($sewaan->status === 'aktif')
                                        @if ($sewaan->permintaan_keluar_pada)
                                            <div class="flex flex-col items-end gap-1.5">
                                                <span class="inline-flex items-center rounded-full bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-500/30">
                                                    Minta check-out {{ $sewaan->permintaan_keluar_pada->translatedFormat('d M Y') }}
                                                </span>
                                                <div class="flex justify-end gap-1.5">
                                                    @if ($blokirCheckout)
                                                        <button type="button" disabled title="{{ $alasanBlokir }}"
                                                            class="inline-flex items-center rounded-lg bg-gray-200 dark:bg-gray-700 px-3 py-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 cursor-not-allowed opacity-60">
                                                            Setujui
                                                        </button>
                                                    @else
                                                        <button wire:click="setujuiCheckout({{ $sewaan->id }})" wire:loading.attr="disabled"
                                                            wire:confirm="Setujui check-out {{ $sewaan->anakKos?->nama }} dari kamar {{ $sewaan->kamar?->nama }}? Sewa akan ditutup dan kamar kembali tersedia."
                                                            class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500 transition disabled:opacity-50">
                                                            Setujui
                                                        </button>
                                                    @endif
                                                    <button wire:click="tolakCheckout({{ $sewaan->id }})" wire:loading.attr="disabled"
                                                        wire:confirm="Tolak pengajuan check-out {{ $sewaan->anakKos?->nama }}? Sewa tetap berjalan aktif."
                                                        class="inline-flex items-center rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition disabled:opacity-50">
                                                        Tolak
                                                    </button>
                                                </div>
                                                @if ($blokirCheckout)
                                                    <span class="block text-right text-[11px] text-amber-600 dark:text-amber-400">{{ $alasanBlokir }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <div class="flex flex-col items-end gap-1">
                                                @if ($blokirCheckout)
                                                    <button type="button" disabled title="{{ $alasanBlokir }}"
                                                        class="inline-flex items-center rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 px-3 py-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 cursor-not-allowed opacity-60">
                                                        Check-out
                                                    </button>
                                                    <span class="text-[11px] text-amber-600 dark:text-amber-400">{{ $alasanBlokir }}</span>
                                                @else
                                                    <button wire:click="checkOut({{ $sewaan->id }})" wire:loading.attr="disabled"
                                                        wire:confirm="Check-out {{ $sewaan->anakKos?->nama }} dari kamar {{ $sewaan->kamar?->nama }}? Kamar akan kembali tersedia."
                                                        class="inline-flex items-center rounded-lg border border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/10 px-3 py-1.5 text-xs font-semibold text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-500/20 transition disabled:opacity-50">
                                                        Check-out
                                                    </button>
                                                @endif
                                            </div>
                                        @endif
                                    @else
                                        <span class="block text-right text-xs text-gray-400 dark:text-gray-500">
                                            Keluar: {{ $sewaan->tanggal_keluar?->translatedFormat('d M Y') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-400 dark:text-gray-500">Belum ada penyewaan. Penyewaan tercatat otomatis saat anak kos menyewa kamar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($modalKtpUrl)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
                <button type="button" wire:click="tutupModalKtp" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm cursor-default" tabindex="-1" aria-label="Tutup"></button>
                <div class="relative min-h-full flex items-end sm:items-center justify-center p-4">
                    <div class="w-full sm:max-w-2xl bg-white dark:bg-gray-800 rounded-2xl shadow-xl ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-bold text-gray-900 dark:text-gray-100">KTP — {{ $modalKtpNama }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Data sensitif. Jangan disebarluaskan.</p>
                            </div>
                            <button type="button" wire:click="tutupModalKtp" class="rounded-lg px-2 py-1 text-lg font-bold text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" aria-label="Tutup">&times;</button>
                        </div>
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/40">
                            @if ($modalKtpIsPdf)
                                <iframe src="{{ $modalKtpUrl }}" class="h-[60vh] w-full rounded-xl bg-white" title="Pratinjau KTP"></iframe>
                            @else
                                <img src="{{ $modalKtpUrl }}" alt="Foto KTP {{ $modalKtpNama }}" class="mx-auto max-h-[60vh] w-auto rounded-xl object-contain bg-white" />
                            @endif
                        </div>
                        <div class="flex flex-col-reverse sm:flex-row gap-2 p-4 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" wire:click="tutupModalKtp"
                                class="flex-1 inline-flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Tutup
                            </button>
                            <a href="{{ $modalKtpUrl }}" target="_blank" rel="noopener"
                                class="flex-1 inline-flex items-center justify-center rounded-lg bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 transition">
                                Buka di tab baru
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
