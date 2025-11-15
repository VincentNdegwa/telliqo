<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $user = $request->user();
        $currentBusiness = null;
        $userPermissions = [];

        if ($user && $user->hasCompletedOnboarding()) {
            // Get current business from session or user's first business
            $businessId = $request->session()->get('current_business_id');
            
            if ($businessId) {
                $currentBusiness = $user->businesses()
                    ->where('business_id', $businessId)
                    ->with('category')
                    ->first();
            }
            
            // Fallback to first business if no session business
            if (! $currentBusiness) {
                $currentBusiness = $user->getCurrentBusiness();
                
                if ($currentBusiness) {
                    $request->session()->put('current_business_id', $currentBusiness->id);
                }
            }

            if ($currentBusiness) {
                $userPermissions = $user->allPermissions()
                    ->pluck('name')
                    ->toArray();
            }
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $user,
                'business' => $currentBusiness,
                'businesses' => $user ? $user->businesses()->with('category')->get() : [],
                'permissions' => $userPermissions,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
                'info' => $request->session()->get('info'),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
