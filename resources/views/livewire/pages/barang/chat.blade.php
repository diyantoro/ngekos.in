<?php

use App\Models\BarangKos;
use App\Models\BarangPesan;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.publik')] class extends Component
{
    public BarangKos $barang;

    public ?User $pembeli = null;

    public string $pesanBaru = '';

    public function mount(BarangKos $barang, ?User $pembeli = null): void
    {
        $this->barang = $barang->loadMissing(['user', 'properti']);

        $user = auth()->user();

        if ($this->isPenjual($user)) {
            if ($pembeli) {
                abort_unless($this->adaPercakapan($pembeli->id), 404);
                $this->pembeli = $pembeli;
            }

            return;
        }

        // Pembeli selalu dipaksa menjadi user yang login (cegah tampering payload).
        abort_if($pembeli && (int) $pembeli->id !== (int) $user->id, 403);
        abort_if((int) $this->barang->user_id === (int) $user->id, 403);
        $this->pembeli = $user;
    }

    protected function isPenjual(?User $user): bool
    {
        return $user && (int) $this->barang->user_id === (int) $user->id;
    }

    protected function adaPercakapan(int $pembeliId): bool
    {
        return BarangPesan::query()
            ->where('barang_kos_id', $this->barang->id)
            ->where('pembeli_id', $pembeliId)
            ->exists();
    }

    protected function berhak(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($this->isPenjual($user)) {
            // Penjual tanpa pembeli tertentu = daftar percakapan.
            if (! $this->pembeli) {
                return true;
            }

            return (int) $this->pembeli->id !== (int) $user->id
                && $this->adaPercakapan($this->pembeli->id);
        }

        return $this->pembeli
            && (int) $this->pembeli->id === (int) $user->id;
    }

    public function with(): array
    {
        abort_unless($this->berhak(), 403);

        $user = auth()->user();
        $sebagaiPenjual = $this->isPenjual($user);
        $daftarPercakapan = collect();
        $pesans = collect();

        if ($sebagaiPenjual && ! $this->pembeli) {
            $ringkas = BarangPesan::query()
                ->where('barang_kos_id', $this->barang->id)
                ->selectRaw('pembeli_id, MAX(id) as last_id, MAX(created_at) as terakhir, SUM(CASE WHEN dibaca_pada IS NULL AND pengirim_id != ? THEN 1 ELSE 0 END) as belum', [$user->id])
                ->groupBy('pembeli_id')
                ->orderByDesc('terakhir')
                ->get();

            $terakhir = BarangPesan::query()
                ->whereIn('id', $ringkas->pluck('last_id'))
                ->with('pembeli:id,nama')
                ->get()
                ->keyBy('id');

            $daftarPercakapan = $ringkas->map(function ($r) use ($terakhir) {
                $contoh = $terakhir->get($r->last_id);

                if (! $contoh) {
                    return null;
                }

                return [
                    'pembeli' => $contoh->pembeli,
                    'terakhir' => $contoh,
                    'belumDibaca' => (int) $r->belum,
                    'url' => route('barang.chat.pembeli', [
                        'barang' => $this->barang->id,
                        'pembeli' => $contoh->pembeli_id,
                    ]),
                ];
            })->filter()->values();
        }

        if ($this->pembeli) {
            BarangPesan::query()
                ->where('barang_kos_id', $this->barang->id)
                ->where('pembeli_id', $this->pembeli->id)
                ->where('pengirim_id', '!=', $user->id)
                ->whereNull('dibaca_pada')
                ->update(['dibaca_pada' => now()]);

            $user->forgetPesanBelumDibacaCache();

            $pesans = BarangPesan::query()
                ->percakapan($this->barang->id, $this->pembeli->id)
                ->with('pengirim:id,nama')
                ->get();
        }

        return [
            'sebagaiPenjual' => $sebagaiPenjual,
            'daftarPercakapan' => $daftarPercakapan,
            'pesans' => $pesans,
            'lawan' => $this->pembeli
                ? ($sebagaiPenjual ? $this->pembeli : $this->barang->user)
                : null,
        ];
    }

    public function kirim(string $pesan = ''): void
    {
        abort_unless($this->berhak() && $this->pembeli, 403);

        if ($pesan !== '') {
            $this->pesanBaru = $pesan;
        }

        $valid = $this->validate([
            'pesanBaru' => 'required|string|max:1000',
        ]);

        BarangPesan::create([
            'barang_kos_id' => $this->barang->id,
            'penjual_id' => $this->barang->user_id,
            'pembeli_id' => $this->pembeli->id,
            'pengirim_id' => auth()->id(),
            'pesan' => trim($valid['pesanBaru']),
        ]);

        $penerimaId = $this->isPenjual(auth()->user())
            ? (int) $this->pembeli->id
            : (int) $this->barang->user_id;

        User::lupakanLencanaNavigasi($penerimaId);

        $this->reset('pesanBaru');
    }
}; ?>

