<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PenggunaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'nullable|string|max:100',
            'peran' => ['nullable', Rule::enum(UserRole::class)],
        ]);

        $users = User::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $cari = $request->search;
                $q->where(fn ($q) => $q
                    ->where('nama', 'like', "%{$cari}%")
                    ->orWhere('email', 'like', "%{$cari}%"));
            })
            ->when($request->filled('peran'), function ($q) use ($request) {
                $q->where('role', $request->peran);
            })
            ->orderBy('nama')
            ->get();

        return response()->json($users->map(fn (User $u) => $this->format($u))->values());
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'Pengguna tidak ditemukan.'], 404);
        }

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Anda tidak bisa mengubah peran akun sendiri.'], 422);
        }

        if ($user->is_super_admin) {
            return response()->json(['message' => 'Akun Super Admin tidak bisa diubah.'], 422);
        }

        $validated = $request->validate([
            'peran' => ['required', Rule::enum(UserRole::class)],
        ]);

        $user->update(['role' => $validated['peran']]);

        return response()->json([
            'message' => "Peran {$user->nama} diubah menjadi {$user->roleValue()}.",
            'pengguna' => $this->format($user->fresh()),
        ]);
    }

    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'Pengguna tidak ditemukan.'], 404);
        }

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Anda tidak bisa menonaktifkan akun sendiri.'], 422);
        }

        if ($user->is_super_admin) {
            return response()->json(['message' => 'Akun Super Admin tidak bisa dinonaktifkan.'], 422);
        }

        if ($user->aktif()) {
            $user->update(['dinonaktifkan_pada' => now()]);
            $user->tokens()->delete();
            $pesan = "Akun {$user->nama} dinonaktifkan.";
        } else {
            $user->update(['dinonaktifkan_pada' => null]);
            $pesan = "Akun {$user->nama} diaktifkan kembali.";
        }

        return response()->json([
            'message' => $pesan,
            'pengguna' => $this->format($user->fresh()),
        ]);
    }

    private function format(User $u): array
    {
        $peran = $u->roleValue();

        return [
            'id' => $u->id,
            'nama' => $u->nama,
            'email' => $u->email,
            'no_hp' => $u->no_hp,
            'avatar' => $u->avatar ? '/storage/'.$u->avatar : null,
            'inisial' => $u->inisial,
            'peran' => $peran,
            'roles' => $peran !== null ? [$peran] : [],
            'dinonaktifkan_pada' => $u->dinonaktifkan_pada,
            'created_at' => $u->created_at,
        ];
    }
}
