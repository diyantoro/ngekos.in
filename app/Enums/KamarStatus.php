<?php

namespace App\Enums;

enum KamarStatus: string
{
    case Tersedia = 'tersedia';
    case Terisi = 'terisi';
    case Perbaikan = 'perbaikan';

    /** @return array<int,string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Tersedia => 'Tersedia',
            self::Terisi => 'Terisi',
            self::Perbaikan => 'Perbaikan',
        };
    }
}
