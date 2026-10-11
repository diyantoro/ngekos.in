<?php

namespace App\Enums;

enum ModeHunian: string
{
    case Tunggal = 'tunggal';
    case Patungan = 'patungan';

    /** @return array<int,string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
