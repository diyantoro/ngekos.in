<?php

namespace App\Mail;

use App\Models\Tagihan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email pengingat / peringatan tagihan (H-3, H-1, H0, telat harian).
 * Dijalankan via queue (database) dari TagihanReminderService.
 */
class PengingatTagihanMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Tagihan $tagihan,
        public string $jenis,
        public array $ringkasan,
        public string $namaKos,
        public string $namaKamar,
        public bool $isPatungan,
        public ?float $porsiSaya = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjek());
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pengingat-tagihan',
            with: [
                'tagihan' => $this->tagihan,
                'jenis' => $this->jenis,
                'subjek' => $this->subjek(),
                'sapaan' => $this->sapaan(),
                'ringkasan' => $this->ringkasan,
                'namaKos' => $this->namaKos,
                'namaKamar' => $this->namaKamar,
                'isPatungan' => $this->isPatungan,
                'porsiSaya' => $this->porsiSaya,
            ],
        );
    }

    public function subjek(): string
    {
        return match ($this->jenis) {
            'h-3' => 'Pengingat: tagihan kos 3 hari lagi jatuh tempo',
            'h-1' => 'Pengingat: tagihan kos besok jatuh tempo',
            'h0' => 'Hari ini jatuh tempo tagihan kos',
            default => 'Tagihan kos terlambat — segera lunasi + denda',
        };
    }

    private function sapaan(): string
    {
        $jatuh = $this->tagihan->jatuh_tempo?->translatedFormat('d F Y') ?? '-';
        $total = 'Rp'.number_format($this->ringkasan['total'], 0, ',', '.');

        return match ($this->jenis) {
            'h-3' => "Tagihan {$this->tagihan->periode} sebesar {$total} jatuh tempo 3 hari lagi ({$jatuh}).",
            'h-1' => "Tagihan {$this->tagihan->periode} sebesar {$total} jatuh tempo besok ({$jatuh}).",
            'h0' => "Tagihan {$this->tagihan->periode} sebesar {$total} jatuh tempo HARI INI ({$jatuh}).",
            default => "Tagihan {$this->tagihan->periode} sudah terlambat {$this->ringkasan['hari_telat']} hari. Total saat ini {$total} (termasuk denda).",
        };
    }
}
