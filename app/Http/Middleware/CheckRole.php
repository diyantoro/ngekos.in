<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pengganti Spatie RoleMiddleware: otorisasi berbasis kolom users.role.
 * Mendukung sintaks yang sama: role:pemilik, role:super_admin|admin.
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Belum masuk.'], 401);
            }

            return redirect()->route('login');
        }

        $diizinkan = preg_split('/[|,]/', $role, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($diizinkan as $peran) {
            if ($user->hasRole(trim($peran))) {
                return $next($request);
            }
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        abort(403);
    }
}
