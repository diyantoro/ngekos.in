<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengeluaran;
use App\Models\Properti;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PengeluaranController extends Controller
{
    private function bolehKelolaSemua(Request $request): bool
    {
        return $request->user()->hasAnyRole(['admin', 'super_admin']);
    }

    /**
     * Properti yang boleh dikelola user ini (pemilik hanya miliknya,
     * admin/super admin semua properti).
     */
    private function propertiIds(Request $request)
    {
        $query = Properti::query();

        if (! $this->bolehKelolaSemua($request)) {
            $query->where('pemilik_id', $request->user()->id);
        }

        return $query->pluck('id');
    }

    public function index(Request $request): JsonResponse
    {
        $propertiId = $request->integer('properti_id', 0);

        $pengeluarans = Pengeluaran::whereIn('properti_id', $this->propertiIds($request))
            ->with('properti:id,nama')
            ->when($propertiId, fn ($q) => $q->where('properti_id', $propertiId))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $total = $pengeluarans->sum('jumlah');

        return response()->json([
            'pengeluarans' => $pengeluarans->map(fn (Pengeluaran $p) => $this->formatPengeluaran($p))->values(),
            'total' => (float) $total,
            'total_dibuat_oleh' => auth()->id(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $properti = $this->findProperti($request, $request->input('properti_id'));

        if (! $properti) {
            return response()->json(['message' => 'Kos tidak ditemukan atau bukan milik Anda.'], 404);
        }

        $validated = $request->validate([
            'properti_id' => 'required|integer',
            'kategori' => 'required|in:listrik,air,internet,maintenance,kebersihan,gaji,renovasi,lainnya',
            'keterangan' => 'nullable|string|max:255',
            'jumlah' => 'required|numeric|min:0',
            'tanggal' => 'required|date',
        ]);

        $pengeluaran = Pengeluaran::create([
            'properti_id' => $validated['properti_id'],
            'kategori' => $validated['kategori'],
            'keterangan' => $validated['keterangan'] ?? null,
            'jumlah' => $validated['jumlah'],
            'tanggal' => $validated['tanggal'],
            'dibuat_oleh' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Pengeluaran berhasil dicatat.',
            'pengeluaran' => $this->formatPengeluaran($pengeluaran->load('properti:id,nama')),
        ], 201);
    }

    /**
     * Delegate POST + _method (PUT/DELETE) yang dipakai aplikasi mobile.
     */
    public function handlePengeluaran(Request $request, int $id): JsonResponse
    {
        return match (strtoupper($request->input('_method', 'PUT'))) {
            'DELETE' => $this->destroy($request, $id),
            default => $this->update($request, $id),
        };
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $pengeluaran = $this->findPengeluaran($request, $id);

        if (! $pengeluaran) {
            return response()->json(['message' => 'Pengeluaran tidak ditemukan.'], 404);
        }

        $properti = $this->findProperti($request, $request->input('properti_id', $pengeluaran->properti_id));

        if (! $properti) {
            return response()->json(['message' => 'Kos tidak ditemukan atau bukan milik Anda.'], 404);
        }

        $validated = $request->validate([
            'properti_id' => 'required|integer',
            'kategori' => 'required|in:listrik,air,internet,maintenance,kebersihan,gaji,renovasi,lainnya',
            'keterangan' => 'nullable|string|max:255',
            'jumlah' => 'required|numeric|min:0',
            'tanggal' => 'required|date',
        ]);

        $pengeluaran->update($validated);

        return response()->json([
            'message' => 'Pengeluaran berhasil diperbarui.',
            'pengeluaran' => $this->formatPengeluaran($pengeluaran->load('properti:id,nama')),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $pengeluaran = $this->findPengeluaran($request, $id);

        if (! $pengeluaran) {
            return response()->json(['message' => 'Pengeluaran tidak ditemukan.'], 404);
        }

        $pengeluaran->delete();

        return response()->json(['message' => 'Pengeluaran dihapus.']);
    }

    private function findPengeluaran(Request $request, int $id): ?Pengeluaran
    {
        return Pengeluaran::where('id', $id)
            ->whereIn('properti_id', $this->propertiIds($request))
            ->first();
    }

    private function findProperti(Request $request, ?int $propertiId): ?Properti
    {
        if (! $propertiId) {
            return null;
        }

        return Properti::where('id', $propertiId)
            ->when(! $this->bolehKelolaSemua($request), fn ($q) => $q->where('pemilik_id', $request->user()->id))
            ->first();
    }

    private function formatPengeluaran(Pengeluaran $p): array
    {
        return [
            'id' => $p->id,
            'properti_id' => $p->properti_id,
            'properti' => $p->properti?->nama,
            'kategori' => $p->kategori,
            'keterangan' => $p->keterangan,
            'jumlah' => (float) $p->jumlah,
            'tanggal' => $p->tanggal->toDateString(),
            'dibuat_oleh' => $p->dibuat_oleh,
            'created_at' => optional($p->created_at)?->toIso8601String(),
        ];
    }
}