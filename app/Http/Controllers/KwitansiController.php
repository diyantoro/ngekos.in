<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Services\KwitansiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KwitansiController extends Controller
{
    /**
     * Unduh PDF kwitansi pembayaran terverifikasi.
     * Anak kos: hanya miliknya. Pemilik/admin: hanya kelolaannya.
     */
    public function unduh(Request $request, int $pembayaranId)
    {
        $user = $request->user();

        $pembayaran = Pembayaran::where('id', $pembayaranId)
            ->where('status', 'diverifikasi')
            ->with(['tagihan.penyewaan.properti'])
            ->firstOrFail();

        $boleh = $pembayaran->anak_kos_id === $user->id;

        if (! $boleh && $user->hasRole('pemilik')) {
            $boleh = $pembayaran->tagihan?->penyewaan?->properti?->pemilik_id === $user->id;
        }

        if (! $boleh && $user->hasAnyRole(['admin', 'super_admin'])) {
            $boleh = true;
        }

        abort_unless($boleh, 403);

        KwitansiService::untuk($pembayaran);

        $path = $pembayaran->refresh()->file_kwitansi;

        abort_unless($path && \App\Services\BuktiStorage::ada($path), 404);

        return response()->download(
            \App\Services\BuktiStorage::pathAbsolut($path),
            ($pembayaran->nomor_kwitansi ?? 'kwitansi').'.pdf'
        );
    }
}
