<?php

namespace App\Notifications;

use App\Models\PesanBantuan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PesanBantuanBaruNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PesanBantuan $pesan
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'id' => $this->pesan->id,
            'nama' => $this->pesan->nama,
            'email' => $this->pesan->email,
            'subjek' => $this->pesan->subjek,
            'pesan' => $this->pesan->pesan,
        ];
    }
}