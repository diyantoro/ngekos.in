<?php

use App\Models\Subscription;
use App\Models\SubscriptionRequest;
use App\Models\User;
use App\Services\SubscriptionService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $cari = '';

    public ?int $userId = null;

    public string $plan = 'pro';

    public string $status = 'active';

    public ?string $startsAt = null;

    public int $durasiHari = 30;

    public ?string $pesan = null;

    public ?string $galat = null;

    public function with(): array
    {
        return [
            'subscriptions' => Subscription::with('user:id,nama,email')
                ->when($this->cari, fn ($q) => $q->whereHas('user', fn ($w) => $w
                    ->where('nama', 'like', "%{$this->cari}%")
                    ->orWhere('email', 'like', "%{$this->cari}%")))
                ->latest()
                ->limit(100)
                ->get(),
            'pemiliks' => User::role('pemilik')->orderBy('nama')->get(['id', 'nama', 'email']),
            'permintaan' => SubscriptionRequest::with('user:id,nama,email')
                ->latest()
                ->limit(50)
                ->get(),
            'pratinjauAkhir' => $this->hitungAkhir(),
        ];
    }

    private function hitungAkhir(): ?string
    {
        // Nilai dari <select> datang sebagai string — cast dulu agar cocok
        // dengan daftar durasi dan pratinjau langsung menyesuaikan.
        $durasi = (int) $this->durasiHari;

        try {
            $mulai = ! empty($this->startsAt) ? \Carbon\Carbon::parse($this->startsAt)->startOfDay() : null;
        } catch (\Throwable $e) {
            return null;
        }

        if (! $mulai || ! in_array($durasi, [30, 90, 180, 365], true)) {
            return null;
        }

        return $mulai->translatedFormat('d M Y').' → '.$mulai->copy()->addDays($durasi)->translatedFormat('d M Y')." ({$durasi} hari)";
    }

    public function simpan(): void
    {
        $this->pesan = null;
        $this->galat = null;

        $this->validate([
            'userId' => ['required', 'integer', 'exists:users,id'],
            'plan' => ['required', 'in:free,pro,business'],
            'status' => ['required', 'in:active,expired,cancelled'],
            'startsAt' => ['required', 'date'],
            'durasiHari' => ['required', 'integer', 'in:30,90,180,365'],
        ], [], [
            'userId' => 'user',
            'startsAt' => 'tanggal mulai',
            'durasiHari' => 'durasi',
        ]);

        $user = User::find($this->userId);

        if (! $user || ! $user->hasRole('pemilik')) {
            $this->galat = 'Langganan hanya bisa diberikan ke akun pemilik kos.';

            return;
        }

        $mulai = \Carbon\Carbon::parse($this->startsAt)->startOfDay();
        $durasi = (int) $this->durasiHari;
        $akhir = $mulai->copy()->addDays($durasi);

        try {
            SubscriptionService::store($user->id, [
                'plan' => $this->plan,
                'status' => $this->status,
                'starts_at' => $mulai,
                'expires_at' => $akhir,
            ]);
        } catch (\InvalidArgumentException $e) {
            $this->galat = $e->getMessage();

            return;
        }

        $namaPlan = strtoupper($this->plan);
        $namaUser = $user->nama;
        $teksMulai = $mulai->translatedFormat('d M Y');
        $teksAkhir = $akhir->translatedFormat('d M Y');

        $this->reset(['userId', 'startsAt']);
        $this->durasiHari = 30;
        $this->plan = 'pro';
        $this->status = 'active';
        $this->pesan = "Langganan {$namaPlan} untuk {$namaUser} disimpan: {$teksMulai} s.d. {$teksAkhir}.";
    }

    public function perpanjang(int $id): void
    {
        $subscription = Subscription::find($id);

        if (! $subscription) {
            return;
        }

        SubscriptionService::perpanjang($subscription, 30);

        $this->pesan = 'Langganan diperpanjang 30 hari.';
    }

    public function akhiri(int $id): void
    {
        $subscription = Subscription::find($id);

        if (! $subscription) {
            return;
        }

        SubscriptionService::akhiri($subscription);

        $this->pesan = 'Langganan diakhiri. Data user tetap tersimpan.';
    }

    public function setujui(int $id): void
    {
        $request = SubscriptionRequest::find($id);

        if (! $request || $request->status !== 'pending') {
            return;
        }

        SubscriptionService::approveRequest($request, 30);

        $this->pesan = 'Permintaan upgrade '.strtoupper($request->requested_plan).' untuk '.$request->user?->nama.' disetujui. Langganan aktif 30 hari.';
    }

    public function tolak(int $id): void
    {
        $request = SubscriptionRequest::find($id);

        if (! $request || $request->status !== 'pending') {
            return;
        }

        SubscriptionService::rejectRequest($request);

        $this->pesan = 'Permintaan upgrade untuk '.$request->user?->nama.' ditolak.';
    }
}; ?>

