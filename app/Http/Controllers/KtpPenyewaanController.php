<?php

namespace App\Http\Controllers;

use App\Models\Penyewaan;
use App\Services\KtpStorage;
use Illuminate\Http\Request;

class KtpPenyewaanController extends Controller
{
    /**
     * Lihat/unduh file KTP penyewa. Hanya pemilik kelolaan / admin / penyewa itu sendiri.
     */
    public function lihat(Request $request, int $sewaanId)
    {
        $user = $request->user();

        $sewaan = Penyewaan::where('id', $sewaanId)->firstOrFail();

        $boleh = $user->hasAnyRole(['admin', 'super_admin']);

        if (! $boleh && $user->hasRole('pemilik')) {
            $boleh = $sewaan->properti?->pemilik_id === $user->id;
        }

        // Anak kos boleh lihat KTP-nya sendiri.
        if (! $boleh && $user->hasRole('anak_kos')) {
            $boleh = $sewaan->anak_kos_id === $user->id;
        }

        abort_unless($boleh, 403);

        $path = $sewaan->ktp_path;

        abort_unless($path && KtpStorage::ada($path), 404);

        return response()->file(KtpStorage::pathAbsolut($path));
    }
}
