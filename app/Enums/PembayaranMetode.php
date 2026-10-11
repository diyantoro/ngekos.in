<?php

namespace App\Enums;

enum PembayaranMetode: string
{
    case Transfer = 'transfer';
    case Cash = 'cash';

    /** @return array<int,string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
