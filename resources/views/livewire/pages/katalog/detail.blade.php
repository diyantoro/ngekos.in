<?php

use App\Models\Kamar;
use App\Models\Properti;
use App\Models\Ulasan;
use App\Services\PenyewaanService;
use App\Support\Koordinat;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.publik')] class extends Component
{
    use WithFileUploads;

    public Properti $properti;

    public ?string $galat = null;

    public ?string $pesan = null;

    public ?int $modalKamarId = null;

    public string $tanggalMasuk = '';

    public int $durasiBulan = 1;

    public int $durasiHari = 1;

    public int $durasiMinggu = 1;

    public string $periodeSewa = 'bulanan';

    public int $langkahSewa = 1;

    public $ktp = null;

    public bool $favorit = false;

    public int $ratingUlasan = 0;

    public string $komentarUlasan = '';

    public ?array $statistikUlasan = null;

    public function mount(): void
    {
        $this->favorit = auth()->check() && $this->properti->peminat()->where('user_id', auth()->id())->exists();

        $this->statistikUlasan = [
            'total' => $this->properti->ulasans()->count(),
            'rata' => $this->properti->ulasans()->avg('rating'),
            'distribusi' => $this->properti->ulasans()->selectRaw('rating, COUNT(*) as jumlah')->groupBy('rating')->pluck('jumlah', 'rating')->toArray(),
        ];
    }

    public function with(): array
    {
        $this->properti->loadCount([
            'kamars as total_kamar',
            'kamars as kamar_tersedia' => fn ($q) => $q->where('status', 'tersedia'),
        ]);
        $this->properti->loadMissing('fotos');

        return [
            'kamars' => Kamar::with('fotos')->where('properti_id', $this->properti->id)->orderBy('nama')->get(),
            'titik' => Koordinat::titik($this->properti->kota, $this->properti->latitude, $this->properti->longitude),
            'ulasans' => Ulasan::with('user')->where('properti_id', $this->properti->id)->latest()->get(),
        ];
    }

    public function pesanKamar(int $kamarId): void
    {
        if (! auth()->user()?->hasRole('anak_kos')) {
            $this->galat = 'Hanya akun pencari kos (anak kos) yang dapat menyewa kamar.';
            $this->pesan = null;

            return;
        }

        $kamar = Kamar::where('id', $kamarId)->where('properti_id', $this->properti->id)->first();

        if (! $kamar || $kamar->status !== 'tersedia') {
            $this->galat = 'Kamar tidak tersedia saat ini.';
            $this->pesan = null;

            return;
        }

        $periodeTersedia = $kamar->periodeTersedia();
        if (empty($periodeTersedia)) {
            $this->galat = 'Kamar ini belum menetapkan harga sewa.';
            $this->pesan = null;

            return;
        }

        $this->galat = null;
        $this->pesan = null;
        $this->modalKamarId = $kamarId;
        $this->tanggalMasuk = today()->toDateString();
        $this->durasiBulan = 1;
        $this->durasiMinggu = 1;
        $this->durasiHari = 1;
        $this->periodeSewa = $periodeTersedia[0];
        $this->langkahSewa = 1;
    }

    public function tutupModalSewa(): void
    {
        $this->modalKamarId = null;
        $this->tanggalMasuk = '';
        $this->durasiBulan = 1;
        $this->durasiMinggu = 1;
        $this->durasiHari = 1;
        $this->periodeSewa = 'bulanan';
        $this->langkahSewa = 1;
        $this->ktp = null;
        $this->resetValidation();
    }

    /**
     * Aturan durasi per periode (dipakai lanjutReview + konfirmasiSewa).
     */
    private function aturanDurasi(): array
    {
        return match ($this->periodeSewa) {
            'mingguan' => ['durasiMinggu' => ['required', 'integer', 'min:1', 'max:12']],
            'harian' => ['durasiHari' => ['required', 'integer', 'min:1', 'max:90']],
            default => ['durasiBulan' => ['required', 'integer', 'min:1', 'max:12']],
        };
    }

    private function pesanDurasi(): array
    {
        return [
            'durasiBulan.required' => 'Pilih lama sewa terlebih dahulu.',
            'durasiBulan.min' => 'Lama sewa minimal 1 bulan.',
            'durasiBulan.max' => 'Lama sewa maksimal 12 bulan.',
            'durasiMinggu.required' => 'Pilih lama sewa mingguan terlebih dahulu.',
            'durasiMinggu.min' => 'Lama sewa mingguan minimal 1 minggu.',
            'durasiMinggu.max' => 'Lama sewa mingguan maksimal 12 minggu.',
            'durasiHari.required' => 'Pilih lama sewa harian terlebih dahulu.',
            'durasiHari.min' => 'Lama sewa harian minimal 1 hari.',
            'durasiHari.max' => 'Lama sewa harian maksimal 90 hari.',
        ];
    }

    public function lanjutReview(): void
    {
        $rules = array_merge([
            'tanggalMasuk' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:' . today()->addMonths(3)->toDateString()],
            'ktp' => PenyewaanService::ATURAN_KTP,
        ], $this->aturanDurasi());

        $this->validate($rules, array_merge([
            'tanggalMasuk.required' => 'Pilih tanggal masuk terlebih dahulu.',
            'tanggalMasuk.date' => 'Tanggal masuk tidak valid.',
            'tanggalMasuk.after_or_equal' => 'Tanggal masuk tidak boleh mundur dari hari ini.',
            'tanggalMasuk.before_or_equal' => 'Tanggal masuk maksimal 3 bulan ke depan.',
        ], $this->pesanDurasi(), PenyewaanService::pesanKtp()));

        // Pastikan periode yang dipilih memang punya harga.
        $kamar = Kamar::where('id', $this->modalKamarId)->where('properti_id', $this->properti->id)->first();

        $hargaPeriode = $kamar ? $kamar->hargaUntuk($this->periodeSewa) : null;
        if ($kamar && ($hargaPeriode === null || $hargaPeriode <= 0)) {
            $this->addError('periodeSewa', 'Periode ini tidak tersedia untuk kamar tersebut.');

            return;
        }

        $this->langkahSewa = 2;
    }

    public function kembaliKeTanggal(): void
    {
        $this->langkahSewa = 1;
        $this->resetValidation();
    }

    public function konfirmasiSewa(): void
    {
        if (! auth()->user()?->hasRole('anak_kos')) {
            $this->resetForm();
            $this->galat = 'Hanya akun pencari kos (anak kos) yang dapat menyewa kamar.';

            return;
        }

        $rules = array_merge([
            'tanggalMasuk' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:' . today()->addMonths(3)->toDateString()],
            'ktp' => PenyewaanService::ATURAN_KTP,
        ], $this->aturanDurasi());

        $this->validate($rules, array_merge([
            'tanggalMasuk.required' => 'Pilih tanggal masuk terlebih dahulu.',
            'tanggalMasuk.date' => 'Tanggal masuk tidak valid.',
            'tanggalMasuk.after_or_equal' => 'Tanggal masuk tidak boleh mundur dari hari ini.',
            'tanggalMasuk.before_or_equal' => 'Tanggal masuk maksimal 3 bulan ke depan.',
        ], $this->pesanDurasi(), PenyewaanService::pesanKtp()));

        $kamar = Kamar::with('properti')
            ->where('id', $this->modalKamarId)
            ->where('properti_id', $this->properti->id)
            ->first();

        if (! $kamar) {
            $this->resetForm();
            $this->galat = 'Kamar tidak ditemukan.';

            return;
        }

        $periodeTersedia = $kamar->periodeTersedia();
        if (! in_array($this->periodeSewa, $periodeTersedia, true)) {
            $this->resetForm();
            $this->galat = 'Periode sewa yang dipilih tidak tersedia untuk kamar ini.';

            return;
        }

        $ktpPath = $this->ktp ? \App\Services\KtpStorage::simpan($this->ktp) : null;

        try {
            app(PenyewaanService::class)->sewaKamar(
                auth()->user(),
                $kamar,
                $this->tanggalMasuk,
                $this->durasiBulan,
                $this->periodeSewa === 'harian' ? $this->durasiHari : null,
                $ktpPath,
                $this->periodeSewa === 'mingguan' ? $this->durasiMinggu : null,
            );
        } catch (DomainException $e) {
            \App\Services\KtpStorage::hapus($ktpPath);
            $this->resetForm();
            $this->galat = $e->getMessage();

            return;
        }

        $harga = $kamar->hargaUntuk($this->periodeSewa) ?? $kamar->harga_sewa_bulanan;
        $durasi = match ($this->periodeSewa) {
            'mingguan' => $this->durasiMinggu,
            'harian' => $this->durasiHari,
            default => $this->durasiBulan,
        };
        $satuan = match ($this->periodeSewa) {
            'mingguan' => 'minggu',
            'harian' => 'hari',
            default => 'bulan',
        };
        $totalTagihan = round((float) $harga * $durasi);
        $pesanBaru = 'Kamar '.$kamar->nama.' berhasil dipesan selama '.$durasi.' '.$satuan.'. '
            .'Rencana masuk: '.Carbon::parse($this->tanggalMasuk)->locale('id')->translatedFormat('d F Y')
            .' — tagihan Rp'.number_format($totalTagihan, 0, ',', '.').' menunggu pembayaran.';
        $this->pesan = $pesanBaru;
        $this->resetForm();
        $this->dispatch('booking-sukses', pesan: $pesanBaru);
    }

    public function toggleFavorit(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login');

            return;
        }

        $user = auth()->user();
        if ($this->favorit) {
            $user->favorits()->detach($this->properti->id);
            $this->favorit = false;
        } else {
            $user->favorits()->syncWithoutDetaching($this->properti->id);
            $this->favorit = true;
        }
    }

    public function simpanUlasan(): void
    {
        if (! auth()->user()?->hasRole('anak_kos')) {
            $this->galat = 'Hanya akun pencari kos (anak kos) yang dapat memberi ulasan.';
            $this->pesan = null;

            return;
        }

        $this->validate([
            'ratingUlasan' => ['required', 'integer', 'between:1,5'],
        ], [
            'ratingUlasan.required' => 'Pilih bintang rating terlebih dahulu.',
        ]);

        Ulasan::updateOrCreate(
            ['user_id' => auth()->id(), 'properti_id' => $this->properti->id],
            ['rating' => $this->ratingUlasan, 'komentar' => $this->komentarUlasan ?: null],
        );

        $this->statistikUlasan = [
            'total' => $this->properti->ulasans()->count(),
            'rata' => round($this->properti->ulasans()->avg('rating'), 1),
            'distribusi' => $this->properti->ulasans()->selectRaw('rating, COUNT(*) as jumlah')->groupBy('rating')->pluck('jumlah', 'rating')->toArray(),
        ];

        $this->pesan = 'Terima kasih! Ulasanmu berhasil disimpan.';
        $this->galat = null;
        $this->ratingUlasan = 0;
        $this->komentarUlasan = '';
    }

    private function resetForm(): void
    {
        $this->modalKamarId = null;
        $this->tanggalMasuk = '';
        $this->durasiBulan = 1;
        $this->durasiMinggu = 1;
        $this->durasiHari = 1;
        $this->periodeSewa = 'bulanan';
        $this->langkahSewa = 1;
        $this->ktp = null;
        $this->resetValidation();
    }
}; ?>

