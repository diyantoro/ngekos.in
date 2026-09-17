<?php

namespace App\Services;

use App\Models\PesanBantuan;
use App\Models\User;
use App\Notifications\PesanBantuanBaruNotification;
use Illuminate\Support\Facades\Log;

class BantuanNotifier
{
    public static function sebarkanPesanBaru(PesanBantuan $pesan, ?int $pengirimId = null): void
    {
        User::role(['super_admin', 'admin'])
            ->with('deviceTokens')
            ->chunkById(100, function ($admins) use ($pesan, $pengirimId) {
                foreach ($admins as $admin) {
                    if ($pengirimId !== null && $admin->id === $pengirimId) {
                        continue;
                    }

                    $admin->notify(new PesanBantuanBaruNotification($pesan));

                    try {
                        PushNotifier::sendToUser(
                            $admin,
                            [
                                'title' => 'Pesan bantuan baru',
                                'body' => ($pesan->subjek ?: 'Pesan bantuan').' dari '.$pesan->nama.'. Lihat di Pesan Masuk.',
                            ],
                            ['type' => 'bantuan_baru', 'pesan_id' => (string) $pesan->id],
                            'bantuan_baru'
                        );
                    } catch (\Throwable $e) {
                        Log::warning('Gagal kirim push bantuan baru #'.$pesan->id.': '.$e->getMessage());
                    }
                }
            });

        cache()->forget('bantuan_masuk_count');
    }
}
