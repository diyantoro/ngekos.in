<?php

use App\Models\ChatPesan;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    public string $tab = 'tagihan';

    public ?int $kosTerbuka = null;

    public ?string $pesan = null;

    public ?string $galat = null;

    public ?int $modalBayarId = null;

    public string $metodeBayar = 'transfer';

    public $bukti = null;

    public function with(): array
    {
        $id = auth()->id();

        $scopeSewa = fn ($q) => $q->where('anak_kos_id', $id)
            ->orWhereHas('anggotas', fn ($w) => $w->where('user_id', $id)->where('status', 'aktif'));

        $dendaGlobal = (float) \App\Models\Pengaturan::dendaPerHari();
        $olesDenda = function ($tagihan) use ($dendaGlobal) {
            if ($tagihan->status === 'lunas') {
                return;
            }
            $tagihan->setAttribute('denda', \App\Services\TagihanService::dendaBerjalan($tagihan));
        };

        return [
            'tagihans' => Tagihan::whereHas('penyewaan', $scopeSewa)
                ->select(['id', 'penyewaan_id', 'periode', 'jumlah', 'denda', 'jatuh_tempo', 'status'])
                ->with(['penyewaan.kamar:id,nama', 'penyewaan.properti:id,nama,kota,alamat,foto,denda_per_hari', 'penyewaan.properti.fotos:id,properti_id,path,urutan', 'penyewaan.anggotas', 'pembayarans:id,tagihan_id,anak_kos_id,jumlah,status'])
                ->orderByDesc('jatuh_tempo')
                ->limit(50)
                ->get()
                ->each($olesDenda),
            'pembayarans' => Pembayaran::where('anak_kos_id', $id)
                ->select(['id', 'tagihan_id', 'metode', 'jumlah', 'bukti', 'status', 'diverifikasi_oleh', 'nomor_kwitansi'])
                ->with(['tagihan:id,penyewaan_id,periode', 'tagihan.penyewaan:id,properti_id,kamar_id', 'tagihan.penyewaan.properti:id,nama,kota,alamat,foto', 'tagihan.penyewaan.properti.fotos:id,properti_id,path,urutan', 'tagihan.penyewaan.kamar:id,nama', 'verifikator:id,nama'])
                ->latest()
                ->limit(50)
                ->get(),
        ];
    }

    public function bayarTagihan(int $tagihanId): void
    {
        $this->resetValidation();
        $this->pesan = null;
        $this->galat = null;
        $this->metodeBayar = 'transfer';
        $this->bukti = null;

        $tagihan = Tagihan::where('id', $tagihanId)
            ->whereHas('penyewaan', fn ($q) => $q->where('anak_kos_id', auth()->id())
                ->orWhereHas('anggotas', fn ($w) => $w->where('user_id', auth()->id())->where('status', 'aktif')))
            ->with(['penyewaan.anggotas', 'penyewaan.properti'])
            ->first();

        if (! $tagihan || $tagihan->status === 'lunas') {
            $this->galat = 'Tagihan tidak ditemukan atau sudah lunas.';

            return;
        }

        \App\Services\TagihanService::sinkronDenda($tagihan);

        if (\App\Services\TagihanService::wajibBayar($tagihan, auth()->id()) <= 0) {
            $this->galat = 'Porsimu untuk tagihan ini sudah lunas. Tinggal menunggu teman sekamarmu bayar porsinya.';

            return;
        }

        $sudahAda = $tagihan->pembayarans()->where('status', 'menunggu_verifikasi')->exists();

        if ($sudahAda) {
            $this->galat = 'Pembayaran untuk tagihan ini masih menunggu verifikasi admin/pemilik.';

            return;
        }

        $this->modalBayarId = $tagihanId;
    }

    public function tutupModalBayar(): void
    {
        $this->modalBayarId = null;
        $this->metodeBayar = 'transfer';
        $this->bukti = null;
        $this->resetValidation();
    }

    public function toggleKos(int $propertiId): void
    {
        $this->kosTerbuka = $this->kosTerbuka === $propertiId ? null : $propertiId;
    }

    public function ubahMetodeBayar(string $metode): void
    {
        $this->metodeBayar = $metode;
        $this->bukti = null;
        $this->clearValidation('bukti');
    }

    public function konfirmasiBayar(): void
    {
        $tagihan = Tagihan::where('id', $this->modalBayarId)
            ->whereHas('penyewaan', fn ($q) => $q->where('anak_kos_id', auth()->id())
                ->orWhereHas('anggotas', fn ($w) => $w->where('user_id', auth()->id())->where('status', 'aktif')))
            ->with(['penyewaan.anggotas', 'penyewaan.properti'])
            ->first();

        if (! $tagihan || $tagihan->status === 'lunas') {
            $this->tutupModalBayar();
            $this->galat = 'Tagihan tidak ditemukan atau sudah lunas.';

            return;
        }

        $wajib = \App\Services\TagihanService::wajibBayar($tagihan, auth()->id());

        if ($wajib <= 0) {
            $this->tutupModalBayar();
            $this->galat = 'Porsimu untuk tagihan ini sudah lunas. Tinggal menunggu teman sekamarmu bayar porsinya.';

            return;
        }

        $sudahAda = $tagihan->pembayarans()->where('status', 'menunggu_verifikasi')->exists();

        if ($sudahAda) {
            $this->tutupModalBayar();
            $this->galat = 'Pembayaran untuk tagihan ini masih menunggu verifikasi admin/pemilik.';

            return;
        }

        $this->validate([
            'metodeBayar' => ['required', 'in:transfer,cash'],
            'bukti' => $this->metodeBayar === 'transfer'
                ? ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:2048']
                : ['nullable'],
        ], [
            'metodeBayar.required' => 'Pilih metode pembayaran terlebih dahulu.',
            'metodeBayar.in' => 'Metode pembayaran tidak valid.',
            'bukti.required' => 'Lampirkan bukti transfer terlebih dahulu.',
            'bukti.mimes' => 'Bukti transfer harus berupa gambar (JPG, PNG, WEBP) atau PDF.',
            'bukti.max' => 'Ukuran bukti transfer maksimal 2MB.',
        ]);

        $buktiPath = null;
        if ($this->metodeBayar === 'transfer' && $this->bukti) {
            $buktiPath = \App\Services\BuktiStorage::simpan($this->bukti, 'bukti-pembayaran');
        }

        $pembayaran = Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'anak_kos_id' => auth()->id(),
            'metode' => $this->metodeBayar,
            'jumlah' => $wajib,
            'bukti' => $buktiPath,
            'status' => 'menunggu_verifikasi',
        ]);

        ChatPesan::notifikasiPembayaranDiajukan($pembayaran);

        $metodeLabel = $this->metodeBayar === 'cash' ? 'tunai' : 'transfer';
        $this->tutupModalBayar();

        $this->pesan = $metodeLabel === 'tunai'
            ? 'Pembayaran tunai tercatat dan sedang menunggu verifikasi admin/pemilik.'
            : 'Pembayaran beserta bukti transfer terkirim dan sedang menunggu verifikasi admin/pemilik.';
    }
}; ?>

