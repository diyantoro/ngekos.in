<?php

namespace App\Enums;

enum BarangStatus: string
{
    case Tersedia = 'tersedia';
    case Terjual = 'terjual';
    case Diarsipkan = 'diarsipkan';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Tersedia => 'Tersedia',
            self::Terjual => 'Terjual',
            self::Diarsipkan => 'Diarsipkan',
        };
    }
}
