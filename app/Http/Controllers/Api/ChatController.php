<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatPesan;
use App\Models\Properti;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $adalahAnakKos = $user->hasRole('anak_kos');

        $conversations = ChatPesan::query()
            ->select('properti_id', 'anak_kos_id')
            ->selectRaw('MAX(created_at) as last_message_at')
            ->selectRaw('SUM(CASE WHEN dibaca_pada IS NULL AND pengirim_id != ? THEN 1 ELSE 0 END) as unread_count', [$user->id])
            ->selectRaw('(SELECT isi FROM chat_pesans c2 WHERE c2.properti_id = chat_pesans.properti_id AND c2.anak_kos_id = chat_pesans.anak_kos_id ORDER BY c2.created_at DESC LIMIT 1) as last_message')
            ->selectRaw('(SELECT pengirim_id FROM chat_pesans c3 WHERE c3.properti_id = chat_pesans.properti_id AND c3.anak_kos_id = chat_pesans.anak_kos_id ORDER BY c3.created_at DESC LIMIT 1) as last_sender_id')
            ->where(function ($q) use ($user) {
                $q->where('anak_kos_id', $user->id)
                    ->orWhereHas('properti', fn ($p) => $p->where('pemilik_id', $user->id));
            })
            ->groupBy('properti_id', 'anak_kos_id')
            ->orderBy('last_message_at', 'desc')
            ->get();

        $result = $conversations->map(function ($conv) use ($user, $adalahAnakKos) {
            $properti = Properti::select('id', 'nama', 'foto')->find($conv->properti_id);

            if ($adalahAnakKos) {
                $lawan = Properti::select('id', 'pemilik_id')->find($conv->properti_id)?->pemilik;
            } else {
                $lawan = \App\Models\User::select('id', 'nama', 'avatar')->find($conv->anak_kos_id);
            }

            return [
                'properti_id' => $conv->properti_id,
                'properti_nama' => $properti?->nama,
                'properti_foto' => $properti?->foto ? asset('storage/' . $properti->foto) : null,
                'anak_kos_id' => $conv->anak_kos_id,
                'lawan' => $lawan ? [
                    'id' => $lawan->id,
                    'nama' => $lawan->nama,
                    'avatar' => $lawan->avatar ? asset('storage/' . $lawan->avatar) : null,
                ] : null,
                'last_message' => $conv->last_message,
                'last_message_at' => $conv->last_message_at,
                'unread_count' => (int) $conv->unread_count,
                'last_sender_id' => (int) $conv->last_sender_id,
            ];
        });

        return response()->json($result);
    }

    public function show(Request $request, int $propertiId): JsonResponse
    {
        $user = $request->user();

        $anakKosId = $user->hasRole('anak_kos') ? $user->id : $request->query('anak_kos_id');

        if (! $anakKosId) {
            return response()->json(['message' => 'anak_kos_id diperlukan.'], 400);
        }

        if (! $this->berhakMengakses($user, $propertiId, $anakKosId)) {
            return response()->json(['message' => 'Anda tidak berhak membaca percakapan ini.'], 403);
        }

        $messages = ChatPesan::where('properti_id', $propertiId)
            ->where('anak_kos_id', $anakKosId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn ($msg) => [
                'id' => $msg->id,
                'pengirim_id' => $msg->pengirim_id,
                'isi' => $msg->isi,
                'dibaca_pada' => $msg->dibaca_pada,
                'created_at' => $msg->created_at,
            ]);

        // Mark unread messages as read
        ChatPesan::where('properti_id', $propertiId)
            ->where('anak_kos_id', $anakKosId)
            ->where('pengirim_id', '!=', $user->id)
            ->whereNull('dibaca_pada')
            ->update(['dibaca_pada' => now()]);

        return response()->json([
            'properti_id' => $propertiId,
            'anak_kos_id' => $anakKosId,
            'messages' => $messages,
        ]);
    }

    public function send(Request $request, int $propertiId): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'isi' => 'required|string|max:5000',
            'anak_kos_id' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) use ($request): void {
                    if (! $request->user()->hasRole('anak_kos') && blank($value)) {
                        $fail('anak_kos_id wajib diisi.');
                    }
                },
            ],
        ]);

        $anakKosId = $user->hasRole('anak_kos') ? $user->id : $validated['anak_kos_id'];

        if (! $anakKosId) {
            return response()->json(['message' => 'anak_kos_id diperlukan.'], 400);
        }

        if (! $this->berhakMengakses($user, $propertiId, $anakKosId)) {
            return response()->json(['message' => 'Anda tidak berhak mengirim pesan di percakapan ini.'], 403);
        }

        $message = ChatPesan::create([
            'properti_id' => $propertiId,
            'anak_kos_id' => $anakKosId,
            'pengirim_id' => $user->id,
            'isi' => $validated['isi'],
        ]);

        return response()->json([
            'id' => $message->id,
            'pengirim_id' => $message->pengirim_id,
            'isi' => $message->isi,
            'created_at' => $message->created_at,
        ], 201);
    }

    /**
     * Cek apakah user berhak membaca/menulis percakapan di properti tertentu:
     * sebagai anak kos pemilik percakapan, pemilik properti, atau admin yang ditugaskan.
     */
    private function berhakMengakses($user, int $propertiId, int $anakKosId): bool
    {
        if ($user->hasRole('anak_kos')) {
            return $user->id === $anakKosId;
        }

        return Properti::where('id', $propertiId)
            ->where(function ($q) use ($user) {
                $q->where('pemilik_id', $user->id)
                    ->orWhereHas('admins', fn ($a) => $a->where('users.id', $user->id));
            })
            ->exists();
    }
}
