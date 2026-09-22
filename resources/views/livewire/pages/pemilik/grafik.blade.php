<?php

use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use App\Models\Penyewaan;
use App\Models\Properti;
use App\Models\Tagihan;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $periode = '12';

    public ?int $propertiId = null;

    public function updatedPeriode(): void
    {
        $this->dispatch('chart:data-updated');
    }

    public function updatedPropertiId(): void
    {
        $this->dispatch('chart:data-updated');
    }

    public function with(): array
    {
        $user = auth()->user();
        $id = $user->id;
        $propertiId = $this->propertiId;
        $maxPeriode = SubscriptionService::maxPeriode($user);
        $bulanCount = SubscriptionService::clampPeriode($user, max(1, (int) $this->periode));
        $isFree = ! SubscriptionService::isExempt($user) && SubscriptionService::getPlan($user) === 'free';
        $sisaTrial = SubscriptionService::sisaTrialHari($user);
        $trialHabis = SubscriptionService::trialExpired($user);
        $laporanKunci = SubscriptionService::laporanDikunci($user);

        if ($laporanKunci) {
            return [
                'terkunci' => true,
                'cek' => SubscriptionService::cekLaporan($user),
                'daftarProperti' => Properti::where('pemilik_id', $id)->orderBy('nama')->get(['id', 'nama']),
                'maxPeriode' => $maxPeriode,
                'isFree' => $isFree,
                'sisaTrial' => $sisaTrial,
                'trialHabis' => $trialHabis,
                'totalKamar' => 0,
                'bulanLabels' => [],
                'chartPendapatan' => [],
                'chartPengeluaran' => [],
                'chartLaba' => [],
                'chartOkupansi' => [],
                'chartTransaksi' => [],
                'chartLunas' => [],
                'chartBelum' => [],
                'chartKategori' => [],
                'totalPendapatan' => 0,
                'totalPengeluaran' => 0,
                'totalTransaksi' => 0,
                'aging' => ['belum_jatuh_tempo' => 0, 'telat_1_7' => 0, 'telat_8_30' => 0, 'telat_lebih_30' => 0],
                'tagihanBelum' => collect(),
                'topProperti' => [],
            ];
        }

        $scopeId = fn ($q) => $q->where('pemilik_id', $id)
            ->when($propertiId, fn ($w) => $w->where('propertis.id', $propertiId));

        $daftarProperti = Properti::where('pemilik_id', $id)
            ->withCount([
                'kamars as total_kamar',
                'kamars as kamar_terisi' => fn ($q) => $q->where('status', 'terisi'),
            ])
            ->orderBy('nama')
            ->get(['id', 'nama']);

        $totalKamar = (int) $daftarProperti->when($propertiId, fn ($c) => $c->where('id', $propertiId))->sum('total_kamar');

        $mulai = now()->startOfMonth()->subMonths($bulanCount - 1);

        $pendapatanPerBulan = Pembayaran::where('status', 'diverifikasi')
            ->whereHas('tagihan.penyewaan.properti', $scopeId)
            ->where('verified_at', '>=', $mulai)
            ->get(['verified_at', 'jumlah'])
            ->groupBy(fn ($p) => $p->verified_at->format('m/Y'))
            ->map(fn ($rows) => (int) $rows->sum('jumlah'));

        $pengeluaranPerBulan = Pengeluaran::whereHas('properti', fn ($q) => $q->where('pemilik_id', $id)
                ->when($propertiId, fn ($w) => $w->where($w->getModel()->getTable().'.id', $propertiId)))
            ->where('tanggal', '>=', $mulai->toDateString())
            ->get(['tanggal', 'jumlah'])
            ->groupBy(fn ($p) => $p->tanggal->format('m/Y'))
            ->map(fn ($rows) => (int) round($rows->sum('jumlah')));

        $transaksiPerBulan = Pembayaran::where('status', 'diverifikasi')
            ->whereHas('tagihan.penyewaan.properti', $scopeId)
            ->where('verified_at', '>=', $mulai)
            ->get(['verified_at'])
            ->groupBy(fn ($p) => $p->verified_at->format('m/Y'))
            ->map(fn ($rows) => $rows->count());

        $lunasPerBulan = Tagihan::where('status', 'lunas')
            ->whereHas('penyewaan.properti', $scopeId)
            ->where('updated_at', '>=', $mulai)
            ->get(['updated_at', 'jumlah', 'denda'])
            ->groupBy(fn ($t) => $t->updated_at->format('m/Y'))
            ->map(fn ($rows) => (int) $rows->sum(fn ($t) => (float) $t->jumlah + (float) $t->denda));

        $belumPerBulan = Tagihan::where('status', '!=', 'lunas')
            ->whereHas('penyewaan.properti', $scopeId)
            ->where('created_at', '>=', $mulai)
            ->get(['created_at', 'jumlah', 'denda'])
            ->groupBy(fn ($t) => $t->created_at->format('m/Y'))
            ->map(fn ($rows) => (int) $rows->sum(fn ($t) => (float) $t->jumlah + (float) $t->denda));

        $bulanLabels = [];
        $chartPendapatan = [];
        $chartPengeluaran = [];
        $chartLaba = [];
        $chartOkupansi = [];
        $chartTransaksi = [];
        $chartLunas = [];
        $chartBelum = [];

        $sewaanOkupansi = Penyewaan::whereHas('properti', $scopeId)
            ->where('tanggal_masuk', '<=', now()->endOfMonth())
            ->where(fn ($w) => $w->whereNull('tanggal_keluar')->orWhere('tanggal_keluar', '>=', $mulai))
            ->get(['kamar_id', 'tanggal_masuk', 'tanggal_keluar']);

        foreach (range($bulanCount - 1, 0) as $i) {
            $start = now()->startOfMonth()->subMonths($i);
            $end = $start->copy()->endOfMonth();
            $key = $start->format('m/Y');
            $bulanLabels[] = $start->translatedFormat('M Y');

            $p = (int) ($pendapatanPerBulan[$key] ?? 0);
            $g = (int) ($pengeluaranPerBulan[$key] ?? 0);
            $chartPendapatan[] = $p;
            $chartPengeluaran[] = $g;
            $chartLaba[] = $p - $g;
            $chartTransaksi[] = (int) ($transaksiPerBulan[$key] ?? 0);
            $chartLunas[] = (int) ($lunasPerBulan[$key] ?? 0);
            $chartBelum[] = (int) ($belumPerBulan[$key] ?? 0);

            $terisi = $sewaanOkupansi
                ->filter(fn ($s) => $s->tanggal_masuk->lte($end) && ($s->tanggal_keluar === null || $s->tanggal_keluar->gte($start)))
                ->pluck('kamar_id')
                ->unique()
                ->count();

            $chartOkupansi[] = $totalKamar > 0 ? (int) round($terisi / $totalKamar * 100) : 0;
        }

        $chartKategori = Pengeluaran::whereHas('properti', fn ($q) => $q->where('pemilik_id', $id)
                ->when($propertiId, fn ($w) => $w->where($w->getModel()->getTable().'.id', $propertiId)))
            ->where('tanggal', '>=', $mulai->toDateString())
            ->get(['kategori', 'jumlah'])
            ->groupBy('kategori')
            ->map(fn ($rows) => (int) round($rows->sum('jumlah')))
            ->sortDesc()
            ->take(7)
            ->map(fn ($nilai, $kategori) => ['label' => str($kategori)->title(), 'value' => $nilai])
            ->values()
            ->all();

        // Aging piutang.
        $belum = Tagihan::where('status', '!=', 'lunas')
            ->whereHas('penyewaan.properti', $scopeId)
            ->with(['penyewaan.anakKos:id,nama', 'penyewaan.kamar:id,nama'])
            ->orderBy('jatuh_tempo')
            ->limit(50)
            ->get();

        $aging = ['belum_jatuh_tempo' => 0, 'telat_1_7' => 0, 'telat_8_30' => 0, 'telat_lebih_30' => 0];

        foreach ($belum as $t) {
            $nilai = (float) $t->jumlah + (float) $t->denda;
            $hari = $t->jatuh_tempo ? today()->diffInDays($t->jatuh_tempo, false) : 0;

            if ($hari >= 0) {
                $aging['belum_jatuh_tempo'] += $nilai;
            } elseif ($hari >= -7) {
                $aging['telat_1_7'] += $nilai;
            } elseif ($hari >= -30) {
                $aging['telat_8_30'] += $nilai;
            } else {
                $aging['telat_lebih_30'] += $nilai;
            }
        }

        // Top properti periode ini.
        $pendapatanProperti = Pembayaran::where('status', 'diverifikasi')
            ->where('verified_at', '>=', $mulai)
            ->whereHas('tagihan.penyewaan.properti', $scopeId)
            ->with('tagihan.penyewaan')
            ->get()
            ->groupBy(fn ($p) => $p->tagihan?->penyewaan?->properti_id)
            ->map(fn ($rows) => (int) round($rows->sum('jumlah')));

        $topProperti = $daftarProperti->map(fn ($p) => [
            'id' => $p->id,
            'nama' => $p->nama,
            'total_kamar' => (int) $p->total_kamar,
            'kamar_terisi' => (int) $p->kamar_terisi,
            'pendapatan' => (int) ($pendapatanProperti[$p->id] ?? 0),
        ])->sortByDesc('pendapatan')->values()->take(10)->all();

        return [
            'terkunci' => false,
            'daftarProperti' => $daftarProperti,
            'maxPeriode' => $maxPeriode,
            'isFree' => $isFree,
            'sisaTrial' => $sisaTrial,
            'trialHabis' => $trialHabis,
            'totalKamar' => $totalKamar,
            'bulanLabels' => $bulanLabels,
            'chartPendapatan' => $chartPendapatan,
            'chartPengeluaran' => $chartPengeluaran,
            'chartLaba' => $chartLaba,
            'chartOkupansi' => $chartOkupansi,
            'chartTransaksi' => $chartTransaksi,
            'chartLunas' => $chartLunas,
            'chartBelum' => $chartBelum,
            'chartKategori' => $chartKategori,
            'totalPendapatan' => array_sum($chartPendapatan),
            'totalPengeluaran' => array_sum($chartPengeluaran),
            'totalTransaksi' => array_sum($chartTransaksi),
            'aging' => array_map('intval', $aging),
            'tagihanBelum' => $belum,
            'topProperti' => $topProperti,
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
                <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">Grafik & Analitik</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Keuangan, kamar terisi, tagihan, dan performa tiap kos.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <select wire:model.live="propertiId"
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                    <option value="">Semua Properti</option>
                    @foreach ($daftarProperti as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }}</option>
                    @endforeach
                </select>
                <select wire:model.live="periode"
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                    <option value="3">3 bulan</option>
                    @if ($maxPeriode >= 6)<option value="6">6 bulan</option>@endif
                    @if ($maxPeriode >= 12)<option value="12">12 bulan</option>@endif
                    @if ($maxPeriode >= 24)<option value="24">24 bulan</option>@endif
                </select>
            </div>
        </div>

        @if ($isFree && $sisaTrial !== null)
            <div class="rounded-xl bg-amber-50 dark:bg-amber-500/10 ring-1 ring-amber-200 dark:ring-amber-500/30 px-4 py-3 text-sm text-amber-800 dark:text-amber-200">
                Masa coba gratis tinggal <strong>{{ $sisaTrial }} hari</strong>. Upgrade ke PRO untuk limit lebih besar & laporan premium.
                <a href="{{ route('langganan.plans') }}" wire:navigate class="font-bold hover:underline">Upgrade</a>
            </div>
        @elseif ($isFree && $sisaTrial === null && $trialHabis)
            <div class="rounded-xl bg-rose-50 dark:bg-rose-500/10 ring-1 ring-rose-200 dark:ring-rose-500/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                Masa coba 7 hari sudah habis. Data tidak hilang, tapi tambah kos/kamar & halaman Laporan dikunci.
                <a href="{{ route('langganan.plans') }}" wire:navigate class="font-bold hover:underline">Upgrade ke PRO</a>
            </div>
        @endif

        @if ($terkunci ?? false)
            <div class="max-w-2xl mx-auto">
                <x-premium-lock requiredPlan="pro" title="Grafik & Analitik" message="Masa coba 7 hari sudah habis. Data tidak hilang, tapi tambah kos/kamar & halaman Laporan dikunci." />
            </div>
        @else

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-stat-card label="Pendapatan Periode" :value="'Rp' . number_format($totalPendapatan, 0, ',', '.')" tone="emerald"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /></svg>' />
            <x-stat-card label="Pengeluaran Periode" :value="'Rp' . number_format($totalPengeluaran, 0, ',', '.')" tone="rose"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />
            <x-stat-card label="Transaksi Terverifikasi" :value="$totalTransaksi" tone="cyan"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Ekspor Rekap Bulanan</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Unduh rekap ringkasan, properti, penyewaan & transaksi per bulan (PDF / Excel).</p>
            </div>
            <form method="GET" class="flex flex-wrap items-center gap-2" target="_blank" rel="noopener">
                <input type="month" name="bulan" value="{{ now()->format('Y-m') }}"
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                <button type="submit" formaction="{{ route('pemilik.rekap.pdf') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    PDF
                </button>
                <button type="submit" formaction="{{ route('pemilik.rekap.excel') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    Excel
                </button>
            </form>
        </div>

        <div id="grafik-data"
            data-labels='@json($bulanLabels)'
            data-pendapatan='@json($chartPendapatan)'
            data-pengeluaran='@json($chartPengeluaran)'
            data-laba='@json($chartLaba)'
            data-okupansi='@json($chartOkupansi)'
            data-transaksi='@json($chartTransaksi)'
            data-lunas='@json($chartLunas)'
            data-belum='@json($chartBelum)'
            data-kategori='@json($chartKategori)'
            class="space-y-6">
            <div class="min-w-0 bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden p-4 sm:p-6">
                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Uang Masuk vs Uang Keluar</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $propertiId ? ($daftarProperti->firstWhere('id', $propertiId)?->nama ?? '') : 'Semua properti' }} · {{ count($bulanLabels) }} bulan terakhir
                </p>
                <div class="relative mt-4 w-full" style="height: 17rem;">
                        <canvas id="grafik-keuangan" class="absolute inset-0 h-full w-full"></canvas>
                    </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="min-w-0 bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden p-4 sm:p-6">
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Tren Transaksi</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Jumlah pembayaran terverifikasi per bulan</p>
                    <div class="relative mt-4 w-full" style="height: 15rem;">
                        <canvas id="grafik-transaksi" class="absolute inset-0 h-full w-full"></canvas>
                    </div>
                </div>
                <div class="min-w-0 bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden p-4 sm:p-6">
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Tagihan Lunas vs Belum</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Nilai tagihan terbit per bulan (Rp)</p>
                                        <div class="relative mt-4 w-full" style="height: 15rem;">
                        <canvas id="grafik-lunas" class="absolute inset-0 h-full w-full"></canvas>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="min-w-0 bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden p-4 sm:p-6">
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Tren Kamar Terisi</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Kamar terisi (%) per bulan</p>
                                        <div class="relative mt-4 w-full" style="height: 15rem;">
                        <canvas id="grafik-okupansi" class="absolute inset-0 h-full w-full"></canvas>
                    </div>
                </div>
                <div class="min-w-0 bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden p-4 sm:p-6">
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Pengeluaran per Kategori</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Komposisi biaya operasional periode ini</p>
                    @if (count($chartKategori) > 0)
                                            <div class="relative mt-4 w-full" style="height: 15rem;">
                        <canvas id="grafik-kategori" class="absolute inset-0 h-full w-full"></canvas>
                    </div>
                    @else
                        <p class="py-12 text-center text-sm text-gray-400 dark:text-gray-500">Belum ada pengeluaran pada periode ini.</p>
                    @endif
                </div>
            </div>
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
                        <p class="mt-1 text-base font-extrabold text-gray-900 dark:text-gray-100">Rp{{ number_format($aging['belum_jatuh_tempo'], 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl bg-amber-50 dark:bg-amber-500/10 p-4">
                        <p class="text-[11px] font-semibold text-amber-600 dark:text-amber-400 uppercase">Baru telat, di bawah seminggu</p>
                        <p class="mt-1 text-base font-extrabold text-amber-700 dark:text-amber-300">Rp{{ number_format($aging['telat_1_7'], 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl bg-orange-50 dark:bg-orange-500/10 p-4">
                        <p class="text-[11px] font-semibold text-orange-600 dark:text-orange-400 uppercase">Telat sampai sebulan</p>
                        <p class="mt-1 text-base font-extrabold text-orange-700 dark:text-orange-300">Rp{{ number_format($aging['telat_8_30'], 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl bg-rose-50 dark:bg-rose-500/10 p-4">
                        <p class="text-[11px] font-semibold text-rose-600 dark:text-rose-400 uppercase">Telat lebih dari sebulan, segera tagih</p>
                        <p class="mt-1 text-base font-extrabold text-rose-700 dark:text-rose-300">Rp{{ number_format($aging['telat_lebih_30'], 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="px-5 pb-5">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">Siapa yang belum bayar (maks 50)</p>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-72 overflow-y-auto">
                        @forelse ($tagihanBelum as $t)
                            <div class="py-2.5 flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $t->penyewaan->anakKos?->nama ?? '-' }} · {{ $t->penyewaan->kamar?->nama }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t->periode }} · tempo {{ $t->jatuh_tempo?->translatedFormat('d M Y') }}</p>
                                </div>
                                <p class="shrink-0 text-sm font-bold text-rose-600 dark:text-rose-400">Rp{{ number_format($t->jumlah + $t->denda, 0, ',', '.') }}</p>
                            </div>
                        @empty
                            <p class="py-8 text-center text-sm text-gray-400">Semua tagihan lunas. Bagus!</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Kos Pemasukan Terbesar</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Berdasarkan pembayaran terverifikasi</p>
                    </div>
                </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($topProperti as $i => $p)
                            <div class="px-5 py-3.5 flex items-center gap-3">
                                <span class="shrink-0 h-8 w-8 rounded-lg bg-teal-50 dark:bg-teal-500/10 text-teal-700 dark:text-teal-300 text-sm font-extrabold flex items-center justify-center">{{ $i + 1 }}</span>
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
        @endif
    </div>
</div>
