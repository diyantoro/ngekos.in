<?php

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Pembayaran;
use App\Models\Penyewaan;
use App\Models\Tagihan;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public string $cari = '';

    public string $tab = 'kamar';

    public ?string $pesan = null;

    public ?string $galat = null;

    public ?string $rencanaMasuk = null;

    public int $durasiBulan = 1;

    public ?int $modalKamarId = null;

    public ?int $modalBayarId = null;

    public $bukti = null;

    public function with(): array
    {
        $id = auth()->id();

        return [
            'penyewaanAktif' => Penyewaan::where('anak_kos_id', $id)->where('status', 'aktif')->count(),
            'tagihanBelumBayar' => Tagihan::where('status', '!=', 'lunas')
                ->whereHas('penyewaan', fn ($q) => $q->where('anak_kos_id', $id))
                ->count(),
            'totalBayar' => (int) Pembayaran::where('anak_kos_id', $id)->where('status', 'diverifikasi')->sum('jumlah'),
            'kamars' => Kamar::where('status', 'tersedia')
                ->with('properti')
                ->when($this->cari, fn ($q) => $q->where(fn ($q) => $q->where('nama', 'like', "%{$this->cari}%")
                    ->orWhereHas('properti', fn ($q) => $q->where('nama', 'like', "%{$this->cari}%"))))
                ->orderBy('harga_sewa_bulanan')
                ->get(),
            'bookings' => Booking::where('anak_kos_id', $id)
                ->with('kamar.properti')
                ->latest()
                ->get(),
            'tagihans' => Tagihan::whereHas('penyewaan', fn ($q) => $q->where('anak_kos_id', $id))
                ->with(['penyewaan.kamar', 'pembayarans'])
                ->orderByDesc('jatuh_tempo')
                ->get(),
            'pembayarans' => Pembayaran::where('anak_kos_id', $id)
                ->with(['tagihan', 'verifikator'])
                ->latest()
                ->get(),
            'sewaans' => Penyewaan::where('anak_kos_id', $id)
                ->with(['kamar.properti', 'tagihans'])
                ->orderByDesc('status')
                ->latest()
                ->get(),
        ];
    }

    public function pesanKamar(int $kamarId): void
    {
        $this->resetValidation();
        $this->pesan = null;
        $this->galat = null;
        $this->rencanaMasuk = null;
        $this->durasiBulan = 1;
        $this->modalKamarId = $kamarId;
    }

    public function tutupModal(): void
    {
        $this->modalKamarId = null;
        $this->resetValidation();
    }

    public function konfirmasiPesan(): void
    {
        $valid = $this->validate([
            'rencanaMasuk' => ['nullable', 'date', 'after_or_equal:today'],
            'durasiBulan' => ['required', 'integer', 'min:1', 'max:12'],
        ], [
            'rencanaMasuk.after_or_equal' => 'Tanggal masuk tidak boleh di masa lalu.',
            'durasiBulan.required' => 'Pilih durasi sewa.',
        ]);

        $kamar = Kamar::where('id', $this->modalKamarId)->where('status', 'tersedia')->with('properti')->first();

        if (! $kamar) {
            $this->modalKamarId = null;
            $this->galat = 'Kamar tidak tersedia lagi.';

            return;
        }

        $sudahAda = Booking::where('anak_kos_id', auth()->id())
            ->where('kamar_id', $kamar->id)
            ->whereIn('status', ['menunggu', 'disetujui'])
            ->exists();

        if ($sudahAda) {
            $this->modalKamarId = null;
            $this->galat = 'Kamu sudah memiliki booking aktif untuk kamar ini.';

            return;
        }

        Booking::create([
            'anak_kos_id' => auth()->id(),
            'kamar_id' => $kamar->id,
            'tanggal_booking' => now()->toDateString(),
            'tanggal_masuk' => $valid['rencanaMasuk'],
            'durasi_bulan' => $valid['durasiBulan'],
            'status' => 'menunggu',
            'catatan' => null,
        ]);

        $this->modalKamarId = null;

        $masuk = $valid['rencanaMasuk']
            ? \Illuminate\Support\Carbon::parse($valid['rencanaMasuk'])->translatedFormat('d M Y')
            : '(diatur pemilik saat check-in)';

        $this->pesan = "Booking kamar {$kamar->nama} di {$kamar->properti?->nama} berhasil diajukan untuk {$valid['durasiBulan']} bulan, masuk {$masuk}. Menunggu persetujuan pemilik kos.";
    }

    public function batalBooking(int $bookingId): void
    {
        $booking = Booking::where('id', $bookingId)->where('anak_kos_id', auth()->id())->first();

        if (! $booking || $booking->status !== 'menunggu') {
            return;
        }

        $booking->update(['status' => 'batal']);
        $this->pesan = 'Booking dibatalkan.';
    }

    public function ajukanKeluar(int $sewaanId): void
    {
        $sewaan = Penyewaan::where('id', $sewaanId)
            ->where('anak_kos_id', auth()->id())
            ->where('status', 'aktif')
            ->first();

        if (! $sewaan || $sewaan->permintaan_keluar_pada) {
            return;
        }

        $sewaan->update(['permintaan_keluar_pada' => now()]);
        $this->pesan = 'Pengajuan check-out terkirim. Pemilik kos akan mengonfirmasi tanggal keluarmu.';
    }

    public function bayarTagihan(int $tagihanId): void
    {
        $this->resetValidation();
        $this->pesan = null;
        $this->galat = null;
        $this->bukti = null;

        $tagihan = Tagihan::where('id', $tagihanId)
            ->whereHas('penyewaan', fn ($q) => $q->where('anak_kos_id', auth()->id()))
            ->first();

        if (! $tagihan || $tagihan->status === 'lunas') {
            $this->galat = 'Tagihan tidak ditemukan atau sudah lunas.';

            return;
        }

        $sudahAda = $tagihan->pembayarans()->where('status', 'menunggu_verifikasi')->exists();

        if ($sudahAda) {
            $this->galat = 'Pembayaran untuk tagihan ini masih menunggu verifikasi admin.';

            return;
        }

        $this->modalBayarId = $tagihanId;
    }

    public function tutupModalBayar(): void
    {
        $this->modalBayarId = null;
        $this->bukti = null;
        $this->resetValidation();
    }

    public function konfirmasiBayar(): void
    {
        $tagihan = Tagihan::where('id', $this->modalBayarId)
            ->whereHas('penyewaan', fn ($q) => $q->where('anak_kos_id', auth()->id()))
            ->first();

        if (! $tagihan || $tagihan->status === 'lunas') {
            $this->tutupModalBayar();
            $this->galat = 'Tagihan tidak ditemukan atau sudah lunas.';

            return;
        }

        $sudahAda = $tagihan->pembayarans()->where('status', 'menunggu_verifikasi')->exists();

        if ($sudahAda) {
            $this->tutupModalBayar();
            $this->galat = 'Pembayaran untuk tagihan ini masih menunggu verifikasi admin.';

            return;
        }

        $this->validate([
            'bukti' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:2048'],
        ], [
            'bukti.required' => 'Lampirkan bukti transfer terlebih dahulu.',
            'bukti.mimes' => 'Bukti transfer harus berupa gambar (JPG, PNG, WEBP) atau PDF.',
            'bukti.max' => 'Ukuran bukti transfer maksimal 2MB.',
        ]);

        Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'anak_kos_id' => auth()->id(),
            'metode' => 'transfer',
            'jumlah' => $tagihan->jumlah + $tagihan->denda,
            'bukti' => $this->bukti->store('bukti-pembayaran', 'public'),
            'status' => 'menunggu_verifikasi',
        ]);

        $this->tutupModalBayar();

        $this->pesan = 'Pembayaran beserta bukti transfer terkirim dan sedang menunggu verifikasi admin.';
    }
}; ?>

