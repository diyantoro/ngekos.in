<?php

use App\Models\ChatPesan;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.publik')] class extends Component
{
    public string $tab = 'kos';

    public function mount(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $adaChatKos = ChatPesan::query()
            ->where(function ($q) use ($user) {
                $q->where('anak_kos_id', $user->id)
                    ->orWhereIn('properti_id', \App\Models\Properti::query()
                        ->where('pemilik_id', $user->id)
                        ->select('id'));
            })
            ->exists();

        if ($adaChatKos) {
            return;
        }

        // Jika hanya ada obrolan Pasar Kos, langsung tampilkan tab itu
        // agar pengguna tidak melihat halaman kosong.
        if (\App\Models\BarangPesan::query()->untukUser($user->getKey())->exists()) {
            $this->tab = 'barang';
        }
    }

    protected function percakapanBarang($user)
    {
        $sebagaiPembeli = \App\Models\BarangPesan::query()
            ->where('pembeli_id', $user->id)
            ->selectRaw('barang_kos_id, pembeli_id, MAX(id) as last_id, MAX(created_at) as terakhir')
            ->groupBy('barang_kos_id', 'pembeli_id')
            ->get();

        $sebagaiPenjual = \App\Models\BarangPesan::query()
            ->where('penjual_id', $user->id)
            ->selectRaw('barang_kos_id, pembeli_id, MAX(id) as last_id, MAX(created_at) as terakhir')
            ->groupBy('barang_kos_id', 'pembeli_id')
            ->get();

        $ringkas = $sebagaiPembeli
            ->concat($sebagaiPenjual)
            ->unique(fn ($r) => $r->barang_kos_id.'-'.$r->pembeli_id)
            ->sortByDesc('terakhir')
            ->take(50)
            ->values();

        if ($ringkas->isEmpty()) {
            return collect();
        }

        $terakhir = \App\Models\BarangPesan::query()
            ->whereIn('id', $ringkas->pluck('last_id'))
            ->with([
                'barangKos:id,nama,foto,foto_tambahan,harga,kota,user_id',
                'pembeli:id,nama',
                'penjual:id,nama',
            ])
            ->get()
            ->keyBy('id');

        $belum = \App\Models\BarangPesan::query()
            ->whereIn('barang_kos_id', $ringkas->pluck('barang_kos_id'))
            ->where('pengirim_id', '!=', $user->id)
            ->whereNull('dibaca_pada')
            ->selectRaw('barang_kos_id, pembeli_id, count(*) as total')
            ->groupBy('barang_kos_id', 'pembeli_id')
            ->get()
            ->keyBy(fn ($r) => $r->barang_kos_id.'-'.$r->pembeli_id);

        return $ringkas->map(function ($r) use ($user, $terakhir, $belum) {
            $contoh = $terakhir->get($r->last_id);

            if (! $contoh) {
                return null;
            }

            $sayaPenjual = (int) $contoh->penjual_id === (int) $user->id;

            return [
                'barang' => $contoh->barangKos,
                'lawan' => $sayaPenjual ? $contoh->pembeli : $contoh->penjual,
                'terakhir' => $contoh,
                'belumDibaca' => (int) ($belum->get($contoh->barang_kos_id.'-'.$contoh->pembeli_id)?->total ?? 0),
                'url' => $sayaPenjual
                    ? route('barang.chat.pembeli', ['barang' => $contoh->barang_kos_id, 'pembeli' => $contoh->pembeli_id])
                    : route('barang.chat', ['barang' => $contoh->barang_kos_id]),
            ];
        })->filter()->values();
    }

    public function with(): array
    {
        $user = auth()->user();
        $adalahPenyewa = $user->hasRole('anak_kos');
        $adalahPemilik = $user->hasRole('pemilik');

        $percakapans = collect();

        if ($adalahPenyewa || $adalahPemilik) {
            $dasar = ChatPesan::query();

            if ($adalahPenyewa) {
                $dasar->where('anak_kos_id', $user->id);
            } else {
                $ids = \App\Models\Properti::where('pemilik_id', $user->id)->pluck('id');
                if ($ids->isEmpty()) {
                    $dasar->whereRaw('1 = 0');
                } else {
                    $dasar->whereIn('properti_id', $ids);
                }
            }

            $ringkas = (clone $dasar)
                ->selectRaw('properti_id, anak_kos_id, MAX(id) as last_id, MAX(created_at) as terakhir, SUM(CASE WHEN dibaca_pada IS NULL AND pengirim_id != ? THEN 1 ELSE 0 END) as belum', [$user->id])
                ->groupBy('properti_id', 'anak_kos_id')
                ->orderByDesc('terakhir')
                ->limit(50)
                ->get();

            if ($ringkas->isNotEmpty()) {
                $terakhir = ChatPesan::whereIn('id', $ringkas->pluck('last_id'))
                    ->with(['properti:id,nama,pemilik_id', 'properti.pemilik:id,nama', 'anakKos:id,nama'])
                    ->get()
                    ->keyBy('id');

                $percakapans = $ringkas->map(function ($r) use ($user, $adalahPenyewa, $terakhir) {
                    $contoh = $terakhir->get($r->last_id);

                    if (! $contoh) {
                        return null;
                    }

                    return [
                        'properti' => $contoh->properti,
                        'lawan' => $adalahPenyewa ? $contoh->properti?->pemilik : $contoh->anakKos,
                        'terakhir' => $contoh,
                        'belumDibaca' => (int) $r->belum,
                        'url' => $adalahPenyewa
                            ? route('chat.room', ['properti' => $contoh->properti_id])
                            : route('chat.room.anak', ['properti' => $contoh->properti_id, 'anakKos' => $contoh->anak_kos_id]),
                    ];
                })->filter()->values();
            }
        }

        return [
            'percakapans' => $percakapans,
            'percakapanBarang' => $this->percakapanBarang($user),
            'bisaChat' => $adalahPenyewa || $adalahPemilik,
            'sebagaiPenyewa' => $adalahPenyewa,
        ];
    }
}; ?>

