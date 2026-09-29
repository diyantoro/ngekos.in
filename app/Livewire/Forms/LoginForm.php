<?php

namespace App\Livewire\Forms;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @param  string|null  $peran  Peran yang dipilih di halaman login (anak_kos|pemilik).
     *
     * @throws ValidationException
     */
    public function authenticate(?string $peran = null): void
    {
        // Normalisasi: spasi / huruf besar di email sering bikin
        // "tidak bisa login lagi" padahal akun masih aktif.
        $this->email = Str::lower(trim($this->email));

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only(['email', 'password']), $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'form.email' => trans('auth.failed'),
            ]);
        }

        if (! Auth::user()->aktif()) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'form.email' => 'Akun Anda dinonaktifkan. Silakan hubungi admin atau super admin.',
            ]);
        }

        // Samakan dengan API mobile: anak kos ↔ pemilik tidak boleh
        // saling "login dari halaman peran lain". Tanpa ini, anak kos bisa
        // "login dari pemilik" (atau sebaliknya) karena Auth::attempt lolos
        // lalu redirect diam-diam ke dashboard role aslinya.
        // Admin/super admin tetap boleh masuk lewat halaman mana pun
        // (mereka tidak punya halaman login sendiri), begitu juga
        // akun lama tanpa role agar tidak terkunci.
        if (in_array($peran, ['anak_kos', 'pemilik'], true)) {
            $user = Auth::user();

            $cocok = $user->hasRole($peran)
                || $user->hasAnyRole(['admin', 'super_admin'])
                || ! $user->hasAnyRole(['anak_kos', 'pemilik']);

            if (! $cocok) {
                Auth::guard('web')->logout();

                $label = $peran === 'pemilik' ? 'Pemilik Kos' : 'Pencari Kos';

                throw ValidationException::withMessages([
                    'form.email' => "Akun ini bukan {$label}. Silakan masuk lewat peran yang sesuai.",
                ]);
            }
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'form.email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower(trim($this->email)).'|'.request()->ip());
    }
}
