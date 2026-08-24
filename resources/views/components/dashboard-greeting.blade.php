@props(['roleLabel', 'description', 'icon'])

<div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 rounded-2xl shadow-lg shadow-teal-200/60 overflow-hidden">
    <div class="p-6 sm:p-8">
        <div class="flex items-start justify-between gap-4">
            <div class="text-white">
                <p class="text-sm text-teal-200">{{ now()->translatedFormat('l, d F Y') }}</p>
                <h3 class="mt-1 text-xl sm:text-2xl font-bold">Selamat datang, {{ auth()->user()->nama }}!</h3>
                <p class="mt-2 text-sm text-teal-100 max-w-2xl">{{ $description }}</p>
            </div>
            <span class="hidden sm:inline-flex items-center gap-2 shrink-0 rounded-full bg-white/15 backdrop-blur px-4 py-1.5 text-sm font-semibold text-white ring-1 ring-white/25">
                {!! $icon !!}
                {{ $roleLabel }}
            </span>
        </div>
    </div>
</div>