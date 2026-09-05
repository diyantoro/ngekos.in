<?php

use App\Models\ChatPesan;
use App\Models\Properti;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.publik')] class extends Component
{
    public Properti $properti;

    public ?User $anakKos = null;

    public string $isiPesan = '';

    public function mount(): void
    {
        $user = auth()->user();

        if ($user->hasRole('anak_kos')) {
            $this->anakKos = $user;

            return;
        }

        abort_unless($this->bisaKelola(), 404);

        if (! $this->anakKos) {
            $this->redirect(route('chat.index'), navigate: true);
        }
    }

    public function with(): array
    {
        ChatPesan::where('properti_id', $this->properti->id)
            ->where('anak_kos_id', $this->anakKos->id)
            ->where('pengirim_id', '!=', auth()->id())
            ->whereNull('dibaca_pada')
            ->update(['dibaca_pada' => now()]);

        return [
            'pesans' => ChatPesan::antara($this->properti->id, $this->anakKos->id)->with('pengirim')->get(),
            'lawan' => auth()->user()->hasRole('anak_kos')
                ? $this->properti->pemilik
                : $this->anakKos,
        ];
    }

    public function kirim(): void
    {
        $valid = $this->validate([
            'isiPesan' => 'required|string|max:1000',
        ]);

        ChatPesan::create([
            'properti_id' => $this->properti->id,
            'anak_kos_id' => $this->anakKos->id,
            'pengirim_id' => auth()->id(),
            'isi' => trim($valid['isiPesan']),
        ]);

        $this->reset('isiPesan');
    }

    protected function bisaKelola(): bool
    {
        $id = auth()->id();

        return $this->properti->pemilik_id === $id
            || $this->properti->admins()->where('users.id', $id)->exists();
    }
}; ?>

<div class="py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('chat.index') }}" wire:navigate
                   class="shrink-0 h-9 w-9 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center justify-center text-gray-600 dark:text-gray-300 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                </a>
                <span class="shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-teal-500 via-emerald-500 to-green-500 flex items-center justify-center text-sm font-extrabold text-white uppercase">
                    {{ mb_substr($lawan?->nama ?? '?', 0, 1) }}
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate">{{ $lawan?->nama ?? 'Pengguna terhapus' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">soal kos {{ $properti->nama }}</p>
                </div>
            </div>
            <a href="{{ route('kos.detail', $properti) }}" wire:navigate
               class="shrink-0 inline-flex items-center rounded-lg border border-teal-200 dark:border-teal-500/30 bg-teal-50 dark:bg-teal-500/10 px-3 py-1.5 text-xs font-semibold text-teal-700 dark:text-teal-300 hover:bg-teal-100 dark:hover:bg-teal-500/20 transition">
                Lihat Kos
            </a>
        </div>

        <!-- Pesan -->
        <div id="ruang-pesan" class="mt-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-5 h-[55vh] overflow-y-auto space-y-3" wire:poll.3s>
            @forelse ($pesans as $p)
                @php
                    $punya = $p->pengirim_id === auth()->id();
                @endphp
                <div class="flex {{ $punya ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[75%] rounded-2xl px-4 py-2.5 {{ $punya ? 'bg-teal-600 text-white rounded-br-md' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-bl-md' }}">
                        <p class="text-sm whitespace-pre-wrap break-words">{{ $p->isi }}</p>
                        <p class="mt-1 text-[10px] {{ $punya ? 'text-teal-200' : 'text-gray-400 dark:text-gray-500' }}">{{ $p->created_at->translatedFormat('d M, H:i') }}</p>
                    </div>
                </div>
            @empty
                <div class="h-full flex flex-col items-center justify-center text-center py-10">
                    <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                    <p class="mt-3 text-sm font-medium text-gray-500 dark:text-gray-400">Belum ada pesan.</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Mulai percakapan dengan menyapa lewat kolom di bawah.</p>
                </div>
            @endforelse
        </div>

        <!-- Input -->
        <form wire:submit="kirim" class="mt-4">
            @error('isiPesan')
                <p class="mb-2 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
            <div class="flex items-end gap-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-2 focus-within:ring-teal-400 transition">
                <textarea wire:model="isiPesan" rows="1" maxlength="1000" placeholder="Tulis pesan..."
                    class="flex-1 resize-none border-0 focus:ring-0 text-sm max-h-32"></textarea>
                <button type="submit" wire:loading.attr="disabled"
                    class="shrink-0 inline-flex items-center gap-1.5 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-500 transition disabled:opacity-50">
                    Kirim
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.125A59.769 59.769 0 0121.485 12 59.768 59.768 0 013.27 20.875L5.999 12zm0 0h7.5" /></svg>
                </button>
            </div>
        </form>
    </div>
</div>

@script
<script>
    const el = document.getElementById('ruang-pesan');
    let tetapBawah = true;

    el.addEventListener('scroll', () => {
        tetapBawah = el.scrollHeight - el.scrollTop - el.clientHeight < 150;
    });

    const keBawah = () => requestAnimationFrame(() => {
        if (tetapBawah) el.scrollTop = el.scrollHeight;
    });

    el.scrollTop = el.scrollHeight;

    Livewire.hook('commit', ({ succeed }) => {
        succeed(() => setTimeout(keBawah, 50));
    });
</script>
@endscript
