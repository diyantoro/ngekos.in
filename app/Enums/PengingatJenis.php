<?php

namespace App\Enums;

enum PengingatJenis: string
{
    case HMin3 = 'h-3';
    case HMin1 = 'h-1';
    case H0 = 'h0';
    case Telat = 'telat';

    /** @return array<int,string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
