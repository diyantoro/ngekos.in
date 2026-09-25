<?php

namespace App\Services;

use App\Models\SubscriptionRequest;
use App\Models\User;
use App\Notifications\LanggananDibayarNotification;
use Illuminate\Support\Facades\Log;

class LanggananNotifier
{
    /**
     * Sebarkan pemberitahuan pembayaran langganan baru ke admin & super_admin.
     * Dipakai setelah pemilik membayar (otomatis terkonfirmasi) agar admin
     * tetap tahu ada pemasukan tanpa harus menyetujui manual.
     */
    public static function sebarkanPembayaranBaru(SubscriptionRequest $permintaan): void
    {
        $permintaan->loadMissing('user:id,nama,email');

        User::role(['super_admin', 'admin'])
            ->with('deviceTokens')
            ->chunkById(100, function ($admins) use ($permintaan) {
                foreach ($admins as $admin) {
                    $admin->notify(new LanggananDibayarNotification($permintaan));

                    try {
                        PushNotifier::sendToUser(
                            $admin,
                            [
                                'title' => 'Pembayaran langganan baru',
                                'body' => ($permintaan->user?->nama ?? 'Pemilik').' membayar '.strtoupper((string) $permintaan->requested_plan).' Rp'.number_format((int) $permintaan->amount, 0, ',', '.').'. Paket langsung aktif.',
                            ],
                            ['type' => 'langganan_baru', 'subscription_request_id' => (string) $permintaan->id],
                            'langganan_baru'
                        );
                    } catch (\Throwable $e) {
                        Log::warning('Gagal kirim push langganan baru #'.$permintaan->id.': '.$e->getMessage());
                    }
                }
            });
    }
}
