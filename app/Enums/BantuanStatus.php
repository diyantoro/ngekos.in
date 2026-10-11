<?php

namespace App\Enums;

enum BantuanStatus: string
{
    case Baru = 'baru';
    case Dibaca = 'dibaca';
    case Selesai = 'selesai';

    /** @return array<int,string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
