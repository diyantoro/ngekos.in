<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\Properti;
use App\Models\User;
use App\Support\FacilityHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PropertiManageController extends Controller
{
    private function bolehKelolaSemua(Request $request): bool
    {
        return $request->user()->hasAnyRole(['admin', 'super_admin']);
    }

    /**
     * Delegate POST + _method (PUT/DELETE) yang dipakai aplikasi mobile.
     */
    public function handleProperti(Request $request, int $id): JsonResponse
    {
        return match (strtoupper($request->input('_method', 'PUT'))) {
            'DELETE' => $this->destroy($request, $id),
            default => $this->update($request, $id),
        };
    }

    /**
     * Delegate POST + _method untuk kamar.
     */
    public function handleKamar(Request $request, int $propertiId, int $kamarId): JsonResponse
    {
        return match (strtoupper($request->input('_method', 'PUT'))) {
            'DELETE' => $this->destroyKamar($request, $propertiId, $kamarId),
            default => $this->updateKamar($request, $propertiId, $kamarId),
        };
    }

    public function index(Request $request): JsonResponse
    {
        $propertis = Properti::query()
            ->when(! $this->bolehKelolaSemua($request), fn ($q) => $q->where('pemilik_id', $request->user()->id))
            ->withCount(['kamars as total_kamar', 'kamars as kamar_terisi' => fn ($q) => $q->where('status', 'terisi')])
            ->with(['kamars:id,nama,kapasitas,harga_sewa_bulanan,jenis_harga,status,foto,properti_id'])
            ->orderBy('nama')
            ->get();

        return response()->json($propertis->map(fn (Properti $p) => $this->formatProperti($p))->values());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateProperti($request, null);

        if ($this->bolehKelolaSemua($request)) {
            $pemilikId = $request->input('pemilik_id')
                ?? ($request->input('pemilikId')
                    ?? null);
        } else {
            $pemilikId = $request->user()->id;
        }

        if ($pemilikId !== null
            && (! User::find($pemilikId)?->hasRole('pemilik'))) {
            return response()->json(['message' => 'Pemilik kos tidak valid.'], 422);
        }

        $data = $this->dataProperti($validated);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('properti', 'public');
        }

        $properti = Properti::create(array_merge($data, [
            'pemilik_id' => $pemilikId ?? $request->user()->id,
            'status' => $validated['status'] ?? 'aktif',
        ]));

        return response()->json([
            'message' => "Kos \"{$properti->nama}\" berhasil dibuat.",
            'properti' => $this->formatProperti($properti->loadCount(['kamars as total_kamar', 'kamars as kamar_terisi' => fn ($q) => $q->where('status', 'terisi')])->load('kamars')),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $properti = $this->findProperti($request, $id);

        if (! $properti) {
            return response()->json(['message' => 'Kos tidak ditemukan.'], 404);
        }

        $validated = $this->validateProperti($request, $properti);
        $data = $this->dataProperti($validated);

        if ($this->bolehKelolaSemua($request) && $request->filled('pemilik_id')) {
            $pemilikId = $request->input('pemilik_id')
                ?? $request->input('pemilikId');

            if (! User::find($pemilikId)?->hasRole('pemilik')) {
                return response()->json(['message' => 'Pemilik kos tidak valid.'], 422);
            }

            $data['pemilik_id'] = $pemilikId;
        }

        if ($request->hasFile('foto')) {
            if ($properti->foto) {
                Storage::disk('public')->delete($properti->foto);
            }
            $data['foto'] = $request->file('foto')->store('properti', 'public');
        }

        $properti->update($data);

        return response()->json([
            'message' => "Kos \"{$properti->nama}\" berhasil diperbarui.",
            'properti' => $this->formatProperti($properti->loadCount(['kamars as total_kamar', 'kamars as kamar_terisi' => fn ($q) => $q->where('status', 'terisi')])->load('kamars')),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $properti = $this->findProperti($request, $id);

        if (! $properti) {
            return response()->json(['message' => 'Kos tidak ditemukan.'], 404);
        }

        $nama = $properti->nama;

        if ($properti->foto) {
            Storage::disk('public')->delete($properti->foto);
        }

        foreach ($properti->kamars as $kamar) {
            if ($kamar->foto) {
                Storage::disk('public')->delete($kamar->foto);
            }
        }

        $properti->delete();

        return response()->json(['message' => "Kos \"{$nama}\" beserta seluruh kamar & datanya telah dihapus."]);
    }

    public function indexKamar(Request $request, int $propertiId): JsonResponse
    {
        $properti = $this->findProperti($request, $propertiId);

        if (! $properti) {
            return response()->json(['message' => 'Kos tidak ditemukan.'], 404);
        }

        $kamars = Kamar::where('properti_id', $propertiId)->orderBy('nama')->get();

        return response()->json($kamars->map(fn (Kamar $k) => $this->formatKamar($k))->values());
    }

    public function storeKamar(Request $request, int $propertiId): JsonResponse
    {
        $properti = $this->findProperti($request, $propertiId);

        if (! $properti) {
            return response()->json(['message' => 'Kos tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'kapasitas' => 'required|integer|min:1|max:10',
            'harga_sewa_bulanan' => 'required|numeric|min:0',
            'harga_sewa_harian' => 'nullable|numeric|min:0',
            'jenis_harga' => 'required|in:bulanan,harian',
            'harga_asli' => 'nullable|numeric|min:0',
            'status' => 'required|in:tersedia,terisi,perbaikan',
            'foto' => 'nullable|image|max:2048',
        ]);

        $data = [
            'properti_id' => $propertiId,
            'nama' => $validated['nama'],
            'kapasitas' => $validated['kapasitas'],
            'harga_sewa_bulanan' => $validated['harga_sewa_bulanan'],
            'harga_sewa_harian' => $validated['harga_sewa_harian'] ?? null,
            'jenis_harga' => $validated['jenis_harga'],
            'harga_asli' => $validated['harga_asli'] ?? null,
            'status' => $validated['status'],
        ];

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('kamar', 'public');
        }

        $kamar = Kamar::create($data);

        return response()->json([
            'message' => "Kamar {$kamar->nama} berhasil ditambahkan.",
            'kamar' => $this->formatKamar($kamar),
        ], 201);
    }

    public function updateKamar(Request $request, int $propertiId, int $kamarId): JsonResponse
    {
        $properti = $this->findProperti($request, $propertiId);

        if (! $properti) {
            return response()->json(['message' => 'Kos tidak ditemukan.'], 404);
        }

        $kamar = Kamar::where('id', $kamarId)->where('properti_id', $propertiId)->first();

        if (! $kamar) {
            return response()->json(['message' => 'Kamar tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'kapasitas' => 'required|integer|min:1|max:10',
            'harga_sewa_bulanan' => 'required|numeric|min:0',
            'harga_sewa_harian' => 'nullable|numeric|min:0',
            'jenis_harga' => 'required|in:bulanan,harian',
            'harga_asli' => 'nullable|numeric|min:0',
            'status' => 'required|in:tersedia,terisi,perbaikan',
            'foto' => 'nullable|image|max:2048',
        ]);

        $data = [
            'nama' => $validated['nama'],
            'kapasitas' => $validated['kapasitas'],
            'harga_sewa_bulanan' => $validated['harga_sewa_bulanan'],
            'harga_sewa_harian' => $validated['harga_sewa_harian'] ?? null,
            'jenis_harga' => $validated['jenis_harga'],
            'harga_asli' => $validated['harga_asli'] ?? null,
            'status' => $validated['status'],
        ];

        if ($request->hasFile('foto')) {
            if ($kamar->foto) {
                Storage::disk('public')->delete($kamar->foto);
            }
            $data['foto'] = $request->file('foto')->store('kamar', 'public');
        }

        $kamar->update($data);

        return response()->json([
            'message' => "Kamar {$kamar->nama} berhasil diperbarui.",
            'kamar' => $this->formatKamar($kamar),
        ]);
    }

    public function destroyKamar(Request $request, int $propertiId, int $kamarId): JsonResponse
    {
        $properti = $this->findProperti($request, $propertiId);

        if (! $properti) {
            return response()->json(['message' => 'Kos tidak ditemukan.'], 404);
        }

        $kamar = Kamar::where('id', $kamarId)->where('properti_id', $propertiId)->first();

        if (! $kamar) {
            return response()->json(['message' => 'Kamar tidak ditemukan.'], 404);
        }

        if ($kamar->foto) {
            Storage::disk('public')->delete($kamar->foto);
        }

        $kamar->delete();

        return response()->json(['message' => "Kamar {$kamar->nama} berhasil dihapus."]);
    }

    private function findProperti(Request $request, int $id): ?Properti
    {
        return Properti::query()
            ->when(! $this->bolehKelolaSemua($request), fn ($q) => $q->where('pemilik_id', $request->user()->id))
            ->find($id);
    }

    private function validateProperti(Request $request, ?Properti $properti): array
    {
        return $request->validate([
            'nama' => 'required|string|max:255',
            'kota' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'alamat' => 'nullable|string|max:500',
            'deskripsi' => 'nullable|string',
            'fasilitas' => 'nullable|string',
            'aturan' => 'nullable|string',
            'denda_per_hari' => 'nullable|numeric|min:0',
            'harga' => 'nullable|numeric|min:0',
            'harga_harian' => 'nullable|numeric|min:0',
            'harga_asli' => 'nullable|numeric|min:0',
            'jenis_harga' => 'required|in:bulanan,harian',
            'status' => 'required|in:aktif,nonaktif',
            'foto' => 'nullable|image|max:2048',
            'pemilik_id' => $properti === null ? ['nullable', 'integer', Rule::exists('users', 'id')] : ['nullable'],
        ]);
    }

    private function dataProperti(array $validated): array
    {
        $data = [
            'nama' => $validated['nama'],
            'kota' => $validated['kota'] ?? null,
            'latitude' => $this->nullableCoord($validated['latitude'] ?? null),
            'longitude' => $this->nullableCoord($validated['longitude'] ?? null),
            'alamat' => $validated['alamat'] ?? null,
            'deskripsi' => $validated['deskripsi'] ?? null,
            'aturan' => $validated['aturan'] ?? null,
            'denda_per_hari' => $validated['denda_per_hari'] ?? null,
            'harga' => $validated['harga'] ?? null,
            'harga_harian' => $validated['harga_harian'] ?? null,
            'harga_asli' => $validated['harga_asli'] ?? null,
            'jenis_harga' => $validated['jenis_harga'],
        ];

        if (! blank($validated['fasilitas'] ?? null)) {
            $data['fasilitas'] = FacilityHelper::normalizeString($validated['fasilitas']);
        } else {
            $data['fasilitas'] = null;
        }

        if (array_key_exists('status', $validated)) {
            $data['status'] = $validated['status'];
        }

        return $data;
    }

    private function formatProperti(Properti $p): array
    {
        return [
            'id' => $p->id,
            'nama' => $p->nama,
            'kota' => $p->kota,
            'latitude' => $p->latitude !== null ? (float) $p->latitude : null,
            'longitude' => $p->longitude !== null ? (float) $p->longitude : null,
            'alamat' => $p->alamat,
            'deskripsi' => $p->deskripsi,
            'fasilitas' => $p->fasilitas ? array_values(array_filter(array_map('trim', explode(',', $p->fasilitas)))) : [],
            'aturan' => $p->aturan,
            'denda_per_hari' => $p->denda_per_hari !== null ? (float) $p->denda_per_hari : null,
            'harga' => $p->harga !== null ? (float) $p->harga : null,
            'harga_harian' => $p->harga_harian !== null ? (float) $p->harga_harian : null,
            'harga_asli' => $p->harga_asli !== null ? (float) $p->harga_asli : null,
            'jenis_harga' => $p->jenis_harga,
            'status' => $p->status,
            'foto' => $p->foto ? '/storage/'.$p->foto : null,
            'total_kamar' => (int) ($p->total_kamar ?? 0),
            'kamar_terisi' => (int) ($p->kamar_terisi ?? 0),
            'kamars' => $p->kamars->map(fn (Kamar $k) => $this->formatKamar($k))->values(),
        ];
    }

    private function formatKamar(Kamar $k): array
    {
        return [
            'id' => $k->id,
            'nama' => $k->nama,
            'kapasitas' => (int) $k->kapasitas,
            'harga_sewa_bulanan' => (float) $k->harga_sewa_bulanan,
            'harga_sewa_harian' => $k->harga_sewa_harian !== null ? (float) $k->harga_sewa_harian : null,
            'jenis_harga' => $k->jenis_harga,
            'harga_asli' => $k->harga_asli !== null ? (float) $k->harga_asli : null,
            'status' => $k->status,
            'foto' => $k->foto ? '/storage/'.$k->foto : null,
        ];
    }

    private function nullableCoord(mixed $value): ?string
    {
        if ($value === null || $value === '' || (is_numeric($value) && (float) $value == 0)) {
            return null;
        }

        return (string) $value;
    }
}
