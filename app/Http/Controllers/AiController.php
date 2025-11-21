<?php

namespace App\Http\Controllers;

use App\AiAgents\ReviewReplyAgent;
use App\Models\Feedback;
use App\Services\FeatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiController extends Controller
{
    public function __construct(protected FeatureService $features)
    {
    }

    public function replySuggestion(Request $request)
    {
        $validated = $request->validate([
            'feedback_id' => 'required|exists:feedback,id',
        ]);

        try {
            $feedback = Feedback::with(['business.category'])
                ->find($validated['feedback_id']);
                
            if (! $feedback) {
                return response()->json([
                    'error' => 'Review not found',
                    'message' => 'Review not found'
                ], 404);
            }

            $business = $feedback->business;

            if (! $business || ! $this->features->canUseFeature($business, 'ai_reply_generator')) {
                return response()->json([
                    'error' => 'Your plan does not support AI reply feature.',
                    'message' => 'Your plan does not support AI reply feature.',
                ], 403);
            }
            
            $key = 'feedback_review_'.$feedback->id;
            
            $agent = new ReviewReplyAgent($key, $feedback);

            $prompt = $agent->getReplyPrompt();

            $mode = $request->input('mode', 'stream'); 
            
            if ($mode === 'prompt') {
                $response = $agent->promptMessage($prompt);
                
                return response()->json([
                    'success' => true,
                    'reply' => $response,
                ]);
            } else {
                return $agent->streamResponse($prompt, 'sse');
            }

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
