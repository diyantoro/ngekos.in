<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePremiumFeature
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $cek = SubscriptionService::featureCheck($user, $feature);

        if (! $cek['allowed']) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => $cek['message'],
                    'required_plan' => $cek['required_plan'],
                ], 403);
            }

            abort(403, $cek['message']);
        }

        return $next($request);
    }
}
