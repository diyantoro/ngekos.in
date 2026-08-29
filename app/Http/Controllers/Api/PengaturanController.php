<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengaturan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;

class PengaturanController extends Controller
{
    private function bolehKelola(Request $request): bool
    {
        return $request->user()->hasPermissionTo('konfigurasi.kelola');
    }

    public function index(Request $request): JsonResponse
    {
        $situs = [
            'nama' => Pengaturan::namaSitus(),
            'deskripsi' => (string) Pengaturan::ambil('situs.deskripsi', ''),
            'email' => (string) Pengaturan::ambil('situs.email', ''),
            'telepon' => (string) Pengaturan::ambil('situs.telepon', ''),
            'alamat' => (string) Pengaturan::ambil('situs.alamat', ''),
        ];

        $kos = [
            'jatuh_tempo' => (string) Pengaturan::ambil('kos.jatuh_tempo', 'akhir'),
            'denda_per_hari' => Pengaturan::dendaPerHari(),
        ];

        $user = $request->user();
        $notifikasi = [];
        foreach (array_keys(User::daftarNotifikasi()) as $kunci) {
            $notifikasi[$kunci] = $user->notif($kunci);
        }

        return response()->json([
            'situs' => $situs,
            'kos' => $kos,
            'notifikasi' => $notifikasi,
            'boleh_kelola' => $this->bolehKelola($request),
        ]);
    }

    public function simpanSitus(Request $request): JsonResponse
    {
        abort_unless($this->bolehKelola($request), 403);

        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:150',
            'telepon' => 'nullable|string|max:30',
            'alamat' => 'nullable|string|max:255',
        ]);

        Pengaturan::simpanBanyak([
            'situs.nama' => $validated['nama'],
            'situs.deskripsi' => $validated['deskripsi'] ?? '',
            'situs.email' => $validated['email'] ?? '',
            'situs.telepon' => $validated['telepon'] ?? '',
            'situs.alamat' => $validated['alamat'] ?? '',
        ]);

        return response()->json(['message' => 'Pengaturan situs berhasil disimpan.']);
    }

    public function simpanKos(Request $request): JsonResponse
    {
        abort_unless($this->bolehKelola($request), 403);

        $validated = $request->validate([
            'jatuh_tempo' => 'required|in:akhir,'.implode(',', range(1, 28)),
            'denda_per_hari' => 'required|numeric|min:0',
        ]);

        Pengaturan::simpanBanyak([
            'kos.jatuh_tempo' => $validated['jatuh_tempo'],
            'kos.denda_per_hari' => number_format((float) $validated['denda_per_hari'], 2, '.', ''),
        ]);

        return response()->json(['message' => 'Pengaturan kos berhasil disimpan.']);
    }

    public function simpanNotifikasi(Request $request): JsonResponse
    {
        $preferensi = [];

        foreach (array_keys(User::daftarNotifikasi()) as $kunci) {
            $preferensi[$kunci] = (bool) $request->boolean($kunci);
        }

        $request->user()->update(['preferensi_notifikasi' => $preferensi]);

        return response()->json(['message' => 'Preferensi notifikasi berhasil disimpan.']);
    }

    public function gantiPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'password_confirmation' => ['required'],
        ]);

        $request->user()->update([
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'Password berhasil diubah.']);
    }
}