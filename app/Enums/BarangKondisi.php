<?php

namespace App\Enums;

enum BarangKondisi: string
{
    case Baru = 'baru';
    case BekasSepertiBaru = 'bekas_seperti_baru';
    case BekasMulus = 'bekas_mulus';
    case BekasMinus = 'bekas_minus';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Baru => 'Baru / Segel',
            self::BekasSepertiBaru => 'Bekas - Seperti Baru',
            self::BekasMulus => 'Bekas - Mulus Normal',
            self::BekasMinus => 'Bekas - Ada Minus',
        };
    }
}
