<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionRequest;
use App\Services\BuktiStorage;
use Illuminate\Http\Request;

class LanggananBuktiController extends Controller
{
    /**
     * Tampilkan/stream bukti pembayaran langganan dari disk private.
     * Pemilik: hanya miliknya. Admin/super_admin: semua.
     */
    public function lihat(Request $request, int $permintaan)
    {
        $user = $request->user();

        $req = SubscriptionRequest::findOrFail($permintaan);

        $boleh = $user->hasAnyRole(['admin', 'super_admin'])
            || (int) $req->user_id === (int) $user->id;

        abort_unless($boleh, 403);
        abort_unless($req->bukti_path && BuktiStorage::ada($req->bukti_path), 404);

        $mime = BuktiStorage::mime($req->bukti_path) ?: 'application/octet-stream';

        if (in_array($mime, ['image/svg+xml', 'text/html', 'application/xhtml+xml'], true)) {
            abort(404);
        }

        $stream = BuktiStorage::stream($req->bukti_path);

        abort_unless(is_resource($stream), 404);

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
            'Cache-Control' => 'private, max-age=60, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
