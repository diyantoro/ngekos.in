<?php

namespace App\Enums;

enum PropertiStatus: string
{
    case Aktif = 'aktif';
    case Nonaktif = 'nonaktif';

    /** @return array<int,string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Aktif => 'Aktif — tampil di Cari Kos',
            self::Nonaktif => 'Nonaktif — disembunyikan',
        };
    }
}
