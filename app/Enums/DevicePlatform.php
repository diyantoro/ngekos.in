<?php

namespace App\Enums;

enum DevicePlatform: string
{
    case Android = 'android';
    case Ios = 'ios';

    /** @return array<int,string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
