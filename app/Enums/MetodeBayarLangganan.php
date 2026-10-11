<?php

namespace App\Enums;

enum MetodeBayarLangganan: string
{
    case Qris = 'qris';
    case Manual = 'manual';

    /** @return array<int,string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
