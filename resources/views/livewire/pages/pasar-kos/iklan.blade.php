<?php

use App\Models\IklanPasarKos;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    #[Validate('required|string|min:3|max:100')]
    public string $judul = '';

    #[Validate('nullable|string|max:500')]
    public ?string $deskripsi = null;

    #[Validate('nullable|image|max:2048')]
    public ?string $gambar = null;

    #[Validate('nullable|url')]
    public ?string $link = null;

    #[Validate('required|string')]
    public string $posisi = 'katalog_sisipan';

    #[Validate('required|integer|min:1|max:7')]
    public int $durasiHari = 7;

    public function simpan(): void
    {
        $validated = $this->validate();

        // Cek batas iklan untuk anak_kos (max 2 iklan aktif)
        $user = auth()->user();
        if ($user->hasRole('anak_kos')) {
            $jumlahIklanAktif = IklanPasarKos::aktif()->where('user_id', $user->id)->count();
            $batasIklan = IklanPasarKos::batasAkunGratis();

            if ($jumlahIklanAktif >= $batasIklan) {
                $this->dispatch('error', 'Anda telah mencapai batas maksimal ' . $batasIklan . ' iklan aktif. Untuk memasang iklan lebih banyak, silakan upgrade ke akun pemilik.');
                return;
            }
        }

        // Simpan gambar jika ada
        $gambarPath = null;
        if ($this->gambar) {
            $gambarPath = $this->gambar->store('iklan_pasar_kos', 'public');
        }

        // Hitung tayang_sampai
        $tayangSampai = now()->addDays($this->durasiHari)->toDateString();

        // Generate gradient acak untuk iklan (opsional)
        $gradients = [
            'from-amber-500 to-orange-600',
            'from-emerald-500 to-green-600',
            'from-blue-500 to-indigo-600',
            'from-purple-500 to-pink-600',
            'from-rose-500 to-red-600',
        ];
        $gradient = $gradients[array_rand($gradients)];

        IklanPasarKos::create([
            'user_id' => $user->id,
            'judul' => trim($this->judul),
            'deskripsi' => $this->deskripsi ? trim($this->deskripsi) : null,
            'gambar' => $gambarPath,
            'link' => $this->link ? trim($this->link) : null,
            'posisi' => $this->posisi,
            'gradient' => $gradient,
            'tayang_dimulai' => now(),
            'tayang_sampai' => $tayangSampai,
            'durasi_hari' => $this->durasiHari,
            'status' => true,
        ]);

        session()->flash('pesan', 'Iklan pasar kos berhasil dipasang dan akan tayang hingga ' . date('d F Y', strtotime($tayangSampai)) . '.');
        $this->redirect(route('dashboard.' . $user->roleValue()), navigate: true);
    }

    public function with(): array
    {
        return [
            'daftarPosisi' => [
                ['value' => 'katalog_sisipan', 'label' => 'Sisipan di Katalog Barang'],
                ['value' => 'banner_atas', 'label' => 'Banner di Atas Halaman'],
            ],
        ];
    }
}; ?>