<div class="py-10">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Pesan</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $sebagaiPenyewa ? 'Riwayat percakapanmu dengan pemilik kos.' : 'Pertanyaan penyewa tentang kos Anda.' }}
            </p>
        </div>

        @if (! $bisaChat)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-8 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">Fitur pesan tersedia untuk peran Anak Kos dan Pemilik Kos.</p>
            </div>
        @else
            {{-- Tab jenis percakapan --}}
            <div class="inline-flex items-center gap-1 rounded-xl bg-stone-100 dark:bg-gray-800 p-1">
                <button type="button" wire:click="$set('tab', 'kos')"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-bold transition {{ $tab === 'kos' ? 'bg-white dark:bg-gray-700 text-brand-800 dark:text-brand-200 shadow-sm' : 'text-slate-500 dark:text-gray-400 hover:text-slate-700 dark:hover:text-gray-200' }}">
                    Tanya Kos
                    @php $belumKos = $percakapans->sum('belumDibaca'); @endphp
                    @if ($belumKos > 0)
                        <span class="inline-flex items-center justify-center h-4 min-w-4 px-1 rounded-full bg-rose-500 text-[10px] font-bold text-white">{{ $belumKos > 9 ? '9+' : $belumKos }}</span>
                    @endif
                </button>
                <button type="button" wire:click="$set('tab', 'barang')"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-bold transition {{ $tab === 'barang' ? 'bg-white dark:bg-gray-700 text-brand-800 dark:text-brand-200 shadow-sm' : 'text-slate-500 dark:text-gray-400 hover:text-slate-700 dark:hover:text-gray-200' }}">
                    Pasar Kos
                    @php $belumBarang = $percakapanBarang->sum('belumDibaca'); @endphp
                    @if ($belumBarang > 0)
                        <span class="inline-flex items-center justify-center h-4 min-w-4 px-1 rounded-full bg-rose-500 text-[10px] font-bold text-white">{{ $belumBarang > 9 ? '9+' : $belumBarang }}</span>
                    @endif
                </button>
            </div>

            @if ($tab === 'barang')
                @if ($percakapanBarang->isEmpty())
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-10 text-center">
                        <div class="mx-auto h-14 w-14 rounded-full bg-brand-50 dark:bg-brand-500/10 flex items-center justify-center mb-4">
                            <svg class="h-7 w-7 text-brand-400 dark:text-brand-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                        </div>
                        <p class="font-medium text-gray-700 dark:text-gray-200">Belum ada percakapan barang kos.</p>
                        <p class="mt-1 text-sm text-gray-400 dark:text-gray-500">Buka barang di Pasar Kos lalu klik "Chat di Aplikasi".</p>
                        <a href="{{ route('barang.index') }}" wire:navigate
                           class="mt-5 inline-flex items-center rounded-lg bg-brand-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-800 transition">
                            Jelajahi Pasar Kos
                        </a>
                    </div>
                @else
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($percakapanBarang as $c)
                            <a href="{{ $c['url'] }}" wire:navigate class="flex items-center gap-4 p-4 sm:p-5 hover:bg-brand-50/60 dark:hover:bg-brand-500/10 transition">
                                <img src="{{ $c['barang']?->fotoUrl() }}" alt="{{ $c['barang']?->nama ?? 'Barang' }}"
                                     class="h-12 w-12 shrink-0 rounded-xl object-cover border border-stone-200 dark:border-gray-700">
                                <span class="min-w-0 flex-1">
                                    <span class="flex items-center justify-between gap-2">
                                        <span class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate">{{ $c['lawan']?->nama ?? 'Pengguna terhapus' }}</span>
                                        <span class="shrink-0 text-[11px] text-gray-400 dark:text-gray-500">{{ $c['terakhir']->created_at->translatedFormat('d M, H:i') }}</span>
                                    </span>
                                    <span class="block text-xs font-medium text-brand-600 dark:text-brand-300 truncate">{{ $c['barang']?->nama ?? 'Barang dihapus' }}</span>
                                    <span class="mt-0.5 block text-sm {{ $c['belumDibaca'] > 0 ? 'font-semibold text-gray-800 dark:text-gray-100' : 'text-gray-400 dark:text-gray-500' }} truncate">
                                        {{ \Illuminate\Support\Str::limit($c['terakhir']->pesan, 60) }}
                                    </span>
                                </span>
                                @if ($c['belumDibaca'] > 0)
                                    <span class="shrink-0 inline-flex items-center justify-center h-5 min-w-5 px-1.5 rounded-full bg-rose-500 text-[11px] font-bold text-white">
                                        {{ $c['belumDibaca'] > 9 ? '9+' : $c['belumDibaca'] }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif
            @elseif ($percakapans->isEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-10 text-center">
                    <div class="mx-auto h-14 w-14 rounded-full bg-teal-50 dark:bg-teal-500/10 flex items-center justify-center mb-4">
                        <svg class="h-7 w-7 text-teal-400 dark:text-teal-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                    </div>
                    <p class="font-medium text-gray-700 dark:text-gray-200">Belum ada percakapan.</p>
                    @if ($sebagaiPenyewa)
                        <p class="mt-1 text-sm text-gray-400 dark:text-gray-500">Buka halaman kos lalu klik "Tanya Pemilik" untuk mulai mengobrol.</p>
                        <a href="{{ route('kos.index') }}" wire:navigate
                           class="mt-5 inline-flex items-center rounded-lg bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-500 transition">
                            Jelajahi Kos
                        </a>
                    @else
                        <p class="mt-1 text-sm text-gray-400 dark:text-gray-500">Pertanyaan penyewa tentang kos Anda akan muncul di sini.</p>
                    @endif
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($percakapans as $c)
                        <a href="{{ $c['url'] }}" wire:navigate class="flex items-center gap-4 p-4 sm:p-5 hover:bg-teal-50/50 dark:hover:bg-teal-500/10 transition">
                            <span class="h-12 w-12 shrink-0 rounded-full bg-brand-800 flex items-center justify-center text-base font-bold text-white uppercase">
                                {{ mb_substr($c['lawan']?->nama ?? '?', 0, 1) }}
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate">{{ $c['lawan']?->nama ?? 'Pengguna terhapus' }}</span>
                                    <span class="shrink-0 text-[11px] text-gray-400 dark:text-gray-500">{{ $c['terakhir']->created_at->translatedFormat('d M, H:i') }}</span>
                                </span>
                                <span class="block text-xs font-medium text-teal-500 dark:text-teal-400 truncate">{{ $c['properti']?->nama }}</span>
                                <span class="mt-0.5 block text-sm {{ $c['belumDibaca'] > 0 ? 'font-semibold text-gray-800 dark:text-gray-100' : 'text-gray-400 dark:text-gray-500' }} truncate">
                                    {{ \Illuminate\Support\Str::limit($c['terakhir']->isi, 60) }}
                                </span>
                            </span>
                            @if ($c['belumDibaca'] > 0)
                                <span class="shrink-0 inline-flex items-center justify-center h-5 min-w-5 px-1.5 rounded-full bg-rose-500 text-[11px] font-bold text-white">
                                    {{ $c['belumDibaca'] > 9 ? '9+' : $c['belumDibaca'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</div>
