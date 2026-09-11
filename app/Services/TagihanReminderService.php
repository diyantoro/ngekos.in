<?php

namespace App\Services;

use App\Mail\PengingatTagihanMail;
use App\Models\ChatPesan;
use App\Models\Penyewaan;
use App\Models\Tagihan;
use App\Models\TagihanPengingat;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Pengingat tagihan H-3 / H-1 / H0 / telat-harian.
 * Idempoten via tabel tagihan_pengingat (unique tagihan+jenis+tanggal).
 * Kanal: ChatPesan (menu Pesan + badge) + Push FCM + Email queue.
 */
class TagihanReminderService
{
    public const JENIS_H3 = 'h-3';

    public const JENIS_H1 = 'h-1';

    public const JENIS_H0 = 'h0';

    public const JENIS_TELAT = 'telat';

    public static function jenisUntukSelisih(int $selisih): ?string
    {
        if ($selisih === 3) {
            return self::JENIS_H3;
        }

        if ($selisih === 1) {
            return self::JENIS_H1;
        }

        if ($selisih === 0) {
            return self::JENIS_H0;
        }

        if ($selisih < 0) {
            return self::JENIS_TELAT;
        }

        return null;
    }

    public static function kunciPreferensi(string $jenis): string
    {
        return $jenis === self::JENIS_TELAT ? 'tagihan_telat' : 'pengingat_tagihan';
    }

    /**
     * @return array{cek: int, kirim: int, lewati: int}
     */
    public static function kirimHarian(?Carbon $pada = null): array
    {
        $pada ??= Carbon::today();
        $hasil = ['cek' => 0, 'kirim' => 0, 'lewati' => 0];

        Tagihan::query()
            ->where('status', '!=', 'lunas')
            ->whereHas('penyewaan', fn ($q) => $q->where('status', 'aktif'))
            ->with(['penyewaan.kamar:id,nama', 'penyewaan.properti:id,nama,pemilik_id', 'penyewaan.anggotas', 'penyewaan.anakKos:id,nama,email'])
            ->chunkById(100, function ($tagihans) use ($pada, &$hasil) {
                foreach ($tagihans as $tagihan) {
                    $hasil['cek']++;

                    if (self::prosesSatu($tagihan, $pada)) {
                        $hasil['kirim']++;
                    } else {
                        $hasil['lewati']++;
                    }
                }
            });

        return $hasil;
    }

    public static function prosesSatu(Tagihan $tagihan, ?Carbon $pada = null): bool
    {
        $pada ??= Carbon::today();

        if ($tagihan->status === 'lunas' || ! $tagihan->penyewaan || $tagihan->penyewaan->status !== 'aktif') {
            return false;
        }

        $selisih = TagihanService::selisihHari($tagihan, $pada);
        $jenis = self::jenisUntukSelisih($selisih);

        if ($jenis === null) {
            return false;
        }

        $sudah = TagihanPengingat::where('tagihan_id', $tagihan->id)
            ->where('jenis', $jenis)
            ->whereDate('tanggal_kirim', $pada->toDateString())
            ->exists();

        if ($sudah) {
            return false;
        }

        TagihanService::sinkronDenda($tagihan, $pada);
        $tagihan->loadMissing(['penyewaan.kamar', 'penyewaan.properti', 'penyewaan.anggotas', 'penyewaan.anakKos']);

        $penyewaan = $tagihan->penyewaan;
        $penghuniIds = $penyewaan->idPenghuniAktif();

        if ($penghuniIds === []) {
            return false;
        }

        $users = User::whereIn('id', $penghuniIds)->get()->keyBy('id');
        $rincian = TagihanService::rincian($tagihan, $pada);
        $isPatungan = $penyewaan->isPatungan();
        $pengirimId = (int) ($penyewaan->properti?->pemilik_id ?? $penyewaan->anak_kos_id);
        $namaKos = (string) ($penyewaan->properti?->nama ?? '-');
        $namaKamar = (string) ($penyewaan->kamar?->nama ?? '-');

        foreach ($penghuniIds as $uid) {
            $user = $users->get($uid);

            if (! $user) {
                continue;
            }

            $porsi = $isPatungan ? PatunganService::porsiTagihan($penyewaan, $tagihan) : null;

            try {
                ChatPesan::notifikasiTagihan(
                    (int) $penyewaan->properti_id,
                    (int) $uid,
                    $pengirimId,
                    self::isiChat($tagihan, $jenis, $rincian, $namaKos, $namaKamar, $isPatungan, $porsi),
                );
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim chat pengingat tagihan #'.$tagihan->id.': '.$e->getMessage());
            }

            try {
                PushNotifier::sendToUser(
                    $user,
                    ['title' => self::judulPush($jenis), 'body' => self::isiPush($tagihan, $jenis, $rincian, $isPatungan, $porsi)],
                    ['type' => 'pengingat_tagihan', 'tagihan_id' => (string) $tagihan->id, 'jenis' => $jenis],
                    self::kunciPreferensi($jenis),
                );
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim push pengingat tagihan #'.$tagihan->id.': '.$e->getMessage());
            }

            if ($user->notif(self::kunciPreferensi($jenis)) && $user->email) {
                try {
                    Mail::to($user->email)->queue(new PengingatTagihanMail(
                        $tagihan, $jenis, $rincian, $namaKos, $namaKamar, $isPatungan, $porsi,
                    ));
                } catch (\Throwable $e) {
                    Log::warning('Gagal antre email pengingat tagihan #'.$tagihan->id.': '.$e->getMessage());
                }
            }
        }

        TagihanPengingat::create([
            'tagihan_id' => $tagihan->id,
            'jenis' => $jenis,
            'tanggal_kirim' => $pada->toDateString(),
            'meta' => [
                'selisih' => $selisih,
                'total' => $rincian['total'],
                'denda' => $rincian['denda'],
                'hari_telat' => $rincian['hari_telat'],
            ],
        ]);

        return true;
    }

