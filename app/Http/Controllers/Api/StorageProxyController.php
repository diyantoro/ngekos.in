<?php

namespace App\Http\Controllers\Api;

use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StorageProxyController extends \App\Http\Controllers\Controller
{
    private const PREFIX_PUBLIK = ['properti', 'properti-fotos', 'kamar', 'kamar-fotos', 'avatar', 'galeri'];

    private const PREFIX_SENSITIF = ['bukti', 'bukti-pembayaran', 'kwitansi'];

    public function __invoke(Request $request, string $path)
    {
        if (str_contains($path, '..') || str_starts_with($path, '/') || str_starts_with($path, 'ktp')) {
            abort(404);
        }

        $kelompok = null;

        foreach (self::PREFIX_PUBLIK as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                $kelompok = 'publik';
                break;
            }
        }

        if ($kelompok === null) {
            foreach (self::PREFIX_SENSITIF as $prefix) {
                if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                    $kelompok = 'sensitif';
                    break;
                }
            }
        }

        abort_unless($kelompok !== null, 404);

        if ($kelompok === 'sensitif' && ! $this->bolehLihatBukti($request, $path)) {
            abort(auth()->check() ? 403 : 401);
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            abort(404);
        }

        try {
            $mime = $disk->mimeType($path) ?: 'application/octet-stream';
        } catch (\Throwable $e) {
            abort(404);
        }

        if (in_array($mime, ['image/svg+xml', 'text/html', 'application/xhtml+xml'], true)) {
            abort(404);
        }

        $stream = $disk->readStream($path);

        if (! is_resource($stream)) {
            abort(404);
        }

        return response()->stream(function () use ($stream) {
            try {
                fpassthru($stream);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline',
            'Cache-Control' => $kelompok === 'sensitif'
                ? 'private, max-age=60, must-revalidate'
                : 'public, max-age=86400, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function bolehLihatBukti(Request $request, string $path): bool
    {
        $user = $request->user() ?? auth('sanctum')->user() ?? auth('web')->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        $pembayaran = Pembayaran::where('bukti', $path)
            ->orWhere('file_kwitansi', $path)
            ->with(['tagihan.penyewaan.properti', 'tagihan.penyewaan.properti.admins'])
            ->first();

        if (! $pembayaran) {
            return false;
        }

        if ((int) $pembayaran->anak_kos_id === (int) $user->id) {
            return true;
        }

        $properti = $pembayaran->tagihan?->penyewaan?->properti;

        if (! $properti) {
            return false;
        }

        if ($user->hasRole('pemilik') && (int) $properti->pemilik_id === (int) $user->id) {
            return true;
        }

        if ($user->hasAnyRole(['admin', 'super_admin'])) {
            return $properti->admins->contains('id', $user->id) || $user->hasRole('super_admin');
        }

        return false;
    }
}
