<?php

use App\Models\ChatPesan;
use App\Models\Kamar;
use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use App\Models\Penyewaan;
use App\Models\Properti;
use App\Models\Tagihan;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;

new class extends Component
{
    public string $cari = '';

    public string $tab = 'properti';

    public ?string $pesan = null;

    public ?string $galat = null;

    public string $periode = '6';

    public function updatedPeriode(): void
    {
        $this->dispatch('chart:data-updated');
    }

    public function with(): array
    {
        $id = auth()->id();
        $scope = fn ($q) => $q->where('pemilik_id', $id);

        $statusKamar = Kamar::whereHas('properti', $scope)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalKamar = (int) $statusKamar->sum();
        $kamarTerisi = (int) ($statusKamar['terisi'] ?? 0);
        $kamarTersedia = (int) ($statusKamar['tersedia'] ?? 0);
        $kamarPerbaikan = (int) ($statusKamar['perbaikan'] ?? 0);
        $okupansiSekarang = $totalKamar > 0 ? (int) round($kamarTerisi / $totalKamar * 100) : 0;

        $pendapatanBulanIni = (int) Pembayaran::where('status', 'diverifikasi')
            ->whereMonth('verified_at', now()->month)
            ->whereYear('verified_at', now()->year)
            ->whereHas('tagihan.penyewaan.properti', $scope)
            ->sum('jumlah');

        $pendapatanBulanLalu = (int) Pembayaran::where('status', 'diverifikasi')
            ->whereMonth('verified_at', now()->subMonth()->month)
            ->whereYear('verified_at', now()->subMonth()->year)
            ->whereHas('tagihan.penyewaan.properti', $scope)
            ->sum('jumlah');

        $pengeluaranBulanIni = (int) Pengeluaran::whereHas('properti', $scope)
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->sum('jumlah');

        $pengeluaranBulanLalu = (int) Pengeluaran::whereHas('properti', $scope)
            ->whereMonth('tanggal', now()->subMonth()->month)
            ->whereYear('tanggal', now()->subMonth()->year)
            ->sum('jumlah');

        $labaBersihBulanIni = $pendapatanBulanIni - $pengeluaranBulanIni;
        $labaBersihBulanLalu = $pendapatanBulanLalu - $pengeluaranBulanLalu;

        $tagihanBelumQuery = fn () => Tagihan::where('status', '!=', 'lunas')
            ->whereHas('penyewaan.properti', $scope);

        $tagihanBelumCount = $tagihanBelumQuery()->count();
        $nilaiTagihanBelum = (int) $tagihanBelumQuery()->selectRaw('COALESCE(SUM(jumlah + denda), 0) as total')->value('total');
        $tagihanTelat = $tagihanBelumQuery()->where('jatuh_tempo', '<', today())->count();
        $tagihanBelumList = $tagihanBelumQuery()
            ->with(['penyewaan.anakKos', 'penyewaan.kamar', 'penyewaan.properti'])
            ->orderBy('jatuh_tempo')
            ->limit(6)
            ->get();

        $bulanCount = max(1, min(24, (int) $this->periode));

        $pendapatanPerBulan = Pembayaran::where('status', 'diverifikasi')
            ->whereHas('tagihan.penyewaan.properti', $scope)
            ->where('verified_at', '>=', now()->startOfMonth()->subMonths($bulanCount - 1))
            ->get(['verified_at', 'jumlah'])
            ->groupBy(fn ($p) => $p->verified_at->format('m/Y'))
            ->map(fn ($rows) => ['month' => $rows->first()->verified_at->format('m/Y'), 'total' => (int) $rows->sum('jumlah')])
            ->keyBy('month');

        $pengeluaranPerBulan = Pengeluaran::whereHas('properti', $scope)
            ->where('tanggal', '>=', now()->startOfMonth()->subMonths($bulanCount - 1))
            ->get(['tanggal', 'jumlah'])
            ->groupBy(fn ($p) => $p->tanggal->format('m/Y'))
            ->map(fn ($rows) => ['month' => $rows->first()->tanggal->format('m/Y'), 'total' => (int) round($rows->sum('jumlah'))])
            ->keyBy('month');

        $bulanLabels = collect(range($bulanCount - 1, 0))
            ->map(fn ($i) => now()->startOfMonth()->subMonths($i)->format('m/Y'))
            ->all();

        $chartPendapatan = [];
        $chartPengeluaran = [];
        $chartLaba = [];
        $chartOkupansi = [];

        $bulanAwal = now()->startOfMonth()->subMonths($bulanCount - 1);
        $bulanAkhir = now()->endOfMonth();

        $sewaanOkupansi = Penyewaan::whereHas('properti', $scope)
            ->where('tanggal_masuk', '<=', $bulanAkhir)
            ->where(fn ($w) => $w->whereNull('tanggal_keluar')->orWhere('tanggal_keluar', '>=', $bulanAwal))
            ->get(['kamar_id', 'tanggal_masuk', 'tanggal_keluar']);

        foreach (range($bulanCount - 1, 0) as $i) {
            $start = now()->startOfMonth()->subMonths($i);
            $end = $start->copy()->endOfMonth();
            $label = $start->format('m/Y');

            $pendapatan = (int) ($pendapatanPerBulan[$label]['total'] ?? 0);
            $pengeluaran = (int) ($pengeluaranPerBulan[$label]['total'] ?? 0);

            $chartPendapatan[] = $pendapatan;
            $chartPengeluaran[] = $pengeluaran;
            $chartLaba[] = $pendapatan - $pengeluaran;

            $kamarTerisiBulan = $sewaanOkupansi
                ->filter(fn ($s) => $s->tanggal_masuk->lte($end) && ($s->tanggal_keluar === null || $s->tanggal_keluar->gte($start)))
                ->pluck('kamar_id')
                ->unique()
                ->count();

            $chartOkupansi[] = $totalKamar > 0 ? (int) round($kamarTerisiBulan / $totalKamar * 100) : 0;
        }

        $chartKategori = Pengeluaran::whereHas('properti', $scope)
            ->where('tanggal', '>=', now()->startOfMonth()->subMonths($bulanCount - 1))
            ->get(['kategori', 'jumlah'])
            ->groupBy('kategori')
            ->map(fn ($rows) => (int) round($rows->sum('jumlah')))
            ->sortDesc()
            ->take(7)
            ->map(fn ($nilai, $kategori) => ['label' => str($kategori)->title(), 'value' => $nilai])
            ->values()
            ->all();

        $pembayaranTerbaru = Pembayaran::where('status', 'diverifikasi')
            ->whereHas('tagihan.penyewaan.properti', $scope)
            ->with(['tagihan.penyewaan.anakKos', 'tagihan.penyewaan.properti'])
            ->latest('verified_at')
            ->limit(6)
            ->get();

        $pembayaranMenunggu = Pembayaran::where('status', 'menunggu_verifikasi')
            ->whereHas('tagihan.penyewaan.properti', $scope)
            ->with(['anakKos', 'tagihan.penyewaan.kamar', 'tagihan.penyewaan.properti'])
            ->latest()
            ->limit(10)
            ->get();

        return [
            'totalProperti' => Properti::where('pemilik_id', $id)->count(),
            'totalKamar' => $totalKamar,
            'kamarTerisi' => $kamarTerisi,
            'kamarTersedia' => $kamarTersedia,
            'kamarPerbaikan' => $kamarPerbaikan,
            'okupansiSekarang' => $okupansiSekarang,
            'penyewaanAktif' => Penyewaan::where('status', 'aktif')
                ->whereHas('properti', $scope)->count(),
            'pendapatanBulanIni' => $pendapatanBulanIni,
            'pendapatanBulanLalu' => $pendapatanBulanLalu,
            'pengeluaranBulanIni' => $pengeluaranBulanIni,
            'pengeluaranBulanLalu' => $pengeluaranBulanLalu,
            'labaBersihBulanIni' => $labaBersihBulanIni,
            'labaBersihBulanLalu' => $labaBersihBulanLalu,
            'tagihanBelumCount' => $tagihanBelumCount,
            'nilaiTagihanBelum' => $nilaiTagihanBelum,
            'tagihanTelat' => $tagihanTelat,
            'tagihanBelumList' => $tagihanBelumList,
            'pembayaranTerbaru' => $pembayaranTerbaru,
            'pembayaranMenunggu' => $pembayaranMenunggu,
            'pembayaranMenungguCount' => $pembayaranMenunggu->count(),
            'propertis' => Properti::where('pemilik_id', $id)
                ->with('kamars')
                ->withCount(['kamars', 'kamars as kamar_terisi' => fn ($q) => $q->where('status', 'terisi')])
                ->when($this->cari, fn ($q) => $q->where('nama', 'like', "%{$this->cari}%"))
                ->orderBy('nama')
                ->limit(100)
                ->get(),
            'sewaans' => Penyewaan::query()
                ->whereHas('properti', $scope)
                ->with(['anakKos', 'kamar.properti', 'tagihans'])
                ->when($this->cari, fn ($q) => $q->whereHas('anakKos', fn ($q) => $q->where('nama', 'like', "%{$this->cari}%")))
                ->latest()
                ->limit(50)
                ->get(),
            'funnelStages' => [
                ['label' => 'Kunjungan', 'sub' => 'seluruh penyewaan dari properti Anda', 'nilai' => Penyewaan::whereHas('properti', $scope)->count()],
                ['label' => 'Aktif', 'sub' => 'penyewaan berstatus aktif', 'nilai' => Penyewaan::where('status', 'aktif')->whereHas('properti', $scope)->count()],
                ['label' => 'Tagihan', 'sub' => 'total tagihan yang terbit', 'nilai' => Tagihan::whereHas('penyewaan.properti', $scope)->count()],
                ['label' => 'Lunas', 'sub' => 'tagihan berstatus lunas', 'nilai' => Tagihan::where('status', 'lunas')->whereHas('penyewaan.properti', $scope)->count()],
            ],
            'bulanLabels' => $bulanLabels,
            'chartPendapatan' => $chartPendapatan,
            'chartPengeluaran' => $chartPengeluaran,
            'chartLaba' => $chartLaba,
            'chartOkupansi' => $chartOkupansi,
            'chartKategori' => $chartKategori,
        ];
    }

    public function checkOut(int $sewaanId): void
    {
        $sewaan = Penyewaan::where('id', $sewaanId)
            ->where('status', 'aktif')
            ->whereHas('properti', fn ($q) => $q->where('pemilik_id', auth()->id()))
            ->with(['anakKos', 'kamar', 'tagihans'])
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
            ? " Perhatian: masih ada {$belumLunas} tagihan belum lunas milik penyewa ini."
            : '';

        $this->pesan = "Check-out {$sewaan->anakKos?->nama} dari kamar {$sewaan->kamar?->nama} berhasil. Kamar kembali tersedia.{$catatan}";
    }

    public function verifikasiPembayaran(int $pembayaranId): void
    {
        $pembayaran = Pembayaran::where('id', $pembayaranId)
            ->where('status', 'menunggu_verifikasi')
            ->whereHas('tagihan.penyewaan.properti', fn ($q) => $q->where('pemilik_id', auth()->id()))
            ->with('anakKos', 'tagihan')
            ->first();

        if (! $pembayaran) {
            $this->galat = 'Pembayaran tidak ditemukan atau sudah diproses.';

            return;
        }

        $pembayaran->update([
            'status' => 'diverifikasi',
            'diverifikasi_oleh' => auth()->id(),
            'verified_at' => now(),
        ]);

        $tagihan = $pembayaran->tagihan;
        $total = $tagihan->pembayarans()->where('status', 'diverifikasi')->sum('jumlah');

        if ($total >= $tagihan->jumlah + $tagihan->denda) {
            $tagihan->update(['status' => 'lunas']);
        }

        ChatPesan::notifikasiPembayaranDiverifikasi($pembayaran, auth()->id());

        $nama = $pembayaran->anakKos?->nama ?? 'Penyewa';

        $this->pesan = "Pembayaran {$nama} sebesar Rp".number_format((float) $pembayaran->jumlah, 0, ',', '.')
            ." telah diverifikasi.";
    }
}; ?>

