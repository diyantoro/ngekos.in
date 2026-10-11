<?php

namespace App\Enums;

enum PembayaranStatus: string
{
    case MenungguVerifikasi = 'menunggu_verifikasi';
    case Diverifikasi = 'diverifikasi';
    case Ditolak = 'ditolak';

    /** @return array<int,string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
