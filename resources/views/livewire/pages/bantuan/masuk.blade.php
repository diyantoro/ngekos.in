<?php

use App\Models\PesanBantuan;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $tab = 'semua';

    public array $balasan = [];

    public function with(): array
    {
        $query = PesanBantuan::query();

        if ($this->tab !== 'semua') {
            $query->where('status', $this->tab);
        }

        return [
            'pesans' => $query->latest()->get(),
            'jumlahBaru' => PesanBantuan::where('status', 'baru')->count(),
            'jumlahDibaca' => PesanBantuan::where('status', 'dibaca')->count(),
            'jumlahSelesai' => PesanBantuan::where('status', 'selesai')->count(),
        ];
    }

    public function balas(int $id): void
    {
        $pesan = PesanBantuan::find($id);

        if (! $pesan) {
            return;
        }

        $teks = trim($this->balasan[$id] ?? '');

        if ($teks === '') {
            return;
        }

        $pesan->update([
            'balasan' => $teks,
            'status' => 'selesai',
            'dibalas_oleh' => auth()->id(),
            'dibalas_at' => now(),
        ]);

        unset($this->balasan[$id]);
    }

    public function tandaiDibaca(int $id): void
    {
        PesanBantuan::where('id', $id)->where('status', 'baru')->update(['status' => 'dibaca']);
    }

    public function tandaiSelesai(int $id): void
    {
        PesanBantuan::where('id', $id)->whereIn('status', ['baru', 'dibaca'])->update(['status' => 'selesai']);
    }
}; ?>

<div class="py-10">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Pesan dari Pengguna</h1>
                <p class="mt-1 text-sm text-gray-500">Pesan bantuan yang dikirim user, balas untuk membantu mereka.</p>
            </div>
            <a href="{{ route('bantuan') }}" wire:navigate class="text-sm font-medium text-teal-600 hover:text-teal-500">Lihat halaman bantuan publik</a>
        </div>

        <!-- Tabs -->
        <div class="flex flex-wrap gap-2">
            @foreach ([
                'semua' => 'Semua',
                'baru' => 'Baru (' . $jumlahBaru . ')',
                'dibaca' => 'Dibaca (' . $jumlahDibaca . ')',
                'selesai' => 'Selesai (' . $jumlahSelesai . ')',
            ] as $nilai => $label)
                <button wire:click="$set('tab', '{{ $nilai }}')"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === $nilai ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="space-y-4">
            @forelse ($pesans as $pesan)
                <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-5 sm:p-6" wire:key="pesan-{{ $pesan->id }}">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-sm font-bold text-gray-900">{{ $pesan->nama }}</h2>
                                <x-status-badge :status="$pesan->status" />
                                @if ($pesan->user)
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">{{ $pesan->user->email }}</span>
                                @endif
                            </div>
                            <p class="mt-0.5 text-xs text-gray-400">
                                {{ $pesan->email }} &middot; {{ $pesan->created_at?->translatedFormat('d M Y, H:i') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if ($pesan->status === 'baru')
                                <button wire:click="tandaiDibaca({{ $pesan->id }})"
                                    class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition">
                                    Tandai Dibaca
                                </button>
                            @endif
                            @if (in_array($pesan->status, ['baru', 'dibaca']))
                                <button wire:click="tandaiSelesai({{ $pesan->id }})"
                                    class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-100 transition">
                                    Tandai Selesai
                                </button>
                            @endif
                        </div>
                    </div>

                    @if ($pesan->subjek)
                        <p class="mt-3 text-xs font-bold text-gray-500 uppercase tracking-wide">{{ $pesan->subjek }}</p>
                    @endif
                    <p class="mt-1.5 text-sm text-gray-700 leading-relaxed">{{ $pesan->pesan }}</p>

                    @if ($pesan->balasan)
                        <div class="mt-4 rounded-xl bg-emerald-50 ring-1 ring-emerald-100 p-4">
                            <p class="text-xs font-bold text-emerald-700 uppercase tracking-wide">Balasan — {{ $pesan->pembalas?->nama ?? 'Admin' }}</p>
                            <p class="mt-1.5 text-sm text-gray-700 leading-relaxed">{{ $pesan->balasan }}</p>
                            <p class="mt-2 text-xs text-emerald-500">{{ $pesan->dibalas_at?->translatedFormat('d M Y, H:i') }}</p>
                        </div>
                    @else
                        <form wire:submit="balas({{ $pesan->id }})" class="mt-4 flex flex-col sm:flex-row gap-2">
                            <input type="text" wire:model="balasan.{{ $pesan->id }}" placeholder="Tulis balasan untuk {{ $pesan->nama }}..."
                                class="flex-1 rounded-lg border-gray-300 text-sm focus:border-teal-500 focus:ring-teal-500">
                            <button type="submit" wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500 transition">
                                Kirim Balasan
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 py-16 text-center">
                    <div class="mx-auto h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                        <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z" /></svg>
                    </div>
                    <p class="text-gray-500 font-medium">Tidak ada pesan dengan kategori ini.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>