<div class="py-6 sm:py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

        {{-- Header barang --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-stone-200 dark:ring-gray-700 p-4 flex items-center gap-3">
            <a href="{{ route('barang.detail', $barang) }}" wire:navigate
               class="shrink-0 h-9 w-9 rounded-full bg-stone-100 dark:bg-gray-700 hover:bg-stone-200 dark:hover:bg-gray-600 flex items-center justify-center text-slate-600 dark:text-gray-300 transition"
               aria-label="Kembali ke detail barang">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
            </a>
            <img src="{{ $barang->fotoUrl() }}" alt="{{ $barang->nama }}"
                 class="h-12 w-12 shrink-0 rounded-xl object-cover border border-stone-200 dark:border-gray-700">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-slate-900 dark:text-gray-100 truncate">{{ $barang->nama }}</p>
                <p class="text-xs text-slate-500 dark:text-gray-400 truncate">
                    {{ $barang->formattedHarga() }} · {{ $barang->kota }}
                    @if ($lawan)
                        · dengan {{ $lawan->nama }}
                    @endif
                </p>
            </div>
            <a href="{{ route('barang.detail', $barang) }}" wire:navigate
               class="shrink-0 hidden sm:inline-flex items-center rounded-lg border border-brand-200 dark:border-brand-500/30 bg-brand-50 dark:bg-brand-500/10 px-3 py-1.5 text-xs font-semibold text-brand-700 dark:text-brand-300 hover:bg-brand-100 dark:hover:bg-brand-500/20 transition">
                Lihat Barang
            </a>
        </div>

        @if ($sebagaiPenjual && ! $pembeli)
            {{-- Daftar percakapan penjual --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-stone-200 dark:ring-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-stone-100 dark:border-gray-700 flex items-center justify-between gap-3">
                    <div>
                        <h1 class="text-base font-bold text-slate-900 dark:text-gray-100">Pesan Pembeli</h1>
                        <p class="text-xs text-slate-500 dark:text-gray-400">Pertanyaan seputar barang ini.</p>
                    </div>
                    <span class="text-xs font-semibold text-brand-700 dark:text-brand-300 bg-brand-50 dark:bg-brand-500/10 px-2.5 py-1 rounded-full">
                        {{ $daftarPercakapan->count() }} percakapan
                    </span>
                </div>

                @if ($daftarPercakapan->isEmpty())
                    <div class="p-10 text-center">
                        <div class="mx-auto h-14 w-14 rounded-full bg-brand-50 dark:bg-brand-500/10 flex items-center justify-center mb-4">
                            <svg class="h-7 w-7 text-brand-400 dark:text-brand-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                            </svg>
                        </div>
                        <p class="font-medium text-slate-700 dark:text-gray-200">Belum ada pembeli yang bertanya.</p>
                        <p class="mt-1 text-sm text-slate-400 dark:text-gray-500">Pertanyaan dari calon pembeli akan muncul di sini.</p>
                    </div>
                @else
                    <div class="divide-y divide-stone-100 dark:divide-gray-700">
                        @foreach ($daftarPercakapan as $c)
                            <a href="{{ $c['url'] }}" wire:navigate class="flex items-center gap-3.5 p-4 sm:p-5 hover:bg-brand-50/50 dark:hover:bg-brand-500/5 transition">
                                <span class="h-11 w-11 shrink-0 rounded-full bg-brand-800 flex items-center justify-center text-sm font-bold text-white uppercase">
                                    {{ mb_substr($c['pembeli']?->nama ?? '?', 0, 1) }}
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex items-center justify-between gap-2">
                                        <span class="text-sm font-bold text-slate-900 dark:text-gray-100 truncate">{{ $c['pembeli']?->nama ?? 'Pengguna terhapus' }}</span>
                                        <span class="shrink-0 text-[11px] text-slate-400 dark:text-gray-500">{{ $c['terakhir']->created_at->translatedFormat('d M, H:i') }}</span>
                                    </span>
                                    <span class="mt-0.5 block text-sm {{ $c['belumDibaca'] > 0 ? 'font-semibold text-slate-800 dark:text-gray-100' : 'text-slate-400 dark:text-gray-500' }} truncate">
                                        {{ $c['terakhir']->pesan }}
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
            </div>
        @else
            {{-- Ruang chat --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-stone-200 dark:ring-gray-700 overflow-hidden">
                <div class="px-5 py-3.5 border-b border-stone-100 dark:border-gray-700 flex items-center gap-3">
                    @if ($sebagaiPenjual)
                        <a href="{{ route('barang.chat', $barang) }}" wire:navigate
                           class="shrink-0 h-8 w-8 rounded-full bg-stone-100 dark:bg-gray-700 hover:bg-stone-200 dark:hover:bg-gray-600 flex items-center justify-center text-slate-600 dark:text-gray-300 transition"
                           aria-label="Kembali ke daftar pembeli">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
                        </a>
                    @endif
                    <span class="h-10 w-10 shrink-0 rounded-full bg-brand-800 flex items-center justify-center text-sm font-bold text-white uppercase">
                        {{ mb_substr($lawan?->nama ?? '?', 0, 1) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-slate-900 dark:text-gray-100 truncate">{{ $lawan?->nama ?? 'Pengguna terhapus' }}</p>
                        <p class="text-xs text-slate-500 dark:text-gray-400 truncate">
                            {{ $sebagaiPenjual ? 'Calon pembeli' : 'Penjual' }} · {{ $barang->kota }}
                        </p>
                    </div>
                    @unless ($sebagaiPenjual)
                        <a href="{{ $barang->waLink() }}" target="_blank" rel="noopener noreferrer"
                           class="shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 px-3 py-1.5 text-xs font-semibold text-white transition">
                            <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.746.953 3.71 1.456 5.711 1.457h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                            WA
                        </a>
                    @endunless
                </div>

                <div id="ruang-pesan-barang" class="p-4 sm:p-5 h-[55vh] overflow-y-auto space-y-3 bg-stone-50/50 dark:bg-gray-900/40" wire:poll.visible.10s>
                    @forelse ($pesans as $p)
                        @php $punya = $p->isPengirim(auth()->user()); @endphp
                        <div class="flex {{ $punya ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[78%] rounded-2xl px-4 py-2.5 {{ $punya ? 'bg-brand-700 text-white rounded-br-md' : 'bg-white dark:bg-gray-700 text-slate-800 dark:text-gray-100 ring-1 ring-stone-200 dark:ring-gray-600 rounded-bl-md' }}">
                                <p class="text-sm whitespace-pre-wrap break-words">{{ $p->pesan }}</p>
                                <p class="mt-1 text-[10px] {{ $punya ? 'text-brand-100/80' : 'text-slate-400 dark:text-gray-500' }}">
                                    {{ $p->created_at->translatedFormat('d M, H:i') }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="h-full flex flex-col items-center justify-center text-center py-10">
                            <div class="h-12 w-12 rounded-full bg-brand-50 dark:bg-brand-500/10 flex items-center justify-center">
                                <svg class="h-6 w-6 text-brand-400 dark:text-brand-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" /></svg>
                            </div>
                            <p class="mt-3 text-sm font-semibold text-slate-700 dark:text-gray-200">Belum ada pesan.</p>
                            <p class="mt-1 text-xs text-slate-400 dark:text-gray-500 max-w-xs">
                                {{ $sebagaiPenjual ? 'Sapa calon pembeli dan jawab pertanyaannya di sini.' : 'Tanyakan kondisi barang, nego harga, atau kesepakatan COD.' }}
                            </p>
                        </div>
                    @endforelse
                </div>

                @unless ($sebagaiPenjual)
                    {{-- Quick reply calon pembeli --}}
                    <div class="flex gap-2 overflow-x-auto scrollbar-hide px-4 py-2 bg-white dark:bg-gray-800 border-t border-stone-100 dark:border-gray-700">
                        @foreach (['Masih tersedia?', 'Bisa nego?', 'Bisa COD di mana?', 'Boleh minta foto tambahan?'] as $cepat)
                            <button type="button" wire:click="kirim(@js($cepat))"
                                class="shrink-0 rounded-full bg-brand-50 dark:bg-brand-500/10 px-3 py-1 text-xs font-medium text-brand-700 dark:text-brand-300 ring-1 ring-inset ring-brand-100 dark:ring-brand-500/30 hover:bg-brand-100 dark:hover:bg-brand-500/20 transition">
                                {{ $cepat }}
                            </button>
                        @endforeach
                    </div>
                @endunless

                <form wire:submit="kirim" class="px-4 pb-4 pt-2 bg-white dark:bg-gray-800">
                    @error('pesanBaru')
                        <p class="mb-2 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                    <div class="flex items-end gap-2 rounded-2xl ring-1 ring-stone-300 dark:ring-gray-600 p-2 focus-within:ring-brand-400 transition">
                        <textarea wire:model="pesanBaru" rows="1" maxlength="1000" placeholder="Tulis pesan..."
                            class="flex-1 resize-none border-0 focus:ring-0 text-sm max-h-32 bg-transparent text-slate-900 dark:text-gray-100 placeholder:text-slate-400 dark:placeholder:text-gray-500"></textarea>
                        <button type="submit" wire:loading.attr="disabled"
                            class="shrink-0 inline-flex items-center gap-1.5 rounded-xl bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-800 transition disabled:opacity-50">
                            Kirim
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.125A59.769 59.769 0 0121.485 12 59.768 59.768 0 013.27 20.875L5.999 12zm0 0h7.5" /></svg>
                        </button>
                    </div>
                    <p class="mt-2 text-[11px] text-slate-400 dark:text-gray-500">
                        Transaksi aman: periksa fisik barang saat COD sebelum membayar.
                    </p>
                </form>
            </div>
        @endif
    </div>
</div>

@script
<script>
    const ambilRuangBarang = () => document.getElementById('ruang-pesan-barang');
    let tetapBawahBarang = true;
    let tickBarang = false;

    const pasangScrollBarang = () => {
        const target = ambilRuangBarang();
        if (!target || target.dataset.scrollSiap === '1') return;
        target.dataset.scrollSiap = '1';
        target.addEventListener('scroll', () => {
            if (tickBarang) return;
            tickBarang = true;
            requestAnimationFrame(() => {
                tetapBawahBarang = target.scrollHeight - target.scrollTop - target.clientHeight < 150;
                tickBarang = false;
            });
        }, { passive: true });
    };

    const keBawahBarang = () => requestAnimationFrame(() => {
        const target = ambilRuangBarang();
        if (!target || !tetapBawahBarang) return;
        target.scrollTo({ top: target.scrollHeight, behavior: 'auto' });
    });

    pasangScrollBarang();
    keBawahBarang();

    Livewire.hook('commit', ({ succeed }) => {
        succeed(() => setTimeout(() => { pasangScrollBarang(); keBawahBarang(); }, 50));
    });
    document.addEventListener('livewire:navigated', () => { pasangScrollBarang(); keBawahBarang(); });
</script>
@endscript
