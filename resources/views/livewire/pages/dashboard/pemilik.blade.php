<?php

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Pembayaran;
use App\Models\Pengaturan;
use App\Models\Penyewaan;
use App\Models\Properti;
use App\Models\Tagihan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;

new class extends Component
{
    public string $cari = '';

    public string $tab = 'properti';

    public ?string $pesan = null;

    public ?string $galat = null;

    public array $tanggalMasuk = [];

    public function with(): array
    {
        $id = auth()->id();

        $bookings = Booking::whereHas('kamar.properti', fn ($q) => $q->where('pemilik_id', $id))
            ->with(['anakKos', 'kamar.properti'])
            ->when($this->cari, fn ($q) => $q->whereHas('anakKos', fn ($q) => $q->where('nama', 'like', "%{$this->cari}%")))
            ->latest()
            ->limit(10)
            ->get();

        // Pra-isi tanggal check-in dengan rencana masuk penyewa.
        foreach ($bookings as $b) {
            if ($b->status === 'disetujui' && $b->tanggal_masuk && ! isset($this->tanggalMasuk[$b->id])) {
                $this->tanggalMasuk[$b->id] = $b->tanggal_masuk->toDateString();
            }
        }

        return [
            'totalProperti' => Properti::where('pemilik_id', $id)->count(),
            'totalKamar' => Kamar::whereHas('properti', fn ($q) => $q->where('pemilik_id', $id))->count(),
            'kamarTerisi' => Kamar::where('status', 'terisi')->whereHas('properti', fn ($q) => $q->where('pemilik_id', $id))->count(),
            'penyewaanAktif' => \App\Models\Penyewaan::where('status', 'aktif')
                ->whereHas('properti', fn ($q) => $q->where('pemilik_id', $id))->count(),
            'pendapatanBulanIni' => (int) Pembayaran::where('status', 'diverifikasi')
                ->whereMonth('verified_at', now()->month)
                ->whereYear('verified_at', now()->year)
                ->whereHas('tagihan.penyewaan.properti', fn ($q) => $q->where('pemilik_id', $id))
                ->sum('jumlah'),
            'propertis' => Properti::where('pemilik_id', $id)
                ->with('kamars')
                ->withCount(['kamars', 'kamars as kamar_terisi' => fn ($q) => $q->where('status', 'terisi')])
                ->when($this->cari, fn ($q) => $q->where('nama', 'like', "%{$this->cari}%"))
                ->orderBy('nama')
                ->limit(100)
                ->get(),
            'bookings' => $bookings,
            'sewaans' => Penyewaan::query()
                ->whereHas('properti', fn ($q) => $q->where('pemilik_id', $id))
                ->with(['anakKos', 'kamar.properti', 'tagihans'])
                ->when($this->cari, fn ($q) => $q->whereHas('anakKos', fn ($q) => $q->where('nama', 'like', "%{$this->cari}%")))
                ->latest()
                ->limit(50)
                ->get(),
        ];
    }

    public function setujuiBooking(int $id): void
    {
        $booking = Booking::where('id', $id)
            ->whereHas('kamar.properti', fn ($q) => $q->where('pemilik_id', auth()->id()))
            ->with('anakKos', 'kamar')
            ->first();

        if (! $booking || $booking->status !== 'menunggu') {
            return;
        }

        $booking->update(['status' => 'disetujui']);
        $this->pesan = "Booking {$booking->anakKos?->nama} untuk kamar {$booking->kamar?->nama} disetujui.";
    }

    public function tolakBooking(int $id): void
    {
        $booking = Booking::where('id', $id)
            ->whereHas('kamar.properti', fn ($q) => $q->where('pemilik_id', auth()->id()))
            ->with('anakKos', 'kamar')
            ->first();

        if (! $booking || $booking->status !== 'menunggu') {
            return;
        }

        $booking->update(['status' => 'ditolak']);
        $this->pesan = "Booking {$booking->anakKos?->nama} untuk kamar {$booking->kamar?->nama} ditolak.";
    }

    public function checkIn(int $bookingId): void
    {
        $booking = Booking::where('id', $bookingId)
            ->whereHas('kamar.properti', fn ($q) => $q->where('pemilik_id', auth()->id()))
            ->with(['anakKos', 'kamar.properti'])
            ->first();

        if (! $booking || $booking->status !== 'disetujui') {
            return;
        }

        $kamar = $booking->kamar;

        if (! $kamar || $kamar->status !== 'tersedia') {
            $this->galat = 'Check-in gagal: kamar sudah tidak tersedia.';

            return;
        }

        $sudahSewa = Penyewaan::where('anak_kos_id', $booking->anak_kos_id)
            ->where('kamar_id', $kamar->id)
            ->where('status', 'aktif')
            ->exists();

        if ($sudahSewa) {
            $this->galat = 'Penyewa ini sudah memiliki penyewaan aktif untuk kamar tersebut.';

            return;
        }

        try {
            $masuk = Carbon::parse($this->tanggalMasuk[$bookingId] ?? $booking->tanggal_masuk?->toDateString() ?? now()->toDateString())->startOfDay();
        } catch (\Throwable) {
            $masuk = Carbon::today();
        }

        if ($masuk->greaterThan(Carbon::today()->addMonth())) {
            $this->galat = 'Tanggal masuk maksimal satu bulan dari sekarang.';

            return;
        }

        $durasi = max(1, (int) ($booking->durasi_bulan ?: 1));

        DB::transaction(function () use ($booking, $kamar, $masuk, $durasi) {
            $sewaan = Penyewaan::create([
                'anak_kos_id' => $booking->anak_kos_id,
                'kamar_id' => $kamar->id,
                'properti_id' => $kamar->properti_id,
                'tanggal_masuk' => $masuk->toDateString(),
                'tanggal_keluar' => null,
                'status' => 'aktif',
            ]);

            // Tagihan untuk seluruh durasi sewa, jatuh tempo sesuai pengaturan aplikasi.
            for ($i = 0; $i < $durasi; $i++) {
                $bulan = $masuk->copy()->addMonthsNoOverflow($i);

                $sewaan->tagihans()->create([
                    'periode' => $bulan->translatedFormat('F Y'),
                    'jumlah' => $kamar->harga_sewa_bulanan,
                    'denda' => 0,
                    'jatuh_tempo' => Pengaturan::jatuhTempoUntuk($bulan)->toDateString(),
                    'status' => 'belum_bayar',
                ]);
            }

            $kamar->update(['status' => 'terisi']);
            $booking->update(['status' => 'check_in']);
        });

        unset($this->tanggalMasuk[$bookingId]);
        $estimasiKeluar = $masuk->copy()->addMonthsNoOverflow($durasi)->subDay()->translatedFormat('d M Y');
        $this->pesan = "Check-in {$booking->anakKos?->nama} ke kamar {$kamar->nama} berhasil ({$durasi} bulan). {$durasi} tagihan dibuatkan, estimasi check-out {$estimasiKeluar}.";
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
                'tanggal_keluar' => Carbon::today()->toDateString(),
                'status' => 'selesai',
            ]);

            optional($sewaan->kamar)->update(['status' => 'tersedia']);
        });

        $catatan = $belumLunas > 0
            ? " Perhatian: masih ada {$belumLunas} tagihan belum lunas milik penyewa ini."
            : '';

        $this->pesan = "Check-out {$sewaan->anakKos?->nama} dari kamar {$sewaan->kamar?->nama} berhasil. Kamar kembali tersedia.{$catatan}";
    }
}; ?>

