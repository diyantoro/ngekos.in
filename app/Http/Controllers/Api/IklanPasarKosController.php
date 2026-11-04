<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BarangKos;
use App\Models\IklanPasarKos;
use App\Models\Pengaturan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class IklanPasarKosController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'per_page' => 'nullable|integer|min:1|max:50',
            'akun_jumlah' => 'nullable|integer|min:1|max:10',
            'posisi' => 'nullable|in:banner_atas,katalog_sisipan',
        ]);

        $akunFilter = $request->input('akun_jumlah');
        $posisiFilter = $request->input('posisi');
        
        $query = IklanPasarKos::with(['barangKos.user', 'barangKos.properti'])
            ->aktif()
            ->where('status', true);

        if ($akunFilter) {
            $query->where('akun_jumlah', '>=', $akunFilter);
        }

        if ($posisiFilter) {
            $query->where('posisi', $posisiFilter);
        }

        $iklans = $query->orderBy('tayang_sampai', 'desc')->paginate($request->input('per_page', 20));

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
            'barang_kos_id' => 'required|exists:barang_kos,id',
            'judul' => 'nullable|string|max:100',
            'deskripsi' => 'nullable|string|max:255',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'link' => 'nullable|string|max:500|url',
            'gradient' => 'nullable|string|max:100',
            'posisi' => 'nullable|in:banner_atas,katalog_sisipan',
            'akun_jumlah' => 'nullable|integer|min:1|max:10',
            'urutan' => 'nullable|integer|min:0',
            'durasi_hari' => 'nullable|integer|min:1|max:7',
        ]);

        $user = auth('sanctum')->user();

        // Cek apakah user bisa membuat iklan
        if (!IklanPasarKos::bisaBuatIklan($user)) {
            return response()->json([
                'message' => 'Batas iklan terlampaui. Akun gratis maksimal 2 iklan.',
                'batas' => IklanPasarKos::batasAkunGratis(),
                'jumlah_iklan_aktif' => IklanPasarKos::jumlahIklanAktifUser($user),
            ], 403);
        }

        $barang = BarangKos::where('id', $request->barang_kos_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $durasiHari = $request->input('durasi_hari', Pengaturan::maksimalDurasiIklanHari());
        $biayaPerHari = Pengaturan::biayaPerHariIklan();
        $biayaTotal = $durasiHari * $biayaPerHari;
        
        $posisi = $request->input('posisi', 'banner_atas');
        $akunJumlah = $request->input('akun_jumlah', 1);
        $urutan = $request->input('urutan', 0);

        // Cek stok barang tersedia
        if ($barang->status !== \App\Enums\BarangStatus::Tersedia->value) {
            return response()->json(['message' => 'Barang tidak tersedia untuk diiklankan.'], 400);
        }

        // Handle upload gambar
        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $fileName = time().'_'.$file->getClientOriginalName();
            $gambarPath = $file->storeAs('iklan_pasar_kos', $fileName, 'public');
        }

        $iklan = IklanPasarKos::create([
            'user_id' => $user->id,
            'barang_kos_id' => $barang->id,
            'judul' => $request->input('judul'),
            'deskripsi' => $request->input('deskripsi'),
            'gambar' => $gambarPath,
            'link' => $request->input('link'),
            'gradient' => $request->input('gradient', 'from-brand-700 via-brand-600 to-emerald-600'),
            'posisi' => $posisi,
            'akun_jumlah' => $akunJumlah,
            'urutan' => $urutan,
            'tayang_dimulai' => Carbon::now(),
            'tayang_sampai' => Carbon::now()->addDays($durasiHari),
            'durasi_hari' => $durasiHari,
            'biaya' => $biayaTotal,
            'status' => true,
        ]);

        // Update barang dengan info tayang
        $barang->update([
            'tayang_sampai' => $iklan->tayang_sampai,
            'tayang_hari' => $durasiHari,
        ]);

        return response()->json([
            'message' => 'Iklan berhasil dibuat',
            'iklan' => $this->formatIklan($iklan),
            'biaya' => 'Rp '.number_format($biayaTotal, 0, ',', '.'),
            'tayang_sampai' => $iklan->tayang_sampai->format('Y-m-d H:i:s'),
            'is_banner' => $posisi === 'banner_atas',
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $iklan = IklanPasarKos::with(['barangKos.user', 'barangKos.properti'])
            ->findOrFail($id);

        return response()->json([
            'iklan' => $this->formatIklan($iklan),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $iklan = IklanPasarKos::findOrFail($id);

        $user = auth('sanctum')->user();
        if ($iklan->user_id !== $user->id && !$user->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'judul' => 'nullable|string|max:100',
            'deskripsi' => 'nullable|string|max:255',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'link' => 'nullable|string|max:500|url',
            'gradient' => 'nullable|string|max:100',
            'posisi' => 'nullable|in:banner_atas,katalog_sisipan',
            'akun_jumlah' => 'nullable|integer|min:1|max:10',
            'urutan' => 'nullable|integer|min:0',
            'durasi_hari' => 'nullable|integer|min:1|max:7',
        ]);

        $durasiHari = $request->input('durasi_hari', $iklan->durasi_hari);
        $biayaPerHari = Pengaturan::biayaPerHariIklan();
        $biayaTotal = $durasiHari * $biayaPerHari;
        
        $posisi = $request->input('posisi', $iklan->posisi);

        // Handle upload gambar baru
        $gambarPath = $iklan->gambar;
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada
            if ($iklan->gambar && Storage::disk('public')->exists($iklan->gambar)) {
                Storage::disk('public')->delete($iklan->gambar);
            }
            $file = $request->file('gambar');
            $fileName = time().'_'.$file->getClientOriginalName();
            $gambarPath = $file->storeAs('iklan_pasar_kos', $fileName, 'public');
        }

        $iklan->update([
            'judul' => $request->input('judul', $iklan->judul),
            'deskripsi' => $request->input('deskripsi', $iklan->deskripsi),
            'gambar' => $gambarPath,
            'link' => $request->input('link', $iklan->link),
            'gradient' => $request->input('gradient', $iklan->gradient),
            'posisi' => $posisi,
            'akun_jumlah' => $request->input('akun_jumlah', $iklan->akun_jumlah),
            'urutan' => $request->input('urutan', $iklan->urutan),
            'durasi_hari' => $durasiHari,
            'biaya' => $biayaTotal,
            'tayang_sampai' => $iklan->tayang_dimulai ? Carbon::parse($iklan->tayang_dimulai)->addDays($durasiHari) : null,
        ]);

        // Update barang juga
        $iklan->barangKos->update([
            'tayang_sampai' => $iklan->tayang_sampai,
            'tayang_hari' => $durasiHari,
        ]);

        return response()->json([
            'message' => 'Iklan berhasil diperbarui',
            'iklan' => $this->formatIklan($iklan),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $iklan = IklanPasarKos::findOrFail($id);

        $user = auth('sanctum')->user();
        if ($iklan->user_id !== $user->id && !$user->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Hapus gambar jika ada
        if ($iklan->gambar && Storage::disk('public')->exists($iklan->gambar)) {
            Storage::disk('public')->delete($iklan->gambar);
        }

        // Update barang - hapus info tayang
        $iklan->barangKos->update([
            'tayang_sampai' => null,
            'tayang_hari' => 0,
        ]);

        $iklan->delete();

        return response()->json(['message' => 'Iklan berhasil dihapus']);
    }

    public function myIklans(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();

        $iklans = IklanPasarKos::with(['barangKos.user', 'barangKos.properti'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
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
            'judul' => $iklan->judul,
            'deskripsi' => $iklan->deskripsi,
            'gambar' => $iklan->gambar ? asset('storage/'.$iklan->gambar) : null,
            'link' => $iklan->link,
            'gradient' => $iklan->gradient,
            'posisi' => $iklan->posisi,
            'akun_jumlah' => $iklan->akun_jumlah,
            'urutan' => $iklan->urutan,
            'barang_kos' => [
                'id' => $iklan->barangKos->id,
                'nama' => $iklan->barangKos->nama,
                'harga' => $iklan->barangKos->harga,
                'foto' => $iklan->barangKos->fotoUrl(),
                'kota' => $iklan->barangKos->kota,
                'status' => $iklan->barangKos->status,
            ],
            'tayang_dimulai' => $iklan->tayang_dimulai ? $iklan->tayang_dimulai->format('Y-m-d H:i:s') : null,
            'tayang_sampai' => $iklan->tayang_sampai ? $iklan->tayang_sampai->format('Y-m-d H:i:s') : null,
            'durasi_hari' => $iklan->durasi_hari,
            'sisa_hari' => $iklan->sisaHari(),
            'biaya' => $iklan->biaya,
            'biaya_formatted' => 'Rp '.number_format($iklan->biaya, 0, ',', '.'),
            'aktif' => $iklan->isAktif(),
            'created_at' => $iklan->created_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Daftar pengaturan pasar kos.
     */
    public function pengaturan(): JsonResponse
    {
        return response()->json([
            'akun_di_katalog' => Pengaturan::akunDiKatalogPasarKos(),
            'maksimal_durasi_hari' => Pengaturan::maksimalDurasiIklanHari(),
            'biaya_per_hari' => Pengaturan::biayaPerHariIklan(),
            'biaya_per_hari_formatted' => 'Rp '.number_format(Pengaturan::biayaPerHariIklan(), 0, ',', '.'),
            'pasar_kos_aktif' => Pengaturan::pasarKosAktif(),
        ]);
    }
}
