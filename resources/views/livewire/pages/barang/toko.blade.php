<?php

use App\Enums\UserRole;
use App\Models\BarangKos;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.publik')] class extends Component
{
    use WithPagination;

    public User $user;

    public function mount(User $user): void
    {
        abort_unless(in_array($user->roleValue(), [
            UserRole::AnakKos->value,
            UserRole::Pemilik->value,
        ], true), 404);

        $this->user = $user;
    }

    public function with(): array
    {
        $query = BarangKos::query()
            ->whereHas('akunTampil', fn (Builder $q) => $q->where('users.id', $this->user->id))
            ->tersedia()
            ->with('user:id,nama,role')
            ->latest();

        $barangs = $query->paginate(12);

        return [
            'barangs' => $barangs,
            'totalBarang' => $barangs->total(),
            'kotaToko' => BarangKos::query()
                ->whereHas('akunTampil', fn (Builder $q) => $q->where('users.id', $this->user->id))
                ->tersedia()
                ->whereNotNull('kota')
                ->where('kota', '!=', '')
                ->select('kota')
                ->distinct()
                ->pluck('kota')
                ->first(),
        ];
    }
}; ?>

<div class="py-6 sm:py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <a href="{{ route('barang.index') }}" wire:navigate
           class="inline-flex items-center gap-1 text-sm font-medium text-brand-700 dark:text-brand-300 hover:underline">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
            Kembali ke Pasar Kos
        </a>

        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-brand-900 via-brand-800 to-emerald-900 p-5 sm:p-8 text-white shadow-xl">
            <div class="absolute -right-10 -bottom-10 h-64 w-64 rounded-full bg-amber-400/10 blur-3xl pointer-events-none"></div>
            <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl font-extrabold ring-1 ring-white/30 backdrop-blur">
                    {{ strtoupper(substr($user->nama ?? 'U', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-amber-300">Toko Pasar Kos</p>
                    <h1 class="mt-0.5 truncate text-xl sm:text-3xl font-extrabold leading-tight">{{ $user->nama }}</h1>
                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-brand-100">
                        <span class="inline-flex items-center gap-1 rounded-full bg-white/10 px-2 py-0.5 font-semibold ring-1 ring-white/20">
                            {{ $user->roleValue() === 'pemilik' ? 'Pemilik Kos' : 'Anak Kos' }}
                        </span>
                        <span><strong class="text-white">{{ $totalBarang }}</strong> barang tampil</span>
                        @if ($kotaToko)
                            <span>• {{ $kotaToko }}</span>
                        @endif
                        <span>• Bergabung {{ $user->created_at?->translatedFormat('M Y') ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if ($barangs->count() > 0)
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2.5 sm:gap-5">
                @foreach ($barangs as $item)
                    <div class="group relative flex flex-col overflow-hidden rounded-xl sm:rounded-2xl border border-stone-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-md">
                        <a href="{{ route('barang.detail', $item) }}" wire:navigate class="relative block aspect-square w-full overflow-hidden bg-stone-100 dark:bg-gray-800">
                            <img src="{{ $item->fotoUrl() }}" alt="{{ $item->nama }}" loading="lazy"
                                 class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            <span class="absolute left-2 top-2 rounded-md bg-brand-950/80 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white backdrop-blur sm:text-[10px]">
                                {{ $item->kondisi?->label() ?? 'Bekas' }}
                            </span>
                        </a>

                        <div class="flex flex-1 flex-col justify-between p-2.5 sm:p-4">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-brand-700 dark:text-brand-300 sm:text-[11px]">
                                    {{ $item->kategori?->label() ?? 'Lainnya' }}
                                </p>
                                <a href="{{ route('barang.detail', $item) }}" wire:navigate class="mt-0.5 block">
                                    <h2 class="line-clamp-2 text-xs font-bold leading-snug text-slate-900 hover:text-brand-700 dark:text-gray-100 dark:hover:text-brand-300 sm:text-sm">
                                        {{ $item->nama }}
                                    </h2>
                                </a>
                                <p class="mt-1.5 text-sm font-extrabold text-slate-900 dark:text-white sm:mt-2 sm:text-base">{{ $item->formattedHarga() }}</p>
                                <p class="mt-1.5 flex items-center gap-1 truncate text-[11px] text-slate-500 dark:text-gray-400 sm:mt-2 sm:text-xs">
                                    <svg class="h-3 w-3 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ $item->kota }}
                                </p>
                                @if ((int) $item->user_id !== (int) $user->id)
                                    <p class="mt-1 truncate text-[10px] text-slate-400 sm:text-[11px]">
                                        Dijual oleh {{ $item->user?->nama ?? 'Penjual' }}
                                    </p>
                                @endif
                            </div>

                            <div class="mt-3 flex items-center justify-between gap-1.5 border-t border-stone-100 pt-2.5 dark:border-gray-800 sm:mt-4 sm:pt-3">
                                <a href="{{ $item->waLink() }}" target="_blank" rel="noopener noreferrer"
                                   title="Chat penjual via WhatsApp"
                                   class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-600 text-white transition hover:bg-emerald-500 sm:h-8 sm:w-8">
                                    <svg class="h-3.5 w-3.5 fill-current sm:h-4 sm:w-4" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.746.953 3.71 1.456 5.711 1.457h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('barang.detail', $item) }}" wire:navigate
                                   class="rounded-lg bg-stone-100 px-2 py-1 text-[11px] font-semibold text-slate-700 transition hover:bg-stone-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 sm:px-2.5 sm:py-1.5 sm:text-xs">
                                    Detail
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $barangs->links() }}
            </div>
        @else
            <div class="rounded-3xl border border-stone-200 bg-white p-10 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mx-auto mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-stone-100 text-stone-400 dark:bg-gray-800">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Belum Ada Barang di Toko Ini</h2>
                <p class="mx-auto mt-1 max-w-md text-sm text-slate-500 dark:text-gray-400">
                    Barang yang ditampilkan di toko ini belum tersedia atau sudah terjual.
                </p>
                <a href="{{ route('barang.index') }}" wire:navigate
                   class="mt-5 inline-flex items-center gap-2 rounded-xl bg-brand-700 px-4 py-2 text-xs font-semibold text-white transition hover:bg-brand-800">
                    Lihat Pasar Kos
                </a>
            </div>
        @endif

    </div>
</div>
