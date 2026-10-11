<?php

namespace App\Enums;

enum TagihanStatus: string
{
    case BelumBayar = 'belum_bayar';
    case Lunas = 'lunas';
    case Batal = 'batal';

    /** @return array<int,string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
