<?php

namespace App\Enums;

/**
 * Peran user — disimpan langsung di kolom users.role (native ENUM DB).
 * Menggantikan tabel roles/model_has_roles Spatie (1 user = 1 role).
 */
enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Pemilik = 'pemilik';
    case Admin = 'admin';
    case AnakKos = 'anak_kos';

    /** @return array<int,string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Pemilik => 'Pemilik',
            self::Admin => 'Admin',
            self::AnakKos => 'Anak Kos',
        };
    }
}
