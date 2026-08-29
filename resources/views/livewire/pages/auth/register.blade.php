<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.guest')] class extends Component
{
    use WithFileUploads;

    public string $nama = '';
    public string $email = '';
    public string $no_hp = '';
    public string $peran = 'anak_kos';
    public string $password = '';
    public string $password_confirmation = '';
    public $avatar = null;

    public function register(): void
    {
        $validated = $this->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'peran' => ['required', 'in:anak_kos,pemilik'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        if ($this->avatar) {
            $validated['avatar'] = $this->avatar->store('avatar', 'public');
        }

        unset($validated['avatar']);

        $data = [
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'no_hp' => $validated['no_hp'],
            'password' => $validated['password'],
        ];

        if (isset($validated['avatar'])) {
            $data['avatar'] = $validated['avatar'];
        }

        $user = User::create($data);
        $user->assignRole($validated['peran']);

        event(new Registered($user));
        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Daftar Akun Baru</h2>
        <p class="mt-1 text-sm text-gray-500">Bergabung dan mulai cari kos impianmu.</p>
    </div>

    <form wire:submit="register" autocomplete="off" class="space-y-5">
        <!-- Avatar Upload -->
        <div class="flex flex-col items-center">
            <div class="relative group" x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false">
                <div class="h-20 w-20 rounded-full bg-gradient-to-br from-teal-100 to-emerald-100 ring-4 ring-white shadow-lg flex items-center justify-center overflow-hidden">
                    @if ($avatar)
                        <img src="{{ $avatar->temporaryUrl() }}" class="h-full w-full object-cover" alt="Avatar">
                    @else
                        <svg class="h-10 w-10 text-teal-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                    @endif
                </div>
                <label class="absolute inset-0 rounded-full bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center cursor-pointer transition-opacity">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" /></svg>
                    <input type="file" wire:model="avatar" accept="image/*" class="sr-only">
                </label>
            </div>
            <p class="mt-2 text-xs text-gray-400">Foto profil (opsional)</p>
            <x-input-error :messages="$errors->get('avatar')" class="mt-1" />
            <div wire:loading wire:target="avatar" class="mt-1 flex items-center gap-1 text-xs font-medium text-teal-600">
                <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                Mengunggah...
            </div>
        </div>

        <!-- Name -->
        <div>
            <x-input-label for="nama" :value="__('Nama Lengkap')" />
            <div class="relative mt-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                </span>
                <x-text-input wire:model="nama" id="nama" class="block w-full pl-10" type="text" name="nama" required autofocus autocomplete="name" placeholder="Nama lengkap Anda" />
            </div>
            <x-input-error :messages="$errors->get('nama')" class="mt-2" />
        </div>

        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <div class="relative mt-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                </span>
                <x-text-input wire:model="email" id="email" class="block w-full pl-10" type="email" name="email" required autocomplete="username" placeholder="nama@email.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- No. HP -->
        <div>
            <x-input-label for="no_hp" :value="__('No. HP')" />
            <div class="relative mt-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" /></svg>
                </span>
                <x-text-input wire:model="no_hp" id="no_hp" class="block w-full pl-10" type="text" name="no_hp" placeholder="08xxxxxxxxxx" autocomplete="tel" />
            </div>
            <x-input-error :messages="$errors->get('no_hp')" class="mt-2" />
        </div>

        <!-- Peran -->
        <div>
            <x-input-label for="peran" :value="__('Daftar Sebagai')" />
            <div x-data="{ peran: @entangle('peran') }" class="mt-1 grid grid-cols-2 gap-3">
                <label class="group cursor-pointer select-none">
                    <input type="radio" name="peran" value="anak_kos" x-model="peran" class="peer sr-only">
                    <span :class="peran === 'anak_kos'
                            ? 'border-teal-600 bg-teal-50 shadow-md shadow-teal-100'
                            : 'border-gray-200 bg-white group-hover:border-teal-300 group-hover:shadow-sm'"
                          class="relative block rounded-2xl border-2 p-4 text-center transition-all duration-200">
                        <span x-show="peran === 'anak_kos'" x-cloak
                              class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-teal-600 text-white ring-2 ring-white">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        </span>
                        <img src="{{ asset('images/login-tenant.svg') }}" alt="Pencari Kos" class="mx-auto h-24 w-auto">
                        <span :class="peran === 'anak_kos' ? 'text-teal-700' : 'text-gray-900'"
                              class="mt-2 block text-sm font-bold transition-colors duration-200">Pencari Kos</span>
                        <span class="block text-xs text-gray-500 mt-0.5">Cari &amp; tanya kamar</span>
                    </span>
                </label>

                <label class="group cursor-pointer select-none">
                    <input type="radio" name="peran" value="pemilik" x-model="peran" class="peer sr-only">
                    <span :class="peran === 'pemilik'
                            ? 'border-emerald-600 bg-emerald-50 shadow-md shadow-emerald-100'
                            : 'border-gray-200 bg-white group-hover:border-emerald-300 group-hover:shadow-sm'"
                          class="relative block rounded-2xl border-2 p-4 text-center transition-all duration-200">
                        <span x-show="peran === 'pemilik'" x-cloak
                              class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-emerald-600 text-white ring-2 ring-white">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        </span>
                        <img src="{{ asset('images/login-owner.svg') }}" alt="Pemilik Kos" class="mx-auto h-24 w-auto">
                        <span :class="peran === 'pemilik' ? 'text-emerald-700' : 'text-gray-900'"
                              class="mt-2 block text-sm font-bold transition-colors duration-200">Pemilik Kos</span>
                        <span class="block text-xs text-gray-500 mt-0.5">Promosikan kos Anda</span>
                    </span>
                </label>
            </div>
            <x-input-error :messages="$errors->get('peran')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <div x-data="{ show: false }" class="relative mt-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                </span>
                <x-text-input wire:model="password" id="password" class="block w-full pl-10 pr-10"
                                x-bind:type="show ? 'text' : 'password'"
                                type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter" />
                <button type="button" @click="show = !show"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                    <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <svg x-show="show" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <div x-data="{ show: false }" class="relative mt-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                </span>
                <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block w-full pl-10 pr-10"
                                x-bind:type="show ? 'text' : 'password'"
                                type="password" name="password_confirmation" required autocomplete="off" placeholder="Ulangi password" />
                <button type="button" @click="show = !show"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                    <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <svg x-show="show" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500" href="{{ route('login') }}" wire:navigate>
                {{ __('Sudah punya akun?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Daftar') }}
            </x-primary-button>
        </div>
    </form>
</div>