<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <x-dashboard-greeting
            roleLabel="Pemilik Kos"
            description="Kelola properti & kamar milik Anda, pantau okupansi dan pendapatan sewa."
            icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .414.336.75.75.75z" /></svg>'
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

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card label="Properti Saya" :value="$totalProperti" tone="cyan"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" /></svg>' />
            <x-stat-card label="Total Kamar" :value="$totalKamar" tone="sky"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" /></svg>' />
            <x-stat-card label="Kamar Terisi" :value="$kamarTerisi" tone="emerald"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />
            <x-stat-card label="Pendapatan Bulan Ini" :value="'Rp' . number_format($pendapatanBulanIni, 0, ',', '.')" tone="emerald"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>' />
        </div>

        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 overflow-hidden">
            <div class="px-4 sm:px-6 pt-4 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100">
                <div class="flex gap-2">
                    <button wire:click="$set('tab', 'properti')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'properti' ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Properti Saya
                    </button>
                    <button wire:click="$set('tab', 'booking')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'booking' ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Booking Masuk
                    </button>
                    <button wire:click="$set('tab', 'sewaan')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'sewaan' ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Penyewaan &amp; Tagihan
                    </button>
                </div>
                <input type="text" wire:model.live.debounce.300ms="cari" placeholder="Cari data..."
                    class="rounded-lg border-gray-300 text-sm focus:ring-teal-500 focus:border-teal-500">
            </div>

            <div class="p-4 sm:p-6">
                @if ($tab === 'properti')
                    @forelse ($propertis as $properti)
                        @php
                            $pct = $properti->total_kamar > 0 ? round($properti->kamar_terisi / $properti->total_kamar * 100) : 0;
                        @endphp
                        <div class="rounded-xl ring-1 ring-gray-100 bg-gray-50/50 p-5 mb-4 last:mb-0">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <h4 class="text-base font-semibold text-gray-900">{{ $properti->nama }}</h4>
                                    <p class="text-sm text-gray-500">{{ $properti->alamat }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="h-2 w-28 rounded-full bg-gray-200 overflow-hidden">
                                            <div class="h-full rounded-full bg-gradient-to-r from-teal-500 to-emerald-500" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="text-xs font-medium text-gray-500">{{ $properti->kamar_terisi }}/{{ $properti->total_kamar }} terisi</span>
                                    </div>
                                    <x-status-badge :status="$properti->status" />
                                </div>
                            </div>
                            <div class="mt-4 overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            <th class="py-2 pr-4">Kamar</th>
                                            <th class="py-2 pr-4">Kapasitas</th>
                                            <th class="py-2 pr-4">Harga/Bulan</th>
                                            <th class="py-2">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach ($properti->kamars as $kamar)
                                            <tr>
                                                <td class="py-2.5 pr-4 text-sm font-medium text-gray-900">{{ $kamar->nama }}</td>
                                                <td class="py-2.5 pr-4 text-sm text-gray-600">{{ $kamar->kapasitas }} orang</td>
                                                <td class="py-2.5 pr-4 text-sm text-gray-600">Rp{{ number_format($kamar->harga_sewa_bulanan, 0, ',', '.') }}</td>
                                                <td class="py-2.5"><x-status-badge :status="$kamar->status" /></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <p class="py-10 text-center text-sm text-gray-400">Belum ada properti. Tambahkan properti melalui menu kelola properti.</p>
                    @endforelse
                @elseif ($tab === 'booking')
                    <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Anak Kos</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kamar</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Properti</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal Booking</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rencana Masuk</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($bookings as $booking)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-4 text-sm font-medium text-gray-900">{{ $booking->anakKos?->nama ?? '-' }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-600">{{ $booking->kamar?->nama ?? '-' }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-600">{{ $booking->kamar?->properti?->nama ?? '-' }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-600">{{ $booking->tanggal_booking?->translatedFormat('d M Y') }}</td>
                                    <td class="px-4 py-4 text-sm">
                                        @if ($booking->tanggal_masuk)
                                            <span class="font-semibold text-gray-800">{{ $booking->tanggal_masuk->translatedFormat('d M Y') }}</span>
                                            <span class="block text-xs text-teal-500 font-medium">{{ $booking->durasi_bulan }} bulan sewa</span>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Tidak ditentukan</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4"><x-status-badge :status="$booking->status" /></td>
                                    <td class="px-4 py-4">
                                        @if ($booking->status === 'menunggu')
                                            <div class="flex justify-end gap-2">
                                                <button wire:click="setujuiBooking({{ $booking->id }})" wire:loading.attr="disabled"
                                                    wire:confirm="Setujui booking kamar {{ $booking->kamar?->nama }} oleh {{ $booking->anakKos?->nama }}?"
                                                    class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500 transition">
                                                    Setujui
                                                </button>
                                                <button wire:click="tolakBooking({{ $booking->id }})" wire:loading.attr="disabled"
                                                    wire:confirm="Tolak booking kamar {{ $booking->kamar?->nama }} oleh {{ $booking->anakKos?->nama }}?"
                                                    class="inline-flex items-center rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-500 transition">
                                                    Tolak
                                                </button>
                                            </div>
                                        @elseif ($booking->status === 'disetujui')
                                            <div class="flex justify-end items-center gap-2">
                                                <input type="date" wire:model="tanggalMasuk.{{ $booking->id }}"
                                                    max="{{ now()->addMonth()->toDateString() }}"
                                                    class="rounded-lg border-gray-300 text-xs focus:ring-teal-500 focus:border-teal-500"
                                                    title="Tanggal check-in">
                                                <button wire:click="checkIn({{ $booking->id }})" wire:loading.attr="disabled"
                                                    wire:confirm="Lakukan check-in {{ $booking->anakKos?->nama }} ke kamar {{ $booking->kamar?->nama }}? Penyewaan & tagihan pertama akan dibuat."
                                                    class="inline-flex items-center rounded-lg bg-sky-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-sky-500 transition disabled:opacity-50">
                                                    Check-in
                                                </button>
                                            </div>
                                        @else
                                            <span class="block text-right text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-10 text-center text-sm text-gray-400">Belum ada booking masuk.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                @elseif ($tab === 'sewaan')
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Penyewa</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kamar</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Masuk</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tagihan</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($sewaans as $sewaan)
                                    @php
                                        $belumLunas = $sewaan->tagihans->where('status', '!=', 'lunas');
                                        $sisa = $belumLunas->sum(fn ($t) => $t->jumlah + $t->denda);
                                        $telat = $belumLunas->filter(fn ($t) => $t->denda > 0)->count();
                                        $mintaKeluar = (bool) $sewaan->permintaan_keluar_pada;
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition {{ $mintaKeluar ? 'bg-amber-50/50' : '' }}">
                                        <td class="px-4 py-4 text-sm font-medium text-gray-900">
                                            {{ $sewaan->anakKos?->nama ?? '-' }}
                                            @if ($mintaKeluar)
                                                <span class="ml-1 inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700 uppercase tracking-wide">Minta keluar</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-600">
                                            {{ $sewaan->kamar?->nama ?? '-' }}
                                            <span class="block text-xs text-gray-400">{{ $sewaan->kamar?->properti?->nama }}</span>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-600">{{ $sewaan->tanggal_masuk?->translatedFormat('d M Y') }}</td>
                                        <td class="px-4 py-4 text-sm">
                                            <span class="font-semibold text-gray-900">Rp{{ number_format($sisa, 0, ',', '.') }}</span>
                                            <span class="block text-xs {{ $belumLunas->isNotEmpty() ? 'text-rose-500' : 'text-emerald-600' }}">
                                                {{ $belumLunas->isEmpty() ? 'Semua lunas' : $belumLunas->count() . ' tagihan belum lunas' . ($telat > 0 ? " ({$telat} telat)" : '') }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4"><x-status-badge :status="$sewaan->status" /></td>
                                        <td class="px-4 py-4">
                                            @if ($sewaan->status === 'aktif')
                                                <div class="flex justify-end">
                                                    <button wire:click="checkOut({{ $sewaan->id }})" wire:loading.attr="disabled"
                                                        wire:confirm="Check-out {{ $sewaan->anakKos?->nama }} dari kamar {{ $sewaan->kamar?->nama }}?{{ $mintaKeluar ? ' Penyewa sudah mengajukan keluar.' : '' }} Kamar akan kembali tersedia."
                                                        class="inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100 transition disabled:opacity-50">
                                                        {{ $mintaKeluar ? 'Setujui Check-out' : 'Check-out' }}
                                                    </button>
                                                </div>
                                            @else
                                                <span class="block text-right text-xs text-gray-400">
                                                    Keluar: {{ $sewaan->tanggal_keluar?->translatedFormat('d M Y') }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-400">Belum ada penyewaan aktif. Lakukan check-in pada booking yang disetujui.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>