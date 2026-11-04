<?php

use App\Models\IklanPasarKos;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    public IklanPasarKos $iklan;

    public string $judul;
    public string $deskripsi = '';
    public ?string $gambarBaru = null;
    public ?string $gambarLama = null;
    public string $link = '';
    public int $durasi = 7;
    public string $gradient = 'from-purple-500 to-pink-500';

    public function mount(IklanPasarKos $iklan): void
    {
        $this->authorize('update', $iklan);

        $this->iklan = $iklan;
        $this->judul = $iklan->judul;
        $this->deskripsi = $iklan->deskripsi ?? '';
        $this->gambarLama = $iklan->gambar;
        $this->link = $iklan->link ?? '';
        $this->gradient = $iklan->gradient;
        $this->durasi = $iklan->tayang_sampai ? now()->diffInDays($iklan->tayang_sampai) : 7;
    }

    public function update(): void
    {
        $validated = $this->validate([
            'judul' => 'required|string|min:3|max:100',
            'deskripsi' => 'nullable|string|max:255',
            'gambarBaru' => 'nullable|image|max:2048',
            'link' => 'nullable|url',
            'durasi' => 'required|integer|min:1|max:7',
            'gradient' => 'required|string|in:from-purple-500 to-pink-500,from-blue-500 to-cyan-500,from-amber-500 to-orange-500,from-emerald-500 to-teal-500,from-rose-500 to-red-500',
        ]);

        // Proses upload gambar baru jika ada
        if ($this->gambarBaru) {
            // Hapus gambar lama jika ada
            if ($this->gambarLama && Storage::disk('public')->exists($this->gambarLama)) {
                Storage::disk('public')->delete($this->gambarLama);
            }
            $validated['gambar'] = $this->gambarBaru->store('iklan', 'public');
        } else {
            $validated['gambar'] = $this->gambarLama;
        }

        $validated['tayang_sampai'] = now()->addDays($validated['durasi']);
        unset($validated['durasi']);

        $this->iklan->update($validated);

        $this->dispatch('success', 'Iklan berhasil diperbarui.');
        $this->redirect(route('iklan-pasar-kos.kelola'));
    }

    public function render()
    {
        return view('livewire.pages.barang.edit-iklan');
    }
};

