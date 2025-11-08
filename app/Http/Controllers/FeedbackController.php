<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FeedbackController extends Controller
{

    public function index(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();
        
        $feedback = $business->feedback()
            ->orderBy('submitted_at', 'desc')
            ->get();

        return Inertia::render('Feedback/Index', [
            'feedback' => $feedback,
        ]);
    }

    /**
     * Show the form for submitting feedback (public page - no auth required).
     */
    public function show(Business $business)
    {
        return Inertia::render('Public/Feedback', [
            'business' => $business->load('category'),
        ]);
    }

    /**
     * Store a newly created feedback (public submission - no auth required).
     */
    public function store(Request $request, Business $business)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'customer_name' => $business->require_customer_name ? 'required|string|max:255' : 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
        ]);

        $feedback = $business->feedback()->create([
            ...$validated,
            'moderation_status' => $business->auto_approve_feedback ? 'approved' : 'pending',
            'is_public' => $business->auto_approve_feedback,
            'submitted_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return Inertia::render('Public/ThankYou', [
            'business' => $business->load('category'),
            'rating' => $feedback->rating,
        ]);
    }
}
