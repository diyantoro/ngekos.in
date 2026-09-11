<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        if (\App\Services\OtpService::bolehTampilDev()) {
            $user = User::query()->where('email', $this->email)->first();

            if ($user) {
                $token = Password::broker()->createToken($user);

                $user->notify(new ResetPassword($token));

                $otp = \App\Services\OtpService::buat($user->email);

                session()->flash('dev_reset_link', route('password.reset', [
                    'token' => $token,
                    'email' => $user->email,
                ]));
                session()->flash('dev_otp', $otp);
                session()->flash('dev_email', $user->email);

                $this->reset('email');

                session()->flash('status', __('Kami telah mengirim tautan reset password ke email Anda.'));

                return;
            }
        }

        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div>
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Lupa Password?</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Masukkan email Anda dan kami akan kirim tautan reset password.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if (session('dev_reset_link'))
        <div class="rounded-xl bg-teal-50 border border-teal-200 dark:bg-teal-500/10 dark:border-teal-500/20 p-4 text-sm mb-4">
            <p class="font-semibold text-teal-800 dark:text-teal-300 mb-1">Mode pengembangan — tautan reset & kode OTP</p>
            <p class="text-teal-700 dark:text-teal-200/80 mb-2">Email: <span class="font-mono font-semibold">{{ session('dev_email') }}</span></p>
            <p class="mb-1">
                <a href="{{ session('dev_reset_link') }}" class="underline font-medium text-teal-700 dark:text-teal-200 hover:text-teal-500 break-all">Buka tautan reset (klik di sini)</a>
            </p>
            <p class="text-teal-700 dark:text-teal-200/80">Kode OTP: <span class="font-mono font-semibold tracking-widest">{{ session('dev_otp') }}</span> <span class="text-teal-600/70 dark:text-teal-300/60">(berlaku 10 menit, bisa dipakai di aplikasi mobile)</span></p>
            <p class="mt-1 text-teal-600/70 dark:text-teal-300/60">Kotak ini hanya tampil di mode pengembangan.</p>
        </div>
    @endif

    <form wire:submit="sendPasswordResetLink" class="space-y-5">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <div class="relative mt-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 dark:text-gray-500">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                </span>
                <x-text-input wire:model="email" id="email" class="block w-full pl-10" type="email" name="email" required autofocus placeholder="nama@email.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500" href="{{ route('login') }}" wire:navigate>
                {{ __('Kembali ke login') }}
            </a>

            <x-primary-button>
                Kirim Tautan Reset
            </x-primary-button>
        </div>
    </form>
</div>
