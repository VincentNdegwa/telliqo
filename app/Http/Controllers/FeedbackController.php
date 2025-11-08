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
        
        // Get per_page from request, default to 10
        $perPage = $request->input('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 20, 50]) ? $perPage : 10;
        
        // Paginate feedback - latest first
        $feedback = $business->feedback()
            ->orderBy('submitted_at', 'desc')
            ->paginate($perPage);

        // Get all feedback for stats
        $allFeedback = $business->feedback;
        
        // Calculate trends (last 7 days vs previous 7 days)
        $now = now();
        $last7Days = $business->feedback()
            ->where('submitted_at', '>=', $now->copy()->subDays(7))
            ->get();
        $previous7Days = $business->feedback()
            ->where('submitted_at', '>=', $now->copy()->subDays(14))
            ->where('submitted_at', '<', $now->copy()->subDays(7))
            ->get();
        
        // Calculate stats with trends
        $stats = [
            'total' => [
                'value' => $allFeedback->count(),
                'trend' => $this->calculateTrend(
                    $last7Days->count(),
                    $previous7Days->count()
                ),
            ],
            'published' => [
                'value' => $allFeedback->where('is_public', true)->count(),
                'trend' => $this->calculateTrend(
                    $last7Days->where('is_public', true)->count(),
                    $previous7Days->where('is_public', true)->count()
                ),
            ],
            'flagged' => [
                'value' => $allFeedback->where('moderation_status', 'flagged')->count(),
                'trend' => $this->calculateTrend(
                    $last7Days->where('moderation_status', 'flagged')->count(),
                    $previous7Days->where('moderation_status', 'flagged')->count()
                ),
            ],
            'avg_rating' => [
                'value' => $allFeedback->avg('rating') ?? 0,
                'trend' => $this->calculateTrend(
                    $last7Days->avg('rating') ?? 0,
                    $previous7Days->avg('rating') ?? 0,
                    true // is rating
                ),
            ],
        ];

        return Inertia::render('Feedback/Index', [
            'feedback' => $feedback,
            'stats' => $stats,
        ]);
    }

    private function calculateTrend($current, $previous, $isRating = false)
    {
        if ($previous == 0) {
            return $current > 0 ? ['percentage' => 100, 'direction' => 'up'] : ['percentage' => 0, 'direction' => 'neutral'];
        }

        $change = $current - $previous;
        $percentage = abs(round(($change / $previous) * 100, 1));
        
        if ($isRating) {
            $direction = $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral');
        } else {
            $direction = $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral');
        }

        return [
            'percentage' => $percentage,
            'direction' => $direction,
        ];
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
     * Reply to a feedback (business response)
     */
    public function reply(Request $request, Feedback $feedback)
    {
        $business = $request->user()->getCurrentBusiness();
        
        if ($feedback->business_id !== $business->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'reply_text' => 'required|string|max:1000',
        ]);

        $feedback->update([
            'reply_text' => $validated['reply_text'],
            'replied_at' => now(),
        ]);

        return back()->with('success', 'Reply posted successfully');
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
            'reason' => 'nullable|string|in:spam,abusive,defamatory,inappropriate,other',
            'notes' => 'nullable|string|max:500',
        ]);

        // TODO: Create flag record in flags table
        // For now, just update the status
        $feedback->update([
            'moderation_status' => 'flagged',
        ]);

        // TODO: Create audit log
        // TODO: Notify admins if high severity

        return back()->with('success', 'Review has been flagged for admin review');
    }
}
