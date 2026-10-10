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
            ->whereNotIn('status', ['lunas', 'batal'])
            ->whereHas('penyewaan', fn ($q) => $q->where('status', 'aktif'))
            ->with(['penyewaan.kamar:id,nama', 'penyewaan.properti:id,nama,pemilik_id', 'penyewaan.anggotas', 'penyewaan.anakKos:id,nama,email', 'pembayarans:id,tagihan_id,anak_kos_id,jumlah,status'])
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

    /**
     * Daftar jenis yang wajib dikirim pada tanggal $pada, termasuk susulan.
     * H-3/H-1/H0 dikirim sekali per tagihan; kalau scheduler mati tepat di
     * hari H-nya, dikirim susulan di run berikutnya (selama tagihannya sudah
     * ada saat tanggal pengingat itu). Telat tetap harian.
     *
     * @return array<int, array{jenis: string, susulan: bool}>
     */
    public static function jenisTertunggak(Tagihan $tagihan, int $selisih, ?Carbon $pada = null): array
    {
        $pada ??= Carbon::today();

        if ($selisih < 0) {
            $sudahHariIni = TagihanPengingat::where('tagihan_id', $tagihan->id)
                ->where('jenis', self::JENIS_TELAT)
                ->whereDate('tanggal_kirim', $pada->toDateString())
                ->exists();

            return $sudahHariIni ? [] : [['jenis' => self::JENIS_TELAT, 'susulan' => false]];
        }

        $daftar = [];
        $dibuat = $tagihan->created_at ? $tagihan->created_at->copy()->startOfDay() : $pada->copy()->startOfDay();
        $jatuh = $tagihan->jatuh_tempo ? $tagihan->jatuh_tempo->copy()->startOfDay() : $pada->copy()->startOfDay();

        $sudahKirim = fn (string $jenis) => TagihanPengingat::where('tagihan_id', $tagihan->id)
            ->where('jenis', $jenis)
            ->exists();

        // H-3: dari H-3 sampai H0 (otomatis). Kirim susulan jika tagihan sudah ada sebelum/tepat tanggal H-3 tapi belum dikirim.
        if (! $sudahKirim(self::JENIS_H3) && $selisih <= 3) {
            $tglH3 = $jatuh->copy()->subDays(3);
            if (! $dibuat->greaterThan($tglH3)) {
                $daftar[] = ['jenis' => self::JENIS_H3, 'susulan' => $selisih !== 3];
            }
        }

        // H-1: dari H-1 sampai H0
        if (! $sudahKirim(self::JENIS_H1) && $selisih <= 1) {
            $tglH1 = $jatuh->copy()->subDay();
            if (! $dibuat->greaterThan($tglH1)) {
                $daftar[] = ['jenis' => self::JENIS_H1, 'susulan' => $selisih !== 1];
            }
        }

        // H0: jatuh tempo
        if (! $sudahKirim(self::JENIS_H0) && $selisih === 0) {
            $daftar[] = ['jenis' => self::JENIS_H0, 'susulan' => false];
        }

        return $daftar;
    }

    public static function prosesSatu(Tagihan $tagihan, ?Carbon $pada = null): bool
    {
        $pada ??= Carbon::today();

        if (in_array($tagihan->status, ['lunas', 'batal'], true) || ! $tagihan->penyewaan || $tagihan->penyewaan->status !== 'aktif') {
            return false;
        }

        $selisih = TagihanService::selisihHari($tagihan, $pada);
        $daftar = self::jenisTertunggak($tagihan, $selisih, $pada);

        if ($daftar === []) {
            return false;
        }

        TagihanService::sinkronDenda($tagihan, $pada);
        $tagihan->loadMissing(['penyewaan.kamar', 'penyewaan.properti', 'penyewaan.anggotas', 'penyewaan.anakKos', 'pembayarans']);

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

        // Jangan ingatkan penghuni yang porsinya sudah lunas / sudah ajukan pembayaran.
        $targetIds = array_values(array_filter($penghuniIds, function ($uid) use ($tagihan) {
            if (TagihanService::wajibBayar($tagihan, (int) $uid) <= 0) {
                return false;
            }

            $menunggu = $tagihan->relationLoaded('pembayarans')
                ? $tagihan->pembayarans->where('anak_kos_id', (int) $uid)->where('status', 'menunggu_verifikasi')->isNotEmpty()
                : $tagihan->pembayarans()->where('anak_kos_id', (int) $uid)->where('status', 'menunggu_verifikasi')->exists();

            return ! $menunggu;
        }));

        if ($targetIds === []) {
            return false;
        }

        $terkirim = false;

        foreach ($daftar as $item) {
            $jenis = $item['jenis'];
            $susulan = $item['susulan'];

            foreach ($targetIds as $uid) {
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
                        self::isiChat($tagihan, $jenis, $rincian, $namaKos, $namaKamar, $isPatungan, $porsi, $susulan),
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
                    'susulan' => $susulan,
                    'total' => $rincian['total'],
                    'denda' => $rincian['denda'],
                    'hari_telat' => $rincian['hari_telat'],
                ],
            ]);

            $terkirim = true;
        }

        return $terkirim;
    }

    public static function isiChat(Tagihan $tagihan, string $jenis, array $rincian, string $namaKos, string $namaKamar, bool $isPatungan, ?float $porsi, bool $susulan = false): string
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

        if ($susulan) {
            $isi .= ' (Pesan susulan: pengingat sebelumnya terlewat karena jadwal otomatis tidak jalan.)';
        }

        return $isi.' Bayar lewat menu Kos Saya. Terima kasih.';
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

        foreach ($penyewaan->tagihans()->whereNotIn('status', ['lunas', 'batal'])->get() as $tagihan) {
            if (self::prosesSatu($tagihan, $pada)) {
                $kirim++;
            }
        }

        return $kirim;
    }
}
