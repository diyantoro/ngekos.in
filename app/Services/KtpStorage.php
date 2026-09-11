<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Satu-satunya pintu simpan/baca/hapus file KTP.
 * KTP adalah data sensitif (UU PDP): wajib di disk private,
 * tidak pernah di-symlink publik, hanya via controller berotorisasi.
 */
class KtpStorage
{
    public const DISK = 'private';

    public const DIREKTORI = 'ktp';

    public static function simpan(UploadedFile $file): string
    {
        return $file->store(self::DIREKTORI, self::DISK);
    }

    public static function ada(?string $path): bool
    {
        return (bool) $path && Storage::disk(self::DISK)->exists($path);
    }

    public static function pathAbsolut(string $path): string
    {
        return Storage::disk(self::DISK)->path($path);
    }

    public static function hapus(?string $path): void
    {
        if ($path) {
            Storage::disk(self::DISK)->delete($path);
        }
    }
}
