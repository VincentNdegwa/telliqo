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

        // Calculate stats
        $stats = [
            'total' => $feedback->count(),
            'published' => $feedback->where('moderation_status', 'published')->count(),
            'flagged' => $feedback->where('moderation_status', 'flagged')->count(),
            'hidden' => $feedback->where('moderation_status', 'hidden')->count(),
            'avg_rating' => $feedback->avg('rating') ?? 0,
        ];

        return Inertia::render('Feedback/Index', [
            'feedback' => $feedback,
            'stats' => $stats,
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
            'moderation_status' => 'published', 
            'is_public' => true, 
            'submitted_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // TODO: Trigger background job for auto-scan (spam/profanity detection)
        // TODO: Send notification to business about new feedback

        return Inertia::render('Public/ThankYou', [
            'business' => $business->load('category'),
            'rating' => $feedback->rating,
        ]);
    }

    /**
     * Flag a review (business can flag problematic content)
     */
    public function flag(Request $request, Feedback $feedback)
    {
        $business = $request->user()->getCurrentBusiness();
        
        if ($feedback->business_id !== $business->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'reason' => 'required|string|in:spam,abusive,defamatory,inappropriate,other',
            'notes' => 'nullable|string|max:500',
        ]);

        // TODO: Create flag record in flags table
        // For now, just update the status
        $feedback->update([
            'status' => 'flagged',
            'flagged_at' => now(),
        ]);

        // TODO: Create audit log
        // TODO: Notify admins if high severity

        return back()->with('success', 'Review has been flagged for admin review');
    }
}
