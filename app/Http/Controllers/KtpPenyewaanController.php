<?php

namespace App\Http\Controllers;

use App\Models\Penyewaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KtpPenyewaanController extends Controller
{
    /**
     * Lihat/𝘶𝘯𝘥𝘶𝘩 file KTP penyewa. Hanya pemilik kelolaan / admin.
     */
    public function lihat(Request $request, int $sewaanId)
    {
        $user = $request->user();

        $sewaan = Penyewaan::where('id', $sewaanId)
            ->with('anggotas')
            ->firstOrFail();

        $boleh = $user->hasAnyRole(['admin', 'super_admin']);

        if (! $boleh && $user->hasRole('pemilik')) {
            $boleh = $sewaan->properti?->pemilik_id === $user->id;
        }

        // Anak kos boleh lihat KTP-nya sendiri.
        if (! $boleh && $user->hasRole('anak_kos')) {
            $boleh = $sewaan->anak_kos_id === $user->id
                || $sewaan->anggotas->where('user_id', $user->id)->where('status', 'aktif')->isNotEmpty();
        }

        abort_unless($boleh, 403);

        $userId = $request->query('user_id') ? (int) $request->query('user_id') : $sewaan->anak_kos_id;

        $path = $userId === $sewaan->anak_kos_id
            ? $sewaan->ktp_path
            : $sewaan->anggotas->firstWhere('user_id', $userId)?->ktp_path;

        abort_unless($path && Storage::disk('public')->exists($path), 404);

        return response()->file(Storage::disk('public')->path($path));
    }
}
