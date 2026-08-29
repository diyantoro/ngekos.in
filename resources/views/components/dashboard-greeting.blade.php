@props(['roleLabel', 'description', 'icon'])

<div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 rounded-2xl shadow-lg shadow-teal-200/60 overflow-hidden">
    <div class="p-5 sm:p-8">
        <div class="flex items-center gap-4">
            <!-- Avatar -->
            <x-user-avatar size="lg" class="ring-4 ring-white/25 shrink-0" />

            <div class="text-white min-w-0 flex-1">
                <p class="text-xs sm:text-sm text-teal-200">{{ now()->translatedFormat('l, d F Y') }}</p>
                <h3 class="mt-0.5 text-lg sm:text-2xl font-bold truncate">Selamat datang, {{ auth()->user()->nama }}!</h3>
                <p class="mt-1 text-xs sm:text-sm text-teal-100 max-w-2xl line-clamp-2">{{ $description }}</p>
            </div>

            <span class="hidden sm:inline-flex items-center gap-2 shrink-0 rounded-full bg-white/15 backdrop-blur px-4 py-1.5 text-sm font-semibold text-white ring-1 ring-white/25">
                {!! $icon !!}
                {{ $roleLabel }}
            </span>
        </div>
    </div>
</div>
