<?php

use App\Models\ChatPesan;
use App\Models\Kamar;
use App\Models\Pembayaran;
use App\Models\Penyewaan;
use App\Models\PesanBantuan;
use App\Models\Properti;
use App\Models\Tagihan;
use App\Models\User;
use App\Support\GrafikBulan;
use Livewire\Volt\Component;

new class extends Component
{
    public string $cari = '';

    public string $tab = 'pembayaran';

    public ?string $pesan = null;

    public ?string $galat = null;

    public string $periode = '6';

    public function updatedPeriode(): void
    {
        $this->dispatch('chart:data-updated');
    }

    private function kelolaan()
    {
        if (auth()->user()->is_super_admin) {
            return fn ($query) => $query;
        }

        $id = auth()->id();

        // Ketat: admin hanya melihat properti yang ditugaskan kepadanya.
        // Properti tanpa admin hanya terlihat oleh super admin (dashboard super-admin).
        return function ($query) use ($id) {
            $query->whereHas('admins', fn ($a) => $a->where('users.id', $id));
        };
    }

    public function with(): array
    {
        $id = auth()->id();
        $bulanCount = max(1, min(24, (int) $this->periode));
        $monthStart = now()->startOfMonth()->subMonths($bulanCount - 1);

        $totalTugas = Properti::where($this->kelolaan())->count();
        $pembayaranMenungguCount = Pembayaran::where('status', 'menunggu_verifikasi')
            ->whereHas('tagihan.penyewaan.properti', $this->kelolaan())
            ->count();
        $penyewaanAktifCount = Penyewaan::where('status', 'aktif')
            ->whereHas('properti', $this->kelolaan())
            ->count();

        $bulanLabels = collect(range($bulanCount - 1, 0))
            ->map(fn ($i) => now()->startOfMonth()->subMonths($i)->format('m/Y'))
            ->all();

        $pendapatanRows = Pembayaran::where('status', 'diverifikasi')
            ->whereHas('tagihan.penyewaan.properti', $this->kelolaan())
            ->where('verified_at', '>=', $monthStart)
            ->selectRaw(GrafikBulan::kolomBulan('verified_at').', SUM(jumlah) as total')
            ->groupBy('bulan')
            ->pluck('total', 'bulan');
        $pendapatanPerBulan = $pendapatanRows
            ->map(fn ($total, $bulan) => ['month' => $bulan, 'total' => (int) $total])
            ->all();

        $tagihanRows = Tagihan::whereHas('penyewaan.properti', $this->kelolaan())
            ->where('created_at', '>=', $monthStart)
            ->selectRaw(GrafikBulan::kolomBulan('created_at').', SUM(jumlah + denda) as total, '.GrafikBulan::jumlahLunas())
            ->groupBy('bulan')
            ->get()
            ->keyBy('bulan');
        $tagihanStatusPerBulan = $tagihanRows
            ->map(fn ($r) => [
                'month' => $r->bulan,
                'total' => (int) round((float) $r->total),
                'lunas' => (int) round((float) $r->lunas),
                'belum' => (int) round((float) $r->total - (float) $r->lunas),
            ])
            ->all();

        return [
            'totalTugas' => $totalTugas,
            'pembayaranMenunggu' => $pembayaranMenungguCount,
            'penyewaanAktif' => $penyewaanAktifCount,
            'pembayarans' => Pembayaran::whereHas('tagihan.penyewaan.properti', $this->kelolaan())
                ->select(['id', 'tagihan_id', 'anak_kos_id', 'metode', 'jumlah', 'bukti', 'status'])
                ->with(['anakKos:id,nama', 'tagihan:id,periode'])
                ->when($this->cari, fn ($q) => $q->whereHas('anakKos', fn ($q) => $q->where('nama', 'like', "%{$this->cari}%")))
                ->latest()
                ->limit(15)
                ->get(),
            'funnelStages' => [
                ['label' => 'Kunjungan', 'sub' => 'properti yang Anda kelola', 'nilai' => $totalTugas],
                ['label' => 'Penyewa', 'sub' => 'penyewaan berstatus aktif', 'nilai' => $penyewaanAktifCount],
                ['label' => 'Tagihan', 'sub' => 'total tagihan yang terbit', 'nilai' => Tagihan::whereHas('penyewaan.properti', $this->kelolaan())->count()],
                ['label' => 'Lunas', 'sub' => 'tagihan berstatus lunas', 'nilai' => Tagihan::where('status', 'lunas')->whereHas('penyewaan.properti', $this->kelolaan())->count()],
            ],
            'pendapatanPerBulan' => $pendapatanPerBulan,
            'needAttention' => [
                'pembayaranMenunggu' => $pembayaranMenungguCount,
                'tagihanTelat' => Tagihan::where('status', '!=', 'lunas')
                    ->where('denda', '>', 0)
                    ->whereHas('penyewaan.properti', $this->kelolaan())->count(),
                'propertiTanpaKamar' => Properti::where($this->kelolaan())
                    ->whereDoesntHave('kamars')->count(),
                'bantuanBaru' => PesanBantuan::jumlahBaru(),
            ],
            'tagihanStatusPerBulan' => $tagihanStatusPerBulan,
            'totalKamar' => Kamar::whereHas('properti', $this->kelolaan())->count(),
            'kamarTerisi' => Kamar::where('status', 'terisi')->whereHas('properti', $this->kelolaan())->count(),
            'tagihanBelum' => Tagihan::where('status', '!=', 'lunas')
                ->whereHas('penyewaan.properti', $this->kelolaan())
                ->select(['id', 'penyewaan_id', 'periode', 'jumlah', 'denda', 'jatuh_tempo'])
                ->with(['penyewaan.anakKos:id,nama', 'penyewaan.properti:id,nama'])
                ->orderBy('jatuh_tempo')
                ->limit(5)
                ->get(),
            'statusProperti' => Properti::where($this->kelolaan())
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'bulanLabels' => $bulanLabels,
            'chartGrowthProperti' => $this->hitungSeries($bulanLabels, Properti::where($this->kelolaan())
                ->where('created_at', '>=', $monthStart)
                ->selectRaw(GrafikBulan::kolomBulan('created_at').', COUNT(*) as total')
                ->groupBy('bulan')
                ->pluck('total', 'bulan')->all()),
            'chartGrowthPenyewaan' => $this->hitungSeries($bulanLabels, Penyewaan::whereHas('properti', $this->kelolaan())
                ->where('created_at', '>=', $monthStart)
                ->selectRaw(GrafikBulan::kolomBulan('penyewaans.created_at').', COUNT(*) as total')
                ->groupBy('bulan')
                ->pluck('total', 'bulan')->all()),
            'chartGrowthPembayaran' => $this->hitungSeries($bulanLabels, Pembayaran::where('status', 'diverifikasi')
                ->whereHas('tagihan.penyewaan.properti', $this->kelolaan())
                ->where('verified_at', '>=', $monthStart)
                ->selectRaw(GrafikBulan::kolomBulan('verified_at').', COUNT(*) as total')
                ->groupBy('bulan')
                ->pluck('total', 'bulan')->all()),
            'chartNilaiTransaksi' => $this->hitungSeries($bulanLabels, Pembayaran::where('status', 'diverifikasi')
                ->whereHas('tagihan.penyewaan.properti', $this->kelolaan())
                ->where('verified_at', '>=', $monthStart)
                ->selectRaw(GrafikBulan::kolomBulan('verified_at').', SUM(jumlah) as total')
                ->groupBy('bulan')
                ->pluck('total', 'bulan')->all(), true),
            'userGrowthMonth' => cache()->remember("admin.userGrowth.{$bulanCount}", 600, fn () => $this->userRoleSeries($bulanCount)),
            'statusPenyewaan' => Penyewaan::whereHas('properti', $this->kelolaan())
                ->where('created_at', '>=', $monthStart)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'statusPembayaran' => Pembayaran::whereHas('tagihan.penyewaan.properti', $this->kelolaan())
                ->where('created_at', '>=', $monthStart)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'periodeBulan' => $bulanCount,
        ];
    }

    private function hitungSeries(array $labels, array $peta, bool $bulat = false): array
    {
        return array_map(fn ($label) => $bulat ? (int) round((float) ($peta[$label] ?? 0)) : (int) ($peta[$label] ?? 0), $labels);
    }

    /**
     * Total nilai (SUM) per bulan selama $bulanCount bulan terakhir.
     * $rows menerima koleksi Pembayaran berisi atribut jumlah + kolom waktu.
     */
    private function nilaiSeries($rows, int $bulanCount, string $column = 'created_at'): array
    {
        $bulanCount = max(1, min(24, $bulanCount));
        $grouped = collect($rows)->groupBy(fn ($r) => $r->{$column}?->format('m/Y'));

        return collect(range($bulanCount - 1, 0))
            ->map(fn ($i) => (int) round(($grouped[now()->startOfMonth()->subMonths($i)->format('m/Y')] ?? collect())->sum('jumlah')))
            ->all();
    }

    /**
     * Registrasi pengguna per bulan untuk periode, dipilah Anak Kos & Pemilik.
     */
    private function userRoleSeries(int $bulanCount): array
    {
        $monthStart = now()->startOfMonth()->subMonths($bulanCount - 1);

        $users = User::with('roles:id,name')
            ->where('created_at', '>=', $monthStart)
            ->get(['id', 'created_at'])
            ->groupBy(fn ($u) => $u->created_at->format('m/Y'));

        $out = ['anak_kos' => [], 'pemilik' => []];

        foreach (range($bulanCount - 1, 0) as $i) {
            $key = now()->startOfMonth()->subMonths($i)->format('m/Y');
            $rows = $users[$key] ?? collect();
            $out['anak_kos'][] = $rows->filter(fn ($u) => $u->roles->pluck('name')->contains('anak_kos'))->count();
            $out['pemilik'][] = $rows->filter(fn ($u) => $u->roles->pluck('name')->contains('pemilik'))->count();
        }

        return $out;
    }

    private function growthSeries($rows, int $bulanCount, string $column = 'created_at'): array
    {
        $grouped = collect($rows)->groupBy(fn ($r) => $r->{$column}?->format('m/Y'));

        return collect(range(max(1, min(24, $bulanCount)) - 1, 0))
            ->map(fn ($i) => ($grouped[now()->startOfMonth()->subMonths($i)->format('m/Y')] ?? collect())->count())
            ->all();
    }

    public function verifikasiPembayaran(int $id): void
    {
        $pembayaran = Pembayaran::where('id', $id)
            ->whereHas('tagihan.penyewaan.properti', $this->kelolaan())
            ->with('anakKos', 'tagihan')
            ->first();

        if (! $pembayaran || $pembayaran->status !== 'menunggu_verifikasi') {
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

<div class="py-10" wire:poll.visible.120s>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <x-dashboard-greeting
            roleLabel="Admin Properti"
            description="Verifikasi pembayaran pada properti yang ditugaskan kepada Anda."
            icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0" /></svg>'
        />

        @if ($pesan)
            <x-notifikasi-popup :pesan="$pesan" judul="Berhasil!" />
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-stat-card label="Properti Ditugaskan" :value="$totalTugas" tone="cyan"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" /></svg>' />
            <x-stat-card label="Pembayaran Menunggu" :value="$pembayaranMenunggu" tone="amber"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />
            <x-stat-card label="Penyewaan Aktif" :value="$penyewaanAktif" tone="emerald"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-stat-card label="Total Kamar Kelolaan" :value="$totalKamar" tone="sky"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" /></svg>' />
            <x-stat-card label="Kamar Terisi" :value="$kamarTerisi" tone="emerald"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />
            <x-stat-card label="Tagihan Belum Dibayar" :value="$tagihanBelum->count()" tone="rose"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach (['aktif' => ['Aktif', 'bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-300'], 'nonaktif' => ['Nonaktif', 'bg-rose-50 dark:bg-rose-900/40 text-rose-600 dark:text-rose-300']] as $status => [$label, $warna])
                <div class="rounded-2xl {{ $warna }} px-4 py-3 flex items-center justify-between">
                    <span class="text-sm font-semibold">Properti {{ $label }}</span>
                    <span class="text-xl font-extrabold">{{ $statusProperti[$status] ?? 0 }}</span>
                </div>
            @endforeach
        </div>

        <div id="growth-data"
            data-labels='@json($bulanLabels)'
            data-growth-properti='@json($chartGrowthProperti)'
            data-growth-penyewaan='@json($chartGrowthPenyewaan)'
            data-growth-pembayaran='@json($chartGrowthPembayaran)'
            class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Pertumbuhan Kelolaan</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Properti &amp; penyewaan baru per bulan</p>
                    </div>
                    <select wire:model.live="periode"
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                        <option value="3">3 bulan</option>
                        <option value="6">6 bulan</option>
                        <option value="12">12 bulan</option>
                    </select>
                </div>
                <canvas id="chart-growth-kelolaan" class="mt-4 max-h-64"></canvas>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-6">
                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Transaksi Terverifikasi</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Jumlah pembayaran diverifikasi per bulan</p>
                <canvas id="chart-growth-transaksi" class="mt-4 max-h-64"></canvas>
            </div>
        </div>

        <div id="analytics-data"
            data-labels='@json($bulanLabels)'
            data-user-anak='@json($userGrowthMonth['anak_kos'])'
            data-user-pemilik='@json($userGrowthMonth['pemilik'])'
            data-nilai-transaksi='@json($chartNilaiTransaksi)'
            class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Pertumbuhan Pengguna</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pendaftaran baru Anak Kos &amp; Pemilik per bulan</p>
                    </div>
                    <select wire:model.live="periode"
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                        <option value="3">3 bulan</option>
                        <option value="6">6 bulan</option>
                        <option value="12">12 bulan</option>
                    </select>
                </div>
                <canvas id="chart-user-growth-admin" class="mt-4 max-h-64"></canvas>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-6">
                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Nilai Transaksi Terverifikasi</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Total nilai pembayaran diverifikasi per bulan</p>
                <canvas id="chart-nilai-transaksi" class="mt-4 max-h-64"></canvas>
            </div>
        </div>

        <div id="status-data-admin"
            data-sewaan='@json($statusPenyewaan)'
            data-pembayaran='@json($statusPembayaran)'
            class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-6">
                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Distribusi Status Penyewaan</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $periodeBulan }} bulan terakhir pada properti kelolaan</p>
                @if (count($statusPenyewaan) > 0)
                    <canvas id="chart-status-penyewaan" class="mt-4 max-h-64"></canvas>
                @else
                    <p class="py-12 text-center text-sm text-gray-400 dark:text-gray-500">Belum ada penyewaan.</p>
                @endif
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-6">
                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Distribusi Status Pembayaran</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $periodeBulan }} bulan terakhir: menunggu verifikasi, diverifikasi, dan ditolak</p>
                @if (count($statusPembayaran) > 0)
                    <canvas id="chart-status-pembayaran" class="mt-4 max-h-64"></canvas>
                @else
                    <p class="py-12 text-center text-sm text-gray-400 dark:text-gray-500">Belum ada pembayaran.</p>
                @endif
            </div>
        </div>

        @if ($tagihanBelum->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Tagihan Belum Dibayar</h3>
                    <span class="text-xs text-rose-500 dark:text-rose-400">{{ $tagihanBelum->count() }} item</span>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($tagihanBelum as $tagihan)
                        <div class="px-5 py-3.5 flex items-center gap-3">
                            <div class="h-9 w-9 shrink-0 rounded-full bg-rose-50 dark:bg-rose-900/40 text-rose-600 dark:text-rose-300 flex items-center justify-center">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $tagihan->penyewaan->anakKos?->nama ?? 'Penyewa' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $tagihan->periode }} · {{ $tagihan->penyewaan->properti?->nama }}</p>
                            </div>
                            <div class="text-end shrink-0">
                                <p class="text-sm font-bold text-rose-600 dark:text-rose-400">Rp{{ number_format($tagihan->jumlah + $tagihan->denda, 0, ',', '.') }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">JT {{ $tagihan->jatuh_tempo?->translatedFormat('d M Y') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if (array_sum($needAttention) > 0)
            <div class="rounded-2xl bg-rose-50/60 dark:bg-rose-500/5 ring-1 ring-rose-200/60 dark:ring-rose-500/20 p-4 sm:p-5">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.008v.008H12v-.008z" /></svg>
                    </span>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Perlu Perhatian</h3>
                </div>
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @if ($needAttention['pembayaranMenunggu'] > 0)
                        <div class="rounded-xl bg-white dark:bg-gray-800 p-3.5 flex items-center gap-3">
                            <span class="shrink-0 h-9 w-9 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-lg font-extrabold text-gray-900 dark:text-gray-100">{{ $needAttention['pembayaranMenunggu'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Pembayaran menunggu verifikasi</p>
                            </div>
                        </div>
                    @endif
                    @if ($needAttention['tagihanTelat'] > 0)
                        <div class="rounded-xl bg-white dark:bg-gray-800 p-3.5 flex items-center gap-3">
                            <span class="shrink-0 h-9 w-9 rounded-xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9.303 3.376c-.866 1.5.217 3.374 1.948 3.374H5.75c1.73 0 2.813-1.874 1.948-3.374L10.05 3.378c.866-1.5 3.032-1.5 3.898 0l8.354 12.748z" /></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-lg font-extrabold text-gray-900 dark:text-gray-100">{{ $needAttention['tagihanTelat'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Tagihan telat (kena denda)</p>
                            </div>
                        </div>
                    @endif
                    @if ($needAttention['propertiTanpaKamar'] > 0)
                        <div class="rounded-xl bg-white dark:bg-gray-800 p-3.5 flex items-center gap-3">
                            <span class="shrink-0 h-9 w-9 rounded-xl bg-teal-50 dark:bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75" /></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-lg font-extrabold text-gray-900 dark:text-gray-100">{{ $needAttention['propertiTanpaKamar'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Properti belum punya kamar</p>
                            </div>
                        </div>
                    @endif
                    @if (($needAttention['bantuanBaru'] ?? 0) > 0)
                        <a href="{{ route('bantuan.masuk') }}" wire:navigate
                            class="rounded-xl bg-white dark:bg-gray-800 p-3.5 flex items-center gap-3 hover:bg-teal-50/50 dark:hover:bg-gray-700/60 transition group">
                            <span class="shrink-0 h-9 w-9 rounded-xl bg-teal-50 dark:bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center group-hover:scale-105 transition">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z" /></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-lg font-extrabold text-gray-900 dark:text-gray-100">{{ $needAttention['bantuanBaru'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Pesan bantuan baru</p>
                            </div>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        <x-dashboard-funnel
            :stages="$funnelStages"
            title="Grafik Pipeline"
            subtitle="Kunjungan → Penyewa → Tagihan → Lunas, properti yang Anda kelola (keseluruhan)"
        />

        <div id="pendapatan-data-admin"
            data-pendapatan='@json($pendapatanPerBulan)'
            data-tagihan='@json($tagihanStatusPerBulan)'
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Pendapatan &amp; Status Tagihan</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $periodeBulan }} bulan terakhir, properti yang Anda kelola</p>
                </div>
            </div>
            <canvas id="chart-pendapatan-admin" class="mt-4 max-h-72"></canvas>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
            <div class="px-4 sm:px-6 pt-4 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 dark:border-gray-700">
                <div class="flex gap-2 overflow-x-auto scrollbar-hide pb-1 -mb-1">
                    <button wire:click="$set('tab', 'pembayaran')"
                        class="flex-shrink-0 whitespace-nowrap px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'pembayaran' ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                        Pembayaran
                    </button>
                </div>
                <input type="text" wire:model.live.debounce.300ms="cari" placeholder="Cari nama anak kos..."
                    class="rounded-lg border-gray-300 text-sm focus:ring-teal-500 focus:border-teal-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Anak Kos</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Periode</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Metode</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Bukti</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($pembayarans as $pembayaran)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $pembayaran->anakKos?->nama ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $pembayaran->tagihan?->periode ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">Rp{{ number_format($pembayaran->jumlah, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $pembayaran->metode === 'cash' ? 'Tunai (Cash)' : 'Transfer' }}</td>
                                <td class="px-6 py-4 text-sm">
                                    @if ($pembayaran->bukti)
                                        <a href="{{ Storage::url($pembayaran->bukti) }}" target="_blank" rel="noopener"
                                            class="inline-flex items-center gap-1 text-xs font-semibold text-teal-600 hover:text-teal-700 hover:underline">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            Lihat
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500 italic">Tidak ada</span>
                                    @endif
                                </td>
<td class="px-6 py-4"><x-status-badge :status="$pembayaran->status" /></td>
                                <td class="px-6 py-4">
                                    @if ($pembayaran->status === 'menunggu_verifikasi')
                                        <div class="flex justify-end">
                                            <button wire:click="verifikasiPembayaran({{ $pembayaran->id }})" wire:loading.attr="disabled"
                                                class="inline-flex items-center rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-500 transition">
                                                Verifikasi
                                            </button>
                                        </div>
                                    @else
                                        <span class="block text-right text-xs text-gray-400 dark:text-gray-500">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-10 text-center text-sm text-gray-400 dark:text-gray-500">Belum ada pembayaran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // Baca dari atribut data-* di DOM (bukan data inline saat load) agar data selalu
        // segar setelah morph Livewire (poll / cari / ganti periode).
        function renderPendapatanAdmin() {
            const wrap = document.getElementById('pendapatan-data-admin');
            if (!wrap) return;
            let pendapatan = {}, tagihan = {};
            try { pendapatan = JSON.parse(wrap.dataset.pendapatan || '{}'); } catch (e) { pendapatan = {}; }
            try { tagihan = JSON.parse(wrap.dataset.tagihan || '{}'); } catch (e) { tagihan = {}; }
            window.renderPendapatanChart('chart-pendapatan-admin', pendapatan, tagihan);
        }

        function renderGrowthAdmin() {
            const wrap = document.getElementById('growth-data');
            if (!wrap) return;

            const labels = JSON.parse(wrap.dataset.labels || '[]');
            const properti = JSON.parse(wrap.dataset.growthProperti || '[]');
            const penyewaan = JSON.parse(wrap.dataset.growthPenyewaan || '[]');
            const pembayaran = JSON.parse(wrap.dataset.growthPembayaran || '[]');

            window.growthBarChart('chart-growth-kelolaan', labels, [
                { label: 'Properti', data: properti, backgroundColor: 'rgba(20,184,166,.85)', borderRadius: 6 },
                { label: 'Penyewaan', data: penyewaan, backgroundColor: 'rgba(99,102,241,.85)', borderRadius: 6 },
            ]);
            window.growthBarChart('chart-growth-transaksi', labels, [
                { label: 'Transaksi', data: pembayaran, backgroundColor: 'rgba(14,165,233,.85)', borderRadius: 6 },
            ]);
        }

        function renderAnalyticsAdmin() {
            const wrap = document.getElementById('analytics-data');
            if (!wrap) return;

            const labels = JSON.parse(wrap.dataset.labels || '[]');
            const userAnak = JSON.parse(wrap.dataset.userAnak || '[]');
            const userPemilik = JSON.parse(wrap.dataset.userPemilik || '[]');
            const nilai = JSON.parse(wrap.dataset.nilaiTransaksi || '[]');

            window.growthLineChart('chart-user-growth-admin', labels, [
                { label: 'Anak Kos', data: userAnak, borderColor: '#0ea5e9', backgroundColor: 'rgba(14,165,233,.1)', tension: .4 },
                { label: 'Pemilik', data: userPemilik, borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,.1)', tension: .4 },
            ]);
            window.rupiahBarChart('chart-nilai-transaksi', labels, nilai, 'Nilai Transaksi');
        }

        function renderStatusAdmin() {
            const wrap = document.getElementById('status-data-admin');
            if (!wrap) return;
            let sewaanRaw = {}, pembayaranRaw = {};
            try { sewaanRaw = JSON.parse(wrap.dataset.sewaan || '{}'); } catch (e) { sewaanRaw = {}; }
            try { pembayaranRaw = JSON.parse(wrap.dataset.pembayaran || '{}'); } catch (e) { pembayaranRaw = {}; }
            const labelMap = {
                aktif: 'Aktif', selesai: 'Selesai',
                menunggu_verifikasi: 'Menunggu Verifikasi', diverifikasi: 'Diverifikasi', ditolak: 'Ditolak',
            };
            const toChart = (map) => {
                const entries = Object.entries(map).map(([k, v]) => ({ label: labelMap[k] ?? k, value: Number(v) }));
                return { labels: entries.map(e => e.label), values: entries.map(e => e.value) };
            };
            if (Object.keys(sewaanRaw).length) {
                const d = toChart(sewaanRaw);
                window.distributionDonutChart('chart-status-penyewaan', d.labels, d.values);
            }
            if (Object.keys(pembayaranRaw).length) {
                const d = toChart(pembayaranRaw);
                window.distributionDonutChart('chart-status-pembayaran', d.labels, d.values);
            }
        }

        // Render ulang semua chart dashboard admin. Dijaga dengan penanda halaman
        // agar listener basi dari navigasi SPA tidak merender halaman lain.
        window.__renderAllAdmin = function () {
            if (!document.getElementById('chart-pendapatan-admin')) return;
            renderPendapatanAdmin(); renderGrowthAdmin(); renderAnalyticsAdmin(); renderStatusAdmin();
        };
        // Antrean debounce: ketikan pencarian / poll / ganti periode menyatu jadi 1 render.
        let __adminRenderTimer = null;
        window.__queueRenderAllAdmin = function () {
            if (__adminRenderTimer) clearTimeout(__adminRenderTimer);
            __adminRenderTimer = setTimeout(() => { try { window.__renderAllAdmin(); } catch (e) {} }, 250);
        };

        (function pasangListenerAdmin() {
            const jalan = () => { try { window.__renderAllAdmin && window.__renderAllAdmin(); } catch (e) {} };
            const antre = () => { try { window.__queueRenderAllAdmin && window.__queueRenderAllAdmin(); } catch (e) {} };
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', jalan, { once: true });
            } else {
                jalan();
            }
            // Daftarkan sekali saja: navigasi SPA mengeksekusi ulang skrip ini,
            // tanpa penjagaan listener menumpuk dan chart dirender berkali-kali.
            if (!window.__adminDashListenerOn) {
                window.__adminDashListenerOn = true;
                document.addEventListener('livewire:navigated', jalan);
                try { if (window.Livewire && typeof window.Livewire.hook === 'function') window.Livewire.hook('morph.updated', antre); } catch (e) {}
                try { if (window.Livewire && typeof window.Livewire.on === 'function') window.Livewire.on('chart:data-updated', antre); } catch (e) {}
            }
        })();
    </script>
@endpush
