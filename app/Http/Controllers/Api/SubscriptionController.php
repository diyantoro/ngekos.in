<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function plans(): JsonResponse
    {
        return response()->json(config('plans', []));
    }

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $plan = SubscriptionService::getPlan($user);
        $subscription = SubscriptionService::getSubscription($user);

        return response()->json([
            'plan' => $plan,
            'status' => $subscription?->status ?? 'active',
            'starts_at' => $subscription?->starts_at,
            'expires_at' => $subscription?->expires_at,
            'property_used' => SubscriptionService::usage($user, 'property'),
            'property_limit' => SubscriptionService::limitFor($plan, 'property'),
            'room_used' => SubscriptionService::usage($user, 'room'),
            'room_limit' => SubscriptionService::limitFor($plan, 'room'),
        ]);
    }

    public function index(): JsonResponse
    {
        $subscriptions = Subscription::with('user:id,nama,email')->latest()->limit(100)->get();

        return response()->json($subscriptions->values());
    }

    public function history(Request $request): JsonResponse
    {
        return response()->json(
            SubscriptionService::history($request->user())->values()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'plan' => ['required', 'in:free,pro,business'],
            'status' => ['required', 'in:active,expired,cancelled'],
            'starts_at' => ['required_if:status,active', 'nullable', 'date'],
            'expires_at' => ['required_if:status,active', 'nullable', 'date', 'after:starts_at'],
        ]);

        $subscription = SubscriptionService::store((int) $validated['user_id'], [
            'plan' => $validated['plan'],
            'status' => $validated['status'],
            'starts_at' => $validated['starts_at'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return response()->json([
            'message' => 'Langganan berhasil disimpan.',
            'subscription' => $subscription,
        ], 201);
    }
}
