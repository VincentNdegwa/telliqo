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

        if (!user_can('business-settings.manage', $business)) {
            abort(403, 'You do not have permission to access business settings.');
        }

        $categories = BusinessCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('settings/business/Profile', [
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

        if (!user_can('business-settings.manage', $business)) {
            return redirect()->back()->with('error', 'You do not have permission to update business settings.');
        }

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


    public function notifications(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();

        if (!user_can('business-settings.notifications', $business)) {
            abort(403, 'You do not have permission to access notification settings.');
        }

        $settings = $business->getSetting('notification_settings', []);

        return Inertia::render('settings/business/Notifications', [
            'business' => $business,
            'settings' => $settings,
        ]);
    }

    public function updateNotifications(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();

        if (!user_can('business-settings.notifications', $business)) {
            return redirect()->back()->with('error', 'You do not have permission to update notification settings.');
        }

        $validated = $request->validate([
            'email_notifications_enabled' => 'boolean',
            'new_feedback_email' => 'boolean',
            'weekly_summary' => 'boolean',
            'monthly_report' => 'boolean',
            'low_rating_alert' => 'boolean',
            'low_rating_threshold' => 'integer|min:1|max:5',
        ]);

        $business->updateSettingGroup('notification_settings', $validated);

        return back()->with('success', 'Notification settings updated successfully.');
    }

    public function display(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();

        if (!user_can('business-settings.display', $business)) {
            abort(403, 'You do not have permission to access display settings.');
        }

        $settings = $business->getSetting('display_settings', []);

        return Inertia::render('settings/business/Display', [
            'business' => $business,
            'settings' => $settings,
        ]);
    }

    public function updateDisplay(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();

        if (!user_can('business-settings.display', $business)) {
            return redirect()->back()->with('error', 'You do not have permission to update display settings.');
        }

        $validated = $request->validate([
            'show_business_profile' => 'boolean',
            'display_logo' => 'boolean',
            'show_total_reviews' => 'boolean',
            'show_average_rating' => 'boolean',
            'show_verified_badge' => 'boolean',
        ]);

        $business->updateSettingGroup('display_settings', $validated);

        return back()->with('success', 'Display settings updated successfully.');
    }

    public function moderation(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();

        if (!user_can('business-settings.moderation', $business)) {
            abort(403, 'You do not have permission to access moderation settings.');
        }

        $settings = $business->getSetting('moderation_settings', []);

        return Inertia::render('settings/business/Moderation', [
            'business' => $business,
            'settings' => $settings,
        ]);
    }

    public function updateModeration(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();

        if (!user_can('business-settings.moderation', $business)) {
            return redirect()->back()->with('error', 'You do not have permission to update moderation settings.');
        }

        $validated = $request->validate([
            'enable_ai_moderation' => 'boolean',
            'enable_ai_sentiment' => 'boolean',
            'block_duplicate_reviews' => 'boolean',
        ]);

        $business->updateSettingGroup('moderation_settings', $validated);

        return back()->with('success', 'Moderation settings updated successfully.');
    }

    public function feedbackSettings(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();

        if (!user_can('business-settings.feedback', $business)) {
            abort(403, 'You do not have permission to access feedback settings.');
        }

        $settings = $business->getSetting('feedback_collection_settings', []);

        return Inertia::render('settings/business/FeedBack', [
            'business' => $business,
            'settings' => $settings,
        ]);
    }

    public function updateFeedbackSettings(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();

        if (!user_can('business-settings.feedback', $business)) {
            return redirect()->back()->with('error', 'You do not have permission to update feedback settings.');
        }

        $validated = $request->validate([
            'require_customer_name' => 'boolean',
            'require_customer_email' => 'boolean',
            'allow_anonymous_feedback' => 'boolean',
        ]);

        $business->updateSettingGroup('feedback_collection_settings', $validated);

        return back()->with('success', 'Feedback collection settings updated successfully.');
    }

    public function externalReviews(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();

        if (!user_can('business-settings.feedback', $business)) {
            abort(403, 'You do not have permission to access external review settings.');
        }

        $settings = $business->getSetting('external_review_settings', []);

        return Inertia::render('settings/business/ExternalReviews', [
            'business' => $business,
            'settings' => $settings,
        ]);
    }

    public function updateExternalReviews(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();

        if (!user_can('business-settings.feedback', $business)) {
            return redirect()->back()->with('error', 'You do not have permission to update external review settings.');
        }

        $validated = $request->validate([
            'enabled' => 'boolean',
            'google_review_url' => 'nullable|url|max:500',
            'rating_threshold' => 'integer|min:1|max:5',
        ]);

        // Auto-disable if URL is not provided
        if (empty($validated['google_review_url'])) {
            $validated['enabled'] = false;
        }

        $business->updateSettingGroup('external_review_settings', $validated);

        return back()->with('success', 'External review settings updated successfully.');
    }
}
