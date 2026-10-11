<?php

namespace App\Enums;

enum AnggotaStatus: string
{
    case Aktif = 'aktif';
    case Keluar = 'keluar';

    /** @return array<int,string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
