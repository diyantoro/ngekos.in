<?php

namespace App\Services;

use App\Models\Subscription;
use App\Notifications\LanggananAkanBerakhirNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class LanggananReminderService
{
    public static function kirimHarian(?Carbon $pada = null): array
    {
        $pada ??= Carbon::today();
        $hasil = ['cek' => 0, 'kirim' => 0, 'lewati' => 0];

        Subscription::query()
            ->whereIn('plan', ['pro', 'business'])
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '>=', $pada->toDateString())
            ->whereDate('expires_at', '<=', $pada->copy()->addDays(3)->toDateString())
            ->with('user:id,nama,email')
            ->chunkById(100, function ($rows) use ($pada, &$hasil) {
                foreach ($rows as $sub) {
                    $hasil['cek']++;

                    if (self::prosesSatu($sub, $pada)) {
                        $hasil['kirim']++;
                    } else {
                        $hasil['lewati']++;
                    }
                }
            });

        return $hasil;
    }

    public static function prosesSatu(Subscription $sub, ?Carbon $pada = null): bool
    {
        $pada ??= Carbon::today();

        if (! $sub->isActive() || ! in_array($sub->plan, ['pro', 'business'], true)) {
            return false;
        }

        $user = $sub->user;

        if (! $user) {
            return false;
        }

        $sisaHari = (int) $pada->copy()->startOfDay()->diffInDays($sub->expires_at->copy()->startOfDay(), false);

        if ($sisaHari < 0 || $sisaHari > 3) {
            return false;
        }

        $sudah = $user->notifications()
            ->where('type', LanggananAkanBerakhirNotification::class)
            ->get()
            ->contains(fn ($n) => ($n->data['subscription_id'] ?? null) == $sub->id
                && ($n->data['sisa_hari'] ?? null) == $sisaHari);

        if ($sudah) {
            return false;
        }

        $tanggal = $sub->expires_at?->translatedFormat('d F Y') ?? '-';
        $plan = strtoupper($sub->plan);

        try {
            $user->notify(new LanggananAkanBerakhirNotification($sub, $sisaHari));
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim notif langganan H-3 #'.$sub->id.': '.$e->getMessage());

            return false;
        }

        try {
            PushNotifier::sendToUser(
                $user,
                [
                    'title' => "Paket {$plan} berakhir {$sisaHari} hari lagi",
                    'body' => "Paket {$plan} berakhir {$tanggal}. Perpanjang agar fitur premium tetap aktif.",
                ],
                ['type' => 'langganan_hampir_berakhir', 'subscription_id' => (string) $sub->id, 'sisa_hari' => (string) $sisaHari],
                'langganan_hampir_berakhir'
            );
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim push langganan H-3 #'.$sub->id.': '.$e->getMessage());
        }

        return true;
    }
}