<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <x-dashboard-greeting
            roleLabel="Pemilik Kos"
            description="Kelola properti & kamar milik Anda, pantau okupansi, pengeluaran, dan laba bersih sewa."
            icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .414.336.75.75.75z" /></svg>'
        />

        <x-promo-ads />

        <x-promo-premium />

        @if ($pesan)
            <x-notifikasi-popup :pesan="$pesan" judul="Berhasil!" />
        @endif

        @if ($galat)
            <div class="flex items-center justify-between gap-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 ring-1 ring-rose-200 dark:ring-rose-500/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                <span>{{ $galat }}</span>
                <button wire:click="$set('galat', null)" class="text-rose-500 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 font-bold">&times;</button>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card label="Properti Saya" :value="$totalProperti" tone="cyan"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" /></svg>' />
            <x-stat-card label="Total Kamar" :value="$totalKamar" tone="sky"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" /></svg>' />
            <x-stat-card label="Kamar Terisi" :value="$kamarTerisi" tone="emerald"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />
            <x-stat-card label="Pendapatan Bulan Ini" :value="'Rp' . number_format($pendapatanBulanIni, 0, ',', '.')" tone="emerald"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>'>
                @if ($pendapatanBulanLalu > 0)
                    @php $delta = round((($pendapatanBulanIni - $pendapatanBulanLalu) / $pendapatanBulanLalu) * 100); @endphp
                    <x-slot name="hint">
                        <span class="{{ $delta >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} font-semibold">{{ $delta >= 0 ? '▲' : '▼' }} {{ abs($delta) }}%</span>
                        <span class="text-gray-400 dark:text-gray-500">vs bulan lalu</span>
                    </x-slot>
                @else
                    <x-slot name="hint">
                        <span class="text-gray-400 dark:text-gray-500">Bulan lalu belum ada pemasukan</span>
                    </x-slot>
                @endif
            </x-stat-card>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card label="Tingkat Okupansi" :value="$okupansiSekarang . '%'" tone="teal"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>'>
                <x-slot name="hint">
                    <span class="text-gray-400 dark:text-gray-500">{{ $kamarTerisi }}/{{ $totalKamar }} kamar terisi</span>
                </x-slot>
            </x-stat-card>
            <x-stat-card label="Kamar Kosong" :value="$kamarTersedia" tone="sky"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>'>
                @if ($kamarPerbaikan > 0)
                    <x-slot name="hint">
                        <span class="text-amber-600 dark:text-amber-400">{{ $kamarPerbaikan }} kamar perbaikan</span>
                    </x-slot>
                @else
                    <x-slot name="hint">
                        <span class="text-gray-400 dark:text-gray-500">Siap disewakan</span>
                    </x-slot>
                @endif
            </x-stat-card>
            <x-stat-card label="Pengeluaran Bulan Ini" :value="'Rp' . number_format($pengeluaranBulanIni, 0, ',', '.')" tone="rose"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'>
                @if ($pengeluaranBulanLalu > 0)
                    @php $deltaB = round((($pengeluaranBulanIni - $pengeluaranBulanLalu) / $pengeluaranBulanLalu) * 100); @endphp
                    <x-slot name="hint">
                        <span class="{{ $deltaB <= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} font-semibold">{{ $deltaB <= 0 ? '▲' : '▼' }} {{ abs($deltaB) }}%</span>
                        <span class="text-gray-400 dark:text-gray-500">vs bulan lalu</span>
                    </x-slot>
                @else
                    <x-slot name="hint">
                        <span class="text-gray-400 dark:text-gray-500">Bulan lalu belum ada pengeluaran</span>
                    </x-slot>
                @endif
            </x-stat-card>
            <x-stat-card label="Laba Bersih Bulan Ini" :value="'Rp' . number_format($labaBersihBulanIni, 0, ',', '.')" tone="teal"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181m8.818 3.181l-3.182-5.511m0 0l-5.511 3.181M4.5 3.75v15m6.75 0v-4.5m3-7.5h.008v.008H14.25V5.25z" /></svg>'>
                @if ($labaBersihBulanLalu != 0)
                    @php $deltaL = $labaBersihBulanLalu > 0 ? round(($labaBersihBulanIni - $labaBersihBulanLalu) / $labaBersihBulanLalu * 100) : 0; @endphp
                    <x-slot name="hint">
                        <span class="{{ $deltaL >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} font-semibold">{{ $deltaL >= 0 ? '▲' : '▼' }} {{ abs($deltaL) }}%</span>
                        <span class="text-gray-400 dark:text-gray-500">vs bulan lalu</span>
                    </x-slot>
                @else
                    <x-slot name="hint">
                        <span class="text-gray-400 dark:text-gray-500">Pendapatan - Pengeluaran</span>
                    </x-slot>
                @endif
            </x-stat-card>
        </div>

        @if ($tagihanBelumCount > 0)
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-2xl bg-rose-50 dark:bg-rose-500/10 ring-1 ring-rose-200 dark:ring-rose-500/30 px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-300 flex items-center justify-center shrink-0">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-rose-700 dark:text-rose-200">Ada {{ $tagihanBelumCount }} tagihan belum dibayar senilai <span class="font-extrabold">Rp{{ number_format($nilaiTagihanBelum, 0, ',', '.') }}</span></p>
                        <p class="text-xs text-rose-600/80 dark:text-rose-300/80">{{ $tagihanTelat > 0 ? $tagihanTelat . ' di antaranya sudah melewati jatuh tempo.' : 'Semuanya masih dalam batas jatuh tempo.' }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @foreach ([['tersedia', 'Kamar Kosong', $kamarTersedia, 'bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-300'], ['terisi', 'Kamar Terisi', $kamarTerisi, 'bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-300'], ['perbaikan', 'Kamar Perbaikan', $kamarPerbaikan, 'bg-sky-50 dark:bg-sky-900/40 text-sky-600 dark:text-sky-300']] as [$status, $label, $nilai, $warna])
                <div class="rounded-2xl {{ $warna }} px-4 py-3 flex items-center justify-between">
                    <span class="text-sm font-semibold">{{ $label }}</span>
                    <span class="text-xl font-extrabold">{{ $nilai }}</span>
                </div>
            @endforeach
        </div>

        {{-- Rekap keuangan & okupansi --}}
        <div id="rekap-data"
            data-labels='{{ json_encode($bulanLabels) }}'
            data-chart-pendapatan='{{ json_encode($chartPendapatan) }}'
            data-chart-pengeluaran='{{ json_encode($chartPengeluaran) }}'
            data-chart-laba='{{ json_encode($chartLaba) }}'
            data-chart-okupansi='{{ json_encode($chartOkupansi) }}'
            data-chart-kategori='{{ json_encode($chartKategori) }}'
            class="space-y-6">
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

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Pendapatan vs Pengeluaran vs Laba</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Periode terpilih, properti milik Anda</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <label for="periode" class="text-xs font-semibold text-gray-500 dark:text-gray-400">Periode</label>
                        <select wire:model.live="periode" id="periode"
                            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                            <option value="3">3 bulan</option>
                            <option value="6">6 bulan</option>
                            <option value="12">12 bulan</option>
                        </select>
                    </div>
                </div>
                <canvas id="chart-rekap-keuangan" class="mt-4 max-h-72"></canvas>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-6">
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Tren Okupansi</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tingkat hunian (%) berdasarkan penyewaan tiap bulan</p>
                    <canvas id="chart-rekap-okupansi" class="mt-4 max-h-64"></canvas>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-6">
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Pengeluaran per Kategori</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Komposisi biaya operasional pada periode terpilih</p>
                    @if (count($chartKategori) > 0)
                        <canvas id="chart-rekap-kategori" class="mt-4 max-h-64"></canvas>
                    @else
                        <p class="py-12 text-center text-sm text-gray-400 dark:text-gray-500">Belum ada pengeluaran pada periode ini.</p>
                    @endif
                </div>
            </div>
        </div>

        @if ($pembayaranMenungguCount > 0)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Pembayaran Menunggu Verifikasi</h3>
                    <span class="text-xs font-medium text-amber-600 dark:text-amber-400">{{ $pembayaranMenungguCount }} item</span>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($pembayaranMenunggu as $pembayaran)
                        <div class="px-5 py-3.5 flex items-center gap-3">
                            <div class="h-9 w-9 shrink-0 rounded-full bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-300 flex items-center justify-center">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $pembayaran->anakKos?->nama ?? 'Penyewa' }} · {{ $pembayaran->tagihan->penyewaan->kamar?->nama }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $pembayaran->tagihan->penyewaan->properti?->nama }} · {{ $pembayaran->tagihan->periode }} · {{ $pembayaran->labelMetode() }}</p>
                            </div>
                            <div class="text-end shrink-0">
                                <p class="text-sm font-bold text-gray-900 dark:text-gray-100">Rp{{ number_format($pembayaran->jumlah, 0, ',', '.') }}</p>
                                <button wire:click="verifikasiPembayaran({{ $pembayaran->id }})" wire:confirm="Verifikasi pembayaran ini?"
                                    class="mt-1 inline-flex items-center gap-1 rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-500 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    Verifikasi
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500">Tidak ada pembayaran menunggu verifikasi.</p>
                    @endforelse
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Pembayaran Terbaru</h3>
                    <span class="text-xs text-gray-400 dark:text-gray-500">Terverifikasi</span>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($pembayaranTerbaru as $pembayaran)
                        <div class="px-5 py-3.5 flex items-center gap-3">
                            <div class="h-9 w-9 shrink-0 rounded-full bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-300 flex items-center justify-center">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $pembayaran->tagihan->penyewaan->anakKos?->nama ?? 'Penyewa' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $pembayaran->tagihan->penyewaan->properti?->nama }} · {{ $pembayaran->tagihan->periode }}</p>
                            </div>
                            <div class="text-end shrink-0">
                                <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400">+Rp{{ number_format($pembayaran->jumlah, 0, ',', '.') }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ $pembayaran->verified_at?->translatedFormat('d M Y') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500">Belum ada pembayaran terverifikasi.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Tagihan Belum Dibayar</h3>
                    <span class="text-xs text-rose-500 dark:text-rose-400">{{ $tagihanBelumCount }} item</span>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($tagihanBelumList as $tagihan)
                        <div class="px-5 py-3.5 flex items-center gap-3">
                            <div class="h-9 w-9 shrink-0 rounded-full bg-rose-50 dark:bg-rose-900/40 text-rose-600 dark:text-rose-300 flex items-center justify-center">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $tagihan->penyewaan->anakKos?->nama ?? 'Penyewa' }} · {{ $tagihan->penyewaan->kamar?->nama }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $tagihan->periode }} · jatuh tempo {{ $tagihan->jatuh_tempo?->translatedFormat('d M Y') }}</p>
                            </div>
                            <div class="text-end shrink-0">
                                <p class="text-sm font-bold text-rose-600 dark:text-rose-400">Rp{{ number_format($tagihan->jumlah + $tagihan->denda, 0, ',', '.') }}</p>
                                @if ($tagihan->denda > 0)
                                    <p class="text-xs text-rose-500 dark:text-rose-400">denda Rp{{ number_format($tagihan->denda, 0, ',', '.') }}</p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500">Semua tagihan sudah lunas. Bagus!</p>
                    @endforelse
                </div>
            </div>
        </div>

        <x-dashboard-funnel
            :stages="$funnelStages"
            title="Grafik Pipeline"
            subtitle="Kunjungan → Penyewa → Tagihan → Lunas, properti milik Anda"
        />

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
            <div class="px-4 sm:px-6 pt-4 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 dark:border-gray-700">
                <div class="flex gap-2 overflow-x-auto scrollbar-hide pb-1 -mb-1">
                    <button wire:click="$set('tab', 'properti')"
                        class="flex-shrink-0 whitespace-nowrap px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'properti' ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                        Properti Saya
                    </button>
                    <button wire:click="$set('tab', 'sewaan')"
                        class="flex-shrink-0 whitespace-nowrap px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'sewaan' ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                        Penyewaan &amp; Tagihan
                    </button>
                </div>
                <input type="text" wire:model.live.debounce.300ms="cari" placeholder="Cari data..."
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
            </div>

            <div class="p-4 sm:p-6">
                @if ($tab === 'properti')
                    @forelse ($propertis as $properti)
                        @php
                            $pct = $properti->total_kamar > 0 ? round($properti->kamar_terisi / $properti->total_kamar * 100) : 0;
                        @endphp
                        <div class="rounded-xl ring-1 ring-gray-100 dark:ring-gray-700 bg-gray-50/50 dark:bg-gray-700/30 p-5 mb-4 last:mb-0">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $properti->nama }}</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $properti->alamat }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="h-2 w-28 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                            <div class="h-full rounded-full bg-gradient-to-r from-teal-500 to-emerald-500" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $properti->kamar_terisi }}/{{ $properti->total_kamar }} terisi</span>
                                    </div>
                                    <x-status-badge :status="$properti->status" />
                                </div>
                            </div>
                            <div class="mt-4 overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                    <thead>
                                        <tr class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            <th class="py-2 pr-4">Kamar</th>
                                            <th class="py-2 pr-4">Kapasitas</th>
                                            <th class="py-2 pr-4">Harga/Bulan</th>
                                            <th class="py-2">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                        @foreach ($properti->kamars as $kamar)
                                            <tr>
                                                <td class="py-2.5 pr-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $kamar->nama }}</td>
                                                <td class="py-2.5 pr-4 text-sm text-gray-600 dark:text-gray-300">{{ $kamar->kapasitas }} orang</td>
                                                <td class="py-2.5 pr-4 text-sm text-gray-600 dark:text-gray-300">Rp{{ number_format($kamar->harga_sewa_bulanan, 0, ',', '.') }}</td>
                                                <td class="py-2.5"><x-status-badge :status="$kamar->status" /></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <p class="py-10 text-center text-sm text-gray-400 dark:text-gray-500">Belum ada properti. Tambahkan properti melalui menu kelola properti.</p>
                    @endforelse
                @elseif ($tab === 'sewaan')
                    <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
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
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse ($sewaans as $sewaan)
                                    @php
                                        $belumLunas = $sewaan->tagihans->where('status', '!=', 'lunas');
                                        $sisa = $belumLunas->sum(fn ($t) => $t->jumlah + $t->denda);
                                        $telat = $belumLunas->filter(fn ($t) => $t->denda > 0)->count();
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
                                        <td class="px-4 py-4"><x-status-badge :status="$sewaan->status" /></td>
                                        <td class="px-4 py-4">
                                            @if ($sewaan->status === 'aktif')
                                                <div class="flex justify-end">
                                                    <button wire:click="checkOut({{ $sewaan->id }})" wire:loading.attr="disabled"
                                                        wire:confirm="Check-out {{ $sewaan->anakKos?->nama }} dari kamar {{ $sewaan->kamar?->nama }}? Kamar akan kembali tersedia."
                                                        class="inline-flex items-center rounded-lg border border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/10 px-3 py-1.5 text-xs font-semibold text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-500/20 transition disabled:opacity-50">
                                                        Check-out
                                                    </button>
                                                </div>
                                            @else
                                                <span class="block text-right text-xs text-gray-400 dark:text-gray-500">
                                                    Keluar: {{ $sewaan->tanggal_keluar?->translatedFormat('d M Y') }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-400 dark:text-gray-500">Belum ada penyewaan aktif. Penyewaan langsung terkonfirmasi saat anak kos menyewa kamar.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function renderRekapPemilik() {
            const wrap = document.getElementById('rekap-data');
            if (!wrap) return;

            const labels = JSON.parse(wrap.dataset.labels || '[]');
            const pendapatan = JSON.parse(wrap.dataset.chartPendapatan || '[]');
            const pengeluaran = JSON.parse(wrap.dataset.chartPengeluaran || '[]');
            const laba = JSON.parse(wrap.dataset.chartLaba || '[]');
            const okupansi = JSON.parse(wrap.dataset.chartOkupansi || '[]');
            const kategori = JSON.parse(wrap.dataset.chartKategori || '[]');

            window.rekapKeuanganChart('chart-rekap-keuangan', labels, pendapatan, pengeluaran, laba);
            window.rekapOkupansiChart('chart-rekap-okupansi', labels, okupansi);
            if (kategori.length) {
                window.rekapKategoriChart('chart-rekap-kategori', kategori.map(k => k.label), kategori.map(k => k.value));
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', renderRekapPemilik);
        } else {
            renderRekapPemilik();
        }
        document.addEventListener('livewire:navigated', renderRekapPemilik);
        Livewire.on('chart:data-updated', () => requestAnimationFrame(renderRekapPemilik));
    </script>
@endpush