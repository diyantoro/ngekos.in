<?php

use App\Models\ChatPesan;
use App\Models\Kamar;
use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use App\Models\Penyewaan;
use App\Models\Tagihan;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;

new class extends Component
{
    public ?string $pesan = null;

    public ?string $galat = null;

    public function with(): array
    {
        $id = auth()->id();

        $scope = fn ($q) => $q->where('pemilik_id', $id);

        $kunciRingkas = "pemilik.ringkas.{$id}.all";

        $ringkas = cache()->remember($kunciRingkas, 60, function () use ($id, $scope) {
            $statusKamar = Kamar::whereHas('properti', $scope)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $totalKamar = (int) $statusKamar->sum();
            $sekarang = now();
            $lalu = now()->subMonth();

            return [

                'totalKamar' => $totalKamar,
                'kamarTerisi' => (int) ($statusKamar['terisi'] ?? 0),
                'kamarTersedia' => (int) ($statusKamar['tersedia'] ?? 0),
                'kamarPerbaikan' => (int) ($statusKamar['perbaikan'] ?? 0),
                'pendapatanBulanIni' => (int) Pembayaran::where('status', 'diverifikasi')
                    ->whereBetween('verified_at', [$sekarang->copy()->startOfMonth(), $sekarang->copy()->endOfMonth()])
                    ->whereHas('tagihan.penyewaan.properti', $scope)
                    ->sum('jumlah'),
                'pendapatanBulanLalu' => (int) Pembayaran::where('status', 'diverifikasi')
                    ->whereBetween('verified_at', [$lalu->copy()->startOfMonth(), $lalu->copy()->endOfMonth()])
                    ->whereHas('tagihan.penyewaan.properti', $scope)
                    ->sum('jumlah'),
                'pengeluaranBulanIni' => (int) Pengeluaran::whereHas('properti', $scope)
                    ->whereBetween('tanggal', [$sekarang->copy()->startOfMonth()->toDateString(), $sekarang->copy()->endOfMonth()->toDateString()])
                    ->sum('jumlah'),
                'pengeluaranBulanLalu' => (int) Pengeluaran::whereHas('properti', $scope)
                    ->whereBetween('tanggal', [$lalu->copy()->startOfMonth()->toDateString(), $lalu->copy()->endOfMonth()->toDateString()])
                    ->sum('jumlah'),
                'penyewaanAktif' => Penyewaan::where('status', 'aktif')
                    ->whereHas('properti', $scope)->count(),
                'tagihanBelumCount' => Tagihan::where('status', '!=', 'lunas')
                    ->whereHas('penyewaan.properti', $scope)->count(),
                'nilaiTagihanBelum' => (int) Tagihan::where('status', '!=', 'lunas')
                    ->whereHas('penyewaan.properti', $scope)
                    ->selectRaw('COALESCE(SUM(jumlah + denda), 0) as total')->value('total'),
                'tagihanTelat' => Tagihan::where('status', '!=', 'lunas')
                    ->where('jatuh_tempo', '<', today()->toDateString())
                    ->whereHas('penyewaan.properti', $scope)->count(),
            ];
        });

        $totalKamar = $ringkas['totalKamar'];
        $kamarTerisi = $ringkas['kamarTerisi'];
        $kamarTersedia = $ringkas['kamarTersedia'];
        $kamarPerbaikan = $ringkas['kamarPerbaikan'];
        $okupansiSekarang = $totalKamar > 0 ? (int) round($kamarTerisi / $totalKamar * 100) : 0;

        $pendapatanBulanIni = $ringkas['pendapatanBulanIni'];
        $pendapatanBulanLalu = $ringkas['pendapatanBulanLalu'];
        $pengeluaranBulanIni = $ringkas['pengeluaranBulanIni'];
        $pengeluaranBulanLalu = $ringkas['pengeluaranBulanLalu'];
        $kamarAktif = $ringkas['penyewaanAktif'] ?? $ringkas['kamarTerisi'] ?? $ringkas['totalKamar'] ?? 0;

        $labaBersihBulanIni = $pendapatanBulanIni - $pengeluaranBulanIni;
        $labaBersihBulanLalu = $pendapatanBulanLalu - $pengeluaranBulanLalu;

        $tagihanBelumQuery = fn () => Tagihan::where('status', '!=', 'lunas')
            ->whereHas('penyewaan.properti', $scope);

        $tagihanBelumCount = $ringkas['tagihanBelumCount'];
        $nilaiTagihanBelum = $ringkas['nilaiTagihanBelum'];
        $tagihanTelat = $ringkas['tagihanTelat'];
        $tagihanBelumList = $tagihanBelumQuery()
            ->select(['id', 'penyewaan_id', 'periode', 'jumlah', 'denda', 'jatuh_tempo', 'status'])
            ->with(['penyewaan.anakKos:id,nama', 'penyewaan.kamar:id,nama', 'penyewaan.properti:id,nama'])
            ->orderBy('jatuh_tempo')
            ->limit(6)
            ->get();

        $pembayaranTerbaru = Pembayaran::where('status', 'diverifikasi')
            ->whereHas('tagihan.penyewaan.properti', $scope)
            ->select(['id', 'tagihan_id', 'anak_kos_id', 'jumlah', 'nomor_kwitansi', 'verified_at'])
            ->with(['tagihan.penyewaan.anakKos:id,nama', 'tagihan.penyewaan.properti:id,nama'])
            ->latest('verified_at')
            ->limit(6)
            ->get();

        $pembayaranMenunggu = Pembayaran::where('status', 'menunggu_verifikasi')
            ->whereHas('tagihan.penyewaan.properti', $scope)
            ->select(['id', 'tagihan_id', 'anak_kos_id', 'metode', 'jumlah', 'bukti', 'created_at'])
            ->with(['anakKos:id,nama', 'tagihan.penyewaan.kamar:id,nama', 'tagihan.penyewaan.properti:id,nama'])
            ->latest()
            ->limit(10)
            ->get();

        return [

            'totalKamar' => $totalKamar,
            'kamarTerisi' => $kamarTerisi,
            'kamarTersedia' => $kamarTersedia,
            'kamarPerbaikan' => $kamarPerbaikan,
            'okupansiSekarang' => $okupansiSekarang,
            'penyewaanAktif' => $ringkas['penyewaanAktif'],
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
            'langganan' => (function () {
                $user = auth()->user();
                $plan = SubscriptionService::getPlan($user);

                return [
                    'plan' => $plan,
                    'roomUsed' => SubscriptionService::usage($user, 'room'),
                    'roomLimit' => SubscriptionService::limitFor($plan, 'room'),
                    'expiresAt' => (SubscriptionService::getSubscriptionAktif($user) ?? SubscriptionService::getSubscription($user))?->expires_at?->translatedFormat('d F Y'),
                    'status' => (SubscriptionService::getSubscriptionAktif($user) ?? SubscriptionService::getSubscription($user))?->status,
                    'sisaTrial' => SubscriptionService::sisaTrialHari($user),
                    'trialHabis' => SubscriptionService::trialExpired($user),
                    'bisaKlaim' => SubscriptionService::bisaKlaimTrial($user),
                    'sisaLangganan' => SubscriptionService::sisaLanggananHari($user),
                ];
            })(),
        ];
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

        try {
            $hasil = \App\Services\PembayaranService::verifikasi($pembayaran, auth()->id(), 'diverifikasi');
        } catch (DomainException $e) {
            $this->galat = $e->getMessage();

            return;
        }

        $nama = $pembayaran->anakKos?->nama ?? 'Penyewa';

        $this->pesan = "Pembayaran {$nama} sebesar Rp".number_format((float) $pembayaran->jumlah, 0, ',', '.')
            .' telah diverifikasi.'
            .($hasil['tagihan_lunas'] ? ' Tagihan LUNAS.' : '')
            .($hasil['kwitansi_url'] ? " Kwitansi {$hasil['pembayaran']->nomor_kwitansi} otomatis terkirim (lihat di chat & unduh di bawah)." : '');
    }
}; ?>

