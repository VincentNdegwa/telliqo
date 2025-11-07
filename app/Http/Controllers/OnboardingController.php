<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    /**
     * Display the onboarding wizard.
     */
    public function show(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        // If user already completed onboarding, redirect to dashboard
        if ($request->user()->hasCompletedOnboarding()) {
            return redirect()->route('dashboard');
        }

        // Get all active business categories
        $categories = BusinessCategory::active()
            ->orderBy('name')
            ->get();

        return Inertia::render('Onboarding/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store the complete business onboarding data.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category_id' => 'required|exists:business_categories,id',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Create the business
            $business = Business::create([
                ...$validated,
                'onboarding_completed_at' => now(),
            ]);

            // Attach the current user as the owner
            $business->users()->attach($request->user()->id, [
                'role' => 'owner',
                'is_active' => true,
                'joined_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('dashboard')->with('success', 'Business created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Failed to create business. Please try again.',
            ]);
        }
    }
}