<div class="py-10">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Langganan Premium</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Aktivasi manual paket Free / Pro / Business. Tanggal berakhir dihitung otomatis dari tanggal mulai + durasi.</p>
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

        <form wire:submit="simpan" class="card p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="lg:col-span-2">
                <x-input-label for="userId" value="User (pemilik)" />
                <select wire:model="userId" id="userId" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm">
                    <option value="">— Pilih —</option>
                    @foreach ($pemiliks as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->email }})</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('userId')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="plan" value="Plan" />
                <select wire:model="plan" id="plan" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm">
                    <option value="free">FREE</option>
                    <option value="pro">PRO</option>
                    <option value="business">BUSINESS</option>
                </select>
                <x-input-error :messages="$errors->get('plan')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="status" value="Status" />
                <select wire:model="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm">
                    <option value="active">ACTIVE</option>
                    <option value="expired">EXPIRED</option>
                    <option value="cancelled">CANCELLED</option>
                </select>
                <x-input-error :messages="$errors->get('status')" class="mt-2" />
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn-primary w-full rounded-xl px-4 py-2 text-xs font-semibold text-white">Simpan</button>
            </div>
            <div>
                <x-input-label for="startsAt" value="Tanggal mulai" />
                <input wire:model.live="startsAt" id="startsAt" type="date" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm">
                <x-input-error :messages="$errors->get('startsAt')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="durasiHari" value="Durasi" />
                <select wire:model.live="durasiHari" id="durasiHari" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm">
                    <option value="30">30 hari (1 bulan)</option>
                    <option value="90">90 hari (3 bulan)</option>
                    <option value="180">180 hari (6 bulan)</option>
                    <option value="365">365 hari (1 tahun)</option>
                </select>
                <x-input-error :messages="$errors->get('durasiHari')" class="mt-2" />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Periode otomatis: <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $pratinjauAkhir ?? '— isi tanggal mulai —' }}</span>
                </p>
            </div>
        </form>

        <input type="text" wire:model.live.debounce.300ms="cari" placeholder="Cari nama atau email..."
            class="w-full sm:w-72 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">

        <div class="card p-5">
            <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">Permintaan Upgrade</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Ajuan pemilik. Setujui untuk mengaktifkan paket 30 hari, atau tolak.</p>
            <div class="mt-4 divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($permintaan as $r)
                    <div class="py-3 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                {{ $r->user?->nama ?? 'User terhapus' }}
                                <span class="font-normal text-xs text-gray-500">({{ $r->user?->email ?? '—' }})</span>
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Upgrade ke <strong>{{ strtoupper($r->requested_plan) }}</strong> · {{ $r->created_at?->translatedFormat('d M Y H:i') }}
                                @if ($r->keterangan)
                                    · {{ $r->keterangan }}
                                @endif
                            </p>
                            @if ($r->amount || $r->payment_method)
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Pembayaran: <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100/80 dark:bg-emerald-900/40 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:text-emerald-300">{{ strtoupper($r->payment_method ?? 'qris') }}</span>
                                    @if ($r->amount)
                                        · Rp{{ number_format($r->amount, 0, ',', '.') }}
                                    @endif
                                    @if ($r->bukti_path)
                                        · <a href="{{ asset('storage/'.$r->bukti_path) }}" target="_blank" class="font-semibold text-teal-600 dark:text-teal-400 hover:underline">Lihat bukti</a>
                                    @endif
                                </p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <x-status-badge :status="$r->status" />
                            @if ($r->status === 'pending')
                                <button type="button" wire:click="setujui({{ $r->id }})"
                                    wire:confirm="Setujui upgrade {{ strtoupper($r->requested_plan) }} untuk {{ $r->user?->nama }} (aktif 30 hari)?" 
                                    class="text-xs font-semibold text-teal-600 dark:text-teal-400 hover:underline">Setujui</button>
                                <span class="mx-1 text-gray-300">·</span>
                                <button type="button" wire:click="tolak({{ $r->id }})"
                                    wire:confirm="Tolak permintaan upgrade {{ $r->user?->nama }}?"
                                    class="text-xs font-semibold text-rose-600 dark:text-rose-400 hover:underline">Tolak</button>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-gray-400">Tidak ada permintaan upgrade.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Plan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Start</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">End</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($subscriptions as $s)
                            <tr wire:key="sub-{{ $s->id }}">
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $s->user?->nama }}<span class="block text-xs font-normal text-gray-500">{{ $s->user?->email }}</span></td>
                                <td class="px-4 py-3 text-sm">{{ strtoupper($s->plan) }}</td>
                                <td class="px-4 py-3 text-sm"><x-status-badge :status="$s->status" /></td>
                                <td class="px-4 py-3 text-sm">{{ $s->starts_at?->translatedFormat('d M Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm">{{ $s->expires_at?->translatedFormat('d M Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                                    <button type="button" wire:click="perpanjang({{ $s->id }})" wire:confirm="Perpanjang {{ strtoupper($s->plan) }} {{ $s->user?->nama }} 30 hari?" class="text-xs font-semibold text-teal-600 dark:text-teal-400 hover:underline">+30 hari</button>
                                    <span class="mx-1 text-gray-300">·</span>
                                    <button type="button" wire:click="akhiri({{ $s->id }})" wire:confirm="Akhiri langganan {{ $s->user?->nama }}? Data tetap tersimpan." class="text-xs font-semibold text-rose-600 dark:text-rose-400 hover:underline">Akhiri</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-400">Belum ada langganan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
