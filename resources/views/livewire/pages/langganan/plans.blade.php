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
        <div class="text-center max-w-2xl mx-auto">
            @if ($isFree && ($bisaKlaim ?? false))
                <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-teal-600 to-emerald-600 px-4 py-1.5 text-xs font-extrabold tracking-wide text-white shadow-lg shadow-teal-600/30">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H4.5a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                    PROMO: TRIAL PRO 7 HARI GRATIS
                </span>
            @else
                <p class="text-[11px] font-semibold uppercase tracking-widest text-brand-700 dark:text-brand-300">Harga jujur, tanpa biaya tersembunyi</p>
            @endif
            <h1 class="mt-3 text-2xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-gray-100">Naikkan Omzet Kos dengan Paket yang Tepat</h1>
            <p class="mt-2 text-sm sm:text-base text-slate-500 dark:text-gray-400">Mulai gratis, upgrade kapan saja. Paket saat ini: <span class="font-bold text-teal-700 dark:text-teal-300">{{ strtoupper($paketAktif) }}</span> &middot; Bayar via QRIS, paket langsung aktif otomatis.</p>
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
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-700 via-emerald-700 to-teal-800 px-6 py-6 sm:px-8 sm:py-7 text-white shadow-xl shadow-teal-900/30">
                <div class="absolute -right-10 -top-16 h-48 w-48 rounded-full bg-white/10"></div>
                <div class="absolute right-24 -bottom-16 h-36 w-36 rounded-full bg-amber-300/20"></div>
                <div class="absolute left-1/2 -bottom-20 h-40 w-72 rounded-full bg-emerald-300/10"></div>
                <div class="relative flex flex-col lg:flex-row lg:items-center gap-5">
                    <span class="hidden sm:flex h-16 w-16 shrink-0 items-center justify-center rounded-3xl bg-white/15 ring-1 ring-white/30 backdrop-blur">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H4.5a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                    </span>
                    <div class="flex-1">
                        <p class="text-lg sm:text-xl font-extrabold tracking-tight">Rasakan Semua Fitur PRO, Gratis 7 Hari</p>
                        <ul class="mt-2.5 flex flex-wrap gap-x-5 gap-y-1.5 text-sm text-teal-50">
                            <li class="inline-flex items-center gap-1.5"><svg class="h-4 w-4 text-amber-300" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>Laporan premium</li>
                            <li class="inline-flex items-center gap-1.5"><svg class="h-4 w-4 text-amber-300" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>Tagihan &amp; denda otomatis</li>
                            <li class="inline-flex items-center gap-1.5"><svg class="h-4 w-4 text-amber-300" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>Ekspor PDF/Excel</li>
                        </ul>
                        <p class="mt-2 text-xs text-teal-100/80">Tanpa kartu kredit &middot; Sekali per akun &middot; Data tetap aman setelah trial berakhir</p>
                    </div>
                    <button wire:click="klaimTrial" wire:loading.attr="disabled" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-2xl bg-white px-6 py-3.5 text-sm font-extrabold text-teal-800 shadow-lg transition-all duration-200 hover:-translate-y-0.5 hover:shadow-xl disabled:opacity-50">
                        <span wire:loading.remove wire:target="klaimTrial">Klaim Trial 7 Hari</span>
                        <span wire:loading wire:target="klaimTrial">Memproses...</span>
                        <svg wire:loading.remove wire:target="klaimTrial" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12l-7.5 7.5M21 12H3" /></svg>
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
                'unlimited_room' => 'Kamar tanpa batas',
                'laporan_24_bulan' => 'Laporan 24 bulan',
                'excel_7_sheet' => 'Excel 7 sheet lengkap',
            ];
        @endphp
        <p class="text-xs text-slate-400 dark:text-gray-500 text-center md:hidden">Geser ke samping untuk melihat paket lain.</p>
        <div class="flex snap-x snap-mandatory gap-4 overflow-x-auto pb-4 scrollbar-hide md:grid md:grid-cols-3 md:gap-6 md:overflow-visible md:pb-0 md:items-stretch">
            @foreach ($pakets as $key => $paket)
                @php
                    $planStyles = [
                        'free' => [
                            'wrap' => 'bg-white dark:bg-gray-800 border-slate-200 dark:border-gray-500 shadow-[0_8px_30px_rgba(15,23,42,0.06)] dark:shadow-[0_12px_36px_rgba(0,0,0,0.5)] hover:shadow-[0_16px_44px_rgba(15,23,42,0.12)] dark:hover:shadow-[0_16px_48px_rgba(0,0,0,0.6)]',
                            'topbar' => 'from-slate-400 via-slate-500 to-slate-700',
                            'medal' => 'bg-gradient-to-br from-slate-600 to-slate-800 dark:from-slate-500 dark:to-slate-700 text-white shadow-lg shadow-slate-900/25 dark:shadow-black/50 ring-1 ring-white/20',
                            'name' => 'text-slate-500 dark:text-gray-300',
                            'price' => 'text-slate-900 dark:text-gray-100',
                            'limitBox' => 'bg-slate-50 dark:bg-gray-700/60 border border-slate-100 dark:border-gray-600',
                            'limitNum' => 'text-slate-900 dark:text-gray-100',
                            'check' => 'bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-sm shadow-emerald-500/30',
                            'btn' => 'btn-secondary w-full !rounded-xl !py-3 !font-bold hover:!-translate-y-0.5 hover:shadow-lg transition-all duration-200',
                            'tab' => null,
                            'tabCls' => '',
                            'tagline' => 'Mulai tanpa modal',
                            'desc' => 'Cocok untuk 1 kos pertama',
                            'scale' => '',
                        ],
                        'pro' => [
                            'wrap' => 'bg-gradient-to-b from-teal-50 via-white to-white dark:from-teal-900/60 dark:via-gray-800 dark:to-gray-900 border-2 border-teal-500 dark:border-teal-300 shadow-[0_24px_70px_rgba(13,148,136,0.35)] dark:shadow-[0_24px_70px_rgba(45,212,191,0.4)] hover:shadow-[0_28px_80px_rgba(13,148,136,0.45)] dark:hover:shadow-[0_28px_80px_rgba(45,212,191,0.5)] ring-2 ring-teal-500/30 dark:ring-teal-300/50',
                            'topbar' => 'from-teal-400 via-emerald-500 to-cyan-500',
                            'medal' => 'bg-gradient-to-br from-teal-400 via-teal-500 to-emerald-600 text-white shadow-xl shadow-teal-500/50 ring-2 ring-teal-200 dark:ring-teal-400/40',
                            'name' => 'text-teal-700 dark:text-teal-300',
                            'price' => 'text-slate-900 dark:text-gray-100',
                            'limitBox' => 'bg-gradient-to-b from-teal-50 to-emerald-50/60 dark:from-teal-500/10 dark:to-emerald-500/5 border border-teal-100 dark:border-teal-500/25',
                            'limitNum' => 'text-teal-800 dark:text-teal-100',
                            'check' => 'bg-gradient-to-br from-teal-500 to-emerald-600 text-white shadow-sm shadow-teal-500/40',
                            'btn' => 'w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-teal-500 via-emerald-600 to-teal-600 bg-[length:200%_auto] px-4 py-3.5 text-[15px] font-extrabold text-white shadow-xl shadow-teal-600/40 ring-1 ring-white/40 transition-all duration-200 hover:-translate-y-0.5 hover:bg-right hover:shadow-2xl active:translate-y-0',
                            'tab' => 'PALING POPULER',
                            'tabCls' => 'bg-gradient-to-r from-teal-600 via-emerald-500 to-teal-600 text-white shadow-lg shadow-teal-600/40 ring-1 ring-white/40',
                            'tagline' => 'Buat kos yang bertumbuh',
                            'desc' => 'Terlaris Â· laporan premium + tagihan otomatis',
                            'scale' => '',
                        ],
                        'business' => [
                            'wrap' => 'bg-gradient-to-b from-slate-900 via-slate-900 to-[#0c2b26] dark:from-[#0f172a] dark:via-[#0b1220] dark:to-[#0c2b26] border border-slate-900 dark:border-amber-200/50 shadow-[0_18px_50px_rgba(2,6,23,0.4)] dark:shadow-[0_24px_70px_rgba(251,191,36,0.25)] hover:shadow-[0_24px_64px_rgba(2,6,23,0.5)] dark:hover:shadow-[0_28px_80px_rgba(251,191,36,0.32)] dark:ring-1 dark:ring-amber-200/30 text-white',
                            'topbar' => 'from-amber-300 via-amber-400 to-orange-400',
                            'medal' => 'bg-gradient-to-br from-amber-300 to-orange-400 text-slate-900 shadow-lg shadow-amber-400/30 ring-1 ring-white/30',
                            'name' => 'text-amber-300',
                            'price' => 'text-white',
                            'limitBox' => 'bg-white/[0.07] border border-white/15 backdrop-blur',
                            'limitNum' => 'text-amber-300',
                            'check' => 'bg-gradient-to-br from-amber-300 to-amber-500 text-slate-900 shadow-sm shadow-amber-400/40',
                            'btn' => 'w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-400 to-orange-500 px-4 py-3 text-sm font-bold text-slate-900 shadow-lg shadow-amber-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-xl hover:brightness-105 active:translate-y-0',
                            'tab' => 'UNTUK BISNIS BESAR',
                            'tabCls' => 'bg-gradient-to-r from-amber-300 to-amber-400 text-slate-900',
                            'tagline' => 'Kelola banyak properti',
                            'desc' => 'Tanpa batas + Excel 7 sheet',
                            'scale' => '',
                        ],
                    ];
                    $c = $planStyles[$key] ?? $planStyles['free'];
                    $isActive = $paketAktif === $key;
                    $gelap = $key === 'business';
                    $teksIsi = $gelap ? 'text-slate-200/90' : 'text-slate-600 dark:text-gray-300';
                    $teksKuat = $gelap ? 'text-white' : 'text-slate-900 dark:text-gray-100';
                    $subtle = $gelap ? 'text-slate-300' : 'text-slate-500 dark:text-gray-400';
                @endphp
                <div class="group relative flex h-full w-[84%] shrink-0 snap-center flex-col overflow-hidden rounded-3xl border transition-all duration-300 hover:-translate-y-1 sm:w-[62%] md:w-auto {{ $c['wrap'] }} {{ $isActive ? 'ring-2 ring-offset-2 ring-teal-500 dark:ring-offset-gray-900' : '' }}">
                    <div class="h-1.5 w-full bg-gradient-to-r {{ $c['topbar'] }}"></div>
                    <div class="relative flex h-10 shrink-0 items-center justify-center px-4">
                        @if ($c['tab'])
                            <span class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-[10px] font-extrabold tracking-[0.18em] shadow-md {{ $c['tabCls'] }}">
                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 1.5l2.6 5.3 5.9.9-4.2 4.1 1 5.8L10 14.9l-5.3 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z" /></svg>
                                {{ $c['tab'] }}
                            </span>
                        @else
                            <span aria-hidden="true" class="invisible inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-[10px] font-extrabold tracking-[0.18em]">placeholder</span>
                        @endif
                    </div>

                    <div class="relative flex flex-1 flex-col p-6">
                    <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-3">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3 {{ $c['medal'] }}">
                            @if ($key === 'business')
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" /></svg>
                            @elseif ($key === 'pro')
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" /></svg>
                            @else
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                            @endif
                        </span>
                        <div>
                            <p class="text-sm font-extrabold uppercase tracking-[0.16em] {{ $c['name'] }}">{{ $paket['name'] }}</p>
                            <p class="text-xs font-medium {{ $subtle }}">
                                {{ $c['tagline'] }}
                            </p>
                        </div>
                    </div>
                        @if ($isActive)
                            <span class="shrink-0 inline-flex items-center gap-1 self-start rounded-full bg-gradient-to-r from-teal-600 to-emerald-600 px-2.5 py-1 text-[10px] font-bold text-white shadow-md shadow-teal-600/30">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                Paketmu
                            </span>
                        @endif
                    </div>
                    <p class="mt-1 min-h-4 text-xs {{ $subtle }}">{{ $c['desc'] }}</p>

                    <div class="mt-3 min-h-[86px]">
                        <p class="flex items-baseline gap-1.5">
                            @if (($paket['price'] ?? 0) > 0)
                                <span class="text-[11px] font-bold uppercase tracking-wider {{ $subtle }}">Rp</span>
                                <span class="text-4xl font-extrabold tracking-tight {{ $c['price'] }}">{{ number_format($paket['price'], 0, ',', '.') }}</span>
                                <span class="text-sm font-medium {{ $subtle }}">/bulan</span>
                            @else
                                <span class="text-4xl font-extrabold tracking-tight {{ $c['price'] }}">Gratis</span>
                                <span class="text-sm font-medium {{ $subtle }}">selamanya</span>
                            @endif
                        </p>
                        @if (($paket['price'] ?? 0) > 0)
                            <p class="mt-1.5 inline-flex w-fit items-center gap-1 rounded-full {{ $gelap ? 'bg-white/10 text-amber-200' : 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300' }} px-2.5 py-0.5 text-[11px] font-bold">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Setara Rp{{ number_format(round(($paket['price'] ?? 0) / 30), 0, ',', '.') }}/hari
                            </p>
                        @else
                            <p class="mt-1.5 inline-flex w-fit items-center gap-1 rounded-full bg-slate-100 dark:bg-gray-700 px-2.5 py-0.5 text-[11px] font-bold text-slate-600 dark:text-gray-300">
                                Tanpa kartu kredit
                            </p>
                        @endif
                    </div>

                    <div class="mt-4 flex justify-center gap-2.5">
                        @foreach ([['Kamar', $paket['limits']['rooms'] ?? null, 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z']] as [$labelLimit, $nilaiLimit, $ikonLimit])
                        <div class="rounded-2xl {{ $c['limitBox'] }} px-4 py-3 text-center transition-transform duration-200 group-hover:scale-[1.02] min-w-[140px]">
                                @if ($nilaiLimit === null)
                                    <p class="inline-flex items-center gap-1 text-sm font-extrabold {{ $c['limitNum'] }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" /></svg>
                                        Unlimited
                                    </p>
                                @else
                                    <p class="text-2xl font-extrabold tabular-nums {{ $c['limitNum'] }}">{{ $nilaiLimit }}</p>
                                @endif
                                <p class="text-[10px] font-bold uppercase tracking-[0.14em] {{ $subtle }}">{{ $labelLimit }}</p>
                            </div>
                        @endforeach
                    </div>

                    <ul class="mt-5 flex-1 space-y-2.5 pb-6 text-sm {{ $teksIsi }}">
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

                    <div class="mt-auto {{ $gelap ? 'border-white/10' : 'border-slate-100 dark:border-gray-700' }} border-t pt-5">
                        @if ($isActive)
                            <span class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl {{ $gelap ? 'bg-white/10 text-white ring-1 ring-white/20' : 'bg-teal-50 dark:bg-teal-500/10 text-teal-800 dark:text-teal-200 ring-1 ring-teal-100 dark:ring-teal-500/30' }} px-4 py-3 text-xs font-extrabold uppercase tracking-wider">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                Paket Aktif
                            </span>
                        @elseif ($permintaan?->status === 'pending')
                            <span class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-amber-50 dark:bg-amber-500/10 px-4 py-3 text-xs font-bold text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30">
                                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                                Menunggu Persetujuan
                            </span>
                        @else
                            <a href="{{ route('langganan.bayar', $key) }}" wire:navigate class="{{ $c['btn'] }}">
                                @if ($key === 'free')
                                    Pilih Gratis
                                @else
                                    Upgrade ke {{ $paket['name'] }}
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12l-7.5 7.5M21 12H3" /></svg>
                                @endif
                            </a>
                        @endif
                    </div>
                    </div>
                </div>
            @endforeach
        </div>

        @php
            $kolomPaket = [
                'free' => ['nama' => $pakets['free']['name'] ?? 'Free', 'sub' => 'Gratis selamanya'],
                'pro' => ['nama' => $pakets['pro']['name'] ?? 'Pro', 'sub' => 'Rp'.number_format($pakets['pro']['price'] ?? 0, 0, ',', '.').'/bulan'],
                'business' => ['nama' => $pakets['business']['name'] ?? 'Business', 'sub' => 'Rp'.number_format($pakets['business']['price'] ?? 0, 0, ',', '.').'/bulan'],
            ];
            $barisBanding = [
                ['jenis' => 'teks', 'label' => 'Jumlah kamar', 'free' => '1', 'pro' => '5', 'business' => '10'],
            ];
            foreach (($pakets['free']['features'] ?? []) as $f) {
                $barisBanding[] = ['jenis' => 'cek', 'label' => $labelFitur[$f] ?? $f, 'free' => true, 'pro' => true, 'business' => true];
            }
            foreach (($pakets['pro']['features'] ?? []) as $f) {
                $barisBanding[] = ['jenis' => 'cek', 'label' => $labelFitur[$f] ?? $f, 'free' => false, 'pro' => true, 'business' => true];
            }
            foreach (($pakets['business']['features'] ?? []) as $f) {
                $barisBanding[] = ['jenis' => 'cek', 'label' => $labelFitur[$f] ?? $f, 'free' => false, 'pro' => false, 'business' => true];
            }
        @endphp

        <div class="pt-4">
            <h2 class="text-center text-lg font-bold tracking-tight text-slate-900 dark:text-gray-100">Bandingkan Fitur Lengkap</h2>
            <p class="mt-1 text-center text-sm text-slate-500 dark:text-gray-400">Paket Pro mencakup semua fitur Free, paket Business mencakup semua fitur Pro.</p>
            <div class="mt-4 overflow-x-auto rounded-3xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                <table class="w-full min-w-[620px] border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-gray-700">
                            <th class="p-4 text-left font-semibold text-slate-500 dark:text-gray-400">Fitur</th>
                            @foreach (['free', 'pro', 'business'] as $kolom)
                                <th class="p-4 text-center {{ $kolom === 'pro' ? 'bg-teal-50/70 dark:bg-teal-500/10' : '' }}">
                                    <span class="block font-extrabold {{ $kolom === 'business' ? 'text-amber-600 dark:text-amber-400' : ($kolom === 'pro' ? 'text-teal-700 dark:text-teal-300' : 'text-slate-700 dark:text-gray-200') }}">{{ $kolomPaket[$kolom]['nama'] }}</span>
                                    <span class="mt-0.5 block text-xs font-medium text-slate-400 dark:text-gray-500">{{ $kolomPaket[$kolom]['sub'] }}</span>
                                    @if ($kolom === 'pro')
                                        <span class="mt-1 inline-block rounded-full bg-gradient-to-r from-teal-600 to-emerald-600 px-2.5 py-0.5 text-[10px] font-extrabold tracking-wider text-white">PALING POPULER</span>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($barisBanding as $baris)
                            <tr class="border-b border-slate-100 dark:border-gray-700/60 last:border-0 hover:bg-slate-50/70 dark:hover:bg-gray-700/30">
                                <td class="p-3.5 pl-4 font-medium text-slate-600 dark:text-gray-300">{{ $baris['label'] }}</td>
                                @foreach (['free', 'pro', 'business'] as $kolom)
                                    <td class="p-3.5 text-center {{ $kolom === 'pro' ? 'bg-teal-50/70 dark:bg-teal-500/10' : '' }}">
                                        @if ($baris['jenis'] === 'teks')
                                            <span class="font-extrabold tabular-nums {{ $kolom === 'business' ? 'text-amber-600 dark:text-amber-400' : ($kolom === 'pro' ? 'text-teal-700 dark:text-teal-300' : 'text-slate-700 dark:text-gray-200') }}">{{ $baris[$kolom] }}</span>
                                        @elseif ($baris[$kolom])
                                            <svg class="mx-auto h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                        @else
                                            <svg class="mx-auto h-5 w-5 text-slate-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3.5">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white shadow-md shadow-teal-500/25">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v.003c0 .621-.504 1.125-1.125 1.125H5.625c-.621 0-1.125-.504-1.125-1.125v-.003zM3.75 9.75c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v.003c0 .621-.504 1.125-1.125 1.125H5.625c-.621 0-1.125-.504-1.125-1.125v-.003zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v.003c0 .621-.504 1.125-1.125 1.125H5.625c-.621 0-1.125-.504-1.125-1.125v-.003zM14.25 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-4.5c-.621 0-1.125-.504-1.125-1.125v-9.75z" /></svg>
                </span>
                <span><span class="block text-sm font-bold text-slate-900 dark:text-gray-100">Bayar via QRIS</span><span class="block text-xs text-slate-500 dark:text-gray-400">Paket langsung aktif otomatis</span></span>
            </div>
            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3.5">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 text-white shadow-md shadow-cyan-500/25">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                </span>
                <span><span class="block text-sm font-bold text-slate-900 dark:text-gray-100">Data selalu aman</span><span class="block text-xs text-slate-500 dark:text-gray-400">Tidak hilang saat ganti paket</span></span>
            </div>
            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3.5">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-md shadow-amber-500/25">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                </span>
                <span><span class="block text-sm font-bold text-slate-900 dark:text-gray-100">Fleksibel</span><span class="block text-xs text-slate-500 dark:text-gray-400">Upgrade &amp; batal kapan saja</span></span>
            </div>
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













