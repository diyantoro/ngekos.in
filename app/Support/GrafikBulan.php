<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class GrafikBulan
{
    public static function bulan(string $kolom): string
    {
        if (! preg_match('/^[A-Za-z0-9_.]+$/', $kolom)) {
            throw new \InvalidArgumentException('Nama kolom bulan tidak valid.');
        }

        return DB::getDriverName() === 'sqlite'
            ? "strftime('%m/%Y', {$kolom})"
            : "DATE_FORMAT({$kolom}, '%m/%Y')";
    }

    public static function kolomBulan(string $kolom): string
    {
        return self::bulan($kolom).' as bulan';
    }

    public static function jumlahLunas(string $ekspresi = 'jumlah + denda'): string
    {
        return "SUM(CASE WHEN status = 'lunas' THEN {$ekspresi} ELSE 0 END) as lunas";
    }

    public static function kurangiHari(string $param, int $hari): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "date({$param}, '-{$hari} days')"
            : "DATE_SUB({$param}, INTERVAL {$hari} DAY)";
    }
}
