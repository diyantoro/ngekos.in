<?php

namespace App\Enums;

enum BarangKategori: string
{
    case Elektronik = 'elektronik';
    case Perabot = 'perabot';
    case Dapur = 'dapur';
    case BukuBelajar = 'buku_belajar';
    case Kebersihan = 'kebersihan';
    case OlahragaHobi = 'olahraga_hobi';
    case PakaianAksesoris = 'pakaian_aksesoris';
    case Lainnya = 'lainnya';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Elektronik => 'Elektronik',
            self::Perabot => 'Perabot & Furnitur',
            self::Dapur => 'Dapur & Masak',
            self::BukuBelajar => 'Buku & Kuliah',
            self::Kebersihan => 'Kebersihan',
            self::OlahragaHobi => 'Olahraga & Hobi',
            self::PakaianAksesoris => 'Pakaian & Aksesoris',
            self::Lainnya => 'Lainnya',
        };
    }
}
