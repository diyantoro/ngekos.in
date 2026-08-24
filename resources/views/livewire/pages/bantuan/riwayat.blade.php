<?php

use App\Models\PesanBantuan;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public function with(): array
    {
        return [
            'pesans' => PesanBantuan::where('user_id', auth()->id())->latest()->get(),
        ];
    }
}; ?>

<div class="py-10">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Riwayat Bantuan</h1>
            <p class="mt-1 text-sm text-gray-500">Semua pesan yang kamu kirim ke admin beserta balasannya.</p>
        </div>

        <div class="space-y-4">
            @forelse ($pesans as $pesan)
                <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-5 sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 class="text-sm font-bold text-gray-900">{{ $pesan->subjek ?: 'Pesan Bantuan' }}</h2>
                        <x-status-badge :status="$pesan->status" />
                    </div>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ $pesan->pesan }}</p>
                    <p class="mt-3 text-xs text-gray-400">Dikirim {{ $pesan->created_at?->translatedFormat('d M Y, H:i') }}</p>

                    @if ($pesan->balasan)
                        <div class="mt-4 rounded-xl bg-teal-50 ring-1 ring-teal-100 p-4">
                            <p class="text-xs font-bold text-teal-600 uppercase tracking-wide">
                                Balasan Admin — {{ $pesan->pembalas?->nama ?? 'Admin' }}
                            </p>
                            <p class="mt-1.5 text-sm text-gray-700 leading-relaxed">{{ $pesan->balasan }}</p>
                            <p class="mt-2 text-xs text-teal-400">{{ $pesan->dibalas_at?->translatedFormat('d M Y, H:i') }}</p>
                        </div>
                    @else
                        <p class="mt-4 rounded-xl bg-amber-50 ring-1 ring-amber-100 px-4 py-3 text-xs font-medium text-amber-700">
                            Menunggu balasan admin. Kamu juga bisa membuka chat di halaman Bantuan untuk pertanyaan cepat.
                        </p>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 py-16 text-center">
                    <div class="mx-auto h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                        <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" /></svg>
                    </div>
                    <p class="text-gray-500 font-medium">Belum ada pesan bantuan.</p>
                    <a href="{{ route('bantuan') }}" wire:navigate
                       class="mt-4 inline-flex items-center rounded-lg bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-500 transition">
                        Kirim Pesan ke Admin
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</div>