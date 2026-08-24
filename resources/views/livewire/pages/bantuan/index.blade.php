<?php

use App\Models\PesanBantuan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('layouts.publik')] class extends Component
{
    #[Validate('required|string|max:255')]
    public string $nama = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('nullable|string|max:255')]
    public ?string $subjek = null;

    #[Validate('required|string|min:10|max:2000')]
    public string $pesan = '';

    public ?string $sukses = null;

    public function mount(): void
    {
        if (auth()->check()) {
            $this->nama = auth()->user()->nama;
            $this->email = auth()->user()->email;
        }
    }

    public function kirim(): void
    {
        $validated = $this->validate();

        PesanBantuan::create([
            'user_id' => auth()->id(),
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'subjek' => $validated['subjek'],
            'pesan' => $validated['pesan'],
            'status' => 'baru',
        ]);

        $this->reset('subjek', 'pesan');
        $this->sukses = 'Pesan kamu berhasil dikirim. Admin akan membalas secepatnya, dan balasan bisa dilihat di halaman Riwayat Bantuan.';
    }
}; ?>

<div>
    <!-- Header -->
    <section class="bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-600">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 text-center">
            <h1 class="text-3xl font-extrabold text-white">Pusat Bantuan</h1>
            <p class="mt-2 text-teal-100">Temukan jawaban, atau hubungi admin kami langsung.</p>
        </div>
    </section>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
        <!-- FAQ -->
        <div>
            <h2 class="text-lg font-bold text-gray-900">Pertanyaan yang Sering Diajukan</h2>
            <div class="mt-4 space-y-3">
                @foreach (config('faq', []) as $index => $item)
                    <div x-data="{ buka: false }" class="bg-white rounded-xl shadow-sm ring-1 ring-gray-100 overflow-hidden">
                        <button type="button" @click="buka = !buka"
                            class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left">
                            <span class="text-sm font-semibold text-gray-900">{{ ucfirst($item['kata_kunci'][0]) }}</span>
                            <svg class="h-5 w-5 text-gray-400 transition-transform" :class="buka && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                        </button>
                        <div x-show="buka" x-cloak class="px-5 pb-4">
                            <p class="text-sm text-gray-600 leading-relaxed">{{ $item['jawaban'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Hubungi Admin -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6 sm:p-8">
                <h2 class="text-lg font-bold text-gray-900">Hubungi Admin</h2>
                <p class="mt-1 text-sm text-gray-500">Tidak menemukan jawaban? Kirim pesan, admin atau super admin akan membalasmu.</p>

                @if ($sukses)
                    <x-notifikasi-popup :pesan="$sukses" judul="Terkirim!" properti="sukses" />
                @endif

                <form wire:submit="kirim" class="mt-5 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="nama" value="Nama" />
                            <x-text-input wire:model="nama" id="nama" class="mt-1 block w-full" placeholder="Nama kamu" />
                            <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="email" value="Email" />
                            <x-text-input wire:model="email" id="email" type="email" class="mt-1 block w-full" placeholder="nama@email.com" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="subjek" value="Subjek (opsional)" />
                        <x-text-input wire:model="subjek" id="subjek" class="mt-1 block w-full" placeholder="Contoh: Tidak bisa login" />
                        <x-input-error :messages="$errors->get('subjek')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="pesan" value="Pesan" />
                        <textarea wire:model="pesan" id="pesan" rows="5" placeholder="Ceritakan masalah atau pertanyaanmu..."
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"></textarea>
                        <x-input-error :messages="$errors->get('pesan')" class="mt-2" />
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="text-xs text-gray-400">Balasan admin bisa dilihat di Riwayat Bantuan.</p>
                        <x-primary-button wire:loading.attr="disabled">Kirim Pesan</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="space-y-5">
                <div class="bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-600 rounded-2xl p-6 sm:p-8 text-white">
                    <h3 class="text-base font-bold">Kamu sudah login?</h3>
                    <p class="mt-1 text-sm text-teal-100">Lihat riwayat pesan bantuanmu dan balasan dari admin di satu tempat.</p>
                    @auth
                        <a href="{{ route('bantuan.riwayat') }}" wire:navigate
                           class="mt-4 inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-teal-600 hover:bg-teal-50 transition">
                            Buka Riwayat Bantuan
                        </a>
                    @else
                        <a href="{{ route('login') }}" wire:navigate
                           class="mt-4 inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-teal-600 hover:bg-teal-50 transition">
                            Masuk untuk Melihat Riwayat
                        </a>
                    @endauth
                </div>

                <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6 sm:p-8">
                    <h3 class="text-base font-bold text-gray-900">Hubungi Admin</h3>
                    <p class="mt-1 text-sm text-gray-500">Coba chatbot di pojok kanan bawah untuk jawaban instan, atau gunakan form di samping untuk pesan langsung ke admin.</p>
                    <div class="mt-4 space-y-2 text-sm text-gray-600">
                        <p class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-teal-50 text-teal-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                            </span>
                            Chatbot siap 24 jam — jawaban instan untuk pertanyaan umum.
                        </p>
                        <p class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-teal-50 text-teal-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </span>
                            Admin membalas pesan, biasanya dalam 1x24 jam.
                        </p>
                        <p class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-teal-50 text-teal-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                            </span>
                            Data pesanmu aman dan hanya dilihat admin.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>