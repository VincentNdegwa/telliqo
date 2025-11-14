<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendReviewRequestEmail;
use App\Models\Customer;
use App\Models\ReviewRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewRequestController extends Controller
{
    /**
     * Display a listing of review requests.
     */
    public function index(Request $request)
    {
        $business = $request->attributes->get('business');
        
        $query = $business->reviewRequests()
            ->with('customer:id,name,email')
            ->latest();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by send_mode
        if ($request->has('send_mode')) {
            $query->where('send_mode', $request->send_mode);
        }

        $reviewRequests = $query->paginate($request->get('per_page', 15));

        return response()->json($reviewRequests);
    }

    /**
     * Store a newly created review request.
     */
    public function store(Request $request)
    {
        $business = $request->attributes->get('business');

        $validator = Validator::make($request->all(), [
            'customer_id' => 'required_without:customer|exists:customers,id',
            'customer' => 'required_without:customer_id|array',
            'customer.name' => 'required_with:customer|string|max:255',
            'customer.email' => 'required_with:customer|email|max:255',
            'customer.phone' => 'nullable|string|max:20',
            'customer.company_name' => 'nullable|string|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'send_mode' => 'required|in:now,scheduled,manual',
            'schedule_hours' => 'required_if:send_mode,scheduled|nullable|integer|min:1|max:720', // Max 30 days
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Create or get customer
        if (isset($validated['customer'])) {
            $customer = $business->customers()->firstOrCreate(
                ['email' => $validated['customer']['email']],
                $validated['customer']
            );
        } else {
            $customer = Customer::findOrFail($validated['customer_id']);
        }

        // Check if customer is opted out
        if ($customer->opted_out) {
            return response()->json([
                'message' => 'Customer has opted out from receiving review requests',
            ], 422);
        }

        // Calculate scheduled_at and sent_at based on send_mode
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
                // Will be sent manually later
                break;
        }

        // Create review request
        $reviewRequest = $business->reviewRequests()->create([
            'customer_id' => $customer->id,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'send_mode' => $validated['send_mode'],
            'is_scheduled' => $isScheduled,
            'scheduled_at' => $scheduledAt,
            'sent_at' => $sentAt,
        ]);

        // Update customer stats
        $customer->increment('total_requests_sent');
        $customer->update(['last_request_sent_at' => now()]);

        // Send email immediately if send_mode is 'now'
        if ($validated['send_mode'] === 'now') {
            SendReviewRequestEmail::dispatch($reviewRequest);
        }

        // Schedule email if send_mode is 'scheduled'
        if ($validated['send_mode'] === 'scheduled') {
            SendReviewRequestEmail::dispatch($reviewRequest)->delay($scheduledAt);
        }

        return response()->json([
            'message' => 'Review request created successfully',
            'data' => $reviewRequest->load('customer:id,name,email')
        ], 201);
    }

    /**
     * Display the specified review request.
     */
    public function show(Request $request, ReviewRequest $reviewRequest)
    {
        $business = $request->attributes->get('business');

        if ($reviewRequest->business_id !== $business->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $reviewRequest->load(['customer:id,name,email,phone,company_name', 'feedback']);

        return response()->json($reviewRequest);
    }

    /**
     * Send a scheduled or manual review request immediately.
     */
    public function send(Request $request, ReviewRequest $reviewRequest)
    {
        $business = $request->attributes->get('business');

        if ($reviewRequest->business_id !== $business->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($reviewRequest->sent_at) {
            return response()->json(['message' => 'Review request already sent'], 422);
        }

        if ($reviewRequest->status === 'expired') {
            return response()->json(['message' => 'Review request has expired'], 422);
        }

        // Update review request
        $reviewRequest->update([
            'sent_at' => now(),
            'send_mode' => 'now',
            'is_scheduled' => false,
            'scheduled_at' => null,
        ]);

        // Send email
        SendReviewRequestEmail::dispatch($reviewRequest);

        return response()->json([
            'message' => 'Review request sent successfully',
            'data' => $reviewRequest->fresh()
        ]);
    }

    /**
     * Update the specified review request (reschedule).
     */
    public function update(Request $request, ReviewRequest $reviewRequest)
    {
        $business = $request->attributes->get('business');

        if ($reviewRequest->business_id !== $business->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($reviewRequest->sent_at) {
            return response()->json(['message' => 'Cannot update a sent review request'], 422);
        }

        $validator = Validator::make($request->all(), [
            'send_mode' => 'sometimes|in:scheduled,manual',
            'schedule_hours' => 'required_if:send_mode,scheduled|nullable|integer|min:1|max:720',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $updateData = [];

        if (isset($validated['send_mode'])) {
            $updateData['send_mode'] = $validated['send_mode'];

            if ($validated['send_mode'] === 'scheduled') {
                $updateData['is_scheduled'] = true;
                $updateData['scheduled_at'] = now()->addHours($validated['schedule_hours']);
            } else {
                $updateData['is_scheduled'] = false;
                $updateData['scheduled_at'] = null;
            }
        }

        $reviewRequest->update($updateData);

        return response()->json([
            'message' => 'Review request updated successfully',
            'data' => $reviewRequest->fresh()
        ]);
    }

    /**
     * Remove the specified review request.
     */
    public function destroy(Request $request, ReviewRequest $reviewRequest)
    {
        $business = $request->attributes->get('business');

        if ($reviewRequest->business_id !== $business->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $reviewRequest->delete();

        return response()->json([
            'message' => 'Review request deleted successfully'
        ]);
    }
}
