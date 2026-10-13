<?php

namespace App\Support;

/**
 * Aturan kos baku yang terurut dan jelas.
 * Urutan disepakati: identitas -> jam malam -> tamu -> kebersihan &
 * ketertiban -> larangan (rokok/miras) -> hewan -> pembayaran -> keluar.
 * Penyimpanan: satu baris per aturan (TEXT, dipisah baris baru).
 */
class AturanKos
{
    /**
     * Template baku (terurut). Pemilik boleh menghapus baris yang
     * tidak relevan atau menambah aturan sendiri di baris baru.
     *
     * @return array<int,string>
     */
    public static function template(): array
    {
        return [
            'Penghuni wajib mengisi data diri (nama & no. HP aktif) saat masuk.',
            'Booking dikonfirmasi setelah Uang Muka (DP) dibayar dan diverifikasi pemilik kos.',
            'Jam malam pukul 22.00 — gerbang dikunci, pulang lewat jam tersebut wajib lapor penjaga kos.',
            'Tamu lawan jenis dilarang masuk kamar; tamu umum wajib lapor dan maksimal berkunjung sampai pukul 21.00.',
            'Dilarang membawa tamu menginap tanpa izin tertulis pemilik kos.',
            'Jaga kebersihan kamar dan area bersama (dapur, jemuran, parkir); buang sampah pada tempatnya.',
            'Dilarang merokok di dalam kamar serta dilarang membawa minuman keras, narkoba, dan senjata tajam.',
            'Dilarang memelihara hewan tanpa izin pemilik kos.',
            'Sewa dibayar lunas sebelum tanggal jatuh tempo; keterlambatan dikenakan denda harian.',
            'Pemberitahuan keluar kos minimal 7 hari sebelumnya; barang yang tertinggal 14 hari dianggap hangus.',
        ];
    }

    /**
     * Pecah teks aturan bebas menjadi daftar baris yang rapi.
     * Mendukung: baris baru, koma/semicolon, prefix nomor ("1. ...", "1) ...", "- ...").
     *
     * @return array<int,string>
     */
    public static function fromString(?string $value): array
    {
        if (blank($value)) {
            return [];
        }

        $value = str_replace(["\r\n", "\r"], "\n", (string) $value);
        // Pecah koma/semicolon yang diikuti spasi bila tidak ada baris baru.
        if (! str_contains($value, "\n")) {
            $value = preg_replace('/\s*[;]\s*/', "\n", $value) ?? $value;
            // Hanya pecah koma bila terlihat seperti daftar (ada >1 koma).
            if (substr_count($value, ',') >= 1 && strlen($value) > 120) {
                $value = preg_replace('/\s*,\s*/', "\n", $value) ?? $value;
            }
        }

        $out = [];

        foreach (explode("\n", $value) as $baris) {
            $baris = trim($baris);
            // Buang prefix penomoran/list.
            $baris = preg_replace('/^(\d+[.)\-:]\s+|[-•*]\s+)/u', '', $baris) ?? $baris;
            $baris = trim($baris, " \t\n\r\0\x0B.,;");

            if ($baris !== '') {
                $out[] = $baris;
            }
        }

        return array_values($out);
    }

    /**
     * Normalisasi untuk disimpan: satu aturan per baris.
     */
    public static function normalizeString(?string $value): ?string
    {
        $daftar = self::fromString($value);

        return $daftar ? implode("\n", $daftar) : null;
    }

    /**
     * Contoh placeholder singkat untuk input form.
     */
    public static function contohSingkat(): string
    {
        return "Contoh, satu baris satu aturan:\nJam malam 22.00, gerbang dikunci\nTamu wajib lapor, dilarang menginap tanpa izin\nDilarang merokok di dalam kamar";
    }
}
