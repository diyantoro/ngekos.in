<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\Properti;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KatalogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Properti::withCount([
            'kamars as total_kamar',
            'kamars as kamar_tersedia' => fn ($q) => $q->where('status', 'tersedia'),
        ])->where('status', 'aktif');

        if ($request->filled('kota')) {
            $query->where('kota', 'like', "%{$request->kota}%");
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%")
                    ->orWhere('kota', 'like', "%{$search}%");
            });
        }

        if ($request->filled('harga_min')) {
            $query->whereHas('kamars', function ($q) use ($request) {
                $q->where('status', 'tersedia')
                    ->where('harga_sewa_bulanan', '>=', $request->harga_min);
            });
        }

        if ($request->filled('harga_max')) {
            $query->whereHas('kamars', function ($q) use ($request) {
                $q->where('status', 'tersedia')
                    ->where('harga_sewa_bulanan', '<=', $request->harga_max);
            });
        }

        if ($request->filled('kapasitas')) {
            $query->whereHas('kamars', function ($q) use ($request) {
                $q->where('status', 'tersedia')
                    ->where('kapasitas', '>=', $request->kapasitas);
            });
        }

        if ($request->filled('sort')) {
            $sort = $request->sort;
            if ($sort === 'trending') {
                $query->orderByRaw('(ifnull(total_kamar, 0) - ifnull(kamar_tersedia, 0)) desc');
            } elseif ($sort === 'tersedia') {
                $query->orderByRaw('kamar_tersedia desc');
            } elseif ($sort === 'termurah') {
                $query->orderBy('harga', 'asc');
            } elseif ($sort === 'termahal') {
                $query->orderBy('harga', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = min(max((int) $request->input('per_page', 20), 1), 50);
        $propertis = $query->paginate($perPage);

        $propertis->getCollection()->transform(fn (Properti $p) => $this->formatRingkas($p));

        return response()->json($propertis);
    }

    public function show(Properti $properti): JsonResponse
    {
        $properti->loadCount([
            'kamars as total_kamar',
            'kamars as kamar_tersedia' => fn ($q) => $q->where('status', 'tersedia'),
        ]);

        $properti->load([
            'kamars' => fn ($q) => $q->orderBy('nama'),
            'pemilik:id,nama,no_hp',
        ]);

        return response()->json($this->formatDetail($properti));
    }

    /**
     * Bentuk ringkas untuk daftar katalog.
     */
    private function formatRingkas(Properti $p): array
    {
        return [
            'id' => $p->id,
            'nama' => $p->nama,
            'kota' => $p->kota,
            'alamat' => $p->alamat,
            'fasilitas' => $this->fasilitasArray($p->fasilitas),
            'foto' => $p->foto ? asset('storage/' . $p->foto) : null,
            'harga' => $p->harga !== null ? (float) $p->harga : null,
            'jenis_harga' => $p->jenis_harga,
            'status' => $p->status,
            'total_kamar' => (int) $p->total_kamar,
            'kamar_tersedia' => (int) $p->kamar_tersedia,
        ];
    }

    /**
     * Bentuk lengkap untuk halaman detail.
     */
    private function formatDetail(Properti $p): array
    {
        return [
            'id' => $p->id,
            'nama' => $p->nama,
            'kota' => $p->kota,
            'alamat' => $p->alamat,
            'deskripsi' => $p->deskripsi,
            'fasilitas' => $this->fasilitasArray($p->fasilitas),
            'aturan' => $p->aturan,
            'denda_per_hari' => $p->denda_per_hari !== null ? (float) $p->denda_per_hari : null,
            'harga' => $p->harga !== null ? (float) $p->harga : null,
            'jenis_harga' => $p->jenis_harga,
            'status' => $p->status,
            'foto' => $p->foto ? asset('storage/' . $p->foto) : null,
            'total_kamar' => (int) $p->total_kamar,
            'kamar_tersedia' => (int) $p->kamar_tersedia,
            'pemilik' => $p->pemilik ? [
                'id' => $p->pemilik->id,
                'nama' => $p->pemilik->nama,
                'no_hp' => $p->pemilik->no_hp,
            ] : null,
            'kamars' => $p->kamars->map(fn (Kamar $k) => [
                'id' => $k->id,
                'nama' => $k->nama,
                'kapasitas' => (int) $k->kapasitas,
                'harga_sewa_bulanan' => (float) $k->harga_sewa_bulanan,
                'jenis_harga' => $k->jenis_harga,
                'status' => $k->status,
                'foto' => $k->foto ? asset('storage/' . $k->foto) : null,
            ])->values(),
        ];
    }

    private function fasilitasArray(?string $fasilitas): array
    {
        if (blank($fasilitas)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $fasilitas))));
    }
}