    public static function isiChat(Tagihan $tagihan, string $jenis, array $rincian, string $namaKos, string $namaKamar, bool $isPatungan, ?float $porsi): string
    {
        $jatuh = $tagihan->jatuh_tempo?->translatedFormat('d F Y') ?? '-';
        $total = TagihanService::rupiah($rincian['total']);
        $sewa = TagihanService::rupiah($rincian['sewa']);
        $pembuka = match ($jenis) {
            self::JENIS_H3 => "Pengingat: tagihan {$tagihan->periode} jatuh tempo 3 hari lagi ({$jatuh}).",
            self::JENIS_H1 => "Pengingat: tagihan {$tagihan->periode} jatuh tempo BESOK ({$jatuh}).",
            self::JENIS_H0 => "Tagihan {$tagihan->periode} jatuh tempo HARI INI ({$jatuh}). Segera bayar agar tidak kena denda.",
            default => "Tagihan {$tagihan->periode} sudah TERLAMBAT {$rincian['hari_telat']} hari (jatuh tempo {$jatuh}).",
        };

        $isi = "{$pembuka} Kos {$namaKos}, kamar {$namaKamar}. Rincian: sewa {$sewa}";

        if ($rincian['denda'] > 0) {
            $isi .= ' + denda '.TagihanService::rupiah($rincian['denda'])
                ." ({$rincian['hari_telat']} hari x ".TagihanService::rupiah($rincian['denda_per_hari']).'/hari)';
        } elseif ($rincian['denda_per_hari'] > 0 && $rincian['hari_telat'] === 0) {
            $isi .= '. Denda '.TagihanService::rupiah($rincian['denda_per_hari']).'/hari bila telat';
        }

        $isi .= ". Total yang harus dibayar: {$total} (harus pas, tidak boleh kurang).";

        if ($isPatungan && $porsi !== null) {
            $isi .= ' Porsi kamu (patungan): '.TagihanService::rupiah($porsi).'.';
        }

        return $isi.' Bayar lewat dashboard (tab Tagihan Saya). Terima kasih.';
    }

    public static function judulPush(string $jenis): string
    {
        return match ($jenis) {
            self::JENIS_H3 => 'Tagihan kos 3 hari lagi jatuh tempo',
            self::JENIS_H1 => 'Tagihan kos besok jatuh tempo',
            self::JENIS_H0 => 'Tagihan kos jatuh tempo hari ini',
            default => 'Tagihan kos terlambat — segera bayar',
        };
    }

    public static function isiPush(Tagihan $tagihan, string $jenis, array $rincian, bool $isPatungan, ?float $porsi): string
    {
        $nominal = TagihanService::rupiah($isPatungan && $porsi !== null ? $porsi : $rincian['total']);

        return match ($jenis) {
            self::JENIS_H3 => "Tagihan {$tagihan->periode} {$nominal}, jatuh tempo 3 hari lagi.",
            self::JENIS_H1 => "Tagihan {$tagihan->periode} {$nominal}, jatuh tempo besok.",
            self::JENIS_H0 => "Tagihan {$tagihan->periode} {$nominal} jatuh tempo hari ini.",
            default => "Tagihan {$tagihan->periode} telat {$rincian['hari_telat']} hari. Total {$nominal}.",
        };
    }

    public static function penyewaanUntukReminder(Penyewaan $penyewaan, ?Carbon $pada = null): int
    {
        $pada ??= Carbon::today();
        $kirim = 0;

        foreach ($penyewaan->tagihans()->where('status', '!=', 'lunas')->get() as $tagihan) {
            if (self::prosesSatu($tagihan, $pada)) {
                $kirim++;
            }
        }

        return $kirim;
    }
}
