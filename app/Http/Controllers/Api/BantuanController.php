<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PesanBantuan;
use App\Services\BantuanNotifier;
use App\Services\PushNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BantuanController extends Controller
{
    public function kirim(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subjek' => 'nullable|string|max:255',
            'pesan' => 'required|string|min:10|max:2000',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'pesan.required' => 'Pesan wajib diisi.',
            'pesan.min' => 'Pesan minimal 10 karakter.',
        ]);

        $pesan = PesanBantuan::create([
            'user_id' => $request->user()?->id,
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'subjek' => $validated['subjek'] ?? null,
            'pesan' => $validated['pesan'],
            'status' => 'baru',
        ]);

        BantuanNotifier::sebarkanPesanBaru($pesan, $request->user()?->id);

        return response()->json(['message' => 'Pesan kamu berhasil dikirim. Admin akan membalas secepatnya.'], 201);
    }

    public function riwayat(Request $request): JsonResponse
    {
        try {
            PesanBantuan::where('user_id', $request->user()->id)
                ->whereNotNull('balasan')
                ->whereNull('dibaca_pada')
                ->update(['dibaca_pada' => now()]);
        } catch (\Throwable $e) {
            // Kolom dibaca_pada mungkin belum ada jika migrasi belum dijalankan.
        }

        $pesans = PesanBantuan::select('id', 'user_id', 'nama', 'email', 'subjek', 'pesan', 'status', 'balasan', 'dibalas_oleh', 'dibalas_at', 'created_at', 'updated_at')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn (PesanBantuan $p) => $this->format($p));

        return response()->json($pesans);
    }

    public function masuk(Request $request): JsonResponse
    {
        $pesans = PesanBantuan::latest()
            ->get()
            ->map(fn (PesanBantuan $p) => $this->format($p));

        return response()->json($pesans);
    }

    public function balas(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(['balasan' => 'required|string|max:2000']);

        $pesan = PesanBantuan::find($id);

        if (! $pesan) {
            return response()->json(['message' => 'Pesan tidak ditemukan.'], 404);
        }

        $pesan->update([
            'balasan' => $validated['balasan'],
            'status' => 'selesai',
            'dibalas_oleh' => $request->user()->id,
            'dibalas_at' => now(),
        ]);

        $this->kirimNotifikasiBalasan($pesan);

        return response()->json(['message' => 'Balasan berhasil dikirim.']);
    }

    private function kirimNotifikasiBalasan(PesanBantuan $pesan): void
    {
        $user = $pesan->user;

        if (! $user) {
            return;
        }

        PushNotifier::sendToUser(
            $user,
            [
                'title' => 'Balasan dari Admin',
                'body' => ($pesan->subjek ?: 'Pesan bantuanmu').' telah dibalas. Lihat di Riwayat Bantuan.',
            ],
            ['type' => 'bantuan_balasan', 'pesan_id' => (string) $pesan->id],
            'bantuan_balasan'
        );
    }

    public function tandaiDibaca(int $id): JsonResponse
    {
        $pesan = PesanBantuan::find($id);

        if (! $pesan) {
            return response()->json(['message' => 'Pesan tidak ditemukan.'], 404);
        }

        if ($pesan->status === 'baru') {
            $pesan->update(['status' => 'dibaca']);
        }

        return response()->json(['message' => 'Pesan ditandai sudah dibaca.']);
    }

    public function notifikasi(Request $request): JsonResponse
    {
        $notifikasi = $request->user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'dibaca' => $n->read_at !== null,
                'tanggal' => $n->created_at->format('Y-m-d H:i:s'),
                'data' => $n->data,
            ]);

        return response()->json($notifikasi);
    }

    public function notifikasiBaca(Request $request): JsonResponse
    {
        $request->user()->notifications()->update(['read_at' => now()]);

        return response()->json(['message' => 'Semua notifikasi ditandai sudah dibaca.']);
    }

    public function notifikasiCount(Request $request): JsonResponse
    {
        $count = $request->user()
            ->unreadNotifications()
            ->count();

        return response()->json(['count' => (int) $count]);
    }

    private function format(PesanBantuan $p): array
    {
        return [
            'id' => $p->id,
            'user_id' => $p->user_id,
            'nama' => $p->nama,
            'email' => $p->email,
            'subjek' => $p->subjek,
            'pesan' => $p->pesan,
            'balasan' => $p->balasan,
            'dibalas_oleh' => $p->pembalas?->nama,
            'dibalas_at' => $p->dibalas_at,
            'status' => $p->status,
            'created_at' => $p->created_at,
            'updated_at' => $p->updated_at,
        ];
    }
}
