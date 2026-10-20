@props([
    'title' => null,
    'subtitle' => null,
    'tinggi' => '15rem',
    'kosong' => false,
    'pesanKosong' => 'Belum ada data pada periode ini.',
])

<div {{ $attributes->merge(['class' => 'min-w-0 overflow-hidden rounded-2xl border border-brand-900/15 bg-white shadow-md ring-1 ring-brand-900/5 dark:border-gray-600 dark:bg-gray-800 dark:ring-gray-700/50']) }}>
    <div class="flex flex-col gap-2 bg-brand-900 px-4 py-3.5 text-white sm:flex-row sm:items-center sm:justify-between sm:px-5">
        <div class="flex min-w-0 items-start gap-2.5">
            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/10 ring-1 ring-inset ring-white/15">
                <svg class="h-4 w-4 text-amber-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                </svg>
            </span>
            <div class="min-w-0">
                @if ($title)
                    <h3 class="text-sm font-bold text-white sm:text-base">{{ $title }}</h3>
                @endif
                @if ($subtitle)
                    <p class="mt-0.5 text-xs text-brand-100/80">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
        @isset($toolbar)
            <div class="shrink-0">{{ $toolbar }}</div>
        @endisset
    </div>

    <div class="bg-stone-100/80 p-3 dark:bg-gray-900/50 sm:p-4">
        <div class="rounded-xl border border-stone-200 bg-white p-2.5 shadow-inner dark:border-gray-600 dark:bg-gray-800 sm:p-3">
            @if ($kosong)
                <div class="flex flex-col items-center justify-center rounded-lg border border-dashed border-stone-300 bg-stone-50 px-4 text-center dark:border-gray-600 dark:bg-gray-800/60" style="min-height: {{ $tinggi }}">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-stone-100 text-slate-400 dark:bg-gray-700 dark:text-gray-500">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                    </span>
                    <p class="mt-2 text-sm text-slate-500 dark:text-gray-400">{{ $pesanKosong }}</p>
                </div>
            @else
                <div class="relative w-full" style="height: {{ $tinggi }}">
                    {{ $slot }}
                </div>
            @endif
        </div>
    </div>
</div>
