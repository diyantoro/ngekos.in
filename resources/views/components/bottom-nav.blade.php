@php
    $isHome = request()->routeIs('home') || request()->routeIs('dashboard*');
@endphp
<div class="fixed bottom-0 left-0 right-0 z-50 bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl border-t border-gray-200/80 dark:border-gray-800 pb-safe sm:hidden safe-bottom">
    <nav class="flex items-center justify-around h-16">
        <a href="{{ route('home') }}" wire:navigate
           class="relative flex flex-col items-center justify-center gap-0.5 w-16 py-1 transition-all duration-200 {{ $isHome ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400 active:text-gray-600 dark:active:text-gray-300' }}">
            @if ($isHome)
                <span class="absolute -top-px left-1/2 -translate-x-1/2 h-0.5 w-8 rounded-full bg-gradient-to-r from-teal-500 to-emerald-500"></span>
            @endif
            <svg class="h-6 w-6 transition-transform duration-200 {{ $isHome ? 'scale-110' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
            <span class="text-[10px] font-semibold">Beranda</span>
        </a>

        <a href="{{ route('kos.index') }}" wire:navigate
           class="relative flex flex-col items-center justify-center gap-0.5 w-16 py-1 transition-all duration-200 {{ request()->routeIs('kos.*') ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400 active:text-gray-600 dark:active:text-gray-300' }}">
            @if (request()->routeIs('kos.*'))
                <span class="absolute -top-px left-1/2 -translate-x-1/2 h-0.5 w-8 rounded-full bg-gradient-to-r from-teal-500 to-emerald-500"></span>
            @endif
            <svg class="h-6 w-6 transition-transform duration-200 {{ request()->routeIs('kos.*') ? 'scale-110' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
            <span class="text-[10px] font-semibold">Cari Kos</span>
        </a>

        @auth
            <a href="{{ route('chat.index') }}" wire:navigate
               class="relative flex flex-col items-center justify-center gap-0.5 w-16 py-1 transition-all duration-200 {{ request()->routeIs('chat.*') ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400 active:text-gray-600 dark:active:text-gray-300' }}">
                @if (request()->routeIs('chat.*'))
                    <span class="absolute -top-px left-1/2 -translate-x-1/2 h-0.5 w-8 rounded-full bg-gradient-to-r from-teal-500 to-emerald-500"></span>
                @endif
                <svg class="h-6 w-6 transition-transform duration-200 {{ request()->routeIs('chat.*') ? 'scale-110' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                @php $unread = auth()->user()->pesanBelumDibaca(); @endphp
                @if ($unread > 0)
                    <span class="absolute top-0.5 right-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[9px] font-bold text-white shadow-sm animate-bounce-gentle">{{ $unread > 9 ? '9+' : $unread }}</span>
                @endif
                <span class="text-[10px] font-semibold">Pesan</span>
            </a>

            @if (auth()->user()->hasAnyRole(['pemilik', 'admin', 'super_admin']))
                <a href="{{ route('pemilik.properti') }}" wire:navigate
                   class="relative flex flex-col items-center justify-center gap-0.5 w-16 py-1 transition-all duration-200 {{ request()->routeIs('pemilik.*') ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400 active:text-gray-600 dark:active:text-gray-300' }}">
                    @if (request()->routeIs('pemilik.*'))
                        <span class="absolute -top-px left-1/2 -translate-x-1/2 h-0.5 w-8 rounded-full bg-gradient-to-r from-teal-500 to-emerald-500"></span>
                    @endif
                    <svg class="h-6 w-6 transition-transform duration-200 {{ request()->routeIs('pemilik.*') ? 'scale-110' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .414.336.75.75.75z" /></svg>
                    <span class="text-[10px] font-semibold">Kelola</span>
                </a>
            @endif

            <a href="{{ route('pengaturan') }}" wire:navigate
               class="relative flex flex-col items-center justify-center gap-0.5 w-16 py-1 transition-all duration-200 {{ request()->routeIs('pengaturan') ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400 active:text-gray-600 dark:active:text-gray-300' }}">
                @if (request()->routeIs('pengaturan'))
                    <span class="absolute -top-px left-1/2 -translate-x-1/2 h-0.5 w-8 rounded-full bg-gradient-to-r from-teal-500 to-emerald-500"></span>
                @endif
                @if (auth()->user()->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->nama }}" class="h-6 w-6 rounded-full object-cover ring-2 {{ request()->routeIs('pengaturan') ? 'ring-teal-500' : 'ring-gray-300 dark:ring-gray-700' }}">
                @else
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-br from-teal-500 to-emerald-500 text-[10px] font-bold text-white ring-2 {{ request()->routeIs('pengaturan') ? 'ring-teal-500' : 'ring-gray-300 dark:ring-gray-700' }}">{{ auth()->user()->inisial }}</span>
                @endif
                <span class="text-[10px] font-semibold">Akun</span>
            </a>
        @else
            <a href="{{ route('login') }}" wire:navigate
               class="flex flex-col items-center justify-center gap-0.5 w-16 py-1 text-gray-400 active:text-gray-600 dark:text-gray-400 dark:active:text-gray-300 transition-all duration-200">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
                <span class="text-[10px] font-semibold">Masuk</span>
            </a>
            <a href="{{ route('register') }}" wire:navigate
               class="flex flex-col items-center justify-center gap-0.5 w-16 py-1 text-gray-400 active:text-gray-600 dark:text-gray-400 dark:active:text-gray-300 transition-all duration-200">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" /></svg>
                <span class="text-[10px] font-semibold">Daftar</span>
            </a>
        @endauth
    </nav>
</div>