<div>
    <!-- Toast sukses booking -->
    <div x-data="{ buka: false, isi: '' }"
         x-on:booking-sukses.window="isi = $event.detail.pesan; buka = true; clearTimeout(window.__toastBooking); window.__toastBooking = setTimeout(() => buka = false, 5000)"
         class="fixed top-4 inset-x-0 z-[60] flex justify-center px-4 pointer-events-none"
         role="alert">
        <div x-show="buka"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-3"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0 -translate-y-3"
             class="pointer-events-auto flex max-w-md items-start gap-3 rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-xl shadow-emerald-900/30 ring-1 ring-white/20">
            <svg class="h-5 w-5 shrink-0 mt-0.5 text-emerald-100" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <div class="min-w-0">
                <p class="font-extrabold">Kamar Berhasil Dipesan</p>
                <p class="mt-0.5 text-emerald-50 text-xs leading-relaxed" x-text="isi"></p>
            </div>
            <button type="button" @click="buka = false" class="shrink-0 -m-1 rounded-lg p-1 text-emerald-100 hover:bg-emerald-500 transition" aria-label="Tutup">&times;</button>
        </div>
    </div>

    <!-- Cover Image -->
    <x-galeri-kos :fotos="$properti->galeriUrls()" :nama="$properti->nama" kelas="relative h-56 sm:h-72">
        <a href="{{ route('kos.index') }}" wire:navigate
           class="absolute top-4 left-4 inline-flex items-center gap-1.5 rounded-xl bg-black/40 backdrop-blur-sm px-3 py-2 text-sm font-medium text-white hover:bg-black/60 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
            Kembali
        </a>
        @auth
            <a href="{{ route('chat.room', ['properti' => $properti->id]) }}" wire:navigate
               class="absolute top-4 right-4 inline-flex items-center gap-1.5 rounded-xl bg-black/40 backdrop-blur-sm px-3 py-2 text-sm font-medium text-white hover:bg-black/60 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                Chat
            </a>
        @endauth
    </x-galeri-kos>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if ($galat)
            <div class="mb-4 flex items-center justify-between gap-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 ring-1 ring-rose-200 dark:ring-rose-500/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                <span>{{ $galat }}</span>
                <button wire:click="$set('galat', null)" class="text-rose-500 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 font-bold">&times;</button>
            </div>
        @endif

        @if ($pesan)
            <div class="mb-4 flex items-center justify-between gap-3 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 ring-1 ring-emerald-200 dark:ring-emerald-500/30 px-4 py-3 text-sm text-emerald-800 dark:text-emerald-200">
                <span>{{ $pesan }}</span>
                <button wire:click="$set('pesan', null)" class="text-emerald-500 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 font-bold">&times;</button>
            </div>
        @endif

        <!-- Info Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-6 -mt-8 relative z-10">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-gray-100">{{ $properti->nama }}</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                        {{ $properti->alamat ?? $properti->kota ?? 'Lokasi belum diisi' }}
                    </p>
                    @if ($titik)
                        <div class="mt-3 overflow-hidden rounded-xl ring-1 ring-gray-100 dark:ring-gray-700">
                            <div id="peta-properti-detail"
                                wire:ignore
                                class="h-48 sm:h-56 w-full z-0"
                                role="region"
                                aria-label="Peta lokasi {{ $properti->nama }}"
                                data-lat="{{ $titik[0] }}"
                                data-lng="{{ $titik[1] }}"
                                data-nama="{{ $properti->nama }}"
                                data-alamat="{{ $properti->alamat }}"></div>
                            <a id="peta-buka-osm" href="#" target="_blank" rel="noopener nofollow"
                               class="flex items-center justify-center gap-1 border-t border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-medium text-teal-600 hover:text-teal-500 dark:text-teal-400 dark:hover:text-teal-300">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" /></svg>
                                Buka di Google Maps
                            </a>
                        </div>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="toggleFavorit"
                        @class(['shrink-0 inline-flex items-center justify-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition h-10 w-10 border',
                                'border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-500/20' => $favorit,
                                'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-400 dark:text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-rose-500' => !$favorit])
                        aria-label="{{ $favorit ? 'Hapus dari favorit' : 'Tambah ke favorit' }}">
                        @if ($favorit)
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                        @else
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                        @endif
                    </button>
                    <x-status-badge :status="$properti->status" />
                    <span class="inline-flex items-center rounded-full bg-teal-50 dark:bg-teal-500/10 px-2.5 py-0.5 text-xs font-medium text-teal-700 dark:text-teal-300 ring-1 ring-inset ring-teal-200 dark:ring-teal-500/30">
                        {{ $properti->kamar_tersedia }}/{{ $properti->total_kamar }} tersedia
                    </span>
                </div>

                @if ($statistikUlasan && $statistikUlasan['total'] > 0)
                    <div class="mt-2 flex items-center gap-1.5">
                        <x-star-rating :rating="round($statistikUlasan['rata'])" size="h-3.5 w-3.5" />
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ number_format($statistikUlasan['rata'], 1, ',', '.') }}</span>
                        <span class="text-xs text-gray-400 dark:text-gray-500">({{ $statistikUlasan['total'] }} ulasan)</span>
                    </div>
                @endif
            </div>

            <div class="mt-4 grid grid-cols-3 gap-3">
                <div class="rounded-xl bg-gray-50 dark:bg-gray-700/50 p-3 text-center">
                    <p class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Harga Mulai</p>
                    <p class="mt-1">
                        @php
                            $termurahBulan = $properti->kamars()->where('status', 'tersedia')->min('harga_sewa_bulanan');
                            $termurahMinggu = $properti->kamars()->where('status', 'tersedia')->whereNotNull('harga_sewa_mingguan')->min('harga_sewa_mingguan');
                            $termurahHari = $properti->kamars()->where('status', 'tersedia')->whereNotNull('harga_sewa_harian')->min('harga_sewa_harian');
                            $adaDiskonDetail = $properti->harga_asli && $termurahBulan && $properti->harga_asli > $termurahBulan;
                        @endphp
                        @if ($termurahBulan)
                            <x-harga-tiga-periode :bulanan="$termurahBulan" :mingguan="$termurahMinggu" :harian="$termurahHari" :asli="$adaDiskonDetail ? $properti->harga_asli : null" varian="baris" />
                        @else
                            <span class="text-xs font-medium text-gray-400 dark:text-gray-500">Penuh</span>
                        @endif
                    </p>
                </div>
                <div class="rounded-xl bg-gray-50 dark:bg-gray-700/50 p-3 text-center">
                    <p class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Denda</p>
                    <p class="mt-1 text-sm font-bold text-gray-900 dark:text-gray-100">
                        @if ($properti->denda_per_hari)
                            Rp{{ number_format($properti->denda_per_hari, 0, ',', '.') }}<span class="text-[10px] text-gray-400 dark:text-gray-500">/hr</span>
                        @else
                            <span class="text-xs text-gray-400 dark:text-gray-500">Tidak ada</span>
                        @endif
                    </p>
                </div>
                <div class="rounded-xl bg-gray-50 dark:bg-gray-700/50 p-3 text-center">
                    <p class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Kontak</p>
                    <p class="mt-1 text-sm font-bold text-gray-900 dark:text-gray-100 truncate">{{ $properti->pemilik?->no_hp ?? '-' }}</p>
                </div>
            </div>

            @if (! auth()->check() || auth()->user()->hasRole('anak_kos'))
                <div class="mt-4 rounded-xl bg-teal-50 dark:bg-teal-500/10 ring-1 ring-teal-100 dark:ring-teal-500/30 p-3 sm:p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Punya pertanyaan?</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Tanya langsung pemiliknya lewat chat.</p>
                    </div>
                    @if (auth()->check())
                        <a href="{{ route('chat.room', ['properti' => $properti->id]) }}" wire:navigate
                           class="shrink-0 inline-flex items-center justify-center gap-1.5 rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                            Tanya Pemilik
                        </a>
                    @else
                        <a href="{{ route('login') }}" wire:navigate
                           class="shrink-0 inline-flex items-center justify-center rounded-lg border border-teal-200 dark:border-teal-500/30 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-semibold text-teal-600 dark:text-teal-400 hover:bg-teal-100 dark:hover:bg-teal-500/10 transition">
                            Masuk untuk Chat
                        </a>
                    @endif
                </div>
            @endif

            @if ($properti->deskripsi)
                <div class="mt-4">
                    <h2 class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wide">Deskripsi</h2>
                    <p class="mt-1.5 text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ $properti->deskripsi }}</p>
                </div>
            @endif

            @if ($properti->fasilitas)
                <div class="mt-4">
                    <h2 class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wide">Fasilitas</h2>
                    <div class="mt-2">
                        <x-facility-icons :fasilitas="$properti->fasilitas" />
                    </div>
                </div>
            @endif

            @if ($properti->aturan)
                <div class="mt-4">
                    <h2 class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wide">Aturan Kos</h2>
                    <p class="mt-1.5 text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ $properti->aturan }}</p>
                </div>
            @endif

        <!-- Iklan Partner -->
        <div class="mt-6">
            <x-promo-ads />
        </div>

        <!-- Informasi Pemilik -->
        @if ($properti->pemilik)
            <div class="mt-6 rounded-2xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-5">
                <h2 class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wide">Informasi Pemilik</h2>
                <div class="mt-3 flex items-center gap-3">
                    <x-user-avatar :user="$properti->pemilik" size="md" />
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate">{{ $properti->pemilik->nama }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                            @if ($properti->pemilik->no_hp)
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" /></svg>
                                {{ $properti->pemilik->no_hp }}
                            @else
                                Kontak belum diisi
                            @endif
                        </p>
                    </div>
                    @auth
                        @if (auth()->user()->id !== $properti->pemilik_id)
                            <a href="{{ route('chat.room', ['properti' => $properti->id]) }}" wire:navigate
                               class="shrink-0 inline-flex items-center gap-1.5 rounded-xl bg-teal-50 dark:bg-teal-500/10 ring-1 ring-teal-200 dark:ring-teal-500/30 px-3.5 py-2 text-xs font-semibold text-teal-700 dark:text-teal-300 hover:bg-teal-100 dark:hover:bg-teal-500/20 transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                                Chat
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        @endif
        </div>

        <!-- Daftar Kamar -->
        <div class="mt-6">
            <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-gray-100">Daftar Kamar</h2>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Pilih kamar yang tersedia dan tanya pemiliknya lewat chat.</p>

            <div class="mt-3 space-y-3">
                @forelse ($kamars as $kamar)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                        <div class="flex">
                            <x-galeri-kos :fotos="$kamar->galeriUrls()" :nama="'Kamar ' . $kamar->nama" kelas="h-28 sm:h-36 w-24 sm:w-36 shrink-0" ringkas />

                            <div class="flex-1 p-3 sm:p-4 flex flex-col justify-between">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-gray-100">{{ $kamar->nama }}</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $kamar->kapasitas }} orang</p>
                                    </div>
                                    <x-status-badge :status="$kamar->status" />
                                </div>

                                <div class="mt-2 flex items-end justify-between">
                                    <div>
                                        <x-harga-tiga-periode :bulanan="$kamar->harga_sewa_bulanan" :mingguan="$kamar->harga_sewa_mingguan" :harian="$kamar->harga_sewa_harian" :asli="($kamar->harga_asli && $kamar->harga_asli > $kamar->harga_sewa_bulanan) ? $kamar->harga_asli : null" varian="rincian" />
                                    </div>

                                    @if ($kamar->status === 'tersedia')
                                        @auth
                                            @if (auth()->user()->hasRole('anak_kos'))
                                                <div class="flex items-center gap-2">
                                                    <button wire:click="pesanKamar({{ $kamar->id }})" wire:loading.attr="disabled"
                                                        class="shrink-0 inline-flex items-center justify-center gap-1.5 rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-500 transition disabled:opacity-50">
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                                                        Sewa
                                                    </button>
                                                    <a href="{{ route('chat.room', ['properti' => $properti->id]) }}" wire:navigate
                                                        class="shrink-0 inline-flex items-center justify-center gap-1.5 rounded-lg border border-teal-200 dark:border-teal-500/30 bg-teal-50 dark:bg-teal-500/10 px-3 py-1.5 text-xs font-semibold text-teal-600 dark:text-teal-400 hover:bg-teal-100 dark:hover:bg-teal-500/20 transition">
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                                                        Chat
                                                    </a>
                                                </div>
                                            @else
                                                <a href="{{ route('dashboard') }}" wire:navigate
                                                    class="shrink-0 inline-flex items-center justify-center rounded-lg border border-teal-200 bg-teal-50 px-3 py-1.5 text-xs font-semibold text-teal-600 dark:border-teal-500/30 dark:bg-teal-500/10 dark:text-teal-300 hover:bg-teal-100 dark:hover:bg-teal-500/20 transition">
                                                    Kelola
                                                </a>
                                            @endif
                                        @else
                                            <a href="{{ route('login') }}" wire:navigate
                                                class="shrink-0 inline-flex items-center justify-center rounded-lg border border-teal-200 bg-teal-50 px-3 py-1.5 text-xs font-semibold text-teal-600 dark:border-teal-500/30 dark:bg-teal-500/10 dark:text-teal-300 hover:bg-teal-100 dark:hover:bg-teal-500/20 transition">
                                                Masuk
                                            </a>
                                        @endauth
                                    @else
                                        <span class="text-xs font-medium text-gray-400 dark:text-gray-500">Terisi</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center">
                        <div class="mx-auto h-12 w-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                            <svg class="h-6 w-6 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                        </div>
                        <p class="text-sm text-gray-400 dark:text-gray-500">Belum ada kamar terdaftar.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Ulasan & Rating -->
        <div class="mt-6">
            <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-gray-100">Ulasan &amp; Rating</h2>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Penilaian dari penghuni kos.</p>

            <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Ringkasan statistik --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-5">
                    @if ($statistikUlasan && $statistikUlasan['total'] > 0)
                        <div class="flex items-center gap-3">
                            <span class="text-3xl sm:text-4xl font-extrabold text-gray-900 dark:text-gray-100">{{ number_format($statistikUlasan['rata'], 1, ',', '.') }}</span>
                            <div>
                                <x-star-rating :rating="round($statistikUlasan['rata'])" size="h-4 w-4" />
                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ $statistikUlasan['total'] }} ulasan</p>
                            </div>
                        </div>
                        <div class="mt-4 space-y-1.5">
                            @for ($bintang = 5; $bintang >= 1; $bintang--)
                                @php
                                    $jumlah = $statistikUlasan['distribusi'][$bintang] ?? 0;
                                    $persen = $statistikUlasan['total'] > 0 ? round(($jumlah / $statistikUlasan['total']) * 100) : 0;
                                @endphp
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="w-8 font-semibold text-gray-500 dark:text-gray-400 shrink-0">{{ $bintang }}★</span>
                                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                        <div class="h-full rounded-full bg-amber-400" style="width: {{ $persen }}%"></div>
                                    </div>
                                    <span class="w-8 text-right text-gray-400 dark:text-gray-500">{{ $jumlah }}</span>
                                </div>
                            @endfor
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada ulasan untuk kos ini. Jadilah yang pertama memberi penilaian.</p>
                    @endif
                </div>

                {{-- Form ulasan pengguna login --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-5 md:col-span-2">
                    @auth
                        @if (auth()->user()->hasRole('anak_kos'))
                            <div x-data="{ nilai: {{ $ratingUlasan }}, hover: 0 }">
                                <p class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wide">Berikan Ulasan</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Klik bintang lalu tekan Kirim untuk menyimpan ulasanmu.</p>
                                <div class="mt-2 flex items-center gap-1">
                                    <template x-for="i in 5" :key="i">
                                        <button type="button" x-on:mouseenter="hover = i" x-on:mouseleave="hover = 0"
                                            x-on:click="nilai = i; $wire.set('ratingUlasan', i)"
                                            class="p-0.5 transition-transform hover:scale-110 focus:outline-none"
                                            :aria-label="'Beri rating ' + i + ' dari 5'">
                                            <svg x-show="i <= (hover || nilai)" class="h-6 w-6 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.446a1 1 0 00-.363 1.118l1.286 3.958c.3.922-.755 1.688-1.539 1.118l-3.367-2.446a1 1 0 00-1.176 0l-3.367 2.446c-.784.57-1.838-.196-1.539-1.118l1.286-3.958a1 1 0 00-.363-1.118L2.126 9.385c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.958z"/></svg>
                                            <svg x-show="i > (hover || nilai)" class="h-6 w-6 text-gray-300 dark:text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.446a1 1 0 00-.363 1.118l1.286 3.958c.3.922-.755 1.688-1.539 1.118l-3.367-2.446a1 1 0 00-1.176 0l-3.367 2.446c-.784.57-1.838-.196-1.539-1.118l1.286-3.958a1 1 0 00-.363-1.118L2.126 9.385c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.958z"/></svg>
                                        </button>
                                    </template>
                                    <span x-show="nilai > 0" x-text="nilai + ' dari 5'" class="ml-2 text-xs font-semibold text-gray-700 dark:text-gray-300"></span>
                                </div>
                                <textarea wire:model="komentarUlasan" rows="3"
                                    class="mt-3 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm"
                                    placeholder="Bagikan pengalamanmu tinggal di kos ini (opsional)"></textarea>
                                <div class="mt-2 flex items-center justify-end gap-2">
                                    <x-primary-button wire:click="simpanUlasan" wire:loading.attr="disabled" x-show="nilai > 0" class="text-xs px-3 py-1.5 disabled:opacity-60">
                                        <span wire:loading.remove wire:target="simpanUlasan">Kirim Ulasan</span>
                                        <span wire:loading wire:target="simpanUlasan" class="inline-flex items-center gap-1.5">
                                            <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                                            Mengirim...
                                        </span>
                                    </x-primary-button>
                                    <span x-show="nilai == 0" class="text-xs text-gray-400 dark:text-gray-500">Pilih bintang dulu untuk mengirim</span>
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">Hanya pengguna dengan akun <strong>anak kos</strong> yang dapat memberikan ulasan.</p>
                        @endif
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">Silakan <a href="{{ route('login') }}" wire:navigate class="font-semibold text-teal-600 dark:text-teal-400 hover:underline">masuk</a> untuk memberikan ulasan.</p>
                    @endauth

                    {{-- Daftar ulasan --}}
                    <div class="mt-4 divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($ulasans as $ulasan)
                            <div class="py-3 first:pt-0 last:pb-0">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <x-user-avatar :user="$ulasan->user" size="sm" />
                                        <div>
                                            <p class="text-xs font-bold text-gray-900 dark:text-gray-100">{{ $ulasan->user->nama }}</p>
                                            <p class="text-[10px] text-gray-400 dark:text-gray-500">{{ $ulasan->created_at->locale('id')->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    <x-star-rating :rating="$ulasan->rating" size="h-3 w-3" />
                                </div>
                                @if ($ulasan->komentar)
                                    <p class="mt-1.5 text-xs text-gray-600 dark:text-gray-300 leading-relaxed">{{ $ulasan->komentar }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="py-3 text-xs text-gray-400 dark:text-gray-500 text-center">Belum ada ulasan yang ditulis.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($modalKamarId)
    @php
        $kamarModal = $kamars->firstWhere('id', $modalKamarId);
    @endphp
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
        <button type="button" wire:click="tutupModalSewa" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm cursor-default" tabindex="-1" aria-label="Tutup"></button>
        <div class="relative min-h-full flex items-end sm:items-center justify-center p-4">
            <div class="w-full sm:max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-xl ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate">Sewa Kamar {{ $kamarModal?->nama }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $properti->nama }}
                            &middot; Rp{{ number_format($kamarModal?->harga_sewa_bulanan ?? 0, 0, ',', '.') }}/bulan
                            @if ($kamarModal?->harga_sewa_mingguan) &middot; Rp{{ number_format($kamarModal->harga_sewa_mingguan, 0, ',', '.') }}/minggu @endif
                            @if ($kamarModal?->harga_sewa_harian) &middot; Rp{{ number_format($kamarModal->harga_sewa_harian, 0, ',', '.') }}/hari @endif</p>
                    </div>
                    <button type="button" wire:click="tutupModalSewa"
                        class="shrink-0 h-8 w-8 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-500 dark:text-gray-400 flex items-center justify-center transition">&times;</button>
                </div>

                <form wire:submit="konfirmasiSewa" class="p-5 space-y-4">
                    {{-- Step 1: Pilih tanggal --}}
                    <div wire:key="langkah-1" @if ($langkahSewa !== 1) class="hidden" @endif>
                        <p class="text-xs text-gray-400 dark:text-gray-500">Kamar yang tersedia akan langsung terkunci untukmu — tanpa menunggu konfirmasi. Pilih tanggal kamu berencana masuk (maksimal 3 bulan ke depan) dan lama sewa. Tagihan dibuat otomatis untuk dibayar.</p>
                        <div class="mt-4">
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Tanggal Masuk</label>
                            <input type="date" wire:model="tanggalMasuk" min="{{ today()->toDateString() }}" max="{{ today()->addMonths(3)->toDateString() }}"
                                class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm text-gray-700">
                            @error('tanggalMasuk') <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="mt-4">
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Periode Sewa</label>
                            @php
                                $opsiPeriode = [
                                    'bulanan' => ['label' => 'Per Bulan', 'harga' => $kamarModal?->harga_sewa_bulanan],
                                    'mingguan' => ['label' => 'Per Minggu', 'harga' => $kamarModal?->harga_sewa_mingguan],
                                    'harian' => ['label' => 'Per Hari', 'harga' => $kamarModal?->harga_sewa_harian],
                                ];
                            @endphp
                            <div class="grid grid-cols-3 gap-2">
                                @foreach ($opsiPeriode as $nilai => $opsi)
                                    @php $tersedia = $opsi['harga'] !== null && (float) $opsi['harga'] > 0; @endphp
                                    <button type="button" wire:click="$set('periodeSewa', '{{ $nilai }}')"
                                        @if (! $tersedia) disabled title="Pemilik tidak membuka sewa {{ strtolower($opsi['label']) }}" @endif
                                        @class(['rounded-xl border px-2 py-2.5 text-sm font-semibold transition',
                                                'bg-teal-600 border-teal-600 text-white' => $periodeSewa === $nilai && $tersedia,
                                                'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' => $periodeSewa !== $nilai && $tersedia,
                                                'border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 text-gray-300 dark:text-gray-600 cursor-not-allowed' => ! $tersedia])>
                                        {{ $opsi['label'] }}
                                        <span class="block text-[10px] font-normal opacity-70">
                                            @if ($tersedia)
                                                Rp{{ number_format($opsi['harga'], 0, ',', '.') }}
                                            @else
                                                Tdk tersedia
                                            @endif
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                            @error('periodeSewa') <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="mt-4">
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">
                                {{ $periodeSewa === 'harian' ? 'Lama Sewa (hari)' : ($periodeSewa === 'mingguan' ? 'Lama Sewa (minggu)' : 'Lama Sewa (bulan)') }}
                            </label>
                            @if ($periodeSewa === 'harian')
                                <select wire:model="durasiHari"
                                    class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm text-gray-700">
                                    @foreach (range(1, 90) as $hari)
                                        <option value="{{ $hari }}">{{ $hari }} hari</option>
                                    @endforeach
                                </select>
                                @error('durasiHari') <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                            @elseif ($periodeSewa === 'mingguan')
                                <select wire:model="durasiMinggu"
                                    class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm text-gray-700">
                                    @foreach (range(1, 12) as $minggu)
                                        <option value="{{ $minggu }}">{{ $minggu }} minggu</option>
                                    @endforeach
                                </select>
                                @error('durasiMinggu') <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                            @else
                                <select wire:model="durasiBulan"
                                    class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm text-gray-700">
                                    @foreach (range(1, 12) as $bulan)
                                        <option value="{{ $bulan }}">{{ $bulan }} bulan</option>
                                    @endforeach
                                </select>
                                @error('durasiBulan') <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                            @endif
                        </div>
                        <div class="mt-4">
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Foto KTP <span class="text-rose-500">*</span></label>
                            <input type="file" wire:model="ktp" accept=".jpg,.jpeg,.png,.webp,.pdf"
                                class="w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-teal-50 dark:file:bg-teal-500/10 file:px-4 file:py-2 file:text-teal-700 dark:file:text-teal-300 file:font-semibold hover:file:bg-teal-100 dark:hover:file:bg-teal-500/20">
                            <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">Wajib. JPG/PNG/WEBP/PDF, maks 2MB. Data hanya untuk verifikasi pemilik.</p>
                            @error('ktp') <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                            <div wire:loading wire:target="ktp" class="mt-2 flex items-center gap-1.5 text-xs font-medium text-teal-600">
                                <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Mengunggah KTP...
                            </div>
                        </div>
                        <div class="flex flex-col-reverse sm:flex-row gap-2 pt-3">
                            <button type="button" wire:click="tutupModalSewa" wire:loading.attr="disabled"
                                class="flex-1 inline-flex items-center justify-center rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Batal
                            </button>
                            <button type="button" wire:click="lanjutReview" wire:loading.attr="disabled" wire:target="lanjutReview"
                                class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-500 transition disabled:opacity-50">
                                Lanjut ke Review
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Step 2: Review ringkasan --}}
                    <div wire:key="langkah-2" @if ($langkahSewa !== 2) class="hidden" @endif>
                        <div class="rounded-xl ring-1 ring-gray-100 dark:ring-gray-700 bg-gray-50 dark:bg-gray-700/30 divide-y divide-gray-100 dark:divide-gray-600 overflow-hidden">
                            @php
                                $harga = $kamarModal?->hargaUntuk($periodeSewa) ?? $kamarModal?->harga_sewa_bulanan ?? 0;
                                $durasi = $periodeSewa === 'harian' ? $durasiHari : ($periodeSewa === 'mingguan' ? $durasiMinggu : $durasiBulan);
                                $periode = $periodeSewa === 'harian' ? 'hari' : ($periodeSewa === 'mingguan' ? 'minggu' : 'bulan');
                            @endphp
                            <div class="flex justify-between gap-2 px-4 py-2.5 text-xs">
                                <span class="text-gray-500 dark:text-gray-400">Kos</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100 text-end">{{ $properti->nama }}</span>
                            </div>
                            <div class="flex justify-between gap-2 px-4 py-2.5 text-xs">
                                <span class="text-gray-500 dark:text-gray-400">Kamar</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100 text-end">{{ $kamarModal?->nama }} &middot; {{ $kamarModal?->kapasitas }} org</span>
                            </div>
                            <div class="flex justify-between gap-2 px-4 py-2.5 text-xs">
                                <span class="text-gray-500 dark:text-gray-400">Harga Sewa</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100 text-end">Rp{{ number_format($harga, 0, ',', '.') }}/{{ $periode }}</span>
                            </div>
                            <div class="flex justify-between gap-2 px-4 py-2.5 text-xs">
                                <span class="text-gray-500 dark:text-gray-400">Tanggal Masuk</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100 text-end">{{ Carbon::parse($tanggalMasuk)->locale('id')->translatedFormat('d F Y') }}</span>
                            </div>
                            <div class="flex justify-between gap-2 px-4 py-2.5 text-xs">
                                <span class="text-gray-500 dark:text-gray-400">Lama Sewa</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100 text-end">{{ $durasi }} {{ $periode }}</span>
                            </div>
                            <div class="flex justify-between gap-2 px-4 py-2.5 text-xs">
                                <span class="text-gray-500 dark:text-gray-400">Estimasi Total Tagihan</span>
                                <span class="font-semibold text-emerald-600 dark:text-emerald-400 text-end">Rp{{ number_format($harga * $durasi, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between gap-2 px-4 py-2.5 text-xs">
                                <span class="text-gray-500 dark:text-gray-400">Sistem Pembayaran</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100 text-end">Transfer</span>
                            </div>
                        </div>

                        <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">Dengan menekan tombol di bawah, kamar langsung terkunci untukmu. Kamu akan melihat tagihan sewa di dashboard.</p>

                        <div class="flex flex-col-reverse sm:flex-row gap-2 pt-3">
                            <button type="button" wire:click="kembaliKeTanggal" wire:loading.attr="disabled"
                                class="flex-1 inline-flex items-center justify-center rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Kembali
                            </button>
                            <button type="submit" wire:loading.attr="disabled" wire:target="konfirmasiSewa"
                                class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-500 transition disabled:opacity-50">
                                <span wire:loading.remove wire:target="konfirmasiSewa">Booking Sekarang</span>
                                <span wire:loading wire:target="konfirmasiSewa" class="inline-flex items-center gap-1.5">
                                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                                    Memproses...
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    window.initPetaDetail = window.initPetaDetail || (() => {
        const el = document.getElementById('peta-properti-detail');
        if (!el || el.dataset.ada === '1') return;

        const lat = parseFloat(el.dataset.lat);
        const lng = parseFloat(el.dataset.lng);
        if (Number.isNaN(lat) || Number.isNaN(lng)) return;

        el.dataset.ada = '1';

        const tautan = document.getElementById('peta-buka-osm');
        if (tautan) {
            tautan.href = 'https://www.google.com/maps/search/?api=1&query=' + lat + ',' + lng;
        }

        if (typeof google === 'undefined' || !google.maps) {
            if (typeof window.pasangGoogleEmbed === 'function') {
                window.pasangGoogleEmbed(el, lat, lng, 16);
            } else {
                el.innerHTML = '<div class="h-full w-full flex items-center justify-center p-4 text-center text-xs text-gray-400">' +
                    'Peta belum dikonfigurasi. Tambahkan GOOGLE_MAPS_API_KEY.</div>';
            }
            return;
        }

        const map = new google.maps.Map(el, {
            center: { lat, lng },
            zoom: 16,
            mapTypeId: 'roadmap',
        });

        const pemuat = new google.maps.Marker({ position: { lat, lng }, map, title: el.dataset.nama || '' });

        const info = new google.maps.InfoWindow();
        const isi = '<div><strong>' + String(el.dataset.nama || '').replace(/</g, '&lt;') + '</strong>' +
            (el.dataset.alamat ? '<br>' + String(el.dataset.alamat).replace(/</g, '&lt;') : '') + '</div>';
        info.setContent(isi);
        info.open({ map, anchor: pemuat });
    });

    (() => {
        const init = () => setTimeout(window.initPetaDetail, 0);
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
        document.addEventListener('livewire:navigated', init);
        window.loadNgekosMaps(window.initPetaDetail);
    })();
</script>
@endpush
