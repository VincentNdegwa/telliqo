<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ApiKeyController extends Controller
{
    /**
     * Display a listing of API keys
     */
    public function index(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();

        if (!user_can('api-integration.manage', $business)) {
            abort(403, 'You do not have permission to access API keys.');
        }
        
        $apiKeys = $business->apiKeys()
            ->latest()
            ->get();

        $stats = [
            'total' => $apiKeys->count(),
            'active' => $apiKeys->where('is_active', true)
                ->filter(fn($key) => !$key->expires_at || $key->expires_at->isFuture())
                ->count(),
            'revoked' => $apiKeys->where('is_active', false)->count(),
            'expired' => $apiKeys->where('is_active', true)
                ->filter(fn($key) => $key->expires_at && $key->expires_at->isPast())
                ->count(),
        ];

        return inertia('Settings/ApiKeys/Index', [
            'apiKeys' => $apiKeys,
            'stats' => $stats,
        ]);
    }

    /**
     * Store a newly created API key
     */
    public function store(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();

        if (!user_can('api-integration.create-key', $business)) {
            return redirect()->back()->with('error', 'You do not have permission to create API keys.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'required|array',
            'permissions.*' => 'string',
            'expires_in_days' => 'nullable|integer|min:1|max:365',
        ]);

        $expiresAt = null;
        if (isset($validated['expires_in_days']) && $validated['expires_in_days']) {
            $expiresAt = now()->addDays($validated['expires_in_days']);
        }

        $result = ApiKey::generate(
            $business,
            $validated['name'],
            $validated['permissions'],
            $expiresAt
        );

        $apiKeys = $business->apiKeys()
            ->latest()
            ->get();

        $stats = [
            'total' => $apiKeys->count(),
            'active' => $apiKeys->where('is_active', true)
                ->filter(fn($key) => !$key->expires_at || $key->expires_at->isFuture())
                ->count(),
            'revoked' => $apiKeys->where('is_active', false)->count(),
            'expired' => $apiKeys->where('is_active', true)
                ->filter(fn($key) => $key->expires_at && $key->expires_at->isPast())
                ->count(),
        ];

        return inertia('Settings/ApiKeys/Index', [
            'apiKeys' => $apiKeys,
            'stats' => $stats,
            'newApiKey' => $result['plain_key'],
        ]);
    }

    /**
     * Update the specified API key
     */
    public function update(Request $request, ApiKey $apiKey)
    {
        $business = $request->user()->getCurrentBusiness();

        // Ensure API key belongs to current business
        if ($apiKey->business_id !== $business->id) {
            abort(403);
        }

        if (!user_can('api-integration.update-key', $business)) {
            return redirect()->back()->with('error', 'You do not have permission to update API keys.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'required|array',
            'permissions.*' => 'string',
        ]);

        $apiKey->update($validated);

        return redirect()->back()->with('success', 'API key updated successfully.');
    }

    /**
     * Revoke (deactivate) the specified API key
     */
    public function revoke(Request $request, ApiKey $apiKey)
    {
        $business = $request->user()->getCurrentBusiness();

        // Ensure API key belongs to current business
        if ($apiKey->business_id !== $business->id) {
            abort(403);
        }

        if (!user_can('api-integration.revoke-key', $business)) {
            return redirect()->back()->with('error', 'You do not have permission to revoke API keys.');
        }
        
        $apiKey->revoke();

        return redirect()->back()->with('success', 'API key revoked successfully.');
    }

    /**
     * Remove the specified API key
     */
    public function destroy(Request $request, ApiKey $apiKey)
    {
        $business = $request->user()->getCurrentBusiness();

        // Ensure API key belongs to current business
        if ($apiKey->business_id !== $business->id) {
            abort(403);
        }

        if (!user_can('api-integration.delete-key', $business)) {
            return redirect()->back()->with('error', 'You do not have permission to delete API keys.');
        }
        
        $apiKey->delete();

        return redirect()->back()->with('success', 'API key deleted successfully.');
    }
}
