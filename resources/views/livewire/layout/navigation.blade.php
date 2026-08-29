<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-14">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2">
                        <x-application-logo class="h-8 w-8" />
                        <x-brand-name class="hidden sm:block text-lg font-bold text-gray-800" />
                    </a>
                </div>

                <div class="hidden space-x-1 sm:-my-px sm:ms-6 sm:flex items-center">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('kos.index')" :active="request()->routeIs('kos.*')" wire:navigate>
                        {{ __('Cari Kos') }}
                    </x-nav-link>
                    @if (auth()->user()->hasAnyRole(['anak_kos', 'pemilik']))
                        <x-nav-link :href="route('chat.index')" :active="request()->routeIs('chat.*')" wire:navigate>
                            {{ __('Pesan') }}
                            @php $unread = auth()->user()->pesanBelumDibaca(); @endphp
                            @if ($unread > 0)
                                <span class="ms-1 inline-flex items-center justify-center h-4 min-w-4 px-1 rounded-full bg-rose-500 text-[10px] font-bold text-white align-middle">
                                    {{ $unread > 9 ? '9+' : $unread }}
                                </span>
                            @endif
                        </x-nav-link>
                    @endif
                    @if (auth()->user()->hasAnyRole(['pemilik', 'admin', 'super_admin']))
                        <x-nav-link :href="route('pemilik.properti')" :active="request()->routeIs('pemilik.properti*')" wire:navigate>
                            {{ __('Kelola Kos') }}
                        </x-nav-link>
                    @endif
                    @if (auth()->user()->hasAnyRole(['super_admin', 'admin']))
                        <x-nav-link :href="route('bantuan.masuk')" :active="request()->routeIs('bantuan.masuk')" wire:navigate>
                            {{ __('Pesan Masuk') }}
                        </x-nav-link>
                    @endif
                    @if (auth()->user()->hasRole('super_admin'))
                        <x-nav-link :href="route('pengguna')" :active="request()->routeIs('pengguna')" wire:navigate>
                            {{ __('Pengguna') }}
                        </x-nav-link>
                    @endif
                    <x-nav-link :href="route('bantuan')" :active="request()->routeIs('bantuan')" wire:navigate>
                        {{ __('Bantuan') }}
                    </x-nav-link>
                    <x-nav-link :href="route('pengaturan')" :active="request()->routeIs('pengaturan')" wire:navigate>
                        {{ __('Pengaturan') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown with Avatar -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 px-2 py-1.5 rounded-xl hover:bg-gray-100 transition">
                            <x-user-avatar size="sm" />
                            <div class="text-left hidden md:block">
                                <div x-data="{{ json_encode(['nama' => auth()->user()->nama]) }}" x-text="nama" x-on:profile-updated.window="nama = $event.detail.nama"
                                     class="text-sm font-semibold text-gray-700 leading-tight"></div>
                                @if (auth()->user()->roles->isNotEmpty())
                                    <span class="text-[10px] font-medium text-teal-600">
                                        {{ str(auth()->user()->roles->first()->name)->replace('_', ' ')->title() }}
                                    </span>
                                @endif
                            </div>
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('pengaturan')" wire:navigate>
                            {{ __('Pengaturan') }}
                        </x-dropdown-link>
                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (mobile) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-xl text-gray-400 hover:text-gray-500 hover:bg-gray-100 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-gray-100 bg-white">
        <div class="px-4 pt-3 pb-2 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('kos.index')" :active="request()->routeIs('kos.*')" wire:navigate>
                {{ __('Cari Kos') }}
            </x-responsive-nav-link>
            @if (auth()->user()->hasAnyRole(['anak_kos', 'pemilik']))
                <x-responsive-nav-link :href="route('chat.index')" :active="request()->routeIs('chat.*')" wire:navigate>
                    {{ __('Pesan') }}
                    @if (auth()->user()->pesanBelumDibaca() > 0)
                        <span class="ms-1 inline-flex items-center justify-center h-4 min-w-4 px-1 rounded-full bg-rose-500 text-[10px] font-bold text-white">
                            {{ auth()->user()->pesanBelumDibaca() > 9 ? '9+' : auth()->user()->pesanBelumDibaca() }}
                        </span>
                    @endif
                </x-responsive-nav-link>
            @endif
            @if (auth()->user()->hasAnyRole(['pemilik', 'admin', 'super_admin']))
                <x-responsive-nav-link :href="route('pemilik.properti')" :active="request()->routeIs('pemilik.properti*')" wire:navigate>
                    {{ __('Kelola Kos') }}
                </x-responsive-nav-link>
            @endif
            @if (auth()->user()->hasAnyRole(['super_admin', 'admin']))
                <x-responsive-nav-link :href="route('bantuan.masuk')" :active="request()->routeIs('bantuan.masuk')" wire:navigate>
                    {{ __('Pesan Masuk') }}
                </x-responsive-nav-link>
            @endif
            @if (auth()->user()->hasRole('super_admin'))
                <x-responsive-nav-link :href="route('pengguna')" :active="request()->routeIs('pengguna')" wire:navigate>
                    {{ __('Pengguna') }}
                </x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('bantuan')" :active="request()->routeIs('bantuan')" wire:navigate>
                {{ __('Bantuan') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('pengaturan')" :active="request()->routeIs('pengaturan')" wire:navigate>
                {{ __('Pengaturan') }}
            </x-responsive-nav-link>
        </div>

        <div class="pt-3 pb-3 border-t border-gray-200">
            <div class="px-4 flex items-center gap-3">
                <x-user-avatar size="md" />
                <div>
                    <div class="font-medium text-sm text-gray-800" x-data="{{ json_encode(['nama' => auth()->user()->nama]) }}" x-text="nama" x-on:profile-updated.window="nama = $event.detail.nama"></div>
                    <div class="text-xs text-gray-500">{{ auth()->user()->email }}</div>
                </div>
            </div>
            <div class="mt-3 px-4 space-y-1">
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>
