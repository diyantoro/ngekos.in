<?php

use App\Models\Properti;
use App\Services\PemilikLaporanPremiumService;
use App\Services\SubscriptionService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $bulan = '';

    public string $periode = '12';

    public ?int $propertiId = null;

    public ?string $galat = null;

    public function mount(): void
    {
        $this->bulan = now()->format('Y-m');
    }

    public function updatedPeriode(): void
    {
        $this->dispatch('laporan:data-updated');
    }

    public function updatedPropertiId(): void
    {
        $this->dispatch('laporan:data-updated');
    }

    public function updatedBulan(): void
    {
        $this->dispatch('laporan:data-updated');
    }

    public function klaimTrial(): void
    {
        $hasil = SubscriptionService::klaimTrialFree(auth()->user());

        if (! $hasil) {
            session()->flash('galat', 'Trial tidak dapat diklaim. Mungkin sudah pernah dipakai atau paket Anda bukan Free.');

            return;
        }

        session()->flash('status', 'Trial PRO 7 hari aktif. Tanpa kartu kredit.');
    }

    public function with(): array
    {
        $user = auth()->user();
        $cek = SubscriptionService::featureCheck($user, 'advanced_report');
        $maxPeriode = SubscriptionService::maxPeriode($user);
        $isFree = ! SubscriptionService::isExempt($user) && SubscriptionService::getPlan($user) === 'free';
        $sisaTrial = SubscriptionService::sisaTrialHari($user);
        $trialHabis = SubscriptionService::trialExpired($user);
        $tier = SubscriptionService::reportTier($user);
        $bisaKlaim = SubscriptionService::bisaKlaimTrial($user);

        if (! $cek['allowed']) {
            return [
                'terkunci' => true,
                'cek' => $cek,
                'bulan' => $this->bulan,
                'periode' => $this->periode,
                'propertiId' => $this->propertiId,
                'maxPeriode' => $maxPeriode,
                'isFree' => $isFree,
                'sisaTrial' => $sisaTrial,
                'trialHabis' => $trialHabis,
                'bisaKlaim' => $bisaKlaim,
                'daftarProperti' => Properti::where('pemilik_id', $user->id)->orderBy('nama')->get(['id', 'nama'])->map(fn ($p) => ['id' => $p->id, 'nama' => $p->nama]),
                'data' => null,
            ];
        }

        try {
            $data = PemilikLaporanPremiumService::data(
                $user->id,
                $this->bulan ?: now()->format('Y-m'),
                SubscriptionService::clampPeriode($user, max(1, (int) $this->periode)),
                $this->propertiId,
                $tier === 'business' ? 'business' : 'pro',
            );
        } catch (InvalidArgumentException $e) {
            $this->galat = $e->getMessage();

            return [
                'terkunci' => false,
                'cek' => $cek,
                'bulan' => $this->bulan,
                'periode' => $this->periode,
                'propertiId' => $this->propertiId,
                'maxPeriode' => $maxPeriode,
                'isFree' => $isFree,
                'sisaTrial' => $sisaTrial,
                'trialHabis' => $trialHabis,
                'daftarProperti' => collect(),
                'data' => null,
            ];
        }

        return [
            'terkunci' => false,
            'cek' => $cek,
            'bulan' => $data['bulan'],
            'periode' => $this->periode,
            'propertiId' => $data['properti_terpilih'],
            'maxPeriode' => $maxPeriode,
            'isFree' => $isFree,
            'sisaTrial' => $sisaTrial,
            'trialHabis' => $trialHabis,
            'daftarProperti' => collect($data['daftar_properti']),
            'data' => $data,
        ];
    }
}; ?>

