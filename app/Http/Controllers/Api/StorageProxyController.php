<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StorageProxyController extends \App\Http\Controllers\Controller
{
    public function __invoke(Request $request, string $path)
    {
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
