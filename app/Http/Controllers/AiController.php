<?php

namespace App\Http\Controllers;

use App\AiAgents\ReviewReplyAgent;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiController extends Controller
{
 
    public function replySuggestion(Request $request)
    {
        $validated = $request->validate([
            'feedback_id' => 'required|exists:feedback,id',
        ]);

        try {

            $feedback = Feedback::with(['business.category'])
                ->find($validated['feedback_id']);
            if (!$feedback) {
                return response()->json([
                    "error" => "Review not found",
                    "message" => "Review not found"
                ], 404);
            }
            $key = "feedback_review_".$feedback->id;
            
            $agent = new ReviewReplyAgent($key, $feedback);

            $suggestion = $agent->generateReply();

            return response()->json([
                'suggestion' => trim($suggestion),
            ]);

        } catch (\Exception $e) {
            Log::error('AI reply generation failed', [
                'feedback_id' => $validated['feedback_id'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to generate AI suggestion. Please try again.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
