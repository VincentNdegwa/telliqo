<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BusinessSettingsController extends Controller
{
    /**
     * Display the business settings form.
     */
    public function edit(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();
        $categories = BusinessCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Business/Settings', [
            'business' => $business->load('category'),
            'categories' => $categories,
        ]);
    }

    /**
     * Update the business settings.
     */
    public function update(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:businesses,slug,' . $business->id,
            'category_id' => 'required|exists:business_categories,id',
            'description' => 'nullable|string|max:1000',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:255',
            'custom_thank_you_message' => 'nullable|string|max:500',
            'brand_color_primary' => 'nullable|string|max:7',
            'brand_color_secondary' => 'nullable|string|max:7',
            'auto_approve_feedback' => 'boolean',
            'require_customer_name' => 'boolean',
            'feedback_email_notifications' => 'boolean',
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            if ($business->logo) {
                Storage::disk('public')->delete($business->logo);
            }
            
            $logoPath = $request->file('logo')->store('logos', 'public');
            $validated['logo'] = $logoPath;
        }

        $business->update($validated);

        return redirect()->route('business.settings')
            ->with('success', 'Business settings updated successfully.');
    }
    
    /**
     * Remove the business logo.
     */
    public function removeLogo(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();
        
        if ($business->logo) {
            Storage::disk('public')->delete($business->logo);
            $business->update(['logo' => null]);
        }
        
        return back()->with('success', 'Logo removed successfully.');
    }
}
