<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Services\BuktiStorage;
use Illuminate\Http\Request;

class PembayaranBuktiController extends Controller
{
    /**
     * Tampilkan/stream bukti transfer dari disk private.
     * Anak kos: hanya miliknya. Pemilik: hanya propertinya. Admin/super_admin: semua.
     */
    public function lihat(Request $request, int $pembayaran)
    {
        $user = $request->user();

        $pembayaran = Pembayaran::with(['tagihan.penyewaan.properti'])->findOrFail($pembayaran);

        // Otorisasi disamakan dengan query list di halaman pemilik/tagihan:
        // pemilik = ada relasi tagihan->penyewaan->properti miliknya.
        $boleh = $user->hasAnyRole(['admin', 'super_admin']);

        if (! $boleh && (int) $pembayaran->anak_kos_id === (int) $user->id) {
            $boleh = true;
        }

        if (! $boleh) {
            $boleh = Pembayaran::where('id', $pembayaran->id)
                ->whereHas('tagihan.penyewaan.properti', function ($w) use ($user) {
                    $w->where('pemilik_id', $user->id);
                })
                ->exists();
        }

        // Admin kos (pivot properti_admins) boleh lihat properti kelolaannya.
        if (! $boleh) {
            $properti = $pembayaran->tagihan?->penyewaan?->properti;
            $boleh = $properti
                && method_exists($properti, 'admins')
                && $properti->admins()->where('users.id', $user->id)->exists();
        }

        if (! $boleh) {
            \Illuminate\Support\Facades\Log::warning('Bukti pembayaran ditolak', [
                'pembayaran_id' => $pembayaran->id,
                'user_id' => $user->id,
                'anak_kos_id' => $pembayaran->anak_kos_id,
                'properti_id' => $pembayaran->tagihan?->penyewaan?->properti_id,
                'pemilik_id' => $pembayaran->tagihan?->penyewaan?->properti?->pemilik_id,
            ]);
        }

        abort_unless($boleh, 403);
        abort_unless($pembayaran->bukti && BuktiStorage::ada($pembayaran->bukti), 404);

        return response()->file(BuktiStorage::pathAbsolut($pembayaran->bukti));
    }
}
