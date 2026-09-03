<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string|max:512',
            'platform' => 'sometimes|string|in:android,ios',
        ], [
            'token.required' => 'Token perangkat wajib diisi.',
            'platform.in' => 'Platform tidak dikenal.',
        ]);

        // Jika token yang sama terdaftar di akun lain (mis. pindah perangkat), pindahkan ke akun ini.
        DeviceToken::where('token', $validated['token'])
            ->where('user_id', '!=', $request->user()->id)
            ->delete();

        DeviceToken::updateOrCreate(
            ['token' => $validated['token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $validated['platform'] ?? 'android',
            ]
        );

        return response()->json(['message' => 'Token perangkat disimpan.']);
    }

    public function destroy(Request $request): JsonResponse
    {
        $token = $request->input('token');

        if ($token) {
            DeviceToken::where('user_id', $request->user()->id)
                ->where('token', $token)
                ->delete();
        }

        return response()->json(['message' => 'Token perangkat dihapus.']);
    }
}
