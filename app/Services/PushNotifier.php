<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pengirim notifikasi push (FCM) ke perangkat user.
 *
 * Jika server key FCM belum diisi di .env (FCM_SERVER_KEY), notifikasi akan
 * ditulis ke log saja agar tetap bisa diuji di lingkungan pengembangan.
 */
class PushNotifier
{
    public static function sendToUser(
        User $user,
        array $title,
        array $data = [],
        ?string $kunciPreferensi = null
    ): void {
        if ($kunciPreferensi && ! $user->notif($kunciPreferensi)) {
            return;
        }

        $tokens = $user->deviceTokens()->pluck('token');

        if ($tokens->isEmpty()) {
            return;
        }

        $serverKey = config('services.fcm.server_key');

        if (! $serverKey) {
            Log::info('[PUSH] Simulasi push untuk '.$user->nama.' ('.implode(',', $tokens->all()).')', [
                'notification' => $title,
                'data' => $data,
            ]);

            return;
        }

        foreach ($tokens as $token) {
            self::sendLegacyFcm($token, $title, $data, $serverKey);
        }
    }

    private static function sendLegacyFcm(string $token, array $title, array $data, string $serverKey): void
    {
        try {
            Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'key '.$serverKey,
                    'Content-Type' => 'application/json',
                ])
                ->post('https://fcm.googleapis.com/fcm/send', [
                    'to' => $token,
                    'notification' => $title,
                    'data' => $data,
                ]);
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim FCM: '.$e->getMessage());
        }
    }
}