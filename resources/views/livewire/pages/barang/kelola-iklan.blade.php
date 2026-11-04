<?php

use App\Models\IklanPasarKos;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public function hapus(IklanPasarKos $iklan): void
    {
        $this->authorize('destroy', $iklan);

        // Hapus gambar jika ada
        if ($iklan->gambar && Storage::disk('public')->exists($iklan->gambar)) {
            Storage::disk('public')->delete($iklan->gambar);
        }

        $iklan->delete();

        $this->dispatch('success', 'Iklan berhasil dihapus.');
    }

    public function render()
    {
        $iklan = IklanPasarKos::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.pages.barang.kelola-iklan', compact('iklan'));
    }
};

?>

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-900 dark:text-white">
                Kelola Iklan Pasar Kos
            </h2>
            <a href="{{ route('iklan-pasar-kos.pasang') }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition">
                + Pasang Iklan Baru
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if ($iklan->count() > 0)
                <div class="grid gap-6">
                    @foreach ($iklan as $item)
                        <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-sm border border-stone-200 dark:border-gray-800 overflow-hidden hover:shadow-md transition">
                            <!-- Header Card -->
                            <div class="bg-gradient-to-r {{ $item->gradient }} p-5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        @if ($item->gambar)
                                            <img src="{{ asset('storage/'.$item->gambar) }}" 
                                                 class="w-12 h-12 rounded-lg object-cover border-2 border-white/30"
                                                 alt="{{ $item->judul }}">
                                        @else
                                            <div class="w-12 h-12 rounded-lg bg-white/20 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                            </div>
                                        @endif
                                        <div>
                                            <h3 class="text-white font-bold text-lg">{{ $item->judul }}</h3>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="px-2 py-0.5 bg-white/20 rounded text-xs font-medium">
                                                    {{ $item->statusValue()?->label() ?? 'Status' }}
                                                </span>
                                                <span class="px-2 py-0.5 bg-white/20 rounded text-xs font-medium">
                                                    {{ \Carbon\Carbon::parse($item->tayang_sampai)->diffInDays() }} hari lagi
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right text-white/80">
                                        <div class="text-sm">Dipasang</div>
                                        <div class="text-xs font-medium">{{ $item->created_at->format('d M Y') }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="p-5">
                                @if ($item->deskripsi)
                                    <p class="text-slate-600 dark:text-gray-400 mb-4 line-clamp-2">{{ $item->deskripsi }}</p>
                                @endif

                                @if ($item->link)
                                    <a href="{{ $item->link }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1 text-amber-600 dark:text-amber-400 font-medium hover:underline mb-4">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                        {{ $item->link }}
                                    </a>
                                @endif

                                <div class="border-t border-stone-200 dark:border-gray-700 pt-4 mt-4 flex items-center justify-between">
                                    <div class="text-xs text-slate-500 dark:text-gray-400">
                                        ID: {{ $item->id }}
                                    </div>
                                    <div class="flex gap-2">
                                        <a href="{{ route('iklan-pasar-kos.ubah', $item) }}" 
                                           class="px-3 py-1.5 bg-stone-100 dark:bg-gray-800 hover:bg-stone-200 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 text-sm font-medium rounded-lg transition">
                                            Edit
                                        </a>
                                        <button type="button" wire:click="hapus({{ $item->id }})"
                                                class="px-3 py-1.5 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 text-red-600 dark:text-red-400 text-sm font-medium rounded-lg transition">
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-sm border border-stone-200 dark:border-gray-800 p-12 text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-stone-100 dark:bg-gray-800 mb-6">
                        <svg class="w-10 h-10 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Belum Ada Iklan</h3>
                    <p class="text-slate-600 dark:text-gray-400 mb-6 max-w-md mx-auto">
                        Belum memasang iklan di pasar kos. Iklan akan muncul di antara daftar barang.
                    </p>
                    <a href="{{ route('iklan-pasar-kos.pasang') }}" 
                       class="inline-flex items-center gap-2 px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                        Pasang Iklan Pertama
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
