<?php

namespace App\Enums;

enum LanggananPlan: string
{
    case Free = 'free';
    case Pro = 'pro';
    case Business = 'business';

    /** @return array<int,string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
