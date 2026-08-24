<?php

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Pembayaran;
use App\Models\Penyewaan;
use App\Models\Properti;
use App\Models\User;
use Livewire\Volt\Component;

new class extends Component
{
    public string $cari = '';

    public string $tab = 'properti';

    public function with(): array
    {
        return [
            'totalUser' => User::count(),
            'totalProperti' => Properti::count(),
            'totalKamar' => Kamar::count(),
            'kamarTerisi' => Kamar::where('status', 'terisi')->count(),
            'penyewaanAktif' => Penyewaan::where('status', 'aktif')->count(),
            'bookingMenunggu' => Booking::where('status', 'menunggu')->count(),
            'pendapatan' => (int) Pembayaran::where('status', 'diverifikasi')->sum('jumlah'),
            'propertis' => Properti::with('pemilik')
                ->withCount(['kamars', 'kamars as kamar_terisi' => fn ($q) => $q->where('status', 'terisi')])
                ->when($this->cari, fn ($q) => $q->where('nama', 'like', "%{$this->cari}%"))
                ->orderBy('nama')
                ->limit(100)
                ->get(),
            'penggunas' => User::with('roles')
                ->when($this->cari, fn ($q) => $q->where(fn ($q) => $q->where('nama', 'like', "%{$this->cari}%")->orWhere('email', 'like', "%{$this->cari}%")))
                ->orderBy('nama')
                ->limit(100)
                ->get(),
            'bookings' => Booking::with(['anakKos', 'kamar.properti'])
                ->when($this->cari, fn ($q) => $q->whereHas('anakKos', fn ($q) => $q->where('nama', 'like', "%{$this->cari}%")))
                ->latest()
                ->limit(10)
                ->get(),
        ];
    }
}; ?>

<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <x-dashboard-greeting
            roleLabel="Super Admin"
            description="Akses penuh ke seluruh data lintas properti, pengguna, dan konfigurasi sistem."
            icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>'
        />

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <x-stat-card label="Total Pengguna" :value="$totalUser" tone="teal"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>' />
            <x-stat-card label="Properti" :value="$totalProperti" tone="cyan"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" /></svg>' />
            <x-stat-card label="Kamar" :value="$totalKamar . ' (' . $kamarTerisi . ' terisi)'" tone="sky"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" /></svg>' />
            <x-stat-card label="Penyewaan Aktif" :value="$penyewaanAktif" tone="emerald"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />
            <x-stat-card label="Booking Menunggu" :value="$bookingMenunggu" tone="amber"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />
            <x-stat-card label="Pendapatan Terkumpul" :value="'Rp' . number_format($pendapatan, 0, ',', '.')" tone="rose"
                icon='<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>' />
        </div>

        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 overflow-hidden">
            <div class="px-4 sm:px-6 pt-4 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100">
                <div class="flex gap-2">
                    <button wire:click="$set('tab', 'properti')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'properti' ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Properti
                    </button>
                    <button wire:click="$set('tab', 'pengguna')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'pengguna' ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Pengguna
                    </button>
                    <button wire:click="$set('tab', 'booking')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'booking' ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Booking
                    </button>
                </div>
                <input type="text" wire:model.live.debounce.300ms="cari" placeholder="Cari data..."
                    class="rounded-lg border-gray-300 text-sm focus:ring-teal-500 focus:border-teal-500">
            </div>

            <div class="overflow-x-auto">
                @if ($tab === 'properti')
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Properti</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pemilik</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Alamat</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Okupansi</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($propertis as $properti)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $properti->nama }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $properti->pemilik?->nama ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $properti->alamat ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-2 w-24 rounded-full bg-gray-100 overflow-hidden">
                                                <div class="h-full rounded-full bg-gradient-to-r from-teal-500 to-emerald-500"
                                                    style="width: {{ $properti->total_kamar > 0 ? round($properti->kamar_terisi / $properti->total_kamar * 100) : 0 }}%"></div>
                                            </div>
                                            <span class="text-xs font-medium text-gray-500">{{ $properti->kamar_terisi }}/{{ $properti->total_kamar }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4"><x-status-badge :status="$properti->status" /></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-10 text-center text-sm text-gray-400">Belum ada data properti.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @elseif ($tab === 'pengguna')
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No. HP</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($penggunas as $pengguna)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $pengguna->nama }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $pengguna->email }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $pengguna->no_hp ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        @php
                                            $roleMap = [
                                                'super_admin' => 'bg-rose-50 text-rose-700 ring-rose-200',
                                                'pemilik' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                                'admin' => 'bg-sky-50 text-sky-700 ring-sky-200',
                                                'anak_kos' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                            ];
                                            $role = $pengguna->roles->first()?->name;
                                        @endphp
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $roleMap[$role] ?? 'bg-gray-100 text-gray-600 ring-gray-200' }}">
                                            {{ str($role ?? '-')->replace('_', ' ')->title() }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-10 text-center text-sm text-gray-400">Tidak ada pengguna yang cocok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Anak Kos</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kamar</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Properti</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($bookings as $booking)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $booking->anakKos?->nama ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $booking->kamar?->nama ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $booking->kamar?->properti?->nama ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $booking->tanggal_booking?->translatedFormat('d M Y') }}</td>
                                    <td class="px-6 py-4"><x-status-badge :status="$booking->status" /></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-10 text-center text-sm text-gray-400">Belum ada booking.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</div>