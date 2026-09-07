<?php

use App\Models\PesanBantuan;
use Livewire\Volt\Component;

new class extends Component
{
    public bool $terbuka = false;

    public string $input = '';

    public array $percakapan = [];

    public function mount(): void
    {
        $this->percakapan[] = [
            'dari' => 'bot',
            'teks' => 'Halo! Saya asisten virtual Ngekos.in 👋 Ada yang bisa saya bantu? Coba tanya seputar daftar akun, cari kos, chat pemilik, atau pembayaran.',
        ];
    }

    public function with(): array
    {
        return [
            'tampil' => ! auth()->user()?->hasAnyRole(['super_admin', 'admin']),
        ];
    }

    public function kirim(?string $pertanyaan = null): void
    {
        $pertanyaan = trim($pertanyaan ?? $this->input);

        if ($pertanyaan === '') {
            return;
        }

        $this->percakapan[] = ['dari' => 'user', 'teks' => $pertanyaan];
        $this->input = '';

        $jawaban = $this->cariJawaban($pertanyaan);

        if ($jawaban === null) {
            $this->teruskanKeAdmin($pertanyaan);

            $this->percakapan[] = [
                'dari' => 'bot',
                'teks' => 'Maaf, saya belum bisa menjawab pertanyaan itu. Pertanyaanmu sudah saya teruskan ke admin dan akan dibalas di halaman Riwayat Bantuan. Atau coba tanya dengan kata kunci seperti "chat pemilik", "pembayaran", atau "daftar akun".',
            ];

            return;
        }

        $this->percakapan[] = ['dari' => 'bot', 'teks' => $jawaban];
    }

    protected function cariJawaban(string $pertanyaan): ?string
    {
        $teks = strtolower($pertanyaan);
        $skorTerbaik = 0;
        $jawaban = null;

        foreach (config('faq', []) as $item) {
            $skor = 0;

            foreach ($item['kata_kunci'] as $kataKunci) {
                if (str_contains($teks, $kataKunci)) {
                    $skor++;
                }
            }

            if ($skor > $skorTerbaik) {
                $skorTerbaik = $skor;
                $jawaban = $item['jawaban'];
            }
        }

        return $jawaban;
    }

    protected function teruskanKeAdmin(string $pertanyaan): void
    {
        PesanBantuan::create([
            'user_id' => auth()->id(),
            'nama' => auth()->user()?->nama ?? 'Tamu Chatbot',
            'email' => auth()->user()?->email ?? 'tamu@ngekos.in',
            'subjek' => 'Pertanyaan via Chatbot',
            'pesan' => $pertanyaan,
            'status' => 'baru',
        ]);
    }
}; ?>

