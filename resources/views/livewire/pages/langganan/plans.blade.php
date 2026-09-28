<?php

use App\Models\SubscriptionRequest;
use App\Services\SubscriptionService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public function mount(): void
    {
        if (! auth()->user()?->hasRole('pemilik')) {
            $this->redirect(route('dashboard'));
        }
    }

    public function klaimTrial(): void
    {
        $hasil = SubscriptionService::klaimTrialFree(auth()->user());

        if (! $hasil) {
            session()->flash('galat', 'Trial tidak dapat diklaim. Mungkin sudah pernah dipakai atau paket Anda bukan Free.');

            return;
        }

        session()->flash('status', 'Trial PRO 7 hari aktif sampai '.$hasil->expires_at?->translatedFormat('d F Y').'. Tanpa kartu kredit.');
    }

    public function with(): array
    {
        $user = auth()->user();
        $plan = SubscriptionService::getPlan($user);
        $isFree = ! SubscriptionService::isExempt($user) && $plan === 'free';

        return [
            'paketAktif' => $plan,
            'pakets' => collect(config('plans', []))->filter(fn ($v) => is_array($v))->all(),
            'isFree' => $isFree,
            'sisaTrial' => SubscriptionService::sisaTrialHari($user),
            'trialHabis' => SubscriptionService::trialExpired($user),
            'bisaKlaim' => SubscriptionService::bisaKlaimTrial($user),
            'permintaan' => SubscriptionRequest::where('user_id', $user->id)
                ->latest('id')
                ->first(),
        ];
    }
}; ?>