?>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 dark:text-white">
            Edit Iklan Pasar Kos
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-sm border border-stone-200 dark:border-gray-800 overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r {{ $gradient }} p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-white">Edit Iklan</h3>
                            <p class="text-white/80 text-sm mt-1">Ubah informasi iklan yang sudah dipasang</p>
                        </div>
                        <div class="hidden sm:block text-right">
                            <div class="text-2xl font-bold text-white">{{ $durasi }}</div>
                            <div class="text-xs text-white/80">Hari Tayang</div>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <div class="p-6 sm:p-8 space-y-6">
                    <div>
                        <label for="judul" class="block text-sm font-medium text-slate-700 dark:text-gray-300 mb-1">
                            Judul Iklan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="judul" id="judul"
                               class="w-full px-4 py-2.5 rounded-xl border border-stone-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                               placeholder="Contoh: Diskon 20% untuk penyewa baru">
                        @error('judul') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="deskripsi" class="block text-sm font-medium text-slate-700 dark:text-gray-300 mb-1">
                            Deskripsi Singkat
                        </label>
                        <textarea wire:model="deskripsi" id="deskripsi" rows="3"
                                  class="w-full px-4 py-2.5 rounded-xl border border-stone-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                                  placeholder="Tulis deskripsi singkat tentang penawaran Anda..."></textarea>
                        @error('deskripsi') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        <p class="text-xs text-slate-500 mt-1">Maksimal 255 karakter</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-gray-300 mb-1">
                            Gambar Iklan
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-stone-300 dark:border-gray-700 border-dashed rounded-xl hover:bg-stone-50 dark:hover:bg-gray-800/50 transition">
                            @if ($gambarBaru)
                                <div class="relative w-full h-48">
                                    <img src="{{ $gambarBaru->temporaryUrl() }}" class="w-full h-full object-contain rounded-lg">
                                    <button type="button" wire:click="$set('gambarBaru', null)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                            @elseif ($gambarLama)
                                <div class="relative w-full h-48">
                                    <img src="{{ asset('storage/'.$gambarLama) }}" class="w-full h-full object-contain rounded-lg">
                                    <button type="button" wire:click="$set('gambarLama', null)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                            @else
                                <div class="space-y-2 text-center">
                                    <svg class="mx-auto h-12 w-12 text-stone-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 5h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-stone-600 dark:text-gray-400">
                                        <label class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-amber-600 hover:text-amber-500 focus-within:outline-none">
                                            <span>Unggah gambar baru</span>
                                            <input type="file" class="sr-only" wire:model="gambarBaru" accept="image/*">
                                        </label>
                                        <p class="pl-1">atau drag and drop</p>
                                    </div>
                                    <p class="text-xs text-stone-500">PNG, JPG, GIF maksimal 2MB</p>
                                </div>
                            @endif
                        </div>
                        @error('gambarBaru') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="link" class="block text-sm font-medium text-slate-700 dark:text-gray-300 mb-1">
                            Link Tujuan (Opsional)
                        </label>
                        <input type="url" wire:model="link" id="link"
                               class="w-full px-4 py-2.5 rounded-xl border border-stone-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                               placeholder="https://...">
                        @error('link') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        <p class="text-xs text-slate-500 mt-1">Link akan ditampilkan sebagai tombol "Kunjungi" di iklan Anda</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-gray-300 mb-3">
                            Durasi Tayang (Maksimal 7 hari)
                        </label>
                        <div class="grid grid-cols-7 gap-2">
                            @foreach ([1, 2, 3, 4, 5, 6, 7] as $hari)
                                <button type="button" wire:click="$set('durasi', {{ $hari }})"
                                        class="py-2 px-3 rounded-lg text-sm font-medium transition border {{
                                            $durasi == $hari 
                                                ? 'bg-amber-500 border-amber-500 text-white shadow-sm' 
                                                : 'bg-white dark:bg-gray-800 border-stone-300 dark:border-gray-700 text-slate-700 dark:text-gray-300 hover:bg-stone-50 dark:hover:bg-gray-700'
                                        }}">
                                    {{ $hari }} hari
                                </button>
                            @endforeach
                        </div>
                        @error('durasi') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-gray-300 mb-3">
                            Gradient Tampilan
                        </label>
                        <div class="flex gap-3">
                            @foreach ([
                                'from-purple-500 to-pink-500' => 'Ungu-Pink',
                                'from-blue-500 to-cyan-500' => 'Biru-Cyan',
                                'from-amber-500 to-orange-500' => 'Kuning-Orange',
                                'from-emerald-500 to-teal-500' => 'Hijau-Teal',
                                'from-rose-500 to-red-500' => 'Merah-Pink'
                            ] as $grad => $label)
                                <button type="button" wire:click="$set('gradient', '{{ $grad }}')"
                                        class="flex-1 py-2 px-3 rounded-lg text-xs font-medium transition border relative {{
                                            $gradient == $grad
                                                ? 'ring-2 ring-offset-2 ring-amber-500 dark:ring-offset-gray-900'
                                                : 'border-stone-300 dark:border-gray-700'
                                        }} {{ str_replace('from-', 'bg-', $grad) }} text-white">
                                    {{ $label }}
                                    @if ($gradient == $grad)
                                        <svg class="w-4 h-4 absolute right-2 top-2 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                        @error('gradient') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-stone-50 dark:bg-gray-800/50 px-6 py-4 flex items-center justify-between border-t border-stone-200 dark:border-gray-700">
                    <a href="{{ route('iklan-pasar-kos.kelola') }}" class="text-sm text-slate-600 dark:text-gray-400 hover:text-slate-800 dark:hover:text-gray-200">
                        &larr; Kembali ke Kelola Iklan
                    </a>
                    <button type="button" wire:click="update"
                            class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5">
                        Update Iklan
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