<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <a href="{{ route('dashboard.pemilik') }}" wire:navigate class="text-sm font-medium text-teal-600 dark:text-teal-400 hover:text-teal-500 inline-flex items-center gap-1">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Kembali ke Dashboard
                </a>
                <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">Laporan Premium</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ringkasan keuangan, tagihan belum bayar, kamar terisi & pemasukan tiap kos.</p>
                @if (! ($terkunci ?? false) && ($data['tier'] ?? null))
                    <span class="mt-2 inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-extrabold uppercase tracking-wider ring-1 {{ ($data['tier'] ?? 'pro') === 'business' ? 'bg-violet-500 text-white ring-violet-500 shadow-lg' : 'bg-teal-600 text-white ring-teal-600 shadow-lg' }}">
                        Paket {{ strtoupper($data['tier']) }}
                    </span>
                    @if (($data['tier'] ?? 'pro') === 'pro')
                        <a href="{{ route('langganan.plans') }}" wire:navigate class="ms-2 text-xs font-semibold text-violet-600 dark:text-violet-400 hover:underline">Naik ke BUSINESS untuk 4 bagian analisis tambahan →</a>
                    @endif
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <input type="month" wire:model.live="bulan"
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                <select wire:model.live="propertiId"
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                    <option value="">Semua Properti</option>
                    @foreach ($daftarProperti as $p)
                        <option value="{{ $p['id'] }}">{{ $p['nama'] }}</option>
                    @endforeach
                </select>
                <select wire:model.live="periode"
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                    <option value="3">3 bulan</option>
                    @if (($maxPeriode ?? 12) >= 6)<option value="6">6 bulan</option>@endif
                    @if (($maxPeriode ?? 12) >= 12)<option value="12">12 bulan</option>@endif
                    @if (($maxPeriode ?? 12) >= 24)<option value="24">24 bulan</option>@endif
                </select>
            </div>
        </div>

        @if (($isFree ?? false) && ($sisaTrial ?? null) !== null && ! ($terkunci ?? false))
            <div class="rounded-xl bg-amber-50 dark:bg-amber-500/10 ring-1 ring-amber-200 dark:ring-amber-500/30 px-4 py-3 text-sm text-amber-800 dark:text-amber-200">
                Masa coba gratis tinggal <strong>{{ $sisaTrial }} hari</strong>. Halaman Laporan dikunci di Free — upgrade ke PRO untuk membukanya.
                <a href="{{ route('langganan.plans') }}" wire:navigate class="font-bold hover:underline">Upgrade</a>
            </div>
        @endif

        @if ($galat)
            <div class="flex items-center justify-between gap-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 ring-1 ring-rose-200 dark:ring-rose-500/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                <span>{{ $galat }}</span>
                <button wire:click="$set('galat', null)" class="font-bold">&times;</button>
            </div>
        @endif

        @if ($terkunci)
            @if (($isFree ?? false) && ($sisaTrial ?? null) === null && ($trialHabis ?? false))
                <div class="max-w-2xl mx-auto space-y-4">
                    <div class="rounded-xl bg-rose-50 dark:bg-rose-500/10 ring-1 ring-rose-200 dark:ring-rose-500/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                        Masa coba 7 hari sudah habis atau belum diklaim. Data tidak hilang, tapi halaman Laporan dikunci. Tambah kos/kamar tetap bisa sampai batas paket Free.
                        <a href="{{ route('langganan.plans') }}" wire:navigate class="font-bold hover:underline">Upgrade ke PRO</a>
                    </div>
                    @if (($bisaKlaim ?? false))
                        <div class="rounded-2xl bg-teal-50 dark:bg-teal-500/10 ring-1 ring-teal-200 dark:ring-teal-500/30 p-6 text-center">
                            <p class="text-base font-extrabold text-teal-800 dark:text-teal-200">Gratis 7 hari fitur PRO, sekali per akun</p>
                            <p class="mt-1 text-sm text-teal-700 dark:text-teal-200/80">Tanpa kartu kredit. Buka laporan premium, grafik 12 bulan, dan ekspor tanpa watermark.</p>
                            <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                                <button wire:click="klaimTrial" wire:loading.attr="disabled" class="inline-flex items-center rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-teal-500 disabled:opacity-50">
                                    Klaim Trial 7 Hari
                                </button>
                                <a href="{{ route('langganan.plans') }}" wire:navigate class="inline-flex items-center rounded-xl ring-1 ring-teal-300 dark:ring-teal-500/40 px-5 py-2.5 text-sm font-bold text-teal-700 dark:text-teal-200 hover:bg-teal-100/60 dark:hover:bg-teal-500/10">
                                    Upgrade PRO
                                </a>
                            </div>
                        </div>
                    @else
                        <x-premium-lock requiredPlan="pro" title="Laporan Premium" message="Tersedia di paket PRO." />
                    @endif
                </div>
            @else
            <div class="max-w-2xl mx-auto">
                <x-premium-lock requiredPlan="pro" title="Laporan Premium" message="Tersedia di paket PRO." />
            </div>
            @endif
        @elseif (! $data)
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-stat-card label="Pendapatan ({{ $data['periode'] }})" :value="'Rp' . number_format($data['ringkasan']['pendapatan'], 0, ',', '.')" tone="emerald"
                    icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>' />
                <x-stat-card label="Pengeluaran ({{ $data['periode'] }})" :value="'Rp' . number_format($data['ringkasan']['pengeluaran'], 0, ',', '.')" tone="rose"
                    icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />
                <x-stat-card label="Untung Bersih" :value="'Rp' . number_format($data['ringkasan']['laba_bersih'], 0, ',', '.')" tone="teal"
                    icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181m8.818 3.181l-3.182-5.511m0 0l-5.511 3.181M4.5 3.75v15m6.75 0v-4.5m3-7.5h.008v.008H14.25V5.25z" /></svg>' />
                <x-stat-card label="Transaksi Terverifikasi" :value="$data['ringkasan']['jumlah_transaksi']" tone="cyan"
                    icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Ringkasan Bulan {{ $data['periode'] }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $data['ringkasan']['total_properti'] }} properti · {{ $data['ringkasan']['total_kamar'] }} kamar · {{ $data['ringkasan']['kamar_terisi'] }} terisi · {{ $data['ringkasan']['penyewaan_aktif'] }} penyewaan aktif</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0 flex-1">
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Ekspor Laporan Premium</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Unduh laporan lengkap dalam PDF atau Excel. Nama file memuat tier paket. Paket BUSINESS mendapat 4 bagian tambahan: rincian tiap kos, transaksi detail, pertumbuhan bulanan, serta metode &amp; top penyewa.</p>
                </div>
                <form method="GET" class="flex flex-wrap items-center gap-2 lg:justify-end" target="_blank" rel="noopener">
                    <input type="month" name="bulan" value="{{ $data['bulan'] }}"
                        class="h-10 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                    <select name="periode" class="h-10 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                        <option value="3" @selected((int) $periode === 3)>3 bulan</option>
                        @if (($maxPeriode ?? 12) >= 6)<option value="6" @selected((int) $periode === 6)>6 bulan</option>@endif
                        @if (($maxPeriode ?? 12) >= 12)<option value="12" @selected((int) $periode === 12)>12 bulan</option>@endif
                        @if (($maxPeriode ?? 12) >= 24)<option value="24" @selected((int) $periode === 24)>24 bulan</option>@endif
                    </select>
                    @if ($propertiId)
                        <input type="hidden" name="properti_id" value="{{ $propertiId }}">
                    @endif
                    <div class="flex items-center gap-2 shrink-0">
                    <button type="submit" formaction="{{ route('pemilik.laporan.pdf') }}"
                        class="inline-flex h-10 shrink-0 whitespace-nowrap items-center justify-center gap-1.5 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                        PDF
                    </button>
                    <button type="submit" formaction="{{ route('pemilik.laporan.excel') }}"
                        class="inline-flex h-10 shrink-0 whitespace-nowrap items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                        Excel
                    </button>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Tagihan Belum Bayar</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Nilai tagihan yang belum dibayar</p>
                    </div>
                    <div class="p-5 grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-gray-50 dark:bg-gray-700/40 p-4">
                            <p class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Belum waktunya bayar</p>
                            <p class="mt-1 text-base font-extrabold text-gray-900 dark:text-gray-100">Rp{{ number_format($data['aging']['belum_jatuh_tempo'], 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-xl bg-amber-50 dark:bg-amber-500/10 p-4">
                            <p class="text-[11px] font-semibold text-amber-600 dark:text-amber-400 uppercase">Baru telat, di bawah seminggu</p>
                            <p class="mt-1 text-base font-extrabold text-amber-700 dark:text-amber-300">Rp{{ number_format($data['aging']['telat_1_7'], 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-xl bg-orange-50 dark:bg-orange-500/10 p-4">
                            <p class="text-[11px] font-semibold text-orange-600 dark:text-orange-400 uppercase">Telat sampai sebulan</p>
                            <p class="mt-1 text-base font-extrabold text-orange-700 dark:text-orange-300">Rp{{ number_format($data['aging']['telat_8_30'], 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-xl bg-rose-50 dark:bg-rose-500/10 p-4">
                            <p class="text-[11px] font-semibold text-rose-600 dark:text-rose-400 uppercase">Telat lebih dari sebulan, segera tagih</p>
                            <p class="mt-1 text-base font-extrabold text-rose-700 dark:text-rose-300">Rp{{ number_format($data['aging']['telat_lebih_30'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <div class="px-5 pb-5">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">Siapa yang belum bayar (maks 50)</p>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-72 overflow-y-auto">
                            @forelse ($data['tagihan_belum'] as $t)
                                <div class="py-2.5 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $t['anak_kos_nama'] }} · {{ $t['kamar_nama'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t['periode'] }} · tempo {{ $t['jatuh_tempo'] }}</p>
                                    </div>
                                    <p class="shrink-0 text-sm font-bold text-rose-600 dark:text-rose-400">Rp{{ number_format($t['jumlah'] + $t['denda'], 0, ',', '.') }}</p>
                                </div>
                            @empty
                                <p class="py-8 text-center text-sm text-gray-400">Semua tagihan lunas. Bagus!</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Kos Pemasukan Terbesar</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Rentang bulan {{ $data['periode_trend'] }}</p>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($data['top_properti'] as $i => $p)
                            <div class="px-5 py-3.5 flex items-center gap-3">
                                <span class="shrink-0 h-8 w-8 rounded-lg bg-violet-50 dark:bg-violet-500/10 text-violet-700 dark:text-violet-300 text-sm font-extrabold flex items-center justify-center">{{ $i + 1 }}</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $p['nama'] }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $p['kamar_terisi'] }}/{{ $p['total_kamar'] }} terisi</p>
                                </div>
                                <p class="shrink-0 text-sm font-bold text-emerald-600 dark:text-emerald-400">Rp{{ number_format($p['pendapatan'], 0, ',', '.') }}</p>
                            </div>
                        @empty
                            <p class="px-5 py-10 text-center text-sm text-gray-400">Belum ada data.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Naik Turun Tiap Bulan</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $data['periode_trend'] }} · uang masuk, uang keluar, untung bersih, dan kamar terisi</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bulan</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pendapatan</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pengeluaran</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Untung Bersih</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kamar Terisi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($data['trend']['labels'] as $i => $label)
                                @php
                                    $labaNilai = $data['trend']['laba'][$i];
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $label }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-emerald-600 dark:text-emerald-400">Rp{{ number_format($data['trend']['pendapatan'][$i], 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-rose-600 dark:text-rose-400">Rp{{ number_format($data['trend']['pengeluaran'][$i], 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-right {{ $labaNilai >= 0 ? 'text-teal-600 dark:text-teal-400' : 'text-rose-600 dark:text-rose-400' }}">Rp{{ number_format($labaNilai, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-700 dark:text-gray-300">{{ $data['trend']['okupansi'][$i] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if (($data['tier'] ?? 'pro') === 'business')
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Pertumbuhan Bulan ke Bulan</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Khusus BUSINESS · persen naik/turun vs bulan sebelumnya</p>
                        </div>
                        <span class="shrink-0 whitespace-nowrap rounded-full bg-violet-500 px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-white">Business</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bulan</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pendapatan</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">±</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Untung</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">±</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse ($data['pertumbuhan'] ?? [] as $p)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $p['bulan'] }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-emerald-600 dark:text-emerald-400">Rp{{ number_format($p['pendapatan'], 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-bold {{ $p['pendapatan_pct'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">{{ $p['pendapatan_pct'] }}%</td>
                                        <td class="px-4 py-3 text-sm text-right text-teal-600 dark:text-teal-400">Rp{{ number_format($p['laba'], 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-bold {{ $p['laba_pct'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">{{ $p['laba_pct'] }}%</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">Belum ada data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Metode Pembayaran</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Khusus BUSINESS · transaksi terverifikasi</p>
                            </div>
                            <span class="shrink-0 whitespace-nowrap rounded-full bg-violet-500 px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-white">Business</span>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse ($data['metode_pembayaran'] ?? [] as $m)
                                <div class="px-5 py-3.5 flex items-center justify-between gap-3">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $m['label'] }} <span class="font-normal text-xs text-gray-500">· {{ $m['jumlah_transaksi'] }} transaksi</span></p>
                                    <p class="shrink-0 text-sm font-bold text-emerald-600 dark:text-emerald-400">Rp{{ number_format($m['total'], 0, ',', '.') }}</p>
                                </div>
                            @empty
                                <p class="px-5 py-10 text-center text-sm text-gray-400">Belum ada data.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Top 10 Penyewa</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Khusus BUSINESS · pembayaran terbesar</p>
                            </div>
                            <span class="shrink-0 whitespace-nowrap rounded-full bg-violet-500 px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-white">Business</span>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse ($data['top_penyewa'] ?? [] as $i => $p)
                                <div class="px-5 py-3 flex items-center gap-3">
                                    <span class="shrink-0 h-7 w-7 rounded-lg bg-violet-50 dark:bg-violet-500/10 text-violet-700 dark:text-violet-300 text-xs font-extrabold flex items-center justify-center">{{ $i + 1 }}</span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $p['nama'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $p['jumlah_transaksi'] }} transaksi</p>
                                    </div>
                                    <p class="shrink-0 text-sm font-bold text-emerald-600 dark:text-emerald-400">Rp{{ number_format($p['total'], 0, ',', '.') }}</p>
                                </div>
                            @empty
                                <p class="px-5 py-10 text-center text-sm text-gray-400">Belum ada data.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Rincian Tiap Kos</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Pendapatan, pengeluaran & untung bersih per kos</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kos</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Terisi</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Masuk</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Keluar</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Untung</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @forelse ($data['rincian_tiap_kos'] ?? [] as $r)
                                        <tr>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $r['nama'] }}</td>
                                            <td class="px-4 py-3 text-sm text-right text-gray-700 dark:text-gray-300">{{ $r['kamar_terisi'] }}/{{ $r['total_kamar'] }} ({{ $r['tingkat_terisi'] }}%)</td>
                                            <td class="px-4 py-3 text-sm text-right text-emerald-600 dark:text-emerald-400">Rp{{ number_format($r['pendapatan'], 0, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-sm text-right text-rose-600 dark:text-rose-400">Rp{{ number_format($r['pengeluaran'], 0, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-sm text-right text-teal-600 dark:text-teal-400">Rp{{ number_format($r['untung_bersih'], 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">Belum ada data.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Daftar Transaksi Detail</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">200 transaksi terakhir yang terverifikasi</p>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-96 overflow-y-auto">
                            @forelse ($data['transaksi_detail'] ?? [] as $t)
                                <div class="px-5 py-2.5 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $t['penyewa'] }} · {{ $t['kamar'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t['kos'] }} · {{ $t['periode'] }} · {{ $t['tanggal'] }}</p>
                                    </div>
                                    <p class="shrink-0 text-sm font-bold text-emerald-600 dark:text-emerald-400">Rp{{ number_format($t['jumlah'], 0, ',', '.') }}</p>
                                </div>
                            @empty
                                <p class="px-5 py-10 text-center text-sm text-gray-400">Belum ada data.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>