<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <x-dashboard-greeting
            roleLabel="Anak Kos"
            description="Cari kamar, pantau booking, tagihan, dan riwayat pembayaranmu di sini."
            icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>'
        />

        @if ($pesan)
            <x-notifikasi-popup :pesan="$pesan" judul="Berhasil!" />
        @endif

        @if ($galat)
            <div class="flex items-center justify-between gap-3 rounded-xl bg-rose-50 ring-1 ring-rose-200 px-4 py-3 text-sm text-rose-800">
                <span>{{ $galat }}</span>
                <button wire:click="$set('galat', null)" class="text-rose-500 hover:text-rose-700 font-bold">&times;</button>
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

        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 overflow-hidden">
            <div class="px-4 sm:px-6 pt-4 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100">
                <div class="flex flex-wrap gap-2">
                    <button wire:click="$set('tab', 'kamar')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'kamar' ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Cari Kamar
                    </button>
                    <button wire:click="$set('tab', 'booking')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'booking' ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Booking Saya
                    </button>
                    <button wire:click="$set('tab', 'sewaan')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'sewaan' ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Sewa Saya
                    </button>
                    <button wire:click="$set('tab', 'tagihan')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'tagihan' ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Tagihan Saya
                    </button>
                    <button wire:click="$set('tab', 'pembayaran')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'pembayaran' ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Pembayaran Saya
                    </button>
                </div>
                @if ($tab === 'kamar')
                    <input type="text" wire:model.live.debounce.300ms="cari" placeholder="Cari kamar atau properti..."
                        class="rounded-lg border-gray-300 text-sm focus:ring-teal-500 focus:border-teal-500">
                @endif
            </div>

            <div class="p-4 sm:p-6">
                @if ($tab === 'kamar')
                    @forelse ($kamars as $kamar)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-xl ring-1 ring-gray-100 p-4 mb-3 last:mb-0 hover:bg-gray-50 transition">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 shrink-0 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Kamar {{ $kamar->nama }}
                                        <span class="ml-2 text-xs font-medium text-gray-500">{{ $kamar->properti?->nama }}</span>
                                    </p>
                                    <p class="text-sm text-gray-500">{{ $kamar->kapasitas }} orang &middot; Rp{{ number_format($kamar->harga_sewa_bulanan, 0, ',', '.') }}/bulan</p>
                                </div>
                            </div>
                            <button wire:click="pesanKamar({{ $kamar->id }})" wire:loading.attr="disabled"
                                class="shrink-0 inline-flex items-center justify-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500 transition">
                                Pesan Kamar
                            </button>
                        </div>
                    @empty
                        <p class="py-10 text-center text-sm text-gray-400">Tidak ada kamar tersedia.</p>
                    @endforelse
                @elseif ($tab === 'booking')
                    <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
                        <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kamar</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Properti</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal Booking</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($bookings as $booking)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-4 text-sm font-medium text-gray-900">{{ $booking->kamar?->nama ?? '-' }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-600">{{ $booking->kamar?->properti?->nama ?? '-' }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-600">{{ $booking->tanggal_booking?->translatedFormat('d M Y') }}</td>
                                    <td class="px-4 py-4"><x-status-badge :status="$booking->status" /></td>
                                    <td class="px-4 py-4">
                                        @if ($booking->status === 'menunggu')
                                            <div class="flex justify-end">
                                                <button wire:click="batalBooking({{ $booking->id }})" wire:loading.attr="disabled"
                                                    class="inline-flex items-center rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-500 transition">
                                                    Batalkan
                                                </button>
                                            </div>
                                        @else
                                            <span class="block text-right text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-gray-400">Kamu belum punya booking.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                @elseif ($tab === 'sewaan')
                    <div class="space-y-4">
                        @forelse ($sewaans as $sewaan)
                            @php
                                $belumLunas = $sewaan->tagihans->where('status', '!=', 'lunas');
                                $sisa = $belumLunas->sum(fn ($t) => $t->jumlah + $t->denda);
                            @endphp
                            <div class="rounded-xl ring-1 {{ $sewaan->status === 'aktif' ? 'ring-teal-100' : 'ring-gray-100 opacity-75' }} p-4 sm:p-5">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-bold text-gray-900">
                                            Kamar {{ $sewaan->kamar?->nama }} &middot; {{ $sewaan->kamar?->properti?->nama }}
                                        </p>
                                        <p class="mt-0.5 text-xs text-gray-500">
                                            Masuk: <span class="font-medium text-gray-700">{{ $sewaan->tanggal_masuk?->translatedFormat('d M Y') ?? '-' }}</span>
                                            @if ($sewaan->tanggal_keluar)
                                                &middot; Keluar: <span class="font-medium text-gray-700">{{ $sewaan->tanggal_keluar->translatedFormat('d M Y') }}</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <x-status-badge :status="$sewaan->status" />
                                        @if ($sewaan->permintaan_keluar_pada && $sewaan->status === 'aktif')
                                            <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-200">
                                                Menunggu konfirmasi keluar
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-3 pt-3 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <p class="text-xs text-gray-500">
                                        @if ($belumLunas->isEmpty())
                                            <span class="font-semibold text-emerald-600">Semua tagihan lunas</span>
                                        @else
                                            <span class="font-semibold text-rose-600">Sisa tagihan Rp{{ number_format($sisa, 0, ',', '.') }}</span> ({{ $belumLunas->count() }} tagihan) &mdash; bayar lewat tab Tagihan Saya
                                        @endif
                                    </p>
                                    @if ($sewaan->status === 'aktif')
                                        @if ($sewaan->permintaan_keluar_pada)
                                            <span class="text-xs text-gray-400 italic">Pengajuan keluar dikirim {{ $sewaan->permintaan_keluar_pada->translatedFormat('d M Y, H:i') }}</span>
                                        @else
                                            <button wire:click="ajukanKeluar({{ $sewaan->id }})" wire:loading.attr="disabled"
                                                wire:confirm="Ajukan check-out dari kamar {{ $sewaan->kamar?->nama }}? Pemilik kos akan mengonfirmasi tanggal keluarmu."
                                                class="shrink-0 inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100 transition">
                                                Ajukan Check-out
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-10 text-center">
                                <p class="text-sm text-gray-400">Belum ada penyewaan aktif.</p>
                                <p class="text-xs text-gray-400 mt-1">Setelah bookingmu disetujui dan di-check-in pemilik, status sewamu muncul di sini.</p>
                            </div>
                        @endforelse
                    </div>
                @elseif ($tab === 'tagihan')
                    <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
                        <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Periode</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kamar</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jumlah</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jatuh Tempo</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($tagihans as $tagihan)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-4 text-sm font-medium text-gray-900">{{ $tagihan->periode }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-600">{{ $tagihan->penyewaan?->kamar?->nama ?? '-' }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-600">Rp{{ number_format($tagihan->jumlah + $tagihan->denda, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-600">{{ $tagihan->jatuh_tempo?->translatedFormat('d M Y') }}</td>
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
                                            <span class="block text-right text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-400">Tidak ada tagihan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                @else
                    <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
                        <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Periode</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jumlah</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Metode</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Bukti</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Diverifikasi Oleh</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($pembayarans as $pembayaran)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-4 text-sm font-medium text-gray-900">{{ $pembayaran->tagihan?->periode ?? '-' }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-600">Rp{{ number_format($pembayaran->jumlah, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-600">{{ $pembayaran->metode }}</td>
                                    <td class="px-4 py-4 text-sm">
                                        @if ($pembayaran->bukti)
                                            <a href="{{ Storage::url($pembayaran->bukti) }}" target="_blank" rel="noopener"
                                                class="inline-flex items-center gap-1 text-xs font-semibold text-teal-600 hover:text-teal-700 hover:underline">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                                Lihat
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Tidak ada</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600">{{ $pembayaran->verifikator?->nama ?? '-' }}</td>
                                    <td class="px-4 py-4"><x-status-badge :status="$pembayaran->status" /></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-400">Belum ada pembayaran.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if ($modalKamarId)
    @php
        $kamarModal = $kamars->firstWhere('id', $modalKamarId);
    @endphp
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
        <button type="button" wire:click="tutupModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm cursor-default" tabindex="-1" aria-label="Tutup"></button>
        <div class="relative min-h-full flex items-end sm:items-center justify-center p-4">
            <div class="w-full sm:max-w-md bg-white rounded-2xl shadow-xl ring-1 ring-gray-100 overflow-hidden">
                <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-gray-100">
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-gray-900 truncate">Pesan Kamar {{ $kamarModal?->nama }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $kamarModal?->properti?->nama }} &middot; Rp{{ number_format($kamarModal?->harga_sewa_bulanan ?? 0, 0, ',', '.') }}/bulan</p>
                    </div>
                    <button type="button" wire:click="tutupModal"
                        class="shrink-0 h-8 w-8 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center transition">&times;</button>
                </div>

                <form wire:submit="konfirmasiPesan" class="p-5 space-y-4">
                    <p class="text-xs text-gray-400">Tanggal masuk boleh dikosongkan &mdash; nanti ditentukan pemilik kos saat check-in.</p>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal Rencana Masuk (opsional)</label>
                        <input type="date" wire:model="rencanaMasuk" min="{{ now()->toDateString() }}"
                            max="{{ now()->addMonthsNoOverflow(6)->toDateString() }}"
                            class="w-full rounded-lg border-gray-300 text-sm focus:ring-teal-500 focus:border-teal-500">
                        @error('rencanaMasuk') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Durasi Sewa</label>
                        <select wire:model="durasiBulan"
                            class="w-full rounded-lg border-gray-300 text-sm focus:ring-teal-500 focus:border-teal-500">
                            @foreach (range(1, 12) as $bulan)
                                <option value="{{ $bulan }}">{{ $bulan }} bulan</option>
                            @endforeach
                        </select>
                        @error('durasiBulan') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex flex-col-reverse sm:flex-row gap-2 pt-1">
                        <button type="button" wire:click="tutupModal" wire:loading.attr="disabled"
                            class="flex-1 inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-500 transition disabled:opacity-50">
                            Ajukan Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @if ($modalBayarId)
    @php
        $tagihanModal = $tagihans->firstWhere('id', $modalBayarId);
        $totalTagihan = ($tagihanModal?->jumlah ?? 0) + ($tagihanModal?->denda ?? 0);
    @endphp
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
        <button type="button" wire:click="tutupModalBayar" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm cursor-default" tabindex="-1" aria-label="Tutup"></button>
        <div class="relative min-h-full flex items-end sm:items-center justify-center p-4">
            <div class="w-full sm:max-w-md bg-white rounded-2xl shadow-xl ring-1 ring-gray-100 overflow-hidden">
                <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-gray-100">
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-gray-900 truncate">Bayar Tagihan {{ $tagihanModal?->periode }}</p>
                        <p class="text-xs text-gray-500 truncate">Total: Rp{{ number_format($totalTagihan, 0, ',', '.') }}</p>
                    </div>
                    <button type="button" wire:click="tutupModalBayar"
                        class="shrink-0 h-8 w-8 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center transition">&times;</button>
                </div>

                <form wire:submit="konfirmasiBayar" class="p-5 space-y-4">
                    <p class="text-xs text-gray-400">Transfer tepat sesuai jumlah tagihan di atas, lalu unggah bukti transfer. Admin akan memverifikasi pembayaranmu.</p>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Bukti Transfer (JPG/PNG/WEBP/PDF, maks 2MB)</label>
                        <input type="file" wire:model="bukti" accept=".jpg,.jpeg,.png,.webp,.pdf"
                            class="w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:text-teal-700 file:font-semibold hover:file:bg-teal-100">
                        @error('bukti') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                        <div wire:loading wire:target="bukti" class="mt-2 flex items-center gap-1.5 text-xs font-medium text-teal-600">
                            <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Mengunggah bukti...
                        </div>
                    </div>
                    <div class="flex flex-col-reverse sm:flex-row gap-2 pt-1">
                        <button type="button" wire:click="tutupModalBayar" wire:loading.attr="disabled"
                            class="flex-1 inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">
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