<?php

namespace App\Enums;

/** Periode sewa yang bisa dipilih pencari kos. */
enum PeriodeHarga: string
{
    case Bulanan = 'bulanan';
    case Mingguan = 'mingguan';
    case Harian = 'harian';

    /** @return array<int,string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
