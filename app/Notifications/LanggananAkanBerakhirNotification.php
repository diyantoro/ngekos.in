<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LanggananAkanBerakhirNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Subscription $subscription,
        public int $sisaHari = 3
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'langganan_hampir_berakhir',
            'subscription_id' => $this->subscription->id,
            'plan' => $this->subscription->plan,
            'sisa_hari' => $this->sisaHari,
            'expires_at' => $this->subscription->expires_at?->toDateTimeString(),
        ];
    }
}
