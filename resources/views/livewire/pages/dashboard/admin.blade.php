<?php

use App\Models\Pembayaran;
use App\Models\Penyewaan;
use App\Models\Properti;
use App\Models\Tagihan;
use Livewire\Volt\Component;

new class extends Component
{
    public string $cari = '';

    public string $tab = 'pembayaran';

    public ?string $pesan = null;

    private function kelolaan()
    {
        if (auth()->user()->is_super_admin) {
            return fn ($query) => $query;
        }

        $id = auth()->id();

        return function ($query) use ($id) {
            $query->where(function ($q) use ($id) {
                $q->whereHas('admins', fn ($a) => $a->where('users.id', $id))
                    ->orWhereDoesntHave('admins');
            });
        };
    }

    public function with(): array
    {
        $id = auth()->id();

        return [
            'totalTugas' => Properti::whereHas('admins', fn ($q) => $q->where('id', $id))->count(),
            'pembayaranMenunggu' => Pembayaran::where('status', 'menunggu_verifikasi')
                ->whereHas('tagihan.penyewaan.properti', $this->kelolaan())
                ->count(),
            'penyewaanAktif' => Penyewaan::where('status', 'aktif')
                ->whereHas('properti', $this->kelolaan())
                ->count(),
            'pembayarans' => Pembayaran::whereHas('tagihan.penyewaan.properti', $this->kelolaan())
                ->with(['anakKos', 'tagihan'])
                ->when($this->cari, fn ($q) => $q->whereHas('anakKos', fn ($q) => $q->where('nama', 'like', "%{$this->cari}%")))
                ->latest()
                ->limit(15)
                ->get(),
            'funnelStages' => [
                ['label' => 'Kunjungan', 'sub' => 'properti yang Anda kelola', 'nilai' => Properti::whereHas('admins', fn ($q) => $q->where('id', $id))->count()],
                ['label' => 'Penyewa', 'sub' => 'penyewaan berstatus aktif', 'nilai' => Penyewaan::where('status', 'aktif')->whereHas('properti', $this->kelolaan())->count()],
                ['label' => 'Tagihan', 'sub' => 'total tagihan yang terbit', 'nilai' => Tagihan::whereHas('penyewaan.properti', $this->kelolaan())->count()],
                ['label' => 'Lunas', 'sub' => 'tagihan berstatus lunas', 'nilai' => Tagihan::where('status', 'lunas')->whereHas('penyewaan.properti', $this->kelolaan())->count()],
            ],
            'pendapatanPerBulan' => Pembayaran::where('status', 'diverifikasi')
                ->whereHas('tagihan.penyewaan.properti', $this->kelolaan())
                ->where('verified_at', '>=', now()->subMonths(5)->startOfMonth())
                ->get(['verified_at', 'jumlah'])
                ->groupBy(fn ($p) => $p->verified_at->format('m/Y'))
                ->map(fn ($rows) => ['month' => $rows->first()->verified_at->format('m/Y'), 'total' => (int) $rows->sum('jumlah')])
                ->keyBy('month')
                ->all(),
            'tagihanStatusPerBulan' => Tagihan::whereHas('penyewaan.properti', $this->kelolaan())
                ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
                ->get(['created_at', 'jumlah', 'denda', 'status'])
                ->groupBy(fn ($t) => $t->created_at->format('m/Y'))
                ->map(function ($rows) {
                    $total = $rows->sum(fn ($t) => (float) $t->jumlah + (float) $t->denda);
                    $lunas = $rows->where('status', 'lunas')->sum(fn ($t) => (float) $t->jumlah + (float) $t->denda);

                    return [
                        'month' => $rows->first()->created_at->format('m/Y'),
                        'total' => (int) round($total),
                        'lunas' => (int) round($lunas),
                        'belum' => (int) round($total - $lunas),
                    ];
                })
                ->keyBy('month')
                ->all(),
        ];
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

        $this->pesan = 'Pembayaran ' . ($pembayaran->anakKos?->nama ?? '-') . ' sebesar Rp'
            . number_format($pembayaran->jumlah, 0, ',', '.') . ' diverifikasi.';
    }
}; ?>

<div class="py-10">
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

        <x-dashboard-funnel
            :stages="$funnelStages"
            title="Grafik Pipeline"
            subtitle="Kunjungan → Penyewa → Tagihan → Lunas, properti yang Anda kelola"
        />

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
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $pembayaran->metode }}</td>
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
