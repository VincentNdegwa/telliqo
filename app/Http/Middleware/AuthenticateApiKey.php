<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Models\Business;
use App\Services\FeatureService;
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
                'message' => 'Unauthorized',
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
        $checkFeature = $this->checkIfHasApiFeature($validKey->business_id);

        if ($checkFeature["error"]) {
            return response()->json([
                'message' => $checkFeature["message"],
            ], 403);
        }

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


    private function checkIfHasApiFeature($business_id): array
    {
        $featureService = new FeatureService();

        $business = Business::find($business_id);

        if (!$business) {
            return [
                'error' => true,
                'message' => 'Workspace not found or has been removed.',
            ];
        }

        if (method_exists($business, 'trashed') && $business->trashed()) {
            return [
                'error' => true,
                'message' => 'Workspace has been deleted.',
            ];
        }

        try {
            $hasFeature = $featureService->hasFeature($business, 'api_integration');
        } catch (\Throwable $e) {
            return [
                'error' => true,
                'message' => 'Unable to verify workspace features at this time. Please try again later.',
            ];
        }

        if (!$hasFeature) {
            return [
                'error' => true,
                'message' => 'API access is not enabled for this workspace. Upgrade your plan to enable API integrations.',
            ];
        }

        return [
            'error' => false,
            'message' => null,
        ];
    }
}
