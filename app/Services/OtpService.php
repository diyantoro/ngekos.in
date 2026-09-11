<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Satu pintu OTP reset password: anti-enumerasi, attempt-limit,
 * lockout, dan dev-bocor hanya di testing.
 */
class OtpService
{
    public const TTL_MENIT = 10;

    public const MAKS_GAGAL = 5;

    public const LOCKOUT_MENIT = 15;

    public static function kunciOtp(string $email): string
    {
        return 'password_reset_otp_'.strtolower(trim($email));
    }

    public static function kunciGagal(string $email): string
    {
        return 'password_reset_otp_fail_'.strtolower(trim($email));
    }

    public static function kunciLock(string $email): string
    {
        return 'password_reset_otp_lock_'.strtolower(trim($email));
    }

    public static function terkunci(string $email): bool
    {
        return (bool) Cache::get(self::kunciLock($email));
    }

    public static function buat(string $email): string
    {
        $otp = (string) random_int(100000, 999999);
        Cache::put(self::kunciOtp($email), $otp, now()->addMinutes(self::TTL_MENIT));
        Cache::forget(self::kunciGagal($email));

        return $otp;
    }

    public static function verifikasi(string $email, string $otp): bool
    {
        if (self::terkunci($email)) {
            return false;
        }

        $tersimpan = Cache::get(self::kunciOtp($email));

        if ($tersimpan && hash_equals((string) $tersimpan, $otp)) {
            Cache::forget(self::kunciOtp($email));
            Cache::forget(self::kunciGagal($email));

            return true;
        }

        $gagal = (int) Cache::get(self::kunciGagal($email), 0) + 1;
        Cache::put(self::kunciGagal($email), $gagal, now()->addMinutes(self::LOCKOUT_MENIT));

        if ($gagal >= self::MAKS_GAGAL) {
            Cache::put(self::kunciLock($email), true, now()->addMinutes(self::LOCKOUT_MENIT));
            Cache::forget(self::kunciOtp($email));
        }

        return false;
    }

    public static function pesanGenerik(): string
    {
        return 'Jika email terdaftar, kode verifikasi telah dikirim ke email kamu.';
    }

    public static function bolehTampilDev(): bool
    {
        return app()->environment('testing');
    }
}
