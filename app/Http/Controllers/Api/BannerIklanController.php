<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IklanPasarKos;
use App\Models\Pengaturan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BannerIklanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'per_page' => 'nullable|integer|min:1|max:50',
            'posisi' => 'nullable|in:banner_atas,katalog_sisipan',
            'aktif' => 'nullable|boolean',
        ]);

        $query = IklanPasarKos::with(['user', 'barangKos']);

        if ($request->has('posisi')) {
            $query->where('posisi', $request->posisi);
        }

        if ($request->has('aktif')) {
            if ($request->aktif) {
                $query->aktif();
            } else {
                $query->where(function ($q) {
                    $q->where('status', false)
                        ->orWhere('tayang_sampai', '<', Carbon::now());
                });
            }
        }

        $iklans = $query->orderBy('urutan', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'iklans' => $iklans->getCollection()->map(fn ($iklan) => $this->formatIklan($iklan)),
            'total' => $iklans->total(),
            'per_page' => $iklans->perPage(),
            'current_page' => $iklans->currentPage(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'judul' => 'required|string|max:100',
            'deskripsi' => 'nullable|string|max:255',
            'gambar' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:500',
            'posisi' => 'required|in:banner_atas,katalog_sisipan',
            'akun_jumlah' => 'nullable|integer|min:1|max:50',
            'urutan' => 'nullable|integer|min:0',
            'durasi_hari' => 'nullable|integer|min:1|max:7',
            'status' => 'nullable|boolean',
        ]);

        $user = auth('sanctum')->user();

        // Hanya super admin dan admin yang bisa membuat iklan banner
        if (!$user->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $durasiHari = $request->input('durasi_hari', Pengaturan::maksimalDurasiIklanHari());
        $biayaPerHari = Pengaturan::biayaPerHariIklan();
        $biayaTotal = $durasiHari * $biayaPerHari;

        $iklan = IklanPasarKos::create([
            'user_id' => $user->id,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'gambar' => $request->gambar,
            'link' => $request->link,
            'posisi' => $request->posisi,
            'akun_jumlah' => $request->input('akun_jumlah', 1),
            'urutan' => $request->input('urutan', 0),
            'tayang_dimulai' => Carbon::now(),
            'tayang_sampai' => Carbon::now()->addDays($durasiHari),
            'durasi_hari' => $durasiHari,
            'biaya' => $biayaTotal,
            'status' => (bool) $request->input('status', true),
        ]);

        return response()->json([
            'message' => 'Iklan banner berhasil dibuat',
            'iklan' => $this->formatIklan($iklan),
            'biaya' => 'Rp '.number_format($biayaTotal, 0, ',', '.'),
            'tayang_sampai' => $iklan->tayang_sampai->format('Y-m-d H:i:s'),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $iklan = IklanPasarKos::with(['user', 'barangKos'])->findOrFail($id);

        return response()->json([
            'iklan' => $this->formatIklan($iklan),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $iklan = IklanPasarKos::findOrFail($id);

        $user = auth('sanctum')->user();
        if (!$user->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'judul' => 'sometimes|required|string|max:100',
            'deskripsi' => 'nullable|string|max:255',
            'gambar' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:500',
            'posisi' => 'sometimes|required|in:banner_atas,katalog_sisipan',
            'akun_jumlah' => 'nullable|integer|min:1|max:50',
            'urutan' => 'nullable|integer|min:0',
            'durasi_hari' => 'nullable|integer|min:1|max:7',
            'status' => 'nullable|boolean',
        ]);

        $durasiHari = $request->input('durasi_hari', $iklan->durasi_hari);
        $biayaPerHari = Pengaturan::biayaPerHariIklan();
        $biayaTotal = $durasiHari * $biayaPerHari;

        $iklan->update([
            'judul' => $request->input('judul', $iklan->judul),
            'deskripsi' => $request->input('deskripsi', $iklan->deskripsi),
            'gambar' => $request->input('gambar', $iklan->gambar),
            'link' => $request->input('link', $iklan->link),
            'posisi' => $request->input('posisi', $iklan->posisi),
            'akun_jumlah' => $request->input('akun_jumlah', $iklan->akun_jumlah),
            'urutan' => $request->input('urutan', $iklan->urutan),
            'durasi_hari' => $durasiHari,
            'biaya' => $biayaTotal,
            'tayang_sampai' => $iklan->tayang_dimulai ? Carbon::parse($iklan->tayang_dimulai)->addDays($durasiHari) : null,
            'status' => (bool) $request->input('status', $iklan->status),
        ]);

        return response()->json([
            'message' => 'Iklan banner berhasil diperbarui',
            'iklan' => $this->formatIklan($iklan),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $iklan = IklanPasarKos::findOrFail($id);

        $user = auth('sanctum')->user();
        if (!$user->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $iklan->delete();

        return response()->json(['message' => 'Iklan banner berhasil dihapus']);
    }

    public function myIklans(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();

        $query = IklanPasarKos::with(['user', 'barangKos'])
            ->where('user_id', $user->id);

        if ($request->has('posisi')) {
            $query->where('posisi', $request->posisi);
        }

        $iklans = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 10));

        return response()->json([
            'iklans' => $iklans->getCollection()->map(fn ($iklan) => $this->formatIklan($iklan)),
            'total' => $iklans->total(),
        ]);
    }

    private function formatIklan(IklanPasarKos $iklan): array
    {
        return [
            'id' => $iklan->id,
            'user' => $iklan->user ? [
                'id' => $iklan->user->id,
                'nama' => $iklan->user->nama,
                'email' => $iklan->user->email,
            ] : null,
            'barang_kos' => $iklan->barangKos ? [
                'id' => $iklan->barangKos->id,
                'nama' => $iklan->barangKos->nama,
                'harga' => $iklan->barangKos->harga,
                'foto' => $iklan->barangKos->fotoUrl(),
                'kota' => $iklan->barangKos->kota,
            ] : null,
            'judul' => $iklan->judul,
            'deskripsi' => $iklan->deskripsi,
            'gambar' => $iklan->gambar,
            'link' => $iklan->link,
            'gradient' => $iklan->gradient,
            'posisi' => $iklan->posisi,
            'akun_jumlah' => $iklan->akun_jumlah,
            'urutan' => $iklan->urutan,
            'tayang_dimulai' => $iklan->tayang_dimulai ? $iklan->tayang_dimulai->format('Y-m-d H:i:s') : null,
            'tayang_sampai' => $iklan->tayang_sampai ? $iklan->tayang_sampai->format('Y-m-d H:i:s') : null,
            'durasi_hari' => $iklan->durasi_hari,
            'sisa_hari' => $iklan->sisaHari(),
            'biaya' => $iklan->biaya,
            'biaya_formatted' => 'Rp '.number_format($iklan->biaya, 0, ',', '.'),
            'status' => $iklan->status,
            'aktif' => $iklan->isAktif(),
            'created_at' => $iklan->created_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Daftar pengaturan iklan banner.
     */
    public function pengaturan(): JsonResponse
    {
        return response()->json([
            'maksimal_durasi_hari' => Pengaturan::maksimalDurasiIklanHari(),
            'biaya_per_hari' => Pengaturan::biayaPerHariIklan(),
            'biaya_per_hari_formatted' => 'Rp '.number_format(Pengaturan::biayaPerHariIklan(), 0, ',', '.'),
        ]);
    }
}