<div x-data="{ scroll() { $nextTick(() => { let el = document.getElementById('chat-riwayat'); if (el) el.scrollTop = el.scrollHeight; }); } }"
     x-init="$wire.$watch('percakapan', () => scroll())" class="fixed right-5 z-50 bottom-24 sm:bottom-5 {{ $tampil ? '' : 'hidden' }}">
    <!-- Tombol chat -->
    <button type="button" wire:click="$toggle('terbuka')"
            class="relative flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-teal-500 via-emerald-500 to-green-500 text-white shadow-lg shadow-emerald-300/60 ring-2 ring-white/60 hover:scale-105 hover:shadow-xl hover:shadow-emerald-300/70 transition"
            aria-label="Buka chatbot bantuan">
        <!-- Ikon robot AI -->
        <svg x-show="!$wire.terbuka" class="h-7 w-7" viewBox="0 0 24 24">
            <path d="M12 2.75v2.4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <circle cx="12" cy="2.25" r="1.05" fill="currentColor"/>
            <rect x="3.5" y="7.5" width="17" height="10.5" rx="3.25" fill="none" stroke="currentColor" stroke-width="1.8"/>
            <circle cx="8.5" cy="12.25" r="1.15" fill="currentColor"/>
            <circle cx="15.5" cy="12.25" r="1.15" fill="currentColor"/>
            <path d="M9.75 15.75h4.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        <!-- Titik status online -->
        <span class="absolute -top-0.5 -right-0.5 h-3.5 w-3.5 rounded-full bg-emerald-400 ring-2 ring-white" x-show="!$wire.terbuka"></span>
        <svg x-show="$wire.terbuka" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
    </button>

    <!-- Panel chat -->
    <div x-show="$wire.terbuka" x-cloak
         class="absolute bottom-16 right-0 w-[calc(100vw-2.5rem)] max-w-sm overflow-hidden rounded-2xl bg-white dark:bg-gray-800 shadow-2xl ring-1 ring-gray-200 dark:ring-gray-600 flex flex-col"
         style="max-height: 70vh; height: 30rem;">
        <!-- Header -->
        <div class="flex items-center justify-between bg-gradient-to-r from-teal-600 to-emerald-600 px-4 py-3">
            <div class="flex items-center gap-2.5">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white/20">
                    <svg class="h-5 w-5 text-white" viewBox="0 0 24 24">
                        <path d="M12 3v2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <circle cx="12" cy="2.5" r="0.9" fill="currentColor"/>
                        <rect x="4" y="8" width="16" height="9.5" rx="3" fill="none" stroke="currentColor" stroke-width="1.8"/>
                        <circle cx="8.5" cy="12.5" r="1" fill="currentColor"/>
                        <circle cx="15.5" cy="12.5" r="1" fill="currentColor"/>
                        <path d="M9.75 15.5h4.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-white">Asisten Ngekos.in</p>
                    <p class="text-xs text-teal-200">Balasan otomatis, cepat</p>
                </div>
            </div>
            <button type="button" wire:click="$set('terbuka', false)" class="text-teal-200 hover:text-white" aria-label="Tutup chat">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <!-- Riwayat -->
        <div id="chat-riwayat" class="flex-1 overflow-y-auto space-y-3 bg-gray-50 dark:bg-gray-900 px-4 py-4">
            @foreach ($percakapan as $index => $pesan)
                <div wire:key="chat-{{ $index }}"
                     class="flex {{ $pesan['dari'] === 'user' ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%] rounded-2xl px-3.5 py-2.5 text-sm leading-relaxed shadow-sm
                        {{ $pesan['dari'] === 'user'
                            ? 'bg-teal-600 text-white rounded-br-sm'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 ring-1 ring-gray-100 dark:ring-gray-600 rounded-bl-sm' }}">
                        {{ $pesan['teks'] }}
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Saran cepat -->
        <div class="flex gap-2 overflow-x-auto px-4 py-2 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700">
            @foreach (['Cara daftar akun', 'Cara chat pemilik kos', 'Cara pembayaran', 'Hubungi admin'] as $saran)
                <button type="button" wire:click="kirim('{{ $saran }}')"
                    class="shrink-0 rounded-full bg-teal-50 dark:bg-teal-500/10 px-3 py-1 text-xs font-medium text-teal-700 dark:text-teal-300 ring-1 ring-inset ring-teal-100 dark:ring-teal-500/30 hover:bg-teal-100 dark:hover:bg-teal-500/20 transition">
                    {{ $saran }}
                </button>
            @endforeach
        </div>

        <!-- Input -->
        <form wire:submit="kirim" class="flex items-center gap-2 bg-white dark:bg-gray-800 px-3 py-2.5 border-t border-gray-100 dark:border-gray-700">
            <input type="text" wire:model="input" placeholder="Ketik pertanyaanmu..."
                   class="flex-1 rounded-full border-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:border-teal-500 focus:ring-teal-500">
            <button type="submit" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-teal-600 text-white hover:bg-teal-500 transition" aria-label="Kirim">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" /></svg>
            </button>
        </form>
    </div>
</div>
