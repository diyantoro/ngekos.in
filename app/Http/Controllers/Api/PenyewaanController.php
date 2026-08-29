<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Services\PenyewaanService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PenyewaanController extends Controller
{
    /**
     * Sewa satu kamar secara langsung (tanpa menunggu konfirmasi admin/pemilik).
     * Kamar tersedia langsung dikunci (status -> terisi) dan dibuatkan penyewaan aktif.
     */
    public function sewaKamar(Request $request, int $propertiId, int $kamarId): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasRole('anak_kos')) {
            return response()->json(['message' => 'Hanya akun pencari kos (anak kos) yang dapat menyewa kamar.'], 403);
        }

        $validated = $request->validate([
            'tanggal_masuk' => [
                'required', 'date',
                'after_or_equal:' . today()->toDateString(),
                'before_or_equal:' . today()->addMonths(3)->toDateString(),
            ],
        ], [
            'tanggal_masuk.required' => 'Tanggal masuk wajib diisi.',
            'tanggal_masuk.date' => 'Tanggal masuk tidak valid.',
            'tanggal_masuk.after_or_equal' => 'Tanggal masuk tidak boleh mundur dari hari ini.',
            'tanggal_masuk.before_or_equal' => 'Tanggal masuk maksimal 3 bulan ke depan.',
        ]);

        $kamar = Kamar::with('properti')
            ->where('id', $kamarId)
            ->where('properti_id', $propertiId)
            ->first();

        if (! $kamar || ! $kamar->properti) {
            return response()->json(['message' => 'Kamar tidak ditemukan.'], 404);
        }

        try {
            $penyewaan = app(PenyewaanService::class)->sewaKamar($user, $kamar, $validated['tanggal_masuk']);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $tanggalLabel = \Illuminate\Support\Carbon::parse($penyewaan->tanggal_masuk)->locale('id')->translatedFormat('d F Y');

        return response()->json([
            'message' => 'Kamar '.$kamar->nama.' berhasil dipesan. Rencana masuk: '.$tanggalLabel.'. Kamar langsung terkunci untukmu.',
            'penyewaan' => [
                'id' => $penyewaan->id,
                'kamar' => $kamar->nama,
                'properti' => $kamar->properti->nama,
                'tanggal_masuk' => $penyewaan->tanggal_masuk,
                'status' => $penyewaan->status,
            ],
        ], 201);
    }
}