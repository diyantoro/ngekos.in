<?php

namespace App\Notifications;

use App\Models\SubscriptionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LanggananDibayarNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SubscriptionRequest $permintaan
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $user = $this->permintaan->user;

        return [
            'type' => 'langganan_baru',
            'subscription_request_id' => $this->permintaan->id,
            'requested_plan' => $this->permintaan->requested_plan,
            'amount' => $this->permintaan->amount,
            'payment_method' => $this->permintaan->payment_method,
            'status' => $this->permintaan->status,
            'pemilik_nama' => $user?->nama,
            'pemilik_email' => $user?->email,
        ];
    }
}
