<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StorageProxyController extends \App\Http\Controllers\Controller
{
    private const PREFIX_DIIZINKAN = ['bukti', 'bukti-pembayaran', 'properti', 'properti-fotos', 'kamar', 'kamar-fotos', 'avatar', 'galeri'];

    public function __invoke(Request $request, string $path)
    {
        if (str_contains($path, '..') || str_starts_with($path, '/') || str_starts_with($path, 'ktp')) {
            abort(404);
        }

        $diizinkan = false;

        foreach (self::PREFIX_DIIZINKAN as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                $diizinkan = true;
                break;
            }
        }

        abort_unless($diizinkan, 404);

        if (! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $mime = Storage::disk('public')->mimeType($path);

        return response()->stream(function () use ($path) {
            fpassthru(Storage::disk('public')->readStream($path));
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'public, max-age=86400, immutable',
        ]);
    }
}