<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <a href="{{ route('dashboard.anak-kos') }}" wire:navigate class="text-sm font-medium text-teal-600 dark:text-teal-400 hover:text-teal-500 dark:hover:text-teal-300 inline-flex items-center gap-1">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Kembali ke dashboard
            </a>
            <h1 class="mt-2 text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">Tagihan &amp; Pembayaran</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kapan tagihan jatuh tempo, berapa yang harus dibayar, dan riwayat pembayaranmu.</p>
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

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
            <div class="px-4 sm:px-6 pt-4 pb-3 border-b border-gray-100 dark:border-gray-700">
                <div class="flex gap-2 overflow-x-auto scrollbar-hide pb-1 -mb-1">
                    <button wire:click="$set('tab', 'tagihan')"
                        class="flex-shrink-0 whitespace-nowrap px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'tagihan' ? 'bg-brand-700 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                        Tagihan Saya
                    </button>
                    <button wire:click="$set('tab', 'pembayaran')"
                        class="flex-shrink-0 whitespace-nowrap px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'pembayaran' ? 'bg-brand-700 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                        Riwayat Pembayaran
                    </button>
                </div>
            </div>

            <div class="p-4 sm:p-6">
                @if ($tab === 'tagihan')
                    @php
                        $grupKos = $tagihans->groupBy(fn ($t) => $t->penyewaan?->properti_id ?? 0);
                    @endphp
                    @if ($grupKos->isEmpty())
                        <div class="py-16 text-center">
                            <p class="text-gray-500 dark:text-gray-400 font-medium text-sm">Belum ada tagihan. Kalau kamu lagi ngekos, tagihan bulanannya muncul di sini.</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($grupKos as $propertiId => $daftarKos)
                                @php
                                    $propertiKos = $daftarKos->first()->penyewaan?->properti;
                                    $kamarKos = $daftarKos->first()->penyewaan?->kamar?->nama;
                                    $aktifKos = $daftarKos->where('status', '!=', 'lunas');
                                    $wajibKos = $daftarKos->sum(fn ($t) => \App\Services\TagihanService::wajibBayar($t, auth()->id()));
                                    $dekatKos = $aktifKos->sortBy('jatuh_tempo')->first();
                                    $telatKos = $dekatKos ? \App\Services\TagihanService::hariTelat($dekatKos) : 0;
                                    $sisaKos = $dekatKos ? \App\Services\TagihanService::selisihHari($dekatKos) : null;
                                    $terbukaKos = $kosTerbuka === (int) $propertiId;
                                    $coverKos = $propertiKos?->fotoCover();
                                @endphp
                                <div class="bg-stone-50 dark:bg-gray-800 rounded-2xl border border-stone-300 dark:border-gray-700 shadow-sm overflow-hidden {{ $terbukaKos ? 'ring-1 ring-brand-200 dark:ring-brand-500/30 shadow-card' : 'hover:shadow-card-hover hover:border-brand-200 transition' }}">
                                    <button type="button" wire:click="toggleKos({{ (int) $propertiId }})" class="w-full text-left">
                                        <div class="flex items-center gap-3 p-3 sm:p-4">
                                            @if ($coverKos)
                                                <img src="{{ $coverKos }}" alt="{{ $propertiKos?->nama ?? 'Kos' }}" loading="lazy" class="h-16 w-16 sm:h-20 sm:w-20 rounded-xl object-cover shrink-0">
                                            @else
                                                <div class="h-16 w-16 sm:h-20 sm:w-20 rounded-xl bg-brand-50 dark:bg-brand-500/10 flex items-center justify-center shrink-0">
                                                    <svg class="h-8 w-8 text-brand-700 dark:text-brand-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
                                                </div>
                                            @endif
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-bold text-slate-900 dark:text-gray-100 truncate">{{ $propertiKos?->nama ?? 'Kos tidak tersedia' }}</p>
                                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 truncate">{{ $propertiKos?->kota ?? '-' }}{{ $kamarKos ? ' · Kamar '.$kamarKos : '' }} · {{ $daftarKos->count() }} tagihan</p>
                                                <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                                    @if ($aktifKos->isNotEmpty())
                                                        <span class="rounded-full bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-500/30">{{ $aktifKos->count() }} aktif</span>
                                                        @if ($telatKos > 0)
                                                            <span class="rounded-full bg-rose-50 dark:bg-rose-500/10 px-2 py-0.5 text-[10px] font-bold text-rose-600 dark:text-rose-300 ring-1 ring-rose-200 dark:ring-rose-500/30">Telat {{ $telatKos }} hari</span>
                                                        @elseif ($sisaKos === 0)
                                                            <span class="rounded-full bg-rose-50 dark:bg-rose-500/10 px-2 py-0.5 text-[10px] font-bold text-rose-600 dark:text-rose-300 ring-1 ring-rose-200 dark:ring-rose-500/30">Hari ini</span>
                                                        @elseif ($sisaKos !== null && $sisaKos <= 7)
                                                            <span class="rounded-full bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-500/30">Sisa {{ $sisaKos }} hari</span>
                                                        @endif
                                                    @else
                                                        <span class="rounded-full bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:text-emerald-300 ring-1 ring-emerald-200 dark:ring-emerald-500/30">Semua lunas</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="shrink-0 text-right">
                                                @if ($wajibKos > 0)
                                                    <p class="text-sm font-extrabold text-slate-900 dark:text-gray-100">Rp{{ number_format($wajibKos, 0, ',', '.') }}</p>
                                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">sisa bayarmu</p>
                                                @else
                                                    <p class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400">Lunas</p>
                                                @endif
                                                <svg class="ml-auto mt-1 h-4 w-4 text-gray-400 transition-transform {{ $terbukaKos ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                            </div>
                                        </div>
                                    </button>
                                    @if ($terbukaKos)
                                        <div class="border-t border-stone-300 dark:border-gray-700 divide-y divide-stone-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                            @foreach ($daftarKos->sortByDesc('jatuh_tempo') as $tagihan)
                                                @php
                                                    $isPatunganTagihan = ($tagihan->penyewaan?->mode_hunian ?? 'tunggal') === 'patungan';
                                                    $porsiSaya = $tagihan->penyewaan ? \App\Services\PatunganService::porsiTagihan($tagihan->penyewaan, $tagihan) : null;
                                                    $sudahSaya = $tagihan->penyewaan ? (float) $tagihan->pembayarans->where('status', 'diverifikasi')->where('anak_kos_id', auth()->id())->sum('jumlah') : 0;
                                                    $wajibSaya = $tagihan->penyewaan ? \App\Services\TagihanService::wajibBayar($tagihan, auth()->id()) : 0;
                                                    $sisaHari = \App\Services\TagihanService::selisihHari($tagihan);
                                                    $telatHari = \App\Services\TagihanService::hariTelat($tagihan);
                                                @endphp
                                                <div class="px-3 sm:px-4 py-3 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                                                    <div class="min-w-0 flex-1">
                                                        <div class="flex items-center gap-2">
                                                            <p class="text-sm font-bold text-slate-900 dark:text-gray-100">{{ $tagihan->periode }}</p>
                                                            <x-status-badge :status="$tagihan->status" />
                                                        </div>
                                                        @if ($tagihan->status !== 'lunas' && $tagihan->jatuh_tempo)
                                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                                Jatuh tempo {{ $tagihan->jatuh_tempo?->translatedFormat('d M Y') }}
                                                                @if ($telatHari > 0)
                                                                    <span class="ml-1 rounded-full bg-rose-50 dark:bg-rose-500/10 px-2 py-0.5 text-[10px] font-bold text-rose-600 dark:text-rose-300 ring-1 ring-rose-200 dark:ring-rose-500/30">Telat {{ $telatHari }} hari</span>
                                                                @elseif ($sisaHari === 0)
                                                                    <span class="ml-1 rounded-full bg-rose-50 dark:bg-rose-500/10 px-2 py-0.5 text-[10px] font-bold text-rose-600 dark:text-rose-300 ring-1 ring-rose-200 dark:ring-rose-500/30">Hari ini</span>
                                                                @elseif ($sisaHari <= 7)
                                                                    <span class="ml-1 rounded-full bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-500/30">Sisa {{ $sisaHari }} hari</span>
                                                                @endif
                                                            </p>
                                                        @endif
                                                        @if ($isPatunganTagihan)
                                                            <p class="mt-0.5 text-[11px] font-bold text-sky-600 dark:text-sky-400">Patungan · porsimu Rp{{ number_format($porsiSaya ?? 0, 0, ',', '.') }}</p>
                                                        @endif
                                                    </div>
                                                    <div class="shrink-0 flex sm:flex-col items-center sm:items-end justify-between gap-1">
                                                        <p class="text-sm font-extrabold text-slate-900 dark:text-gray-100">Rp{{ number_format($tagihan->jumlah + $tagihan->denda, 0, ',', '.') }}</p>
                                                        @if ($tagihan->status !== 'lunas' && $wajibSaya > 0)
                                                            <button wire:click="bayarTagihan({{ $tagihan->id }})" wire:loading.attr="disabled"
                                                                class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500 transition">
                                                                Bayar Rp{{ number_format($wajibSaya, 0, ',', '.') }}
                                                            </button>
                                                        @elseif ($tagihan->status !== 'lunas' && $isPatunganTagihan)
                                                            <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 sm:text-right">Porsimu lunas · menunggu teman</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                @else
                    @php
                        $grupBayar = $pembayarans->groupBy(fn ($p) => $p->tagihan?->penyewaan?->properti_id ?? 0);
                    @endphp
                    @if ($grupBayar->isEmpty())
                        <div class="py-16 text-center">
                            <p class="text-gray-500 dark:text-gray-400 font-medium text-sm">Belum ada riwayat pembayaran. Semua pembayaranmu yang udah diverifikasi tercatat di sini.</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($grupBayar as $propertiId => $daftarBayar)
                                @php
                                    $propertiBayar = $daftarBayar->first()->tagihan?->penyewaan?->properti;
                                    $kamarBayar = $daftarBayar->first()->tagihan?->penyewaan?->kamar?->nama;
                                    $totalBayar = $daftarBayar->sum('jumlah');
                                    $terbukaBayar = $kosTerbuka === (int) $propertiId;
                                    $coverBayar = $propertiBayar?->fotoCover();
                                @endphp
                                <div class="bg-stone-50 dark:bg-gray-800 rounded-2xl border border-stone-300 dark:border-gray-700 shadow-sm overflow-hidden {{ $terbukaBayar ? 'ring-1 ring-brand-200 dark:ring-brand-500/30 shadow-card' : 'hover:shadow-card-hover hover:border-brand-200 transition' }}">
                                    <button type="button" wire:click="toggleKos({{ (int) $propertiId }})" class="w-full text-left">
                                        <div class="flex items-center gap-3 p-3 sm:p-4">
                                            @if ($coverBayar)
                                                <img src="{{ $coverBayar }}" alt="{{ $propertiBayar?->nama ?? 'Kos' }}" loading="lazy" class="h-16 w-16 sm:h-20 sm:w-20 rounded-xl object-cover shrink-0">
                                            @else
                                                <div class="h-16 w-16 sm:h-20 sm:w-20 rounded-xl bg-brand-50 dark:bg-brand-500/10 flex items-center justify-center shrink-0">
                                                    <svg class="h-8 w-8 text-brand-700 dark:text-brand-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
                                                </div>
                                            @endif
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-bold text-slate-900 dark:text-gray-100 truncate">{{ $propertiBayar?->nama ?? 'Kos tidak tersedia' }}</p>
                                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 truncate">{{ $propertiBayar?->kota ?? '-' }}{{ $kamarBayar ? ' · Kamar '.$kamarBayar : '' }} · {{ $daftarBayar->count() }} pembayaran</p>
                                                <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                                    <span class="rounded-full bg-brand-50 dark:bg-brand-500/10 px-2 py-0.5 text-[10px] font-bold text-brand-700 dark:text-brand-300 ring-1 ring-brand-200 dark:ring-brand-500/30">Riwayat pembayaran</span>
                                                </div>
                                            </div>
                                            <div class="shrink-0 text-right">
                                                <p class="text-sm font-extrabold text-slate-900 dark:text-gray-100">Rp{{ number_format($totalBayar, 0, ',', '.') }}</p>
                                                <p class="text-[11px] text-gray-400 dark:text-gray-500">total dibayar</p>
                                                <svg class="ml-auto mt-1 h-4 w-4 text-gray-400 transition-transform {{ $terbukaBayar ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                            </div>
                                        </div>
                                    </button>
                                    @if ($terbukaBayar)
                                        <div class="border-t border-stone-300 dark:border-gray-700 divide-y divide-stone-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                            @foreach ($daftarBayar as $pembayaran)
                                                <div class="px-3 sm:px-4 py-3 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                                                    <div class="min-w-0 flex-1">
                                                        <div class="flex items-center gap-2">
                                                            <p class="text-sm font-bold text-slate-900 dark:text-gray-100">{{ $pembayaran->tagihan?->periode ?? '-' }}</p>
                                                            <x-status-badge :status="$pembayaran->status" />
                                                        </div>
                                                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                            {{ $pembayaran->metode === 'cash' ? 'Tunai (Cash)' : 'Transfer' }}
                                                            · Diverifikasi: {{ $pembayaran->verifikator?->nama ?? '-' }}
                                                        </p>
                                                    </div>
                                                    <div class="shrink-0 flex sm:flex-col items-center sm:items-end justify-between gap-1">
                                                        <p class="text-sm font-extrabold text-slate-900 dark:text-gray-100">Rp{{ number_format($pembayaran->jumlah, 0, ',', '.') }}</p>
                                                        <div class="flex items-center gap-1.5">
                                                            @if ($pembayaran->bukti)
                                                                <a href="{{ Storage::url($pembayaran->bukti) }}" target="_blank" rel="noopener"
                                                                    class="inline-flex items-center gap-1 rounded-lg border border-stone-200 dark:border-gray-600 px-2.5 py-1.5 text-xs font-semibold text-brand-700 dark:text-brand-300 hover:bg-stone-50 dark:hover:bg-gray-700 transition">
                                                                    Bukti
                                                                </a>
                                                            @endif
                                                            @if ($pembayaran->status === 'diverifikasi')
                                                                <a href="{{ route('pembayaran.kwitansi', $pembayaran) }}" target="_blank" rel="noopener"
                                                                    class="inline-flex items-center gap-1 rounded-lg bg-brand-700 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">
                                                                    {{ $pembayaran->nomor_kwitansi ?? 'Kwitansi' }}
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    @if ($modalBayarId)
    @php
        $tagihanModal = $tagihans->firstWhere('id', $modalBayarId);
        if (! $tagihanModal) {
            $tagihanModal = \App\Models\Tagihan::select(['id', 'penyewaan_id', 'periode', 'jumlah', 'denda', 'jatuh_tempo', 'status'])
                ->with(['penyewaan.anggotas', 'penyewaan.properti:id,denda_per_hari'])
                ->find($modalBayarId);
            if ($tagihanModal) {
                $tagihanModal->setAttribute('denda', \App\Services\TagihanService::dendaBerjalan($tagihanModal));
            }
        }
        $sewaModal = (float) ($tagihanModal?->jumlah ?? 0);
        $dendaModal = (float) ($tagihanModal?->denda ?? 0);
        $totalTagihan = $sewaModal + $dendaModal;
        $hariTelatModal = $tagihanModal ? \App\Services\TagihanService::hariTelat($tagihanModal) : 0;
        $dendaHarianModal = $tagihanModal ? \App\Services\TagihanService::dendaPerHari($tagihanModal) : 0;
        $porsiModal = $tagihanModal?->penyewaan ? \App\Services\PatunganService::porsiTagihan($tagihanModal->penyewaan, $tagihanModal) : $totalTagihan;
        $isPatunganModal = (bool) $tagihanModal?->penyewaan?->isPatungan();
        $wajibModal = $isPatunganModal && $tagihanModal
            ? round(max(0, $porsiModal - (float) $tagihanModal->pembayarans()->where('anak_kos_id', auth()->id())->where('status', 'diverifikasi')->sum('jumlah')), 2)
            : $totalTagihan;
        $jatuhModal = $tagihanModal?->jatuh_tempo?->translatedFormat('d M Y') ?? '-';
    @endphp
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
        <button type="button" wire:click="tutupModalBayar" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm cursor-default" tabindex="-1" aria-label="Tutup"></button>
        <div class="relative min-h-full flex items-end sm:items-center justify-center p-4">
            <div class="w-full sm:max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-xl ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate">Bayar Tagihan {{ $tagihanModal?->periode }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Jatuh tempo {{ $tagihanModal?->jatuh_tempo?->translatedFormat('d F Y') ?? '-' }}
                            @if ($isPatunganModal)
                                · Porsimu: <span class="font-bold text-sky-600 dark:text-sky-400">Rp{{ number_format($porsiModal, 0, ',', '.') }}</span>
                            @endif
                        </p>
                        @php
                            $sisaModal = $tagihanModal ? \App\Services\TagihanService::selisihHari($tagihanModal) : 0;
                            $telatModal = $hariTelatModal;
                        @endphp
                        @if ($telatModal > 0)
                            <p class="mt-1.5 inline-flex items-center rounded-full bg-rose-50 dark:bg-rose-500/10 px-2.5 py-1 text-[11px] font-bold text-rose-600 dark:text-rose-300 ring-1 ring-rose-200 dark:ring-rose-500/30">
                                Terlambat {{ $telatModal }} hari{{ $dendaHarianModal > 0 ? ' — denda Rp'.number_format($dendaHarianModal, 0, ',', '.').'/hari' : '' }}
                            </p>
                        @elseif ($sisaModal === 0)
                            <p class="mt-1.5 inline-flex items-center rounded-full bg-amber-50 dark:bg-amber-500/10 px-2.5 py-1 text-[11px] font-bold text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-500/30">
                                Jatuh tempo hari ini — bayar sebelum lewat hari ini
                            </p>
                        @else
                            <p class="mt-1.5 inline-flex items-center rounded-full bg-brand-50 dark:bg-brand-500/10 px-2.5 py-1 text-[11px] font-bold text-brand-700 dark:text-brand-300 ring-1 ring-brand-200 dark:ring-brand-500/30">
                                Sisa {{ $sisaModal }} hari (bayar sebelum {{ $jatuhModal }})
                            </p>
                        @endif
                    </div>
                    <button type="button" wire:click="tutupModalBayar"
                        class="shrink-0 h-8 w-8 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-500 dark:text-gray-400 flex items-center justify-center transition">&times;</button>
                </div>

                <form wire:submit="konfirmasiBayar" class="p-5 space-y-4">
                    <div class="rounded-xl bg-gray-50 dark:bg-gray-700/40 ring-1 ring-gray-100 dark:ring-gray-700 px-4 py-3 text-xs space-y-1">
                        <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Sewa</span><span class="font-semibold text-gray-800 dark:text-gray-100">Rp{{ number_format($sewaModal, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Denda{{ $hariTelatModal > 0 ? " ({$hariTelatModal} hari × Rp".number_format($dendaHarianModal, 0, ',', '.').'/hari)' : '' }}</span><span class="font-semibold {{ $dendaModal > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-800 dark:text-gray-100' }}">Rp{{ number_format($dendaModal, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between border-t border-gray-200 dark:border-gray-600 pt-1.5"><span class="font-bold text-gray-700 dark:text-gray-200">{{ $isPatunganModal ? 'Porsimu (harus pas)' : 'Total (harus pas)' }}</span><span class="font-extrabold text-emerald-600 dark:text-emerald-400">Rp{{ number_format($wajibModal, 0, ',', '.') }}</span></div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">Metode Pembayaran</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" wire:click="ubahMetodeBayar('transfer')"
                                class="rounded-xl border px-4 py-2.5 text-sm font-semibold transition {{ $metodeBayar === 'transfer' ? 'border-brand-600 bg-brand-50 text-brand-700 ring-1 ring-brand-600 dark:bg-brand-500/10 dark:text-brand-300' : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                <span class="block text-xs font-bold">Transfer</span>
                                <span class="block text-[11px] font-normal opacity-70">Unggah bukti transfer</span>
                            </button>
                            <button type="button" wire:click="ubahMetodeBayar('cash')"
                                class="rounded-xl border px-4 py-2.5 text-sm font-semibold transition {{ $metodeBayar === 'cash' ? 'border-brand-600 bg-brand-50 text-brand-700 ring-1 ring-brand-600 dark:bg-brand-500/10 dark:text-brand-300' : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                <span class="block text-xs font-bold">Tunai (Cash)</span>
                                <span class="block text-[11px] font-normal opacity-70">Bayar langsung ke admin/pemilik</span>
                            </button>
                        </div>
                        @error('metodeBayar') <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    @if ($metodeBayar === 'transfer')
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Bukti Transfer (JPG/PNG/WEBP/PDF, maks 2MB)</label>
                            <input type="file" wire:model="bukti" accept=".jpg,.jpeg,.png,.webp,.pdf"
                                class="w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 dark:file:bg-brand-500/10 file:px-4 file:py-2 file:text-brand-700 dark:file:text-brand-300 file:font-semibold hover:file:bg-brand-100 dark:hover:file:bg-brand-500/20">
                            @error('bukti') <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                            <div wire:loading wire:target="bukti" class="mt-2 text-xs font-medium text-brand-600">Mengunggah bukti...</div>
                        </div>
                    @else
                        <p class="rounded-xl bg-brand-50 dark:bg-brand-500/10 ring-1 ring-brand-100 dark:ring-brand-500/20 px-4 py-3 text-xs text-brand-800 dark:text-brand-200">
                            Tidak perlu unggah bukti. Status pembayaran menunggu konfirmasi admin/pemilik setelah tunai diterima.
                        </p>
                    @endif
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
