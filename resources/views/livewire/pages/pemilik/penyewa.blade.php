<?php

use App\Models\Penyewaan;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $cari = '';

    public ?string $pesan = null;

    public ?string $galat = null;

    public function with(): array
    {
        $user = auth()->user();
        $lihatSemua = $user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super_admin']);
        $scope = fn ($q) => $lihatSemua ? $q : $q->where('pemilik_id', $user->id);

        return [
            'sewaans' => Penyewaan::query()
                ->whereHas('properti', $scope)
                ->select(['id', 'anak_kos_id', 'kamar_id', 'properti_id', 'tanggal_masuk', 'tanggal_keluar', 'tanggal_berakhir', 'status', 'permintaan_keluar_pada'])
                ->with(['anakKos:id,nama', 'kamar:id,nama,properti_id', 'kamar.properti:id,nama', 'tagihans:id,penyewaan_id,periode,jumlah,jenis,denda,status,jatuh_tempo', 'tagihans.pembayarans:id,tagihan_id,anak_kos_id,jumlah,bukti,status'])
                ->when($this->cari, fn ($q) => $q->whereHas('anakKos', fn ($q) => $q->where('nama', 'like', "%{$this->cari}%")))
                ->latest()
                ->paginate(20),
        ];
    }

    public function checkOut(int $sewaanId): void
    {
        $user = auth()->user();
        $lihatSemua = $user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super_admin']);

        $sewaan = Penyewaan::where('id', $sewaanId)
            ->where('status', 'aktif')
            ->when(! $lihatSemua, fn ($q) => $q->whereHas('properti', fn ($w) => $w->where('pemilik_id', $user->id)))
            ->with(['anakKos', 'kamar', 'tagihans'])
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

        $tambahan = $hasil['kamar_tersedia'] ? ' Kamar kembali tersedia.' : '';

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
            ->with(['anakKos', 'kamar', 'tagihans', 'properti'])
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

        $tambahan = $hasil['kamar_tersedia'] ? ' Kamar kembali tersedia.' : '';

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

    public function setujuiBooking(int $sewaanId): void
    {
        $this->pesan = null;
        $this->galat = null;
        $user = auth()->user();
        $lihatSemua = $user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super_admin']);

        $sewaan = Penyewaan::where('id', $sewaanId)
            ->where('status', 'menunggu_verifikasi')
            ->when(! $lihatSemua, fn ($q) => $q->whereHas('properti', fn ($w) => $w->where('pemilik_id', $user->id)))
            ->with(['anakKos', 'kamar', 'tagihans.pembayarans', 'properti'])
            ->first();

        if (! $sewaan) {
            $this->galat = 'Booking tidak ditemukan.';

            return;
        }

        try {
            $hasil = app(\App\Services\PenyewaanService::class)->setujuiBooking($sewaan, $user->id, $lihatSemua);
        } catch (DomainException $e) {
            $this->galat = $e->getMessage();

            return;
        }

        $kwi = $hasil['pembayaran']->nomor_kwitansi ? " Kwitansi {$hasil['pembayaran']->nomor_kwitansi} otomatis dibuat." : '';
        $this->pesan = "Booking {$sewaan->anakKos?->nama} (kamar {$sewaan->kamar?->nama}) disetujui — booking fiks (Dibooking). Tandai SUDAH MASUK saat dia datang; pembayaran sewa pertama juga otomatis mengaktifkan sewa.{$kwi}";
    }

    public function tolakBooking(int $sewaanId): void
    {
        $this->pesan = null;
        $this->galat = null;
        $user = auth()->user();
        $lihatSemua = $user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super_admin']);

        $sewaan = Penyewaan::where('id', $sewaanId)
            ->where('status', 'menunggu_verifikasi')
            ->when(! $lihatSemua, fn ($q) => $q->whereHas('properti', fn ($w) => $w->where('pemilik_id', $user->id)))
            ->with(['anakKos', 'kamar', 'tagihans.pembayarans', 'properti'])
            ->first();

        if (! $sewaan) {
            $this->galat = 'Booking tidak ditemukan.';

            return;
        }

        try {
            app(\App\Services\PenyewaanService::class)->tolakBooking($sewaan, $user->id, $lihatSemua);
        } catch (DomainException $e) {
            $this->galat = $e->getMessage();

            return;
        }

        $this->pesan = "Booking {$sewaan->anakKos?->nama} (kamar {$sewaan->kamar?->nama}) ditolak — booking dibatalkan dan kamar kembali tersedia.";
    }

    /**
     * Pemilik menandai penyewa SUDAH MASUK (dibooking -> aktif).
     */
    public function tandaiMasuk(int $sewaanId): void
    {
        $this->pesan = null;
        $this->galat = null;
        $user = auth()->user();
        $lihatSemua = $user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super_admin']);

        $sewaan = Penyewaan::where('id', $sewaanId)
            ->where('status', 'dibooking')
            ->when(! $lihatSemua, fn ($q) => $q->whereHas('properti', fn ($w) => $w->where('pemilik_id', $user->id)))
            ->with(['anakKos', 'kamar', 'properti'])
            ->first();

        if (! $sewaan) {
            $this->galat = 'Sewaan tidak ditemukan atau tidak berstatus Dibooking.';

            return;
        }

        try {
            \App\Services\CheckoutService::tandaiMasuk($sewaan, $user->id, $lihatSemua);
        } catch (DomainException $e) {
            $this->galat = $e->getMessage();

            return;
        }

        $this->pesan = "{$sewaan->anakKos?->nama} ditandai SUDAH MASUK kamar {$sewaan->kamar?->nama}. Sewa kini aktif — checkout wajib lunas bulan berjalan.";
    }

    /**
     * Pemilik menyetujui AJUKAN BATAL sewa (dibooking, belum masuk).
     */
    public function setujuiBatal(int $sewaanId): void
    {
        $this->pesan = null;
        $this->galat = null;
        $user = auth()->user();
        $lihatSemua = $user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super_admin']);

        $sewaan = Penyewaan::where('id', $sewaanId)
            ->where('status', 'dibooking')
            ->whereNotNull('permintaan_keluar_pada')
            ->when(! $lihatSemua, fn ($q) => $q->whereHas('properti', fn ($w) => $w->where('pemilik_id', $user->id)))
            ->with(['anakKos', 'kamar', 'tagihans', 'properti'])
            ->first();

        if (! $sewaan) {
            $this->galat = 'Pengajuan tidak ditemukan.';

            return;
        }

        $dp = $sewaan->tagihans->first(fn ($t) => \App\Services\PenyewaanService::isDp($t));

        try {
            \App\Services\CheckoutService::setujuiBatalSewa($sewaan, $user->id, $lihatSemua);
        } catch (DomainException $e) {
            $this->galat = $e->getMessage();

            return;
        }

        $this->pesan = 'Pembatalan sewa '.$sewaan->anakKos?->nama.' di kamar '.$sewaan->kamar?->nama.' disetujui. '
            .'DP Rp'.number_format((float) ($dp?->jumlah ?? 0), 0, ',', '.').' hangus untukmu (tidak dikembalikan). Kamar kembali tersedia.';
    }

    /**
     * Pemilik menolak AJUKAN BATAL sewa.
     */
    public function tolakBatal(int $sewaanId): void
    {
        $this->pesan = null;
        $this->galat = null;
        $user = auth()->user();
        $lihatSemua = $user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super_admin']);

        $sewaan = Penyewaan::where('id', $sewaanId)
            ->where('status', 'dibooking')
            ->whereNotNull('permintaan_keluar_pada')
            ->when(! $lihatSemua, fn ($q) => $q->whereHas('properti', fn ($w) => $w->where('pemilik_id', $user->id)))
            ->with(['anakKos', 'kamar', 'properti'])
            ->first();

        if (! $sewaan) {
            $this->galat = 'Pengajuan tidak ditemukan.';

            return;
        }

        try {
            \App\Services\CheckoutService::tolakBatalSewa($sewaan, $user->id, $lihatSemua);
        } catch (DomainException $e) {
            $this->galat = $e->getMessage();

            return;
        }

        $this->pesan = 'Pengajuan batal sewa '.$sewaan->anakKos?->nama.' ditolak. Booking tetap fiks — tunggu dia masuk.';
    }

    /**
     * Pemilik membatalkan sendiri: toleransi telat masuk sudah lewat.
     */
    public function batalOtomatis(int $sewaanId): void
    {
        $this->pesan = null;
        $this->galat = null;
        $user = auth()->user();
        $lihatSemua = $user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super_admin']);

        $sewaan = Penyewaan::where('id', $sewaanId)
            ->where('status', 'dibooking')
            ->when(! $lihatSemua, fn ($q) => $q->whereHas('properti', fn ($w) => $w->where('pemilik_id', $user->id)))
            ->with(['anakKos', 'kamar', 'tagihans', 'properti'])
            ->first();

        if (! $sewaan) {
            $this->galat = 'Sewaan tidak ditemukan atau tidak berstatus Dibooking.';

            return;
        }

        $dp = $sewaan->tagihans->first(fn ($t) => \App\Services\PenyewaanService::isDp($t));

        try {
            \App\Services\CheckoutService::batalOtomatis($sewaan, $user->id);
        } catch (DomainException $e) {
            $this->galat = $e->getMessage();

            return;
        }

        $this->pesan = 'Booking '.$sewaan->anakKos?->nama.' (kamar '.$sewaan->kamar?->nama.') dibatalkan — telat masuk lewat batas. '
            .'DP Rp'.number_format((float) ($dp?->jumlah ?? 0), 0, ',', '.').' hangus untukmu. Kamar kembali tersedia.';
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
                                $kurangBulanIni = \App\Services\CheckoutService::tagihanWajibBelumLunas($sewaan);
                                $blokirCheckout = $kurangBulanIni->isNotEmpty();
                                $alasanBlokir = $blokirCheckout ? 'Ada '.$kurangBulanIni->count().' tagihan sampai bulan ini yang belum lunas.' : null;
                                $masaDepan = \App\Services\CheckoutService::tagihanMasaDepan($sewaan);
                                $masaDepanTotal = $masaDepan->sum(fn ($t) => (float) $t->jumlah + (float) $t->denda);
                                // DP = pembayaran terverifikasi pertama. Tanpa itu booking belum fiks.
                                $dpLunas = $sewaan->tagihans->flatMap(fn ($t) => $t->pembayarans)->where('status', 'diverifikasi')->isNotEmpty();
                                $dpTagihanRow = $sewaan->tagihans->first(fn ($t) => ($t->jenis ?? 'sewa') === 'dp');
                                $dpBayarRow = $dpTagihanRow
                                    ? $dpTagihanRow->pembayarans->firstWhere('status', 'menunggu_verifikasi')
                                        ?? $dpTagihanRow->pembayarans->firstWhere('status', 'diverifikasi')
                                    : null;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $sewaan->anakKos?->nama ?? '-' }}
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
                                <td class="px-4 py-4"><x-status-badge :status="$sewaan->status" />
                                    @if ($sewaan->status === 'menunggu_verifikasi')
                                        <span class="mt-1 inline-flex items-center rounded-full bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-500/30">Booking: cek bukti DP lalu Setujui/Tolak</span>
                                    @elseif (! $dpLunas)
                                        <span class="mt-1 inline-flex items-center rounded-full bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-500/30">Menunggu DP</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    @if ($sewaan->status === 'menunggu_verifikasi')
                                        <div class="flex flex-col items-end gap-1.5">
                                            @if ($dpTagihanRow)
                                                <span class="text-[11px] font-semibold text-gray-600 dark:text-gray-300">DP Rp{{ number_format($dpTagihanRow->jumlah, 0, ',', '.') }}</span>
                                            @endif
                                            @if ($dpBayarRow?->bukti)
                                                <a href="{{ route('pembayaran.bukti', $dpBayarRow->id) }}" target="_blank" rel="noopener"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-teal-200 dark:border-teal-500/30 bg-teal-50 dark:bg-teal-500/10 px-2.5 py-1.5 text-xs font-semibold text-teal-700 dark:text-teal-300 hover:bg-teal-100 dark:hover:bg-teal-500/20 transition">
                                                    Lihat Bukti DP
                                                </a>
                                            @else
                                                <span class="text-[11px] italic text-gray-400">Bukti belum ada</span>
                                            @endif
                                            <div class="flex justify-end gap-1.5">
                                                <button wire:click="setujuiBooking({{ $sewaan->id }})" wire:loading.attr="disabled"
                                                    wire:confirm="Setujui booking {{ $sewaan->anakKos?->nama }} (kamar {{ $sewaan->kamar?->nama }})? Booking jadi fiks (Dibooking); tandai SUDAH MASUK saat dia datang."
                                                    class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500 transition disabled:opacity-50">
                                                    Setujui
                                                </button>
                                                <button wire:click="tolakBooking({{ $sewaan->id }})" wire:loading.attr="disabled"
                                                    wire:confirm="Tolak booking {{ $sewaan->anakKos?->nama }}? Booking dibatalkan dan kamar kembali tersedia."
                                                    class="inline-flex items-center rounded-lg border border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/10 px-3 py-1.5 text-xs font-semibold text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-500/20 transition disabled:opacity-50">
                                                    Tolak
                                                </button>
                                            </div>
                                        </div>
                                    @elseif ($sewaan->status === 'dibooking')
                                        @php
                                            $hariTelat = \App\Services\CheckoutService::hariTelatMasuk($sewaan);
                                            $toleransi = \App\Services\CheckoutService::TOLERANSI_BATAL_HARI;
                                            $batas = $sewaan->tanggal_masuk?->copy()->addDays($toleransi);
                                        @endphp
                                        <div class="flex flex-col items-end gap-1.5">
                                            @if ($sewaan->permintaan_keluar_pada)
                                                <span class="inline-flex items-center rounded-full bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-500/30">
                                                    Minta BATAL sewa — {{ $sewaan->permintaan_keluar_pada->translatedFormat('d M Y') }}
                                                </span>
                                                <div class="max-w-[16rem] rounded-lg bg-rose-50 dark:bg-rose-500/10 ring-1 ring-rose-200 dark:ring-rose-500/30 px-2.5 py-1.5 text-left">
                                                    <p class="text-[11px] font-bold text-rose-700 dark:text-rose-300">Konsekuensi batal sewa awal</p>
                                                    <p class="mt-0.5 text-[11px] text-rose-700 dark:text-rose-300">DP Rp{{ number_format((float) ($dpTagihanRow?->jumlah ?? 0), 0, ',', '.') }} HANGUS (tidak dikembalikan). Semua tagihan lain dibatalkan, kamar langsung tersedia.</p>
                                                </div>
                                                <div class="flex justify-end gap-1.5">
                                                    <button wire:click="setujuiBatal({{ $sewaan->id }})" wire:loading.attr="disabled"
                                                        wire:confirm="Setujui BATAL sewa {{ $sewaan->anakKos?->nama }} (kamar {{ $sewaan->kamar?->nama }})? DP hangus untukmu, kamar langsung tersedia."
                                                        class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500 transition disabled:opacity-50">
                                                        Setujui Batal
                                                    </button>
                                                    <button wire:click="tolakBatal({{ $sewaan->id }})" wire:loading.attr="disabled"
                                                        wire:confirm="Tolak pengajuan batal {{ $sewaan->anakKos?->nama }}? Booking tetap fiks, tunggu dia masuk."
                                                        class="inline-flex items-center rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition disabled:opacity-50">
                                                        Tolak
                                                    </button>
                                                </div>
                                            @else
                                                @if ($hariTelat > 0)
                                                    <span class="inline-flex items-center rounded-full bg-rose-50 dark:bg-rose-500/10 px-2 py-0.5 text-[10px] font-bold text-rose-700 dark:text-rose-300 ring-1 ring-rose-200 dark:ring-rose-500/30">
                                                        Telat masuk {{ $hariTelat }} hari
                                                    </span>
                                                    @if ($hariTelat > $toleransi)
                                                        <span class="inline-flex items-center rounded-full bg-rose-50 dark:bg-rose-500/10 px-2 py-0.5 text-[10px] font-bold text-rose-700 dark:text-rose-300 ring-1 ring-rose-200 dark:ring-rose-500/30">
                                                            Lewat batas ({{ $toleransi }} hari) — bisa batal
                                                        </span>
                                                    @else
                                                        <span class="block text-right text-[11px] text-amber-600 dark:text-amber-400">Bisa batal otomatis mulai {{ $batas?->translatedFormat('d M Y') }} (telat &gt; {{ $toleransi }} hari).</span>
                                                    @endif
                                                @else
                                                    <span class="block text-right text-[11px] text-gray-500 dark:text-gray-400">Belum masuk. Batas batal otomatis: {{ $batas?->translatedFormat('d M Y') }}.</span>
                                                @endif
                                                <div class="flex justify-end gap-1.5">
                                                    <button wire:click="tandaiMasuk({{ $sewaan->id }})" wire:loading.attr="disabled"
                                                        wire:confirm="Tandai {{ $sewaan->anakKos?->nama }} SUDAH MASUK kamar {{ $sewaan->kamar?->nama }}? Sewa langsung aktif dan checkout ketat berlaku."
                                                        class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500 transition disabled:opacity-50">
                                                        Sudah Masuk
                                                    </button>
                                                    @if ($hariTelat > $toleransi)
                                                        <button wire:click="batalOtomatis({{ $sewaan->id }})" wire:loading.attr="disabled"
                                                            wire:confirm="BATALKAN booking {{ $sewaan->anakKos?->nama }} (kamar {{ $sewaan->kamar?->nama }})? Telat masuk lewat {{ $toleransi }} hari. DP hangus untukmu, kamar langsung tersedia."
                                                            class="inline-flex items-center rounded-lg border border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/10 px-3 py-1.5 text-xs font-semibold text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-500/20 transition disabled:opacity-50">
                                                            Batal Sewa
                                                        </button>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @elseif ($sewaan->status === 'aktif')
                                        @if ($sewaan->permintaan_keluar_pada)
                                            <div class="flex flex-col items-end gap-1.5">
                                                <span class="inline-flex items-center rounded-full bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-500/30">
                                                    Minta check-out {{ $sewaan->permintaan_keluar_pada->translatedFormat('d M Y') }}
                                                </span>
                                                <div class="max-w-[16rem] rounded-lg bg-sky-50 dark:bg-sky-500/10 ring-1 ring-sky-200 dark:ring-sky-500/30 px-2.5 py-1.5 text-left">
                                                    <p class="text-[11px] font-bold text-sky-700 dark:text-sky-300">Penjelasan tagihan check-out</p>
                                                    @if ($blokirCheckout)
                                                        <p class="mt-0.5 text-[11px] text-amber-700 dark:text-amber-300">Belum bisa disetujui: {{ strtolower($alasanBlokir) }} Minta penyewa melunasinya dulu.</p>
                                                    @else
                                                        <p class="mt-0.5 text-[11px] text-sky-700 dark:text-sky-300">Tagihan sampai bulan ini sudah lunas, boleh disetujui.</p>
                                                    @endif
                                                    @if ($masaDepan->isNotEmpty())
                                                        <p class="mt-1 text-[11px] text-gray-600 dark:text-gray-300">Bila disetujui, {{ $masaDepan->count() }} tagihan bulan depan otomatis <span class="font-bold">dibatalkan</span> (total Rp{{ number_format($masaDepanTotal, 0, ',', '.') }}):</p>
                                                        <ul class="mt-0.5 list-disc pl-4 text-[11px] text-gray-600 dark:text-gray-300">
                                                            @foreach ($masaDepan->take(4) as $t)
                                                                <li>{{ $t->periode }} — Rp{{ number_format($t->jumlah, 0, ',', '.') }}</li>
                                                            @endforeach
                                                            @if ($masaDepan->count() > 4)
                                                                <li>dan {{ $masaDepan->count() - 4 }} lainnya…</li>
                                                            @endif
                                                        </ul>
                                                    @else
                                                        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">Tidak ada tagihan bulan depan yang perlu dibatalkan.</p>
                                                    @endif
                                                </div>
                                                <div class="flex justify-end gap-1.5">
                                                    @if ($blokirCheckout)
                                                        <button type="button" disabled title="{{ $alasanBlokir }}"
                                                            class="inline-flex items-center rounded-lg bg-gray-200 dark:bg-gray-700 px-3 py-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 cursor-not-allowed opacity-60">
                                                            Setujui
                                                        </button>
                                                    @else
                                                        <button wire:click="setujuiCheckout({{ $sewaan->id }})" wire:loading.attr="disabled"
                                                            wire:confirm="Setujui check-out {{ $sewaan->anakKos?->nama }} dari kamar {{ $sewaan->kamar?->nama }}? Sewa akan ditutup, kamar kembali tersedia{{ $masaDepan->isNotEmpty() ? ', dan '.$masaDepan->count().' tagihan bulan depan (Rp'.number_format($masaDepanTotal, 0, ',', '.').') akan dibatalkan' : '' }}."
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
                                                        wire:confirm="Check-out {{ $sewaan->anakKos?->nama }} dari kamar {{ $sewaan->kamar?->nama }}? Kamar akan kembali tersedia{{ $masaDepan->isNotEmpty() ? ', dan '.$masaDepan->count().' tagihan bulan depan (Rp'.number_format($masaDepanTotal, 0, ',', '.').') akan dibatalkan' : '' }}."
                                                        class="inline-flex items-center rounded-lg border border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/10 px-3 py-1.5 text-xs font-semibold text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-500/20 transition disabled:opacity-50">
                                                        Check-out
                                                    </button>
                                                    @if ($masaDepan->isNotEmpty())
                                                        <span class="text-right text-[11px] text-gray-500 dark:text-gray-400">{{ $masaDepan->count() }} tagihan bulan depan (Rp{{ number_format($masaDepanTotal, 0, ',', '.') }}) akan dibatalkan saat check-out.</span>
                                                    @endif
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
            @if ($sewaans->hasPages())
                <div class="px-4 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $sewaans->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
