<?php

namespace App\Enums;

enum PengeluaranKategori: string
{
    case Listrik = 'listrik';
    case Air = 'air';
    case Internet = 'internet';
    case Maintenance = 'maintenance';
    case Kebersihan = 'kebersihan';
    case Gaji = 'gaji';
    case Renovasi = 'renovasi';
    case Lainnya = 'lainnya';

    /** @return array<int,string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
