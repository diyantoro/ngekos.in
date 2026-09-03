<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class PenggunaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::with('roles')
            ->when($request->filled('search'), function ($q) use ($request) {
                $cari = $request->search;
                $q->where(fn ($q) => $q
                    ->where('nama', 'like', "%{$cari}%")
                    ->orWhere('email', 'like', "%{$cari}%"));
            })
            ->when($request->filled('peran'), function ($q) use ($request) {
                $q->role($request->peran);
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
            'peran' => 'required|in:super_admin,admin,pemilik,anak_kos',
        ]);

        $role = Role::findByName($validated['peran']);

        $user->syncRoles([$role]);

        return response()->json([
            'message' => "Peran {$user->nama} diubah menjadi {$role->name}.",
            'pengguna' => $this->format($user->fresh()->load('roles')),
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
            'pengguna' => $this->format($user->fresh()->load('roles')),
        ]);
    }

    private function format(User $u): array
    {
        return [
            'id' => $u->id,
            'nama' => $u->nama,
            'email' => $u->email,
            'no_hp' => $u->no_hp,
            'avatar' => $u->avatar ? '/storage/'.$u->avatar : null,
            'inisial' => $u->inisial,
            'peran' => $u->getRoleNames()->first(),
            'roles' => $u->roles->pluck('name')->values(),
            'dinonaktifkan_pada' => $u->dinonaktifkan_pada,
            'created_at' => $u->created_at,
        ];
    }
}
