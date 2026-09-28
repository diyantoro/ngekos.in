<div class="relative overflow-hidden rounded-2xl bg-brand-950 dark:bg-black border border-brand-800 dark:border-brand-500/20 shadow-lg">
    <div class="absolute inset-0 bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 dark:from-black dark:via-brand-950 dark:to-brand-900"></div>
    <!-- Watermark logo Ngekos.in menyatu dengan gradasi -->
    <x-application-logo class="absolute -right-10 -bottom-14 h-52 w-52 opacity-25 pointer-events-none select-none" />
    <div class="absolute inset-0 bg-gradient-to-r from-brand-950/70 via-brand-950/20 to-transparent dark:from-black/70 dark:via-transparent"></div>
    <div class="absolute -left-10 -bottom-16 h-40 w-40 rounded-full bg-amber-400/15 blur-3xl pointer-events-none"></div>
    <div class="relative p-5 sm:p-6 flex items-center gap-4">
        <x-user-avatar size="lg" class="ring-2 ring-white/40 shrink-0" />

        <div class="min-w-0 flex-1">
            <p class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] font-bold uppercase tracking-widest text-brand-100">
                <span>{{ now()->translatedFormat('l, d F Y') }}</span>
                <span class="inline-flex items-center gap-1 rounded-full bg-amber-400 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-widest text-brand-950">{{ $roleLabel }}</span>
            </p>
            <h3 class="mt-1 text-lg sm:text-2xl font-extrabold tracking-tight text-white truncate drop-shadow">Halo, {{ auth()->user()->nama }}!</h3>
            <p class="mt-0.5 text-xs sm:text-sm text-brand-100 max-w-2xl line-clamp-2">{{ $description }}</p>
        </div>

        <span class="hidden sm:flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/10 border border-white/20 text-amber-300 backdrop-blur-sm">
            {!! $icon !!}
        </span>
    </div>
</div>
