<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        $apiKey = $request->header('X-API-Key') ?? $request->bearerToken();

        if (!$apiKey) {
            return response()->json([
                'message' => 'API key is required. Provide it in X-API-Key header or Authorization: Bearer token',
            ], 401);
        }

        $validKey = $this->findValidApiKey($apiKey);

        if (!$validKey) {
            return response()->json([
                'message' => 'Invalid or expired API key',
            ], 401);
        }

        if ($permission && !$validKey->hasPermission($permission)) {
            return response()->json([
                'message' => 'API key does not have the required permission: ' . $permission,
            ], 403);
        }

        $validKey->recordUsage($request->ip());

        $request->merge(['api_business_id' => $validKey->business_id]);
        $request->attributes->set('api_key', $validKey);
        $request->attributes->set('business', $validKey->business);

        return $next($request);
    }

    /**
     * Find a valid API key from the database
     */
    private function findValidApiKey(string $plainKey): ?ApiKey
    {
        $apiKeys = ApiKey::active()->get();

        foreach ($apiKeys as $apiKey) {
            if ($apiKey->verify($plainKey) && $apiKey->isValid()) {
                return $apiKey;
            }
        }

        return null;
    }
}