<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <x-dashboard-greeting
            roleLabel="Pemilik Kos"
            description="Kamar yang kosong, tagihan yang telat, sama pemasukan bulan ini — semuanya kelihatan di sini."
            icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .414.336.75.75.75z" /></svg>'
        />

        <div wire:ignore>
            <x-promo-ads />
        </div>

        <x-subscription-card
            :plan="$langganan['plan']"
            :roomUsed="$langganan['roomUsed']"
            :roomLimit="$langganan['roomLimit']"
            :expiresAt="$langganan['expiresAt']"
            :status="$langganan['status'] ?? null"
            :sisaTrial="$langganan['sisaTrial'] ?? null"
            :trialHabis="$langganan['trialHabis'] ?? null"
            :bisaKlaim="$langganan['bisaKlaim'] ?? false"
            :sisaLangganan="$langganan['sisaLangganan'] ?? null"
        />

        <x-promo-premium />

        @if ($pesan)
            <x-notifikasi-popup :pesan="$pesan" judul="Berhasil!" />
        @endif

        @if ($galat)
            <div class="flex items-center justify-between gap-3 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 px-4 py-3 text-sm text-red-800 dark:text-red-200">
                <span>{{ $galat }}</span>
                <button wire:click="$set('galat', null)" class="text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-bold">&times;</button>
            </div>
        @endif

        <div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-brand-700 dark:text-brand-300">Ringkasan bisnis</p>
            <h2 class="text-base sm:text-lg font-extrabold tracking-tight text-slate-900 dark:text-gray-100">Sekilas kos-kosanmu</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 !mt-3">
            <x-stat-card label="Kamar Aktif" :value="$kamarAktif ?? ($ringkas['penyewaanAktif'] ?? $totalKamar ?? 0)" tone="cyan"
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

        <div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-brand-700 dark:text-brand-300">Keuangan bulan ini</p>
            <h2 class="text-base sm:text-lg font-extrabold tracking-tight text-slate-900 dark:text-gray-100">Uang masuk vs keluar</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 !mt-3">
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
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-lg bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-300 flex items-center justify-center shrink-0">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-red-800 dark:text-red-200">Ada {{ $tagihanBelumCount }} tagihan belum dibayar senilai <span class="font-bold">Rp{{ number_format($nilaiTagihanBelum, 0, ',', '.') }}</span></p>
                        <p class="text-xs text-red-700/80 dark:text-red-300/80">{{ $tagihanTelat > 0 ? $tagihanTelat . ' di antaranya sudah melewati jatuh tempo.' : 'Semuanya masih dalam batas jatuh tempo.' }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @foreach ([['tersedia', 'Kamar Kosong', $kamarTersedia, 'bg-brand-50 dark:bg-brand-500/10 text-brand-800 dark:text-brand-200 border-brand-100 dark:border-brand-500/20'], ['terisi', 'Kamar Terisi', $kamarTerisi, 'bg-amber-50 dark:bg-amber-500/10 text-amber-800 dark:text-amber-200 border-amber-200 dark:border-amber-500/20'], ['perbaikan', 'Kamar Perbaikan', $kamarPerbaikan, 'bg-stone-100 dark:bg-gray-700/50 text-slate-600 dark:text-gray-300 border-stone-200 dark:border-gray-700']] as [$status, $label, $nilai, $warna])
                <div class="rounded-xl border {{ $warna }} px-4 py-3 flex items-center justify-between">
                    <span class="text-sm font-medium">{{ $label }}</span>
                    <span class="text-xl font-bold">{{ $nilai }}</span>
                </div>
            @endforeach
        </div>

        {{-- Grafik & analitik pindah ke menu sendiri agar tidak membingungkan --}}
        <a href="{{ route('pemilik.grafik') }}" wire:navigate
            class="group flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-xl bg-brand-900 p-5 sm:p-6 text-white transition hover:bg-brand-950">
            <div class="flex items-center gap-4">
                <span class="shrink-0 h-12 w-12 rounded-lg bg-white/10 text-white flex items-center justify-center border border-white/15">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                </span>
                <div>
                    <p class="text-base font-bold">Grafik & Analitik</p>
                    <p class="mt-0.5 text-xs text-brand-100">Keuangan, okupansi, piutang & performa tiap properti — pindah ke menu Grafik di sidebar.</p>
                </div>
            </div>
            <span class="shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-brand-900 group-hover:bg-brand-50 transition">
                Buka Grafik
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12l-7.5 7.5M21 12H3" /></svg>
            </span>
        </a>

        @if ($pembayaranMenungguCount > 0)
            <div class="card overflow-hidden">
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
                                @if ($pembayaran->bukti)
                                    <a href="{{ route('pembayaran.bukti', $pembayaran->id) }}" target="_blank" rel="noopener"
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
                                <button wire:click="verifikasiPembayaran({{ $pembayaran->id }})" wire:confirm="Sudah periksa bukti transfernya? Verifikasi pembayaran ini?"
                                    class="mt-1 inline-flex items-center gap-1 rounded-lg bg-brand-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-800 transition">
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
            <div class="card overflow-hidden">
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
                                <a href="{{ route('pembayaran.kwitansi', $pembayaran) }}" target="_blank" rel="noopener"
                                    class="mt-1 inline-flex items-center gap-1 rounded-lg bg-brand-700 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-brand-800 transition">
                                    Kwitansi{{ $pembayaran->nomor_kwitansi ? ' '.$pembayaran->nomor_kwitansi : '' }}
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500">Belum ada pembayaran terverifikasi.</p>
                    @endforelse
                </div>
            </div>

            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Tagihan Belum Dibayar</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-rose-500 dark:text-rose-400">{{ $tagihanBelumCount }} item</span>
                        <a href="{{ route('pemilik.tagihan') }}" wire:navigate class="text-xs font-semibold text-teal-600 dark:text-teal-400 hover:underline">Lihat semua →</a>
                    </div>
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

    </div>
</div>

{{-- Grafik & analitik ditampilkan penuh di menu Grafik (pemilik.grafik) --}}
