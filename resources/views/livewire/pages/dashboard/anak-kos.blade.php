<?php

use App\Models\ChatPesan;
use App\Models\Pembayaran;
use App\Models\Penyewaan;
use App\Models\Properti;
use App\Models\Tagihan;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public ?string $pesan = null;

    public ?string $galat = null;

    public ?int $modalBayarId = null;

    public string $metodeBayar = 'transfer';

    public $bukti = null;

    public $ktpSusulan = null;

    public ?int $modalKtpId = null;

    public string $emailTeman = '';

    public $ktpTeman = null;

    public ?int $modalTemanId = null;

    public function with(): array
    {
        $id = auth()->id();

        $scopeSewa = fn ($q) => $q->where('anak_kos_id', $id)
            ->orWhereHas('anggotas', fn ($w) => $w->where('user_id', $id)->where('status', 'aktif'));

        $kotaAktif = Penyewaan::where('anak_kos_id', $id)
            ->where('status', 'aktif')
            ->with('properti:id,kota')
            ->first()?->properti?->kota;

        $propertiTerpakai = Penyewaan::where('anak_kos_id', $id)
            ->where('status', 'aktif')
            ->pluck('properti_id');

        $idFavorit = auth()->user()->favorits()->pluck('propertis.id');

        $kandidatBerikutnya = Tagihan::where('status', '!=', 'lunas')
            ->whereHas('penyewaan', $scopeSewa)
            ->select(['id', 'penyewaan_id', 'periode', 'jumlah', 'denda', 'jatuh_tempo', 'status'])
            ->with(['penyewaan.kamar.properti:id,nama', 'penyewaan.kamar:id,nama,properti_id', 'penyewaan.anggotas', 'penyewaan.properti:id,denda_per_hari', 'pembayarans:id,tagihan_id,anak_kos_id,jumlah,status'])
            ->orderBy('jatuh_tempo')
            ->limit(10)
            ->get();

        $dendaGlobal = (float) \App\Models\Pengaturan::dendaPerHari();
        $olesDenda = function ($tagihan) use ($dendaGlobal) {
            if ($tagihan->status === 'lunas') {
                return;
            }
            $perHari = (float) ($tagihan->penyewaan?->properti?->denda_per_hari ?? $dendaGlobal);
            $tagihan->setAttribute('denda', \App\Services\TagihanService::dendaBerjalan($tagihan));
        };

        $kandidatBerikutnya->each($olesDenda);

        // Banner harus menunjuk ke tagihan yang porsi user-nya masih > 0.
        // Kalau porsi user sudah lunas tapi teman belum bayar, tagihan itu dilewati
        // agar tidak dikira "masih ada tagihan" / nominal dobel.
        $tagihanBerikutnya = $kandidatBerikutnya->first(fn ($t) => \App\Services\TagihanService::wajibBayar($t, $id) > 0)
            ?? $kandidatBerikutnya->first();
        $wajibBerikutnya = $tagihanBerikutnya ? \App\Services\TagihanService::wajibBayar($tagihanBerikutnya, $id) : 0;

        // Blok tab "Sewa & tagihanku" dihapus dari dashboard (semua pindah ke menu
        // Tagihan). Koleksi dikembalikan kosong agar variabel blade tetap terdefinisi;
        // modal bayar memakai fallback query langsung bila dibutuhkan.
        $tagihans = collect();

        return [
            'tagihans' => $tagihans,
            'pembayarans' => collect(),
            'sewaans' => collect(),
            'tagihanBerikutnya' => $tagihanBerikutnya,
            'wajibBerikutnya' => $wajibBerikutnya,
            'rekomendasi' => (function () use ($id, $propertiTerpakai, $idFavorit, $kotaAktif) {
                $ids = cache()->remember("anak-kos.rekomendasi.{$id}", 600, fn () => Properti::query()
                    ->where('status', 'aktif')
                    ->whereNotIn('id', $propertiTerpakai->all())
                    ->whereNotIn('id', $idFavorit->all())
                    ->when($kotaAktif, fn ($q) => $q->where('kota', $kotaAktif))
                    ->withCount([
                        'kamars as total_kamar',
                        'kamars as kamar_terisi' => fn ($q) => $q->where('status', 'terisi'),
                    ])
                    ->orderByDesc('kamar_terisi')
                    ->limit(12)
                    ->pluck('id')
                    ->all());

                if ($ids === []) {
                    return collect();
                }

                return Properti::query()
                    ->whereIn('id', $ids)
                    ->select(['id', 'nama', 'kota', 'alamat', 'foto', 'fasilitas', 'harga', 'harga_asli', 'tipe_hunian'])
                    ->with('fotos:id,properti_id,path,urutan')
                    ->withCount([
                        'kamars as total_kamar',
                        'kamars as kamar_terisi' => fn ($q) => $q->where('status', 'terisi'),
                        'kamars as kamar_tersedia' => fn ($q) => $q->where('status', 'tersedia'),
                        'ulasans as total_ulasan',
                    ])
                    ->withAvg('ulasans as rating_ulasan', 'rating')
                    ->withMin(['kamars as harga_termurah' => fn ($q) => $q->where('status', 'tersedia')->where('harga_sewa_bulanan', '>', 0)], 'harga_sewa_bulanan')
                    ->get()
                    ->sortBy(fn ($p) => array_search($p->id, $ids))
                    ->filter(fn ($p) => $p->total_kamar > $p->kamar_terisi)
                    ->values();
            })(),
            'kosPromo' => (function () use ($propertiTerpakai, $idFavorit) {
                return Properti::query()
                    ->where('status', 'aktif')
                    ->whereNotNull('harga_asli')
                    ->whereColumn('harga_asli', '>', 'harga')
                    ->whereNotIn('id', $propertiTerpakai->all())
                    ->whereNotIn('id', $idFavorit->all())
                    ->select(['id', 'nama', 'kota', 'alamat', 'foto', 'fasilitas', 'harga', 'harga_asli', 'tipe_hunian'])
                    ->with('fotos:id,properti_id,path,urutan')
                    ->withCount([
                        'kamars as total_kamar',
                        'kamars as kamar_terisi' => fn ($q) => $q->where('status', 'terisi'),
                        'kamars as kamar_tersedia' => fn ($q) => $q->where('status', 'tersedia'),
                    ])
                    ->orderBy('harga')
                    ->limit(8)
                    ->get()
                    ->filter(fn ($p) => $p->total_kamar > $p->kamar_terisi)
                    ->values();
            })(),
        ];
    }

    public function checkOut(int $sewaanId): void
    {
        $this->pesan = null;
        $this->galat = null;
        $uid = auth()->id();
        $sewaan = Penyewaan::where('id', $sewaanId)
            ->where('status', 'aktif')
            ->where(fn ($q) => $q->where('anak_kos_id', $uid)
                ->orWhereHas('anggotas', fn ($w) => $w->where('user_id', $uid)->where('status', 'aktif')))
            ->with(['kamar', 'tagihans.pembayarans', 'anggotas'])
            ->first();

        if (! $sewaan) {
            $this->galat = 'Penyewaan tidak ditemukan.';

            return;
        }

        try {
            $hasil = \App\Services\CheckoutService::keluarPenghuni($sewaan, $uid);
        } catch (DomainException $e) {
            $this->galat = $e->getMessage();

            return;
        }

        if ($hasil['jenis'] === 'partial') {
            $this->pesan = 'Kamu sudah keluar dari kamar patungan. Porsi berikutnya menjadi tanggung jawab penghuni yang stay.';

            return;
        }

        $catatan = $hasil['tagihan_belum_lunas'] > 0
            ? " Perhatian: masih ada {$hasil['tagihan_belum_lunas']} tagihan belum lunas."
            : '';

        $this->pesan = "Check-out dari kamar {$sewaan->kamar?->nama} berhasil. Kamar kembali tersedia.{$catatan}";
    }

    public function bukaModalKtp(int $sewaanId): void
    {
        $this->modalKtpId = $sewaanId;
        $this->ktpSusulan = null;
        $this->resetValidation();
    }

    public function tutupModalKtp(): void
    {
        $this->modalKtpId = null;
        $this->ktpSusulan = null;
        $this->resetValidation();
    }

    public function simpanKtpSusulan(): void
    {
        $uid = auth()->id();

        $sewaan = Penyewaan::where('id', $this->modalKtpId)
            ->where('status', 'aktif')
            ->where(fn ($q) => $q->where('anak_kos_id', $uid)
                ->orWhereHas('anggotas', fn ($w) => $w->where('user_id', $uid)->where('status', 'aktif')))
            ->with('anggotas')
            ->first();

        if (! $sewaan) {
            $this->tutupModalKtp();
            $this->galat = 'Penyewaan tidak ditemukan.';

            return;
        }

        $this->validate([
            'ktpSusulan' => \App\Services\PenyewaanService::ATURAN_KTP,
        ], \App\Services\PenyewaanService::pesanKtp());

        $path = \App\Services\KtpStorage::simpan($this->ktpSusulan);

        if ($sewaan->anak_kos_id === $uid) {
            \App\Services\KtpStorage::hapus($sewaan->ktp_path);
            $sewaan->update(['ktp_path' => $path]);
        } else {
            $anggota = $sewaan->anggotas()->where('user_id', $uid)->where('status', 'aktif')->first();

            if (! $anggota) {
                \App\Services\KtpStorage::hapus($path);
                $this->tutupModalKtp();
                $this->galat = 'Kamu bukan penghuni aktif kamar ini.';

                return;
            }

            \App\Services\KtpStorage::hapus($anggota->ktp_path);
            $anggota->update(['ktp_path' => $path]);
        }

        $this->tutupModalKtp();
        $this->pesan = 'Foto KTP berhasil dilengkapi. Terima kasih.';
    }

    public function bukaModalTeman(int $sewaanId): void
    {
        $this->modalTemanId = $sewaanId;
        $this->emailTeman = '';
        $this->ktpTeman = null;
        $this->resetValidation();
    }

    public function tutupModalTeman(): void
    {
        $this->modalTemanId = null;
        $this->emailTeman = '';
        $this->ktpTeman = null;
        $this->resetValidation();
    }

    public function simpanTeman(): void
    {
        $this->pesan = null;
        $this->galat = null;
        $sewaan = Penyewaan::where('id', $this->modalTemanId)
            ->where('status', 'aktif')
            ->where('anak_kos_id', auth()->id())
            ->with(['kamar', 'anggotas'])
            ->first();

        if (! $sewaan) {
            $this->tutupModalTeman();
            $this->galat = 'Penyewaan tidak ditemukan.';

            return;
        }

        $this->validate([
            'emailTeman' => ['required', 'email', 'exists:users,email'],
            'ktpTeman' => \App\Services\PenyewaanService::ATURAN_KTP,
        ], array_merge(\App\Services\PenyewaanService::pesanKtp(), [
            'emailTeman.required' => 'Email teman wajib diisi.',
            'emailTeman.email' => 'Format email tidak valid.',
            'emailTeman.exists' => 'Akun teman tidak ditemukan. Minta temanmu daftar dulu.',
        ]));

        $teman = \App\Models\User::where('email', $this->emailTeman)->first();

        if (! $teman?->hasRole('anak_kos')) {
            $this->addError('emailTeman', 'Hanya akun anak kos yang bisa jadi teman sekamar.');

            return;
        }

        $ktpPath = \App\Services\KtpStorage::simpan($this->ktpTeman);

        try {
            \App\Services\PatunganService::tambahAnggota($sewaan, $teman, $ktpPath);
        } catch (DomainException $e) {
            \App\Services\KtpStorage::hapus($ktpPath);
            $this->addError('emailTeman', $e->getMessage());

            return;
        }

        $this->tutupModalTeman();
        $this->pesan = "Teman sekamar {$teman->nama} berhasil ditambahkan (patungan 50/50).";
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

        $rincian = \App\Services\TagihanService::rincian($tagihan);
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
        <x-dashboard-greeting
            roleLabel="Anak Kos"
            description="Tagihan dan riwayat bayarmu ada di menu Tagihan. Scroll ke bawah buat cari kos."
            icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>'
        />

        <div class="rounded-2xl bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-gray-700 p-3 sm:p-4 border-t-4 !border-t-brand-700">
            <p class="text-sm font-bold text-gray-900 dark:text-gray-100">Mau cari kos di mana?</p>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Ketik nama lokasi, area, atau alamat — misal "Tembalang" atau "Kukusan".</p>
            <form action="{{ route('kos.index') }}" method="GET" class="mt-3 flex items-center gap-2">
                <div class="flex-1 flex items-center gap-2 rounded-xl bg-gray-100 dark:bg-gray-700/60 px-3 py-2.5">
                    <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    <input type="text" name="cari" placeholder="Masukan nama lokasi/area/alamat"
                        class="w-full bg-transparent text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 border-0 focus:ring-0 focus:outline-none p-0">
                </div>
                <button type="submit" class="btn-primary shrink-0 !rounded-xl !px-6">Cari</button>
            </form>
            <div class="mt-3 flex gap-2 overflow-x-auto scrollbar-hide pb-1">
                @foreach (['UGM Jogja' => 'Pogung', 'UNDIP Semarang' => 'Tembalang', 'UI Depok' => 'Kukusan', 'UNPAD Jatinangor' => 'Jatinangor', 'UB Malang' => 'Lowokwaru', 'UNAIR Surabaya' => 'Bratang', 'ITB Bandung' => 'Dago', 'Udayana Bali' => 'Denpasar'] as $label => $kata)
                    <a href="{{ route('kos.index') }}?cari={{ urlencode($kata) }}"
                        class="shrink-0 whitespace-nowrap rounded-full bg-gray-100 dark:bg-gray-700 px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-brand-700 hover:text-white transition">{{ $label }}</a>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-gray-700 p-4">
            <p class="text-sm font-bold text-gray-900 dark:text-gray-100">Area kos terpopuler</p>
            <div class="mt-2.5 flex flex-wrap gap-2">
                @foreach (['Yogyakarta', 'Jakarta', 'Bandung', 'Surabaya', 'Malang', 'Semarang', 'Medan', 'Denpasar'] as $kotaPop)
                    <a href="{{ route('kos.index') }}?cari={{ urlencode($kotaPop) }}"
                        class="rounded-lg border border-gray-200 dark:border-gray-600 px-3 py-1.5 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:border-brand-600 hover:bg-brand-700 hover:text-white transition">Kos {{ $kotaPop }}</a>
                @endforeach
            </div>
        </div>

        @if ($pesan)
            <x-notifikasi-popup :pesan="$pesan" judul="Berhasil!" />
        @endif

        @if ($galat)
            <div class="flex items-center justify-between gap-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 ring-1 ring-rose-200 dark:ring-rose-500/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                <span>{{ $galat }}</span>
                <button wire:click="$set('galat', null)" class="text-rose-500 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 font-bold">&times;</button>
            </div>
        @endif

        @if ($tagihanBerikutnya)
            @php
                $sisaBanner = \App\Services\TagihanService::selisihHari($tagihanBerikutnya);
                $telatBanner = \App\Services\TagihanService::hariTelat($tagihanBerikutnya);
                $dendaHarianBanner = \App\Services\TagihanService::dendaPerHari($tagihanBerikutnya);
                $isPatunganBanner = (bool) $tagihanBerikutnya->penyewaan?->isPatungan();
                $nominalBanner = ($wajibBerikutnya ?? 0) > 0 ? ($wajibBerikutnya ?? 0) : ($tagihanBerikutnya->jumlah + $tagihanBerikutnya->denda);
                // Fallback bila variable lama tidak terisi (mis. cache view): hitung ulang.
                if (! isset($wajibBerikutnya)) {
                    $wajibBerikutnya = \App\Services\TagihanService::wajibBayar($tagihanBerikutnya, auth()->id());
                    $nominalBanner = $wajibBerikutnya > 0 ? $wajibBerikutnya : ($tagihanBerikutnya->jumlah + $tagihanBerikutnya->denda);
                }
            @endphp
            <div class="rounded-xl p-5 text-white {{ $telatBanner > 0 || $sisaBanner <= 3 ? 'bg-red-800 dark:bg-red-900' : ($sisaBanner <= 7 ? 'bg-amber-800 dark:bg-amber-900' : 'bg-brand-900 dark:bg-brand-950') }}">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <span class="shrink-0 h-11 w-11 rounded-lg bg-white/10 text-white flex items-center justify-center border border-white/15">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </span>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-white/80">Tagihan Berikutnya</p>
                            <p class="text-sm font-bold text-white mt-0.5">
                                {{ $tagihanBerikutnya->periode }} &middot; {{ $tagihanBerikutnya->penyewaan?->kamar?->properti?->nama }}
                                {{ $tagihanBerikutnya->penyewaan?->kamar ? '- Kamar ' . $tagihanBerikutnya->penyewaan->kamar->nama : '' }}
                            </p>
                            <p class="text-xs text-white/90 mt-0.5">
                                Jatuh tempo {{ $tagihanBerikutnya->jatuh_tempo?->translatedFormat('d F Y') ?? '-' }}
                            </p>
                            <p class="mt-1.5 inline-flex items-center rounded-full bg-white/20 px-2.5 py-1 text-[11px] font-bold backdrop-blur">
                                @if ($telatBanner > 0)
                                    Terlambat {{ $telatBanner }} hari{{ $dendaHarianBanner > 0 ? ' — denda Rp'.number_format($dendaHarianBanner, 0, ',', '.').'/hari' : '' }}
                                @elseif ($sisaBanner === 0)
                                    Jatuh tempo hari ini — bayar sebelum lewat hari ini
                                @else
                                    Sisa {{ $sisaBanner }} hari (bayar sebelum {{ $tagihanBerikutnya->jatuh_tempo?->translatedFormat('d M Y') }})
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="shrink-0 text-end">
                        <p class="text-2xl font-bold tracking-tight text-white">Rp{{ number_format($nominalBanner, 0, ',', '.') }}</p>
                        @if ($isPatunganBanner)
                            <p class="text-[11px] text-white/80">Porsimu (50%) · total penuh Rp{{ number_format($tagihanBerikutnya->jumlah + $tagihanBerikutnya->denda, 0, ',', '.') }}</p>
                        @endif
                        @if (($wajibBerikutnya ?? 0) > 0)
                            <button wire:click="bayarTagihan({{ $tagihanBerikutnya->id }})" wire:loading.attr="disabled"
                                class="mt-1.5 inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-slate-900 hover:bg-stone-100 transition">
                                Bayar Sekarang
                            </button>
                        @else
                            <p class="mt-1.5 inline-flex items-center rounded-full bg-emerald-400/20 px-2.5 py-1 text-[11px] font-bold">Porsimu lunas · menunggu teman sekamar</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <x-kos-trending />

        @if ($rekomendasi->isNotEmpty())
            <div>
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-extrabold text-gray-900 dark:text-gray-100">Rekomendasi kos buat kamu</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kos lain di area yang sedang kamu tempati — harga bulan pertama udah termasuk diskon</p>
                    </div>
                    <a href="{{ route('kos.index') }}" wire:navigate
                        class="shrink-0 inline-flex items-center gap-0.5 text-sm font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                        Lihat Semua
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                    </a>
                </div>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ($rekomendasi as $k)
                        @php
                            $sisaK = $k->total_kamar - $k->kamar_terisi;
                            $hargaK = $k->harga ?? $k->harga_termurah;
                            $diskonK = $k->harga_asli && $hargaK && $k->harga_asli > $hargaK ? $k->harga_asli - $hargaK : 0;
                            $fasilitasK = array_filter(array_map('trim', explode('·', str_replace(',', '·', $k->fasilitas ?? ''))));
                        @endphp
                        <a href="{{ route('kos.detail', $k) }}" wire:navigate
                            class="group rounded-xl bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 overflow-hidden hover:shadow-card-hover transition">
                            <div class="relative h-28 bg-stone-200 dark:bg-gray-800 overflow-hidden">
                                @php $coverRekom = $k->fotoCover(); @endphp
                                @if ($coverRekom)
                                    <img src="{{ $coverRekom }}" alt="{{ $k->nama }}" loading="lazy" decoding="async" class="h-full w-full object-cover">
                                @else
                                    <div class="h-full w-full flex items-center justify-center">
                                        <svg class="h-8 w-8 text-brand-700 dark:text-brand-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                                    </div>
                                @endif
                                @if ($k->tipe_hunian)
                                    <span class="absolute top-2 left-2 rounded px-1.5 py-0.5 text-[10px] font-bold text-white {{ $k->tipe_hunian === 'putri' ? 'bg-pink-600' : ($k->tipe_hunian === 'putra' ? 'bg-sky-700' : 'bg-violet-700') }}">{{ ucfirst($k->tipe_hunian) }}</span>
                                @endif
                                <span class="absolute top-2 right-2 rounded-full px-2 py-1 text-[10px] font-bold text-white {{ $sisaK > 0 ? 'bg-emerald-600' : 'bg-gray-800' }}">
                                    {{ $sisaK > 0 ? 'Sisa ' . $sisaK . ' kamar' : 'Penuh' }}
                                </span>
                            </div>
                            <div class="p-3">
                                @if (($k->total_ulasan ?? 0) > 0)
                                    <p class="flex items-center gap-1 text-[11px] font-semibold text-gray-600 dark:text-gray-300">
                                        <svg class="h-3 w-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 1.5l2.6 5.3 5.9.9-4.2 4.1 1 5.8L10 14.9l-5.3 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z" /></svg>
                                        {{ number_format($k->rating_ulasan, 1, ',', '.') }}
                                    </p>
                                @endif
                                <p class="mt-0.5 text-sm font-bold text-gray-900 dark:text-gray-100 truncate group-hover:text-brand-700 dark:group-hover:text-brand-300 transition">{{ $k->nama }}</p>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 truncate">{{ $k->kota }}{{ $k->alamat ? ', ' . $k->alamat : '' }}</p>
                                @if ($fasilitasK !== [])
                                    <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500 truncate">{{ implode('·', array_slice($fasilitasK, 0, 5)) }}</p>
                                @endif
                                @if ($hargaK)
                                    <div class="mt-1.5">
                                        @if ($diskonK > 0)
                                            <p class="text-[11px] text-gray-400">Diskon {{ number_format($diskonK / 1000, 0) }}rb <span class="line-through">Rp{{ number_format($k->harga_asli, 0, ',', '.') }}</span></p>
                                        @endif
                                        <p class="text-sm font-extrabold text-gray-900 dark:text-gray-100">Rp{{ number_format($hargaK, 0, ',', '.') }} <span class="text-[10px] font-medium text-gray-400">(Bulan pertama)</span></p>
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if (($kosPromo ?? collect())->isNotEmpty())
            <div>
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-extrabold text-gray-900 dark:text-gray-100">Kos yang lagi promo</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Diskon bulan pertama, lumayan buat hemat awal ngekos</p>
                    </div>
                    <a href="{{ route('kos.index') }}" wire:navigate
                        class="shrink-0 inline-flex items-center gap-0.5 text-sm font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                        Lihat Semua
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                    </a>
                </div>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ($kosPromo as $kp)
                        @php
                            $sisaP = $kp->total_kamar - $kp->kamar_terisi;
                            $fasilitasP = array_filter(array_map('trim', explode('·', str_replace(',', '·', $kp->fasilitas ?? ''))));
                        @endphp
                        <a href="{{ route('kos.detail', $kp) }}" wire:navigate
                            class="group rounded-2xl bg-white dark:bg-gray-800 ring-1 ring-gray-100 dark:ring-gray-700 shadow-sm overflow-hidden hover:shadow-md transition">
                            <div class="relative h-28 bg-gradient-to-br from-amber-50 to-orange-100 dark:from-amber-500/10 dark:to-orange-500/10 overflow-hidden">
                                @php $coverPromo = $kp->fotoCover(); @endphp
                                @if ($coverPromo)
                                    <img src="{{ $coverPromo }}" alt="{{ $kp->nama }}" loading="lazy" decoding="async" class="h-full w-full object-cover">
                                @else
                                    <div class="h-full w-full flex items-center justify-center">
                                        <svg class="h-8 w-8 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                                    </div>
                                @endif
                                @if ($kp->tipe_hunian)
                                    <span class="absolute top-2 left-2 rounded px-1.5 py-0.5 text-[10px] font-bold text-white {{ $kp->tipe_hunian === 'putri' ? 'bg-pink-600' : ($kp->tipe_hunian === 'putra' ? 'bg-sky-700' : 'bg-violet-700') }}">{{ ucfirst($kp->tipe_hunian) }}</span>
                                @endif
                                <span class="absolute {{ $kp->tipe_hunian ? 'top-9' : 'top-2' }} right-2 rounded-full px-2 py-1 text-[10px] font-bold text-white {{ $sisaP > 0 ? 'bg-emerald-600' : 'bg-gray-800' }}">{{ $sisaP > 0 ? 'Sisa ' . $sisaP . ' kamar' : 'Penuh' }}</span>
                            </div>
                            <div class="p-3">
                                <p class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate group-hover:text-brand-700 dark:group-hover:text-brand-300 transition">{{ $kp->nama }}</p>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 truncate">{{ $kp->kota }}{{ $kp->alamat ? ', ' . $kp->alamat : '' }}</p>
                                @if ($fasilitasP !== [])
                                    <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500 truncate">{{ implode('·', array_slice($fasilitasP, 0, 5)) }}</p>
                                @endif
                                @if ($kp->harga)
                                    <p class="mt-1.5 text-[11px] text-gray-400">Diskon {{ number_format(($kp->harga_asli - $kp->harga) / 1000, 0) }}rb <span class="line-through">Rp{{ number_format($kp->harga_asli, 0, ',', '.') }}</span></p>
                                    <p class="text-sm font-extrabold text-gray-900 dark:text-gray-100">Rp{{ number_format($kp->harga, 0, ',', '.') }} <span class="text-[10px] font-medium text-gray-400">(Bulan pertama)</span></p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

    @if ($modalBayarId)
    @php
        $tagihanModal = $tagihans->firstWhere('id', $modalBayarId)
            ?? ($tagihanBerikutnya?->id === $modalBayarId ? $tagihanBerikutnya : null);
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
                        @if ($dendaHarianModal > 0)
                            <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">Jika lewat jatuh tempo, denda Rp{{ number_format($dendaHarianModal, 0, ',', '.') }} per hari otomatis ditambahkan.</p>
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
                        @if ($isPatunganModal)
                            <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Total tagihan penuh</span><span class="text-gray-500 dark:text-gray-400">Rp{{ number_format($totalTagihan, 0, ',', '.') }}</span></div>
                        @endif
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

                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        @if ($metodeBayar === 'cash')
                            Kamu memilih pembayaran <strong class="text-gray-600 dark:text-gray-300">tunai</strong>. Bayarkan langsung total di atas kepada admin/pemilik kos; mereka akan memverifikasi bahwa pembayaran sudah diterima.
                        @else
                            Transfer tepat sesuai jumlah tagihan di atas, lalu unggah bukti transfer. Admin akan memverifikasi pembayaranmu.
                        @endif
                    </p>

                    @if ($metodeBayar === 'transfer')
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Bukti Transfer (JPG/PNG/WEBP/PDF, maks 2MB)</label>
                            <input type="file" wire:model="bukti" accept=".jpg,.jpeg,.png,.webp,.pdf"
                                class="w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 dark:file:bg-brand-500/10 file:px-4 file:py-2 file:text-brand-700 dark:file:text-brand-300 file:font-semibold hover:file:bg-brand-100 dark:hover:file:bg-brand-500/20">
                            @error('bukti') <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                            <div wire:loading wire:target="bukti" class="mt-2 flex items-center gap-1.5 text-xs font-medium text-brand-600">
                                <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Mengunggah bukti...
                            </div>
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

    @if ($modalKtpId)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
        <button type="button" wire:click="tutupModalKtp" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm cursor-default" tabindex="-1" aria-label="Tutup"></button>
        <div class="relative min-h-full flex items-end sm:items-center justify-center p-4">
            <div class="w-full sm:max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-xl ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <p class="text-sm font-bold text-gray-900 dark:text-gray-100">Lengkapi Foto KTP</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Wajib untuk validasi sewa. JPG/PNG/WEBP/PDF, maks 2MB.</p>
                </div>
                <form wire:submit="simpanKtpSusulan" class="p-5 space-y-4">
                    <div>
                        <input type="file" wire:model="ktpSusulan" accept=".jpg,.jpeg,.png,.webp,.pdf"
                            class="w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 dark:file:bg-brand-500/10 file:px-4 file:py-2 file:text-brand-700 dark:file:text-brand-300 file:font-semibold hover:file:bg-brand-100 dark:hover:file:bg-brand-500/20">
                        @error('ktpSusulan') <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                        <div wire:loading wire:target="ktpSusulan" class="mt-2 text-xs font-medium text-brand-600">Mengunggah KTP...</div>
                    </div>
                    <div class="flex flex-col-reverse sm:flex-row gap-2 pt-1">
                        <button type="button" wire:click="tutupModalKtp"
                            class="flex-1 inline-flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="simpanKtpSusulan"
                            class="flex-1 inline-flex items-center justify-center rounded-lg bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 transition disabled:opacity-50">
                            Simpan KTP
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @if ($modalTemanId)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
        <button type="button" wire:click="tutupModalTeman" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm cursor-default" tabindex="-1" aria-label="Tutup"></button>
        <div class="relative min-h-full flex items-end sm:items-center justify-center p-4">
            <div class="w-full sm:max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-xl ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <p class="text-sm font-bold text-gray-900 dark:text-gray-100">Tambah Teman Sekamar</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Patungan 50/50. Teman harus sudah punya akun anak kos.</p>
                </div>
                <form wire:submit="simpanTeman" class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Email Teman</label>
                        <input type="email" wire:model="emailTeman" placeholder="teman@email.com"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm">
                        @error('emailTeman') <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Foto KTP Teman (wajib)</label>
                        <input type="file" wire:model="ktpTeman" accept=".jpg,.jpeg,.png,.webp,.pdf"
                            class="w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 dark:file:bg-brand-500/10 file:px-4 file:py-2 file:text-brand-700 dark:file:text-brand-300 file:font-semibold hover:file:bg-brand-100 dark:hover:file:bg-brand-500/20">
                        @error('ktpTeman') <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex flex-col-reverse sm:flex-row gap-2 pt-1">
                        <button type="button" wire:click="tutupModalTeman"
                            class="flex-1 inline-flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="simpanTeman"
                            class="flex-1 inline-flex items-center justify-center rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-sky-500 transition disabled:opacity-50">
                            Tambah
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
