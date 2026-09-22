<?php

use App\Models\SubscriptionRequest;
use App\Services\SubscriptionService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public function with(): array
    {
        $user = auth()->user();
        $subscription = SubscriptionService::getSubscription($user);
        $plan = SubscriptionService::getPlan($user);
        $isFree = ! SubscriptionService::isExempt($user) && $plan === 'free';

        return [
            'plan' => $plan,
            'subscription' => $subscription,
            'propertyUsed' => SubscriptionService::usage($user, 'property'),
            'propertyLimit' => SubscriptionService::limitFor($plan, 'property'),
            'roomUsed' => SubscriptionService::usage($user, 'room'),
            'roomLimit' => SubscriptionService::limitFor($plan, 'room'),
            'riwayat' => SubscriptionService::history($user),
            'isFree' => $isFree,
            'sisaTrial' => SubscriptionService::sisaTrialHari($user),
            'trialHabis' => SubscriptionService::trialExpired($user),
            'permintaan' => SubscriptionRequest::where('user_id', $user->id)
                ->where('status', 'pending')
                ->latest('id')
                ->first(),
        ];
    }
}; ?>

<div class="py-10">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">Langganan Saya</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Status paket dan penggunaan limit Anda.</p>
        </div>

        @if ($permintaan)
            <div class="flex items-start justify-between gap-3 rounded-xl bg-amber-50 dark:bg-amber-500/10 ring-1 ring-amber-200 dark:ring-amber-500/30 px-4 py-3 text-sm text-amber-800 dark:text-amber-200">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2 w-2 shrink-0 mt-1">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                    </span>
                    <span>Permintaan upgrade ke <strong>{{ strtoupper($permintaan->requested_plan) }}</strong> sedang menunggu persetujuan admin.</span>
                </div>
                <a href="{{ route('langganan.plans') }}" wire:navigate class="shrink-0 font-bold hover:underline">Lihat Paket</a>
            </div>
        @endif

        @if ($isFree && $sisaTrial !== null)
            <div class="rounded-xl bg-amber-50 dark:bg-amber-500/10 ring-1 ring-amber-200 dark:ring-amber-500/30 px-4 py-3 text-sm text-amber-800 dark:text-amber-200">
                Masa coba gratis tinggal <strong>{{ $sisaTrial }} hari</strong>. Upgrade ke PRO untuk limit lebih besar & laporan premium.
                <a href="{{ route('langganan.plans') }}" wire:navigate class="font-bold hover:underline">Upgrade</a>
            </div>
        @elseif ($isFree && $sisaTrial === null && $trialHabis)
            <div class="rounded-xl bg-rose-50 dark:bg-rose-500/10 ring-1 ring-rose-200 dark:ring-rose-500/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                Masa coba 7 hari sudah habis. Data tidak hilang, tapi tambah kos/kamar & halaman Laporan dikunci.
                <a href="{{ route('langganan.plans') }}" wire:navigate class="font-bold hover:underline">Upgrade ke PRO</a>
            </div>
        @endif

        <div class="card p-6 space-y-3">
            <div class="flex items-center justify-between gap-3">
                <p class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">{{ strtoupper($plan) }}</p>
                <x-status-badge :status="$subscription?->status ?? 'active'" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Tanggal mulai</p>
                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $subscription?->starts_at?->translatedFormat('d F Y') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Tanggal berakhir</p>
                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $subscription?->expires_at?->translatedFormat('d F Y') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Penggunaan property</p>
                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $propertyUsed }} / {{ $propertyLimit ?? '∞' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Penggunaan kamar</p>
                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $roomUsed }} / {{ $roomLimit ?? '∞' }}</p>
                </div>
            </div>
            <div class="pt-2">
                <a href="{{ route('langganan.plans') }}" wire:navigate class="btn-primary inline-flex items-center rounded-xl px-4 py-2 text-xs font-semibold text-white">Lihat Paket</a>
            </div>
        </div>

        <div class="card p-6">
            <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">Riwayat Langganan</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Paket, tanggal, dan status Anda sejauh ini.</p>
            <div class="mt-4 divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($riwayat as $r)
                    <div class="py-3 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ strtoupper($r->plan) }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $r->starts_at?->translatedFormat('d M Y') ?? '—' }} → {{ $r->expires_at?->translatedFormat('d M Y') ?? '—' }}</p>
                        </div>
                        <x-status-badge :status="$r->status" />
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-gray-400">Belum ada riwayat. Anda menggunakan paket FREE.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
