@props([
    'pesan',
    'judul' => 'Berhasil!',
    'properti' => 'pesan',
])

@if ($pesan)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="alert" aria-live="assertive">
        <button type="button" wire:click="$set('{{ $properti }}', null)"
            class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm cursor-default" tabindex="-1" aria-label="Tutup"></button>

        <div x-data="{ tampil: false }" x-init="$nextTick(() => tampil = true)" x-show="tampil"
            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl ring-1 ring-gray-100 overflow-hidden">

            <div class="h-1.5 bg-gradient-to-r from-teal-500 via-emerald-500 to-green-500"></div>

            <div class="px-6 py-7 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>

                <h3 class="mt-4 text-lg font-extrabold text-gray-900">{{ $judul }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $pesan }}</p>

                <button type="button" wire:click="$set('{{ $properti }}', null)"
                    class="mt-6 w-full inline-flex items-center justify-center rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-500 transition">
                    Mengerti
                </button>
            </div>
        </div>
    </div>
@endif
