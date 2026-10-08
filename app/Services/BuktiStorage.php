<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Satu-satunya pintu simpan/baca file bukti pembayaran & kwitansi.
 * Bukti transfer adalah data sensitif: wajib di disk private,
 * tidak pernah di-symlink publik, hanya via controller/proxy berotorisasi.
 * Mendukung baca legacy di disk public untuk masa migrasi.
 */
class BuktiStorage
{
    public const DISK = 'private';

    public const DISK_LEGACY = 'public';

    public static function simpan(UploadedFile $file, string $direktori = 'bukti'): string
    {
        return $file->store($direktori, self::DISK);
    }

    public static function ada(?string $path): bool
    {
        if (blank($path)) {
            return false;
        }

        return Storage::disk(self::DISK)->exists($path)
            || Storage::disk(self::DISK_LEGACY)->exists($path);
    }

    public static function pathAbsolut(string $path): string
    {
        if (Storage::disk(self::DISK)->exists($path)) {
            return Storage::disk(self::DISK)->path($path);
        }

        return Storage::disk(self::DISK_LEGACY)->path($path);
    }

    public static function stream(?string $path)
    {
        if (blank($path)) {
            return null;
        }

        if (Storage::disk(self::DISK)->exists($path)) {
            return Storage::disk(self::DISK)->readStream($path);
        }

        if (Storage::disk(self::DISK_LEGACY)->exists($path)) {
            return Storage::disk(self::DISK_LEGACY)->readStream($path);
        }

        return null;
    }

    public static function mime(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        try {
            if (Storage::disk(self::DISK)->exists($path)) {
                return Storage::disk(self::DISK)->mimeType($path) ?: null;
            }

            if (Storage::disk(self::DISK_LEGACY)->exists($path)) {
                return Storage::disk(self::DISK_LEGACY)->mimeType($path) ?: null;
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    public static function hapus(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        Storage::disk(self::DISK)->delete($path);
        // Jangan hapus legacy otomatis agar migrasi bertahap aman.
    }
}
