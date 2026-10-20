<?php

namespace App\Enums;

enum PenyewaanStatus: string
{
    case Aktif = 'aktif';
    case Selesai = 'selesai';

    /**
     * Booking FIKS (DP terverifikasi) tapi anak kos BELUM masuk.
     * Kamar ditahan untuknya. Di fase ini sewa boleh dibatalkan:
     * DP hangus untuk pemilik, tagihan lain batal, kamar langsung
     * tersedia lagi. Jadi 'aktif' setelah pemilik menandai "sudah masuk"
     * atau pembayaran sewa pertama terverifikasi.
     */
    case Dibooking = 'dibooking';

    /** Booking menunggu verifikasi bukti DP oleh pemilik/admin. Kamar ditahan. */
    case MenungguVerifikasi = 'menunggu_verifikasi';

    /** Booking ditolak/dibatalkan sebelum aktif. Kamar dilepas kembali. */
    case Dibatalkan = 'dibatalkan';

    /** @return array<int,string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
