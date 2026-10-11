<?php

namespace App\Enums;

enum PenyewaanStatus: string
{
    case Aktif = 'aktif';
    case Selesai = 'selesai';

    /** @return array<int,string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
