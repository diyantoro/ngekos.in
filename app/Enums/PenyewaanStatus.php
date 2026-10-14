<?php

namespace App\Enums;

enum PenyewaanStatus: string
{
    case Aktif = 'aktif';
    case Selesai = 'selesai';

    /** Booking menunggu verifikasi bukti DP oleh pemilik/admin. Kamar ditahan. */
    case MenungguVerifikasi = 'menunggu_verifikasi';

    /** Booking ditolak/dibatalkan sebelum aktif. Kamar dilepas kembali. */
    case Dibatalkan = 'dibatalkan';

    /** @return array<int,string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