<div class="py-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="text-center max-w-xl mx-auto">
            <p class="text-[11px] font-semibold uppercase tracking-widest text-brand-700 dark:text-brand-300">Harga jujur, tanpa biaya tersembunyi</p>
            <h1 class="mt-1 text-xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-gray-100">Pilih Paket Sesuai Bisnis Kosmu</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-gray-400">Paket saat ini: <span class="font-bold text-slate-900 dark:text-gray-100">{{ strtoupper($paketAktif) }}</span> &middot; Bayar via QRIS, paket langsung aktif otomatis.</p>
        </div>

        @if (session('status'))
            <div class="rounded-xl bg-brand-50 dark:bg-brand-500/10 border border-brand-200 dark:border-brand-500/30 px-4 py-3 text-sm text-brand-800 dark:text-brand-200">
                {{ session('status') }}
            </div>
        @endif

        @if (session('galat'))
            <div class="rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 px-4 py-3 text-sm text-red-800 dark:text-red-200">
                {{ session('galat') }}
            </div>
        @endif

        @if ($isFree && ($bisaKlaim ?? false))
            <div class="relative overflow-hidden rounded-2xl bg-brand-800 px-5 py-5 text-white shadow-lg shadow-brand-900/25">
                <div class="absolute -right-8 -top-12 h-36 w-36 rounded-full bg-emerald-400/20"></div>
                <div class="absolute right-20 -bottom-14 h-28 w-28 rounded-full bg-amber-400/25"></div>
                <div class="relative flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex-1">
                        <p class="text-sm font-bold">Coba PRO gratis 7 hari, sekali per akun.</p>
                        <p class="text-xs text-brand-100 mt-0.5">Tanpa kartu kredit. Batal kapan saja, data tetap aman.</p>
                    </div>
                    <button wire:click="klaimTrial" wire:loading.attr="disabled" class="btn-accent shrink-0 !text-xs disabled:opacity-50">
                        Klaim Trial 7 Hari
                    </button>
                </div>
            </div>
        @endif

        @if ($isFree && $sisaTrial !== null)
            <div class="rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 px-4 py-3 text-sm text-amber-800 dark:text-amber-200">
                Masa coba gratis tinggal <strong>{{ $sisaTrial }} hari</strong>. Upgrade ke PRO untuk limit lebih besar &amp; laporan premium.
            </div>
        @elseif ($isFree && $sisaTrial === null && $trialHabis)
            <div class="rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 px-4 py-3 text-sm text-red-800 dark:text-red-200">
                Masa coba 7 hari sudah habis atau belum diklaim. Data tidak hilang, tapi halaman Laporan dikunci. Tambah kos/kamar tetap bisa sampai batas paket Free. Upgrade ke PRO untuk membuka lagi.
            </div>
        @endif

        @php
            $labelFitur = [
                'basic_dashboard' => 'Dashboard dasar',
                'basic_property' => 'Kelola properti',
                'basic_room' => 'Kelola kamar',
                'basic_tenant' => 'Kelola penyewa',
                'basic_billing' => 'Tagihan bulanan',
                'basic_report' => 'Laporan dasar',
                'export_pdf_basic' => 'Unduh PDF dasar',
                'advanced_analytics' => 'Analitik lanjutan',
                'advanced_report' => 'Laporan premium',
                'export_report' => 'Ekspor PDF/Excel',
                'automatic_invoice' => 'Tagihan otomatis',
                'automatic_fine' => 'Denda otomatis',
                'broadcast' => 'Broadcast pengumuman',
                'maintenance' => 'Manajemen perawatan',
                'multi_property' => 'Multi properti',
                'unlimited_property' => 'Properti tanpa batas',
                'unlimited_room' => 'Kamar tanpa batas',
                'laporan_24_bulan' => 'Laporan 24 bulan',
                'excel_7_sheet' => 'Excel 7 sheet lengkap',
            ];
        @endphp
        <p class="text-xs text-slate-400 dark:text-gray-500 text-center md:hidden">Geser ke samping untuk melihat paket lain.</p>
        <div class="flex snap-x snap-mandatory gap-4 overflow-x-auto pb-4 scrollbar-hide md:grid md:grid-cols-3 md:gap-5 md:overflow-visible md:pb-0 md:items-stretch">
            @foreach ($pakets as $key => $paket)
                @php
                    $planStyles = [
                        'free' => [
                            'card' => 'bg-white dark:bg-gray-800 border-stone-200 dark:border-gray-700 shadow-sm',
                            'medal' => 'bg-slate-900 text-white',
                            'name' => 'text-slate-500 dark:text-gray-400',
                            'price' => 'text-slate-900 dark:text-gray-100',
                            'limitBox' => 'bg-stone-100 dark:bg-gray-700/60',
                            'limitNum' => 'text-slate-900 dark:text-gray-100',
                            'check' => 'bg-emerald-600 text-white',
                            'btn' => 'btn-secondary w-full',
                            'tab' => null,
                        ],
                        'pro' => [
                            'card' => 'bg-gradient-to-b from-brand-50 via-white to-white dark:from-brand-500/10 dark:via-gray-800 dark:to-gray-800 border-2 border-brand-600 dark:border-brand-500 shadow-xl shadow-brand-900/15',
                            'medal' => 'bg-brand-600 text-white shadow-md shadow-brand-600/40',
                            'name' => 'text-brand-700 dark:text-brand-300',
                            'price' => 'text-brand-800 dark:text-brand-100',
                            'limitBox' => 'bg-brand-600/10 dark:bg-brand-500/10 border border-brand-600/15 dark:border-brand-500/20',
                            'limitNum' => 'text-brand-800 dark:text-brand-100',
                            'check' => 'bg-brand-600 text-white',
                            'btn' => 'btn-primary w-full',
                            'tab' => 'PALING POPULER',
                        ],
                        'business' => [
                            'card' => 'bg-brand-950 dark:bg-gray-800 border-brand-950 dark:border-gray-700 shadow-xl shadow-brand-950/30',
                            'medal' => 'bg-amber-400 text-brand-950 shadow-md shadow-amber-400/30',
                            'name' => 'text-amber-300',
                            'price' => 'text-white',
                            'limitBox' => 'bg-white/10 border border-white/10',
                            'limitNum' => 'text-amber-300',
                            'check' => 'bg-amber-400 text-brand-950',
                            'btn' => 'btn-accent w-full',
                            'tab' => 'UNTUK BISNIS BESAR',
                        ],
                    ];
                    $c = $planStyles[$key] ?? $planStyles['free'];
                    $isActive = $paketAktif === $key;
                    $gelap = $key === 'business';
                    $teksIsi = $gelap ? 'text-brand-100/90' : 'text-slate-600 dark:text-gray-300';
                    $teksKuat = $gelap ? 'text-white' : 'text-slate-900 dark:text-gray-100';
                @endphp
                <div class="relative flex h-full w-[84%] shrink-0 snap-center flex-col overflow-hidden rounded-2xl border sm:w-[62%] md:w-auto {{ $c['card'] }} {{ $isActive ? 'ring-2 ring-offset-2 ring-brand-600 dark:ring-offset-gray-900' : '' }}">
                    @if ($key === 'business')
                        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-amber-400/10 pointer-events-none"></div>
                        <div class="absolute -left-12 -bottom-14 h-44 w-44 rounded-full bg-emerald-400/10 pointer-events-none"></div>
                    @endif
                    @if ($c['tab'])
                        <div class="relative shrink-0 px-3 py-2 text-center text-[10px] font-bold tracking-widest {{ $key === 'business' ? 'bg-amber-400 text-brand-950' : 'bg-brand-700 text-white' }}">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 1.5l2.6 5.3 5.9.9-4.2 4.1 1 5.8L10 14.9l-5.3 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z" /></svg>
                                {{ $c['tab'] }}
                            </span>
                        </div>
                    @endif

                    <div class="relative flex flex-1 flex-col p-6">
                    <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $c['medal'] }}">
                            @if ($key === 'business')
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" /></svg>
                            @elseif ($key === 'pro')
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" /></svg>
                            @else
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                            @endif
                        </span>
                        <div>
                            <p class="text-sm font-bold uppercase tracking-widest {{ $c['name'] }}">{{ $paket['name'] }}</p>
                            <p class="text-[11px] {{ $gelap ? 'text-brand-200/70' : 'text-slate-400 dark:text-gray-500' }}">
                                @if ($key === 'free') Mulai tanpa modal @elseif ($key === 'pro') Buat kos yang bertumbuh @else Kelola banyak properti @endif
                            </p>
                        </div>
                        @if ($isActive)
                            <span class="shrink-0 inline-flex items-center gap-1 self-start rounded-full bg-brand-700 px-2.5 py-1 text-[10px] font-bold text-white">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                Paketmu
                            </span>
                        @endif
                    </div>
                    </div>

                    <p class="mt-4 flex items-baseline gap-1">
                        @if (($paket['price'] ?? 0) > 0)
                            <span class="text-3xl font-bold tracking-tight {{ $c['price'] }}">Rp{{ number_format($paket['price'], 0, ',', '.') }}</span>
                            <span class="text-sm font-medium {{ $gelap ? 'text-brand-200/70' : 'text-slate-400 dark:text-gray-500' }}">/bulan</span>
                        @else
                            <span class="text-3xl font-bold tracking-tight {{ $c['price'] }}">Gratis</span>
                            <span class="text-sm font-medium {{ $gelap ? 'text-brand-200/70' : 'text-slate-400 dark:text-gray-500' }}">selamanya</span>
                        @endif
                    </p>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        @foreach ([['Properti', $paket['limits']['properties'] ?? null], ['Kamar', $paket['limits']['rooms'] ?? null]] as [$labelLimit, $nilaiLimit])
                            <div class="rounded-xl {{ $c['limitBox'] }} px-2 py-2.5 text-center">
                                @if ($nilaiLimit === null)
                                    <p class="text-xs font-bold leading-6 {{ $c['limitNum'] }}">Tanpa batas</p>
                                @else
                                    <p class="text-xl font-bold {{ $c['limitNum'] }}">{{ $nilaiLimit }}</p>
                                @endif
                                <p class="text-[10px] font-semibold uppercase tracking-wider {{ $gelap ? 'text-brand-200/70' : 'text-slate-400 dark:text-gray-500' }}">{{ $labelLimit }}</p>
                            </div>
                        @endforeach
                    </div>

                    <ul class="mt-5 flex-1 space-y-2.5 text-sm {{ $teksIsi }}">
                        @if ($key === 'pro')
                            <li class="flex min-w-0 items-start gap-2.5">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full {{ $c['check'] }}">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                </span>
                                <span class="min-w-0 break-words"><strong class="{{ $teksKuat }}">Semua fitur Free</strong>, plus:</span>
                            </li>
                        @elseif ($key === 'business')
                            <li class="flex min-w-0 items-start gap-2.5">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full {{ $c['check'] }}">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                </span>
                                <span class="min-w-0 break-words"><strong class="{{ $teksKuat }}">Semua fitur Pro</strong>, plus:</span>
                            </li>
                        @endif
                        @foreach ($paket['features'] ?? [] as $fitur)
                            <li class="flex min-w-0 items-start gap-2.5">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full {{ $c['check'] }}">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                </span>
                                <span class="min-w-0 break-words">{{ $labelFitur[$fitur] ?? str($fitur)->replace('_', ' ')->title() }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-6 {{ $gelap ? 'border-white/10' : 'border-stone-200 dark:border-gray-700' }} border-t pt-5">
                        @if ($isActive)
                            <span class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg {{ $gelap ? 'bg-white/10 text-white' : 'bg-brand-50 dark:bg-brand-500/10 text-brand-800 dark:text-brand-200' }} px-4 py-2.5 text-xs font-bold">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                Paket Aktif
                            </span>
                        @elseif ($permintaan?->status === 'pending')
                            <span class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-amber-50 dark:bg-amber-500/10 px-4 py-2.5 text-xs font-bold text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30">
                                Menunggu Persetujuan
                            </span>
                        @else
                            <a href="{{ route('langganan.bayar', $key) }}" wire:navigate class="{{ $c['btn'] }}">
                                @if ($key === 'free') Pilih Gratis @else Upgrade ke {{ $paket['name'] }} @endif
                            </a>
                        @endif
                    </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-x-8 gap-y-2 pt-2 text-xs text-slate-500 dark:text-gray-400">
            <span class="inline-flex items-center gap-1.5">
                <svg class="h-4 w-4 text-brand-700 dark:text-brand-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Bayar via QRIS, aktif otomatis
            </span>
            <span class="inline-flex items-center gap-1.5">
                <svg class="h-4 w-4 text-brand-700 dark:text-brand-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Data aman, tidak hilang saat ganti paket
            </span>
            <span class="inline-flex items-center gap-1.5">
                <svg class="h-4 w-4 text-brand-700 dark:text-brand-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Batal kapan saja
            </span>
        </div>
        <p class="text-center text-[11px] text-slate-400 dark:text-gray-500 max-w-2xl mx-auto">
            Jumlah penyewa tidak dibatasi paket.
        </p>
    </div>
</div>

<style>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>
