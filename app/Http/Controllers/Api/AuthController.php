<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PesanBantuan;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'nullable|string|in:anak_kos,pemilik',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if (! $user->aktif()) {
            throw ValidationException::withMessages([
                'email' => ['Akun kamu telah dinonaktifkan.'],
            ]);
        }

        // Login dari aplikasi mobile hanya untuk pencari kos / pemilik kos.
        // Admin & Super Admin tidak boleh masuk lewat aplikasi demi keamanan.
        if ($user->hasRole('admin') || $user->hasRole('super_admin')) {
            throw ValidationException::withMessages([
                'email' => ['Login admin hanya dapat dilakukan melalui web.'],
            ]);
        }

        if ($request->filled('role')) {
            $label = match ($request->role) {
                'anak_kos' => 'Pencari Kos',
                'pemilik' => 'Pemilik Kos',
                default => 'peran tersebut',
            };

            $cocok = match ($request->role) {
                'anak_kos' => $user->hasRole('anak_kos'),
                'pemilik' => $user->hasRole('pemilik'),
                default => true,
            };

            if (! $cocok) {
                throw ValidationException::withMessages([
                    'role' => ["Akun ini bukan {$label}. Silakan pilih peran yang sesuai."],
                ]);
            }
        }

        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'user' => $this->formatUser($user),
            'token' => $token,
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users',
            'no_hp' => 'nullable|string|max:20',
            'peran' => 'required|in:anak_kos,pemilik',
            'password' => 'required|string|confirmed|min:8',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $data = collect($validated)->only(['nama', 'email', 'no_hp', 'password'])->toArray();

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatar', 'public');
        }

        $user = User::create($data);
        $user->assignRole($validated['peran']);

        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'user' => $this->formatUser($user),
            'token' => $token,
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil logout.']);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json($this->formatUser($request->user()));
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'nama' => 'sometimes|string|max:255',
            'no_hp' => 'nullable|string|max:20',
        ]);

        if ($request->filled('current_password')) {
            $validatedPassword = $request->validate([
                'current_password' => ['required', 'current_password'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'password_confirmation' => ['required'],
            ]);

            $user->update([
                'password' => Hash::make($validatedPassword['password']),
                'remember_token' => Str::random(60),
            ]);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user' => $this->formatUser($user),
        ]);
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Akun berhasil dihapus.']);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Tautan reset password sudah dikirim ke email kamu.'])
            : response()->json(['message' => $status === Password::INVALID_USER ? 'Email tidak terdaftar.' : 'Gagal mengirim tautan reset.'], 422);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'password_confirmation' => ['required'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password berhasil direset. Silakan login.'])
            : response()->json(['message' => 'Token reset tidak valid atau sudah kedaluwarsa.'], 422);
    }

    public function forgotPasswordOtp(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return response()->json(['message' => 'Email tidak terdaftar.'], 422);
        }

        $otp = (string) random_int(100000, 999999);
        $cacheKey = 'password_reset_otp_'.$user->email;
        Cache::put($cacheKey, $otp, now()->addMinutes(10));

        try {
            Mail::raw(
                'Kode verifikasi reset password kamu adalah: '.$otp."\n\n".
                'Kode ini berlaku selama 10 menit. Jangan bagikan kode ini kepada siapa pun.',
                function (Message $message) use ($user) {
                    $message->to($user->email)
                        ->subject('Kode Reset Password Ngekos.in');
                }
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal mengirim email OTP: '.$e->getMessage());
        }

        $data = ['message' => 'Kode verifikasi telah dikirim ke email kamu.'];
        if (app()->environment('local', 'testing')) {
            $data['dev_otp'] = $otp;
        }

        return response()->json($data);
    }

    public function verifyPasswordOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'password_confirmation' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return response()->json(['message' => 'Email tidak terdaftar.'], 422);
        }

        $cacheKey = 'password_reset_otp_'.$user->email;
        $stored = Cache::get($cacheKey);

        if (! $stored || ! hash_equals((string) $stored, $request->otp)) {
            return response()->json(['message' => 'Kode verifikasi salah atau sudah kedaluwarsa.'], 422);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        Cache::forget($cacheKey);

        $user->tokens()->delete();

        event(new PasswordReset($user));

        return response()->json(['message' => 'Password berhasil direset. Silakan login.']);
    }

    public function sendVerificationEmail(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email kamu sudah terverifikasi.']);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Tautan verifikasi baru sudah dikirim ke email kamu.']);
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'nama' => $user->nama,
            'email' => $user->email,
            'no_hp' => $user->no_hp,
            'avatar' => $user->avatar ? '/storage/'.$user->avatar : null,
            'inisial' => $user->inisial,
            'peran' => $user->getRoleNames()->first(),
            'pesan_belum_dibaca' => $user->pesanBelumDibaca(),
            'bantuan_belum_dibaca' => PesanBantuan::where('user_id', $user->id)
                ->whereNotNull('balasan')
                ->whereNull('dibaca_pada')
                ->count(),
        ];
    }
}
