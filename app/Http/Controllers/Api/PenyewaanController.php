<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Services\PenyewaanService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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
                'after_or_equal:'.today()->toDateString(),
                'before_or_equal:'.today()->addMonths(3)->toDateString(),
            ],
            'periode' => ['sometimes', 'in:bulanan,harian'],
            'durasi_bulan' => ['sometimes', 'integer', 'min:1', 'max:12'],
            'durasi_hari' => ['sometimes', 'integer', 'min:1', 'max:90'],
        ], [
            'tanggal_masuk.required' => 'Tanggal masuk wajib diisi.',
            'tanggal_masuk.date' => 'Tanggal masuk tidak valid.',
            'tanggal_masuk.after_or_equal' => 'Tanggal masuk tidak boleh mundur dari hari ini.',
            'tanggal_masuk.before_or_equal' => 'Tanggal masuk maksimal 3 bulan ke depan.',
            'periode.in' => 'Periode sewa harus bulanan atau harian.',
            'durasi_bulan.min' => 'Lama sewa minimal 1 bulan.',
            'durasi_bulan.max' => 'Lama sewa maksimal 12 bulan.',
            'durasi_hari.min' => 'Lama sewa harian minimal 1 hari.',
            'durasi_hari.max' => 'Lama sewa harian maksimal 90 hari.',
        ]);

        $kamar = Kamar::with('properti')
            ->where('id', $kamarId)
            ->where('properti_id', $propertiId)
            ->first();

        if (! $kamar || ! $kamar->properti) {
            return response()->json(['message' => 'Kamar tidak ditemukan.'], 404);
        }

        $periode = $validated['periode'] ?? 'bulanan';
        $durasiBulan = (int) ($validated['durasi_bulan'] ?? 1);
        $durasiHari = $periode === 'harian' ? (int) ($validated['durasi_hari'] ?? 1) : null;

        try {
            $penyewaan = app(PenyewaanService::class)->sewaKamar($user, $kamar, $validated['tanggal_masuk'], $durasiBulan, $durasiHari);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $tanggalLabel = Carbon::parse($penyewaan->tanggal_masuk)->locale('id')->translatedFormat('d F Y');

        $pesan = $periode === 'harian'
            ? 'Kamar '.$kamar->nama.' berhasil dipesan untuk '.$durasiHari.' hari. Rencana masuk: '.$tanggalLabel.'. Tagihan sewa sudah dibuat dan menunggu pembayaran.'
            : 'Kamar '.$kamar->nama.' berhasil dipesan untuk '.$durasiBulan.' bulan. Rencana masuk: '.$tanggalLabel.'. Tagihan sewa sudah dibuat dan menunggu pembayaran.';

        return response()->json([
            'message' => $pesan,
            'penyewaan' => [
                'id' => $penyewaan->id,
                'kamar' => $kamar->nama,
                'properti' => $kamar->properti->nama,
                'tanggal_masuk' => $penyewaan->tanggal_masuk,
                'tanggal_keluar' => $penyewaan->tanggal_keluar,
                'periode' => $periode,
                'durasi_bulan' => $periode === 'harian' ? null : $durasiBulan,
                'durasi_hari' => $periode === 'harian' ? $durasiHari : null,
                'status' => $penyewaan->status,
            ],
        ], 201);
    }
}
