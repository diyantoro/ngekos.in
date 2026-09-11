<?php

use App\Models\ChatPesan;
use App\Models\Pembayaran;
use App\Models\Penyewaan;
use App\Models\Properti;
use App\Models\Tagihan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public string $tab = 'sewaan';

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

        $sewaanAktif = Penyewaan::where('anak_kos_id', $id)
            ->where('status', 'aktif')
            ->with('properti')
            ->get();

        $kotaAktif = $sewaanAktif->first()?->properti?->kota;

        $propertiTerpakai = Penyewaan::where('anak_kos_id', $id)
            ->where('status', 'aktif')
            ->pluck('properti_id');

        $idFavorit = auth()->user()->favorits()->pluck('propertis.id');

        $tagihans = Tagihan::whereHas('penyewaan', $scopeSewa)
            ->with(['penyewaan.kamar', 'penyewaan.properti', 'penyewaan.anggotas', 'pembayarans'])
            ->orderByDesc('jatuh_tempo')
            ->get();

        foreach ($tagihans->where('status', '!=', 'lunas') as $tagihan) {
            \App\Services\TagihanService::sinkronDenda($tagihan);
        }

        $tagihanBerikutnya = Tagihan::where('status', '!=', 'lunas')
            ->whereHas('penyewaan', $scopeSewa)
            ->with(['penyewaan.kamar.properti'])
            ->orderBy('jatuh_tempo')
            ->first();

        if ($tagihanBerikutnya) {
            \App\Services\TagihanService::sinkronDenda($tagihanBerikutnya);
        }

        return [
            'penyewaanAktif' => Penyewaan::where('anak_kos_id', $id)->where('status', 'aktif')->count()
                + \App\Models\PenyewaanAnggota::where('user_id', $id)->where('status', 'aktif')->count(),
            'tagihanBelumBayar' => $tagihans->where('status', '!=', 'lunas')->count(),
            'totalBayar' => (int) Pembayaran::where('anak_kos_id', $id)->where('status', 'diverifikasi')->sum('jumlah'),
            'tagihans' => $tagihans,
            'pembayarans' => Pembayaran::where('anak_kos_id', $id)
                ->with(['tagihan', 'verifikator'])
                ->latest()
                ->get(),
            'sewaans' => Penyewaan::where($scopeSewa)
                ->with(['kamar.properti', 'kamar', 'anakKos', 'anggotas.user', 'tagihans.pembayarans'])
                ->orderByDesc('status')
                ->latest()
                ->get(),
            'jumlahFavorit' => $idFavorit->count(),
            'pesanBelumDibaca' => auth()->user()->pesanBelumDibaca(),
            'tagihanBerikutnya' => $tagihanBerikutnya,
            'rekomendasi' => Properti::query()
                ->where('status', 'aktif')
                ->whereNotIn('id', $propertiTerpakai)
                ->whereNotIn('id', $idFavorit)
                ->when($kotaAktif, fn ($q) => $q->where('kota', $kotaAktif))
                ->with('fotos')
                ->withCount([
                    'kamars as total_kamar',
                    'kamars as kamar_terisi' => fn ($q) => $q->where('status', 'terisi'),
                ])
                ->orderByDesc('kamar_terisi')
                ->limit(4)
                ->get()
                ->filter(fn ($p) => $p->total_kamar > $p->kamar_terisi),
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
            $buktiPath = $this->bukti->store('bukti-pembayaran', 'public');
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
            description="Pantau penyewaan, tagihan, dan riwayat pembayaranmu di sini."
            icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>'
        />

        <x-promo-ads />

        @if ($pesan)
            <x-notifikasi-popup :pesan="$pesan" judul="Berhasil!" />
        @endif

        @if ($galat)
            <div class="flex items-center justify-between gap-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 ring-1 ring-rose-200 dark:ring-rose-500/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                <span>{{ $galat }}</span>
                <button wire:click="$set('galat', null)" class="text-rose-500 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 font-bold">&times;</button>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-stat-card label="Penyewaan Aktif" :value="$penyewaanAktif" tone="emerald"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />
            <x-stat-card label="Tagihan Belum Bayar" :value="$tagihanBelumBayar" tone="amber"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />
            <x-stat-card label="Total Sudah Dibayar" :value="'Rp' . number_format($totalBayar, 0, ',', '.')" tone="cyan"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>' />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-stat-card label="Favorit" :value="$jumlahFavorit" tone="rose"
                href="{{ route('favorit') }}"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>' />
            <x-stat-card label="Pesan Belum Dibaca" :value="$pesanBelumDibaca" tone="sky"
                href="{{ route('chat.index') }}"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.13.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" /></svg>' />
        </div>

        @if ($tagihanBerikutnya)
            <div class="rounded-2xl bg-gradient-to-r from-teal-600 to-emerald-600 dark:from-teal-700 dark:to-emerald-700 p-5 text-white shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <span class="shrink-0 h-11 w-11 rounded-2xl bg-white/15 text-white flex items-center justify-center">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </span>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-teal-100">Tagihan Berikutnya</p>
                            <p class="text-sm font-bold text-white mt-0.5">
                                {{ $tagihanBerikutnya->periode }} &middot; {{ $tagihanBerikutnya->penyewaan?->kamar?->properti?->nama }}
                                {{ $tagihanBerikutnya->penyewaan?->kamar ? '- Kamar ' . $tagihanBerikutnya->penyewaan->kamar->nama : '' }}
                            </p>
                            <p class="text-xs text-teal-100 mt-0.5">
                                Jatuh tempo {{ $tagihanBerikutnya->jatuh_tempo?->translatedFormat('d M Y') }}
                                @if ($tagihanBerikutnya->jatuh_tempo)
                                    &middot; {{ $tagihanBerikutnya->jatuh_tempo->isPast() ? 'terlambat ' . $tagihanBerikutnya->jatuh_tempo->diffForHumans() : 'sisa ' . $tagihanBerikutnya->jatuh_tempo->diffForHumans() }}
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="shrink-0 text-end">
                        <p class="text-2xl font-extrabold text-white">Rp{{ number_format($tagihanBerikutnya->jumlah + $tagihanBerikutnya->denda, 0, ',', '.') }}</p>
                        <button wire:click="bayarTagihan({{ $tagihanBerikutnya->id }})" wire:loading.attr="disabled"
                            class="mt-1.5 inline-flex items-center rounded-lg bg-white/15 px-3 py-1.5 text-xs font-semibold text-white backdrop-blur hover:bg-white/25 transition">
                            Bayar Sekarang
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <x-kos-trending />

        @if ($rekomendasi->isNotEmpty())
            <div>
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-extrabold text-gray-900 dark:text-gray-100">Rekomendasi untukmu</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kos lain di area yang sedang kamu tempati</p>
                    </div>
                    <a href="{{ route('kos.index') }}" wire:navigate
                        class="shrink-0 inline-flex items-center gap-0.5 text-sm font-semibold text-teal-600 hover:text-teal-700 dark:text-teal-400 dark:hover:text-teal-300">
                        Lihat Semua
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                    </a>
                </div>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ($rekomendasi as $k)
                        <a href="{{ route('kos.detail', $k) }}" wire:navigate
                            class="group rounded-2xl bg-white dark:bg-gray-800 ring-1 ring-gray-100 dark:ring-gray-700 shadow-sm overflow-hidden hover:shadow-md transition">
                            <div class="relative h-28 bg-gradient-to-br from-teal-50 to-cyan-100 dark:from-teal-500/10 dark:to-cyan-500/10 overflow-hidden">
                                @php $coverRekom = $k->fotoCover(); @endphp
                                @if ($coverRekom)
                                    <img src="{{ $coverRekom }}" alt="{{ $k->nama }}" class="h-full w-full object-cover">
                                @else
                                    <div class="h-full w-full flex items-center justify-center">
                                        <svg class="h-8 w-8 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                                    </div>
                                @endif
                                @if (count($k->galeriUrls()) > 1)
                                    <span class="absolute bottom-2 left-2 rounded-full bg-black/50 px-1.5 py-0.5 text-[9px] font-bold text-white backdrop-blur-sm">{{ count($k->galeriUrls()) }} foto</span>
                                @endif
                                <span class="absolute top-2 right-2 rounded-full px-2 py-1 text-[10px] font-bold text-white {{ $k->kamar_terisi < $k->total_kamar ? 'bg-emerald-600' : 'bg-gray-800' }}">
                                    {{ $k->kamar_terisi < $k->total_kamar ? ($k->total_kamar - $k->kamar_terisi) . ' Kamar' : 'Penuh' }}
                                </span>
                            </div>
                            <div class="p-3">
                                <p class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate group-hover:text-teal-700 dark:group-hover:text-teal-300 transition">{{ $k->nama }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $k->kota }}{{ $k->alamat ? ', ' . $k->alamat : '' }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
            <div class="px-4 sm:px-6 pt-4 pb-3 border-b border-gray-100 dark:border-gray-700">
                <div class="flex gap-2 overflow-x-auto scrollbar-hide pb-1 -mb-1">
                    <button wire:click="$set('tab', 'sewaan')"
                        class="flex-shrink-0 whitespace-nowrap px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'sewaan' ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                        Sewa Saya
                    </button>
                    <button wire:click="$set('tab', 'tagihan')"
                        class="flex-shrink-0 whitespace-nowrap px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'tagihan' ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                        Tagihan Saya
                    </button>
                    <button wire:click="$set('tab', 'pembayaran')"
                        class="flex-shrink-0 whitespace-nowrap px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'pembayaran' ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                        Pembayaran Saya
                    </button>
                </div>
            </div>

            <div class="p-4 sm:p-6">
                @if ($tab === 'sewaan')
                    <div class="space-y-4">
                        @forelse ($sewaans as $sewaan)
                            @php
                                $belumLunas = $sewaan->tagihans->where('status', '!=', 'lunas');
                                $sisa = $belumLunas->sum(fn ($t) => $t->jumlah + $t->denda);
                                $isUtama = $sewaan->anak_kos_id === auth()->id();
                                $ktpSaya = $isUtama ? $sewaan->ktp_path : $sewaan->anggotas->firstWhere('user_id', auth()->id())?->ktp_path;
                                $anggotaAktif = $sewaan->anggotas->where('status', 'aktif');
                                $isPatungan = ($sewaan->mode_hunian ?? 'tunggal') === 'patungan' || $anggotaAktif->isNotEmpty();
                                $bisaTambahTeman = $isUtama && $sewaan->status === 'aktif' && $anggotaAktif->count() < 1 && ($sewaan->kamar?->kapasitas ?? 1) >= 2;
                                $riwayatKeluar = $sewaan->anggotas->where('status', 'keluar')->sortByDesc('tanggal_keluar')->first();
                                $tampilBannerStay = $sewaan->status === 'aktif' && ! $isPatungan && $riwayatKeluar && $riwayatKeluar->tanggal_keluar && $riwayatKeluar->tanggal_keluar->diffInDays(now()) <= 30;
                            @endphp
                            <div class="rounded-xl ring-1 {{ $sewaan->status === 'aktif' ? 'ring-teal-100 dark:ring-teal-500/30' : 'ring-gray-100 dark:ring-gray-700 opacity-75' }} p-4 sm:p-5">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                            Kamar {{ $sewaan->kamar?->nama }} &middot; {{ $sewaan->kamar?->properti?->nama }}
                                            @if ($isPatungan)
                                                <x-status-badge status="patungan" />
                                            @endif
                                            @if (! $isUtama)
                                                <span class="ml-1 inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-[10px] font-bold text-gray-500 dark:text-gray-300">Anggota</span>
                                            @endif
                                        </p>
                                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                            Masuk: <span class="font-medium text-gray-700 dark:text-gray-200">{{ $sewaan->tanggal_masuk?->translatedFormat('d M Y') ?? '-' }}</span>
                                            @if ($sewaan->tanggal_keluar)
                                                &middot; Keluar: <span class="font-medium text-gray-700 dark:text-gray-200">{{ $sewaan->tanggal_keluar->translatedFormat('d M Y') }}</span>
                                            @endif
                                        </p>
                                        @if ($isPatungan)
                                            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                                <span class="inline-flex items-center gap-1 rounded-full bg-teal-50 dark:bg-teal-500/10 ring-1 ring-teal-200 dark:ring-teal-500/30 px-2.5 py-1 text-[11px] font-semibold text-teal-700 dark:text-teal-300">
                                                    <span class="h-4 w-4 rounded-full bg-teal-600 text-[9px] font-bold text-white flex items-center justify-center">{{ mb_substr($sewaan->anakKos?->nama ?? '?', 0, 1) }}</span>
                                                    {{ $sewaan->anakKos?->nama ?? '-' }} · utama
                                                </span>
                                                @foreach ($anggotaAktif as $ag)
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-sky-50 dark:bg-sky-500/10 ring-1 ring-sky-200 dark:ring-sky-500/30 px-2.5 py-1 text-[11px] font-semibold text-sky-700 dark:text-sky-300">
                                                        <span class="h-4 w-4 rounded-full bg-sky-600 text-[9px] font-bold text-white flex items-center justify-center">{{ mb_substr($ag->user?->nama ?? '?', 0, 1) }}</span>
                                                        {{ $ag->user?->nama ?? '-' }} · {{ (int) $ag->porsi_persen }}%
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <x-status-badge :status="$sewaan->status" />
                                    </div>
                                </div>

                                @if (! $isUtama && $sewaan->status === 'aktif')
                                    <div class="mt-3 flex items-start gap-2 rounded-xl bg-sky-50 dark:bg-sky-500/10 ring-1 ring-sky-200 dark:ring-sky-500/30 px-4 py-3">
                                        <p class="text-xs text-sky-800 dark:text-sky-200"><span class="font-bold">Kamu ditambahkan sebagai teman sekamar (patungan 50/50).</span> Porsimu 50% tiap tagihan — bayar lewat tab Tagihan Saya. Kabar ini juga masuk ke menu Pesan.</p>
                                    </div>
                                @endif

                                @if ($tampilBannerStay)
                                    <div class="mt-3 flex items-start gap-2 rounded-xl bg-teal-50 dark:bg-teal-500/10 ring-1 ring-teal-200 dark:ring-teal-500/30 px-4 py-3">
                                        <p class="text-xs text-teal-800 dark:text-teal-200"><span class="font-bold">{{ $riwayatKeluar->user?->nama ?? 'Teman sekamarmu' }} sudah keluar, kamu tetap stay.</span> Mulai tagihan berikutnya porsimu 100%. Kamar tetap terisi. Kabar ini juga masuk ke menu Pesan.</p>
                                    </div>
                                @endif

                                @if (! $ktpSaya && $sewaan->status === 'aktif')
                                    <div class="mt-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 rounded-xl bg-amber-50 dark:bg-amber-500/10 ring-1 ring-amber-200 dark:ring-amber-500/30 px-4 py-3">
                                        <p class="text-xs font-semibold text-amber-800 dark:text-amber-200">Foto KTP belum dilengkapi. Lengkapi agar sewa tetap valid.</p>
                                        <button wire:click="bukaModalKtp({{ $sewaan->id }})"
                                            class="shrink-0 inline-flex items-center rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-500 transition">
                                            Lengkapi KTP
                                        </button>
                                    </div>
                                @endif

                                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        @if ($belumLunas->isEmpty())
                                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">Semua tagihan lunas</span>
                                        @else
                                            <span class="font-semibold text-rose-600 dark:text-rose-400">Sisa tagihan Rp{{ number_format($sisa, 0, ',', '.') }}</span> ({{ $belumLunas->count() }} tagihan) &mdash; bayar lewat tab Tagihan Saya
                                        @endif
                                    </p>
                                    @if ($sewaan->status === 'aktif')
                                        <div class="flex flex-wrap items-center gap-2">
                                            @if ($bisaTambahTeman)
                                                <button wire:click="bukaModalTeman({{ $sewaan->id }})"
                                                    class="shrink-0 inline-flex items-center rounded-lg border border-sky-200 dark:border-sky-500/30 bg-sky-50 dark:bg-sky-500/10 px-4 py-2 text-xs font-semibold text-sky-700 dark:text-sky-300 hover:bg-sky-100 dark:hover:bg-sky-500/20 transition">
                                                    + Tambah Teman
                                                </button>
                                            @endif
                                            <button wire:click="checkOut({{ $sewaan->id }})" wire:loading.attr="disabled"
                                                wire:confirm="{{ $isPatungan ? 'Keluar dari kamar patungan? Porsimu harus sudah lunas.' : 'Check-out dari kamar ' . $sewaan->kamar?->nama . '? Kamar akan kembali tersedia.' }}"
                                                class="shrink-0 inline-flex items-center rounded-lg border border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/10 px-4 py-2 text-xs font-semibold text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-500/20 transition">
                                                {{ $isPatungan ? 'Keluar Patungan' : 'Check-out' }}
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-10 text-center">
                                <p class="text-sm text-gray-400 dark:text-gray-500">Belum ada penyewaan aktif.</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Cari kos di halaman <a href="{{ route('kos.index') }}" wire:navigate class="text-teal-600 dark:text-teal-400 hover:underline font-medium">Cari Kos</a> untuk mulai menyewa.</p>
                            </div>
                        @endforelse
                    </div>
                @elseif ($tab === 'tagihan')
                    <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Periode</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kamar</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jumlah</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jatuh Tempo</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse ($tagihans as $tagihan)
                                @php
                                    $isPatunganTagihan = ($tagihan->penyewaan?->mode_hunian ?? 'tunggal') === 'patungan';
                                    $porsiSaya = null;
                                    $sudahSaya = 0;
                                    if ($tagihan->penyewaan) {
                                        $porsiSaya = \App\Services\PatunganService::porsiTagihan($tagihan->penyewaan, $tagihan);
                                        $sudahSaya = (float) $tagihan->pembayarans->where('status', 'diverifikasi')->where('anak_kos_id', auth()->id())->sum('jumlah');
                                    }
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $tagihan->periode }}
                                        @if ($isPatunganTagihan)
                                            <span class="block text-[10px] font-bold text-sky-600 dark:text-sky-400">Patungan · porsimu Rp{{ number_format($porsiSaya ?? 0, 0, ',', '.') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $tagihan->penyewaan?->kamar?->nama ?? '-' }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        Rp{{ number_format($tagihan->jumlah + $tagihan->denda, 0, ',', '.') }}
                                        @if ($isPatunganTagihan)
                                            <span class="block text-[11px] text-gray-400">Sudah bayar: Rp{{ number_format($sudahSaya, 0, ',', '.') }} · Sisa porsi: Rp{{ number_format(max(0, ($porsiSaya ?? 0) - $sudahSaya), 0, ',', '.') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $tagihan->jatuh_tempo?->translatedFormat('d M Y') }}</td>
                                    <td class="px-4 py-4"><x-status-badge :status="$tagihan->status" /></td>
                                    <td class="px-4 py-4">
                                        @if ($tagihan->status !== 'lunas')
                                            <div class="flex justify-end">
                                                <button wire:click="bayarTagihan({{ $tagihan->id }})" wire:loading.attr="disabled"
                                                    class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500 transition">
                                                    Bayar Sekarang
                                                </button>
                                            </div>
                                        @else
                                            <span class="block text-right text-xs text-gray-400 dark:text-gray-500">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-400 dark:text-gray-500">Tidak ada tagihan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                @else
                    <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Periode</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jumlah</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Metode</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bukti</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Diverifikasi Oleh</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kwitansi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse ($pembayarans as $pembayaran)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $pembayaran->tagihan?->periode ?? '-' }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">Rp{{ number_format($pembayaran->jumlah, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $pembayaran->metode === 'cash' ? 'Tunai (Cash)' : 'Transfer' }}</td>
                                    <td class="px-4 py-4 text-sm">
                                        @if ($pembayaran->bukti)
                                            <a href="{{ Storage::url($pembayaran->bukti) }}" target="_blank" rel="noopener"
                                                class="inline-flex items-center gap-1 text-xs font-semibold text-teal-600 dark:text-teal-400 hover:text-teal-700 dark:hover:text-teal-300 hover:underline">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                                Lihat
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-400 dark:text-gray-500 italic">Tidak ada</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $pembayaran->verifikator?->nama ?? '-' }}</td>
                                    <td class="px-4 py-4"><x-status-badge :status="$pembayaran->status" /></td>
                                    <td class="px-4 py-4">
                                        @if ($pembayaran->status === 'diverifikasi')
                                            <div class="flex justify-end">
                                                <a href="{{ route('pembayaran.kwitansi', $pembayaran) }}" target="_blank" rel="noopener"
                                                    class="inline-flex items-center gap-1 rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-500 transition">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                                    {{ $pembayaran->nomor_kwitansi ?? 'Unduh' }}
                                                </a>
                                            </div>
                                        @else
                                            <span class="block text-right text-xs text-gray-400 dark:text-gray-500">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-10 text-center text-sm text-gray-400 dark:text-gray-500">Belum ada pembayaran.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if ($modalBayarId)
    @php
        $tagihanModal = $tagihans->firstWhere('id', $modalBayarId);
        $sewaModal = (float) ($tagihanModal?->jumlah ?? 0);
        $dendaModal = (float) ($tagihanModal?->denda ?? 0);
        $totalTagihan = $sewaModal + $dendaModal;
        $hariTelatModal = $tagihanModal ? \App\Services\TagihanService::hariTelat($tagihanModal) : 0;
        $dendaHarianModal = $tagihanModal ? \App\Services\TagihanService::dendaPerHari($tagihanModal) : 0;
        $porsiModal = $tagihanModal?->penyewaan ? \App\Services\PatunganService::porsiTagihan($tagihanModal->penyewaan, $tagihanModal) : $totalTagihan;
        $isPatunganModal = (bool) $tagihanModal?->penyewaan?->isPatungan();
        $wajibModal = $tagihanModal ? \App\Services\TagihanService::wajibBayar($tagihanModal, auth()->id()) : $totalTagihan;
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
                            Jatuh tempo {{ $jatuhModal }}
                            @if ($isPatunganModal)
                                · Porsimu: <span class="font-bold text-sky-600 dark:text-sky-400">Rp{{ number_format($porsiModal, 0, ',', '.') }}</span>
                            @endif
                        </p>
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
                                class="rounded-xl border px-4 py-2.5 text-sm font-semibold transition {{ $metodeBayar === 'transfer' ? 'border-teal-600 bg-teal-50 text-teal-700 ring-1 ring-teal-600 dark:bg-teal-500/10 dark:text-teal-300' : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                <span class="block text-xs font-bold">Transfer</span>
                                <span class="block text-[11px] font-normal opacity-70">Unggah bukti transfer</span>
                            </button>
                            <button type="button" wire:click="ubahMetodeBayar('cash')"
                                class="rounded-xl border px-4 py-2.5 text-sm font-semibold transition {{ $metodeBayar === 'cash' ? 'border-teal-600 bg-teal-50 text-teal-700 ring-1 ring-teal-600 dark:bg-teal-500/10 dark:text-teal-300' : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
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
                                class="w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-teal-50 dark:file:bg-teal-500/10 file:px-4 file:py-2 file:text-teal-700 dark:file:text-teal-300 file:font-semibold hover:file:bg-teal-100 dark:hover:file:bg-teal-500/20">
                            @error('bukti') <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                            <div wire:loading wire:target="bukti" class="mt-2 flex items-center gap-1.5 text-xs font-medium text-teal-600">
                                <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Mengunggah bukti...
                            </div>
                        </div>
                    @else
                        <p class="rounded-xl bg-teal-50 dark:bg-teal-500/10 ring-1 ring-teal-100 dark:ring-teal-500/20 px-4 py-3 text-xs text-teal-800 dark:text-teal-200">
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
                            class="w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-teal-50 dark:file:bg-teal-500/10 file:px-4 file:py-2 file:text-teal-700 dark:file:text-teal-300 file:font-semibold hover:file:bg-teal-100 dark:hover:file:bg-teal-500/20">
                        @error('ktpSusulan') <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                        <div wire:loading wire:target="ktpSusulan" class="mt-2 text-xs font-medium text-teal-600">Mengunggah KTP...</div>
                    </div>
                    <div class="flex flex-col-reverse sm:flex-row gap-2 pt-1">
                        <button type="button" wire:click="tutupModalKtp"
                            class="flex-1 inline-flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="simpanKtpSusulan"
                            class="flex-1 inline-flex items-center justify-center rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-500 transition disabled:opacity-50">
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
                            class="w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-teal-50 dark:file:bg-teal-500/10 file:px-4 file:py-2 file:text-teal-700 dark:file:text-teal-300 file:font-semibold hover:file:bg-teal-100 dark:hover:file:bg-teal-500/20">
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
