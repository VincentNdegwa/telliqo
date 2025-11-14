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
        // Ensure API key belongs to current business
        if ($apiKey->business_id !== $request->user()->getCurrentBusiness()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'required|array',
            'permissions.*' => 'string',
        ]);

        $apiKey->update($validated);

        return redirect()->back()->with('message', 'API key updated successfully.');
    }

    /**
     * Revoke (deactivate) the specified API key
     */
    public function revoke(Request $request, ApiKey $apiKey)
    {
        // Ensure API key belongs to current business
        if ($apiKey->business_id !== $request->user()->getCurrentBusiness()->id) {
            abort(403);
        }
        
        $apiKey->revoke();

        return redirect()->back()->with('message', 'API key revoked successfully.');
    }

    /**
     * Remove the specified API key
     */
    public function destroy(Request $request, ApiKey $apiKey)
    {
        // Ensure API key belongs to current business
        if ($apiKey->business_id !== $request->user()->getCurrentBusiness()->id) {
            abort(403);
        }
        
        $apiKey->delete();

        return redirect()->back()->with('message', 'API key deleted successfully.');
    }
}
