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
                'before_or_equal:'.today()->addDays(10)->toDateString(),
            ],
            'periode' => ['sometimes', 'in:bulanan,mingguan,harian'],
            'durasi_bulan' => ['required_if:periode,bulanan', 'integer', 'min:1', 'max:12'],
            'durasi_minggu' => ['required_if:periode,mingguan', 'integer', 'min:1', 'max:12'],
            'durasi_hari' => ['required_if:periode,harian', 'integer', 'min:1', 'max:90'],
            // Booking bulanan wajib melampirkan bukti transfer DP.
            'bukti_dp' => ['required_if:periode,bulanan', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:2048'],
        ], [
            'tanggal_masuk.required' => 'Tanggal masuk wajib diisi.',
            'tanggal_masuk.date' => 'Tanggal masuk tidak valid.',
            'tanggal_masuk.after_or_equal' => 'Tanggal masuk tidak boleh mundur dari hari ini.',
            'tanggal_masuk.before_or_equal' => 'Tanggal masuk maksimal 10 hari ke depan.',
            'periode.in' => 'Periode sewa harus bulanan, mingguan, atau harian.',
            'durasi_bulan.required_if' => 'Lama sewa bulanan wajib diisi.',
            'durasi_bulan.min' => 'Lama sewa minimal 1 bulan.',
            'durasi_bulan.max' => 'Lama sewa maksimal 12 bulan.',
            'durasi_minggu.required_if' => 'Lama sewa mingguan wajib diisi.',
            'durasi_minggu.min' => 'Lama sewa mingguan minimal 1 minggu.',
            'durasi_minggu.max' => 'Lama sewa mingguan maksimal 12 minggu.',
            'durasi_hari.required_if' => 'Lama sewa harian wajib diisi.',
            'durasi_hari.min' => 'Lama sewa harian minimal 1 hari.',
            'durasi_hari.max' => 'Lama sewa harian maksimal 90 hari.',
            'bukti_dp.required_if' => 'Lampirkan bukti transfer DP Rp100.000 terlebih dahulu sebelum menyewa bulanan.',
            'bukti_dp.mimes' => 'Bukti transfer harus berupa gambar (JPG, PNG, WEBP) atau PDF.',
            'bukti_dp.max' => 'Ukuran bukti transfer maksimal 2MB.',
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
        $durasiMinggu = $periode === 'mingguan' ? (int) ($validated['durasi_minggu'] ?? 1) : null;
        $durasiHari = $periode === 'harian' ? (int) ($validated['durasi_hari'] ?? 1) : null;

        $periodeTersedia = $kamar->periodeTersedia();
        if (! in_array($periode, $periodeTersedia, true)) {
            return response()->json([
                'message' => 'Periode sewa '.$periode.' tidak tersedia untuk kamar ini.',
            ], 422);
        }

        try {
            $buktiDpPath = null;
            if ($periode === 'bulanan' && $request->hasFile('bukti_dp')) {
                $buktiDpPath = \App\Services\BuktiStorage::simpan($request->file('bukti_dp'), 'bukti-pembayaran');
            }

            $penyewaan = app(PenyewaanService::class)->sewaKamar($user, $kamar, $validated['tanggal_masuk'], $durasiBulan, $durasiHari, $durasiMinggu, $buktiDpPath);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $tanggalLabel = Carbon::parse($penyewaan->tanggal_masuk)->locale('id')->translatedFormat('d F Y');

        $dp = $periode === 'bulanan' ? PenyewaanService::dpUntuk((float) $kamar->harga_sewa_bulanan) : 0;

        $pesan = match ($periode) {
            'harian' => 'Kamar '.$kamar->nama.' berhasil dipesan untuk '.$durasiHari.' hari. Rencana masuk: '.$tanggalLabel.'. Tagihan sewa sudah dibuat — segera bayar Uang Muka (DP) agar booking fiks.',
            'mingguan' => 'Kamar '.$kamar->nama.' berhasil dipesan untuk '.$durasiMinggu.' minggu. Rencana masuk: '.$tanggalLabel.'. Tagihan sewa sudah dibuat — segera bayar Uang Muka (DP) agar booking fiks.',
            default => 'Pengajuan booking kamar '.$kamar->nama.' terkirim ('.$durasiBulan.' bulan, masuk '.$tanggalLabel.'). Bukti DP Rp'.number_format($dp, 0, ',', '.').' sudah dilampirkan — kamar BELUM otomatis tersewa. Sewa aktif setelah pemilik/admin memverifikasi. H-3 sebelum berakhir kamu ditanya mau lanjut atau tidak.',
        };

        return response()->json([
            'message' => $pesan,
            'penyewaan' => [
                'id' => $penyewaan->id,
                'kamar' => $kamar->nama,
                'properti' => $kamar->properti->nama,
                'tanggal_masuk' => $penyewaan->tanggal_masuk,
                'tanggal_keluar' => $penyewaan->tanggal_keluar,
                'tanggal_berakhir' => $penyewaan->tanggal_berakhir,
                'dp_nominal' => $periode === 'bulanan' ? $dp : null,
                'sisa_bulan_pertama' => $periode === 'bulanan' ? max(0, (float) $kamar->harga_sewa_bulanan - $dp) : null,
                'periode' => $periode,
                'durasi_bulan' => $periode === 'bulanan' ? $durasiBulan : null,
                'durasi_minggu' => $periode === 'mingguan' ? $durasiMinggu : null,
                'durasi_hari' => $periode === 'harian' ? $durasiHari : null,
                'status' => $penyewaan->status,
            ],
        ], 201);
    }
}
