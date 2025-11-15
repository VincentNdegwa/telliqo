<?php

namespace App\Http\Controllers;

use App\Jobs\SendReviewRequestEmail;
use App\Models\Customer;
use App\Models\ReviewRequest;
use App\Mail\ReviewRequestEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class ReviewRequestsController extends Controller
{
    public function index(Request $request)
    {
        $business = Auth::user()->getCurrentBusiness();
        
        if (!user_can('review-request.manage', $business)) {
            abort(403, 'You do not have permission to access review requests.');
        }
        
        $query = ReviewRequest::query()
            ->forBusiness($business->id)
            ->with('customer:id,name,email')
            ->select([
                'id',
                'business_id',
                'customer_id',
                'subject',
                'status',
                'sent_at',
                'opened_at',
                'completed_at',
                'expires_at',
                'reminder_sent_count',
                'created_at'
            ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('customer', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $reviewRequests = $query->latest()->paginate(15);

        $stats = [
            'total' => ReviewRequest::forBusiness($business->id)->count(),
            'pending' => ReviewRequest::forBusiness($business->id)->pending()->count(),
            'opened' => ReviewRequest::forBusiness($business->id)->opened()->count(),
            'completed' => ReviewRequest::forBusiness($business->id)->completed()->count(),
            'expired' => ReviewRequest::forBusiness($business->id)->expired()->count(),
        ];

        return Inertia::render('ReviewRequests/Index', [
            'reviewRequests' => $reviewRequests,
            'stats' => $stats,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function create()
    {
        $business = Auth::user()->getCurrentBusiness();
        
        if (!user_can('review-request.create', $business)) {
            abort(403, 'You do not have permission to create review requests.');
        }
        
        $customers = Customer::forBusiness($business->id)
            ->active()
            ->select('id', 'name', 'email', 'company_name')
            ->orderBy('name')
            ->get();

        return Inertia::render('ReviewRequests/Create', [
            'customers' => $customers,
        ]);
    }

    public function store(Request $request)
    {
        $business = Auth::user()->getCurrentBusiness();

        if (!user_can('review-request.create', $business)) {
            return redirect()->back()->with('error', 'You do not have permission to create review requests.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'send_mode' => 'required|in:now,scheduled,manual',
            'schedule_hours' => 'required_if:send_mode,scheduled|nullable|integer|min:1|max:720',
        ]);

        $scheduledAt = null;
        $sentAt = null;
        $isScheduled = false;

        switch ($validated['send_mode']) {
            case 'now':
                $sentAt = now();
                break;
            case 'scheduled':
                $scheduledAt = now()->addHours($validated['schedule_hours']);
                $isScheduled = true;
                break;
            case 'manual':
                break;
        }

        $reviewRequest = $business->reviewRequests()->create([
            'customer_id' => $validated['customer_id'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'send_mode' => $validated['send_mode'],
            'is_scheduled' => $isScheduled,
            'scheduled_at' => $scheduledAt,
            'sent_at' => $sentAt,
        ]);

        $reviewRequest->customer->increment('total_requests_sent');
        $reviewRequest->customer->update(['last_request_sent_at' => now()]);

        // Send email immediately if send_mode is 'now'
        if ($validated['send_mode'] === 'now') {
            SendReviewRequestEmail::dispatch($reviewRequest);
        }

        // Schedule email if send_mode is 'scheduled'
        if ($validated['send_mode'] === 'scheduled') {
            SendReviewRequestEmail::dispatch($reviewRequest)->delay($scheduledAt);
        }

        $message = match($validated['send_mode']) {
            'now' => 'Review request created and sent successfully.',
            'scheduled' => 'Review request created and scheduled successfully.',
            'manual' => 'Review request created. You can send it manually when ready.',
        };

        return redirect()->route('review-requests.index')
            ->with('success', $message);
    }

    public function show(ReviewRequest $reviewRequest)
    {
        $business = Auth::user()->getCurrentBusiness();

        if (!user_can('review-request.view', $business)) {
            abort(403, 'You do not have permission to view review requests.');
        }

        $reviewRequest->load([
            'customer:id,name,email,phone,company_name',
            'feedback:id,review_request_id,rating,comment,created_at'
        ]);

        $canSendReminder = $reviewRequest->canSendReminder();

        return Inertia::render('ReviewRequests/Show', [
            'reviewRequest' => $reviewRequest,
            'canSendReminder' => $canSendReminder,
        ]);
    }

    public function destroy(ReviewRequest $reviewRequest)
    {
        $business = Auth::user()->getCurrentBusiness();

        if (!user_can('review-request.delete', $business)) {
            return redirect()->back()->with('error', 'You do not have permission to delete review requests.');
        }

        $reviewRequest->delete();

        return redirect()->route('review-requests.index')
            ->with('success', 'Review request deleted successfully.');
    }

    public function sendReminder(ReviewRequest $reviewRequest)
    {
        $business = Auth::user()->getCurrentBusiness();

        if (!user_can('review-request.send', $business)) {
            return redirect()->back()->with('error', 'You do not have permission to send review requests.');
        }

        if (!$reviewRequest->canSendReminder()) {
            return back()->with('error', 'Cannot send reminder for this request.');
        }

        Mail::to($reviewRequest->customer->email)
            ->send(new ReviewRequestEmail($reviewRequest, true));

        $reviewRequest->increment('reminder_sent_count');
        $reviewRequest->update(['last_reminder_at' => now()]);

        return back()->with('success', 'Reminder sent successfully.');
    }

    public function publicShow(string $token)
    {
        $reviewRequest = ReviewRequest::where('unique_token', $token)->firstOrFail();

        if ($reviewRequest->isExpired()) {
            $reviewRequest->markAsExpired();
            return Inertia::render('ReviewRequest/Expired', [
                'business' => $reviewRequest->business->only(['name', 'logo_url']),
            ]);
        }

        if ($reviewRequest->status === 'completed') {
            return Inertia::render('ReviewRequest/AlreadyCompleted', [
                'business' => $reviewRequest->business->only(['name', 'logo_url']),
            ]);
        }

        $reviewRequest->markAsOpened();

        return Inertia::render('ReviewRequest/Submit', [
            'reviewRequest' => [
                'id' => $reviewRequest->id,
                'business' => $reviewRequest->business->only(['name', 'logo_url']),
                'customer' => $reviewRequest->customer->only(['name']),
                'subject' => $reviewRequest->subject,
                'message' => $reviewRequest->message,
                'expires_at' => $reviewRequest->expires_at,
            ],
            'token' => $token,
        ]);
    }

    public function submitReview(Request $request, string $token)
    {
        $reviewRequest = ReviewRequest::where('unique_token', $token)->firstOrFail();

        if ($reviewRequest->status === 'completed') {
            return back()->with('error', 'This review request has already been completed.');
        }

        if ($reviewRequest->isExpired()) {
            $reviewRequest->markAsExpired();
            return back()->with('error', 'This review request has expired.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $feedback = $reviewRequest->business->feedback()->create([
            'customer_id' => $reviewRequest->customer_id,
            'review_request_id' => $reviewRequest->id,
            'customer_name' => $reviewRequest->customer->name,
            'customer_email' => $reviewRequest->customer->email,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'submitted_at' => now(),
            'is_public' => true,
            'moderation_status' => 'published',
        ]);

        $reviewRequest->markAsCompleted();
        
        $reviewRequest->customer->increment('total_feedbacks');
        $reviewRequest->customer->update(['last_feedback_at' => now()]);

        return Inertia::render('ReviewRequest/ThankYou', [
            'business' => $reviewRequest->business,
        ]);
    }

    public function optOut(string $token)
    {
        $reviewRequest = ReviewRequest::where('unique_token', $token)->firstOrFail();
        
        $reviewRequest->customer->update(['opted_out' => true]);

        return Inertia::render('ReviewRequest/OptedOut', [
            'business' => $reviewRequest->business->only(['name', 'logo_url']),
        ]);
    }
}
