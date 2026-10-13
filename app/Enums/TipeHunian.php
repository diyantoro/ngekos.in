<?php

namespace App\Enums;

enum TipeHunian: string
{
    case Putra = 'putra';
    case Putri = 'putri';
    case Campur = 'campur';

    /** @return array<int,string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Putra => 'Putra',
            self::Putri => 'Putri',
            self::Campur => 'Campur',
        };
    }
}
