<?php

namespace App\Http\Controllers;

use App\Jobs\Ai\AnalyzeReview;
use App\Services\EmailNotificationService;
use App\Services\FeatureService;
use App\Models\Business;
use App\Models\Enums\ModerationStatus;
use App\Models\Enums\Sentiments;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FeedbackController extends Controller
{
    protected EmailNotificationService $emails;
    protected FeatureService $features;

    public function __construct(EmailNotificationService $emails, FeatureService $features)
    {
        $this->emails = $emails;
        $this->features = $features;
    }

    public function index(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();
        
        if (!user_can('feedback.manage', $business)) {
            abort(403, 'You do not have permission to access feedback.');
        }
        
        $perPage = $request->input('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 20, 50]) ? $perPage : 10;
        
        // Paginate feedback - latest first
        $feedback = $business->feedback()
            ->orderBy('submitted_at', 'desc')
            ->paginate($perPage)
            ->through(function ($feedback) {
                return [
                    'id' => $feedback->id,
                    'customer_name' => $feedback->customer_name,
                    'customer_email' => $feedback->customer_email,
                    'rating' => $feedback->rating,
                    'comment' => $feedback->comment,
                    'reply_text' => $feedback->reply_text,
                    'sentiment' => $feedback->sentiment?->serialize(),
                    'moderation_status' => $feedback->moderation_status->serialize(),
                    'is_public' => $feedback->is_public,
                    'submitted_at' => $feedback->submitted_at,
                    'replied_at' => $feedback->replied_at,
                ];
            });

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
                'value' => $allFeedback->where('moderation_status', ModerationStatus::FLAGGED)->count(),
                'trend' => $this->calculateTrend(
                    $last7Days->where('moderation_status', ModerationStatus::FLAGGED)->count(),
                    $previous7Days->where('moderation_status', ModerationStatus::FLAGGED)->count(),
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


    public function show(Business $business)
    {
        $feedbackSettings = $business->getSetting('feedback_collection_settings', []);
        
        return Inertia::render('Public/Feedback', [
            'business' => $business->load('category'),
            'feedbackSettings' => $feedbackSettings,
        ]);
    }


    public function store(Request $request, Business $business)
    {
        $feedbackSettings = $business->getSetting('feedback_collection_settings', []);
        $moderationSettings = $business->getSetting('moderation_settings', []);
        
        if (! $this->features->canUseFeature($business, 'feedback_submissions')) {
            return redirect()->back()->with('error', 'This business is not currently accepting feedback submissions.');
        }

        $requireName = $feedbackSettings['require_customer_name'] ?? false;
        $requireEmail = $feedbackSettings['require_customer_email'] ?? false;
        $allowAnonymous = $feedbackSettings['allow_anonymous_feedback'] ?? true;
        
        if (!$allowAnonymous && !$requireName && !$requireEmail) {
            $requireName = true;
        }
        
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'customer_name' => $requireName ? 'required|string|max:255' : 'nullable|string|max:255',
            'customer_email' => $requireEmail ? 'required|email|max:255' : 'nullable|email|max:255',
        ]);

        $enableAiModeration = $moderationSettings['enable_ai_moderation'] ?? true;

        if ($enableAiModeration && ! $this->features->canUseFeature($business, 'ai_sentiment')) {
            $enableAiModeration = false;
        }

        $blockDuplicates = $moderationSettings['block_duplicate_reviews'] ?? true;
        
        if ($blockDuplicates && $validated['customer_email']) {
            $existingFeedback = $business->feedback()
                ->where('customer_email', $validated['customer_email'])
                ->where('created_at', '>=', now()->subDays(7))
                ->exists();
            
            if ($existingFeedback) {
                return redirect()->back()->with("error", 'You have already submitted feedback recently. Please wait before submitting again.');
            }
        }

        $feedback = $business->feedback()->create(array_merge($validated, [
            'moderation_status' => ModerationStatus::PUBLISHED,
            'sentiment' => Sentiments::NOT_DETERMINED,
            'is_public' => true, 
            'submitted_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]));

        $feedback->refresh();

        $this->features->recordUsage($business, 'feedback_submissions');
        
        if ($enableAiModeration) {
            AnalyzeReview::dispatch($feedback);
        }

        try {
            $this->emails->sendNewFeedbackNotification($feedback);
            $this->emails->sendLowRatingAlert($feedback);
        } catch (\Throwable $e) {
            report($e);
        }

        return Inertia::render('Public/ThankYou', [
            'business' => $business->load('category'),
            'rating' => $feedback->rating,
        ]);
    }


    public function reply(Request $request, Feedback $feedback)
    {
        $business = $request->user()->getCurrentBusiness();
        
        if ($feedback->business_id !== $business->id) {
            abort(403, 'Unauthorized');
        }

        if (!user_can('feedback.reply', $business)) {
            return redirect()->back()->with("error", "You do not have permission to reply to feedback.");
        }

        if (! $this->features->canUseFeature($business, 'manual_feedback_reply')) {
            return redirect()->back()->with('error', 'Your plan does not support manual reply feature.');
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


    public function flag(Request $request, Feedback $feedback)
    {
        $business = $request->user()->getCurrentBusiness();
        
        if ($feedback->business_id !== $business->id) {
            abort(403, 'Unauthorized');
        }

        if (!user_can('feedback.flag', $business)) {
            return redirect()->back()->with("error", "You do not have permission to flag feedback.");
        }

        if (! $this->features->canUseFeature($business, 'manual_moderation')) {
            return redirect()->back()->with('error', 'Your plan does not support manual moderation feature.');
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|in:spam,abusive,defamatory,inappropriate,other',
            'notes' => 'nullable|string|max:500',
        ]);

        // TODO: Create flag record in flags table
        // For now, just update the status
        $feedback->update([
            'moderation_status' => ModerationStatus::FLAGGED,
        ]);

        return back()->with('success', 'Review has been flagged for admin review');
    }
}