<div class="py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- Header Form --}}
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('dashboard.' . auth()->user()->roleValue()) }}" wire:navigate class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-700 dark:text-brand-300 hover:underline mb-2">
                    ← Kembali ke Dashboard
                </a>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Pasang Iklan Pasar Kos
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                    Pasang iklan untuk promosikan produk, jasa, atau promosi khusus anak kos.
                </p>
            </div>
        </div>

        @php
            $user = auth()->user();
            $sisaIklan = 0;
            $batasIklan = IklanPasarKos::batasAkunGratis();
            
            if ($user->hasRole('anak_kos')) {
                $jumlahIklanAktif = IklanPasarKos::aktif()->where('user_id', $user->id)->count();
                $sisaIklan = max(0, $batasIklan - $jumlahIklanAktif);
            }
        @endphp

        {{-- Info Batas Iklan --}}
        @if ($user->hasRole('anak_kos'))
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-300">Batas Iklan Gratis</h3>
                    <p class="mt-1 text-sm text-amber-700 dark:text-amber-400">
                        Akun anak kos gratis memiliki batas {{ $batasIklan }} iklan aktif. Sisa kuota iklan Anda: <span class="font-bold">{{ $sisaIklan }}</span>
                    </p>
                </div>
            </div>
        @endif

        <form wire:submit="simpan" class="bg-white dark:bg-gray-900 border border-stone-200 dark:border-gray-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-8">

            {{-- 1. Info Dasar Iklan --}}
            <div class="space-y-4">
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 text-xs">1</span>
                    Informasi Iklan
                </h2>

                <div>
                    <label for="judul" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300 mb-1.5">
                        Judul Iklan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="judul" wire:model="judul"
                           placeholder="Contoh: Jual Seragam Kos Max 50K, Promo Kontrakan, dll"
                           class="w-full rounded-xl border border-stone-300 dark:border-gray-700 bg-stone-50 dark:bg-gray-800 text-slate-800 dark:text-gray-100 text-sm px-4 py-2.5 focus:ring-2 focus:ring-brand-500">
                    @error('judul') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="deskripsi" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300 mb-1.5">
                        Deskripsi Iklan <span class="text-xs text-slate-500">(opsional)</span>
                    </label>
                    <textarea id="deskripsi" wire:model="deskripsi" rows="3"
                              placeholder="Isi deskripsi singkat tentang iklan Anda..."
                              class="w-full rounded-xl border border-stone-300 dark:border-gray-700 bg-stone-50 dark:bg-gray-800 text-slate-800 dark:text-gray-100 text-sm px-4 py-2.5 focus:ring-2 focus:ring-brand-500"></textarea>
                    @error('deskripsi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-[10px] text-slate-500">Maksimal 500 karakter</p>
                </div>

                <div>
                    <label for="gambar" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300 mb-1.5">
                        Gambar Iklan <span class="text-xs text-slate-500">(opsional)</span>
                    </label>
                    <input type="file" id="gambar" wire:model="gambar"
                           accept="image/*"
                           class="w-full rounded-xl border border-stone-300 dark:border-gray-700 bg-stone-50 dark:bg-gray-800 text-slate-800 dark:text-gray-100 text-sm px-4 py-2.5 focus:ring-2 focus:ring-brand-500">
                    @error('gambar') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    @if ($gambar)
                        <div class="mt-3">
                            <img src="{{ $gambar->temporaryUrl() }}" alt="Preview" class="w-32 h-32 object-contain rounded-lg shadow-md">
                        </div>
                    @endif
                    <p class="mt-1 text-[10px] text-slate-500">Format: JPG, PNG. Maksimal 2MB</p>
                </div>

                <div>
                    <label for="link" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300 mb-1.5">
                        Link Tujuan <span class="text-xs text-slate-500">(opsional)</span>
                    </label>
                    <input type="url" id="link" wire:model="link"
                           placeholder="https://..."
                           class="w-full rounded-xl border border-stone-300 dark:border-gray-700 bg-stone-50 dark:bg-gray-800 text-slate-800 dark:text-gray-100 text-sm px-4 py-2.5 focus:ring-2 focus:ring-brand-500">
                    @error('link') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-[10px] text-slate-500">Link tujuan saat iklan diklik (bisa link WA, website, atau halaman detail)</p>
                </div>
            </div>

            {{-- 2. Posisi & Durasi Tayang --}}
            <div class="space-y-4">
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 text-xs">2</span>
                    Posisi & Durasi Tayang
                </h2>

                <div>
                    <label for="posisi" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300 mb-1.5">
                        Posisi Iklan <span class="text-red-500">*</span>
                    </label>
                    <select id="posisi" wire:model="posisi"
                            class="w-full rounded-xl border border-stone-300 dark:border-gray-700 bg-stone-50 dark:bg-gray-800 text-slate-800 dark:text-gray-100 text-sm px-4 py-2.5 focus:ring-2 focus:ring-brand-500">
                        @foreach ($daftarPosisi as $posisi)
                            <option value="{{ $posisi['value'] }}">{{ $posisi['label'] }}</option>
                        @endforeach
                    </select>
                    @error('posisi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-[10px] text-slate-500">
                        <strong>Sisipan di Katalog Barang:</strong> Tampil di antara daftar barang<br>
                        <strong>Banner di Atas Halaman:</strong> Tampil di bagian atas halaman pasar kos
                    </p>
                </div>

                <div>
                    <label for="durasiHari" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300 mb-1.5">
                        Durasi Tayang <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-4">
                        <select id="durasiHari" wire:model="durasiHari"
                                class="w-1/2 rounded-xl border border-stone-300 dark:border-gray-700 bg-stone-50 dark:bg-gray-800 text-slate-800 dark:text-gray-100 text-sm px-4 py-2.5 focus:ring-2 focus:ring-brand-500">
                            @for ($i = 1; $i <= 7; $i++)
                                <option value="{{ $i }}">{{ $i }} Hari{{ $i > 1 ? ' (7 hari maksimal)' : '' }}</option>
                            @endfor
                        </select>
                        <span class="text-sm text-slate-600 dark:text-gray-400">maksimal 7 hari</span>
                    </div>
                    @error('durasiHari') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- 3. Summary & Submit --}}
            <div class="space-y-4">
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 text-xs">3</span>
                    Ringkasan
                </h2>

                @if ($user->hasRole('anak_kos'))
                    <div class="bg-stone-50 dark:bg-gray-800 rounded-xl p-4 space-y-2">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-600 dark:text-gray-400">Jumlah iklan aktif Anda:</span>
                            <span class="font-semibold text-slate-900 dark:text-white">{{ IklanPasarKos::aktif()->where('user_id', $user->id)->count() }} dari {{ $batasIklan }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-600 dark:text-gray-400">Durasi tayang:</span>
                            <span class="font-semibold text-slate-900 dark:text-white">{{ $durasiHari }} hari</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-600 dark:text-gray-400">Estimasi tayang sampai:</span>
                            <span class="font-semibold text-slate-900 dark:text-white">{{ date('d F Y', strtotime(now()->addDays($durasiHari))) }}</span>
                        </div>
                        @if ($sisaIklan <= 0)
                            <p class="mt-2 text-xs text-red-600 font-semibold">Batas iklan Anda sudah penuh!</p>
                        @endif
                    </div>
                @endif

                <button type="submit"
                        class="w-full sm:w-auto px-6 py-3 bg-brand-700 hover:bg-brand-600 text-white font-bold rounded-xl transition shadow-lg hover:shadow-xl hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed">
                    Pasang Iklan Sekarang
                </button>
            </div>
        </form>
    </div>
</div>
