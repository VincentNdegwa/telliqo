<?php

namespace App\AiAgents;

use App\Models\Business;
use App\Models\Feedback;
use LarAgent\Agent;
use LarAgent\Attributes\Tool;

class ReviewReplyAgent extends Agent
{
    // protected $history = 'in_memory';

    protected $model = 'llama3.2:1b';

    protected $provider = 'ollama';

    protected $tools = [];

    private $feedback;
    private $businessData;
    private $reviewData;

    public function __construct($key, Feedback $feedback)
    {
        parent::__construct($key);
        $this->feedback = $feedback;
        
        // Parse business and review data in constructor
        $this->businessData = $this->parseBusinessData();
        $this->reviewData = $this->parseReviewData();
    }


    private function parseBusinessData(): array
    {
        if (!$this->feedback || !$this->feedback->business) {
            return [
                'name' => 'Unknown Business',
                'category' => 'General',
                'description' => 'No description available',
                'custom_message' => null,
            ];
        }

        $business = $this->feedback->business;
        
        return [
            'name' => $business->name,
            'phone' => $business->phone ?? 'N/A',
            'email' => $business->email ?? 'N/A',
            'address' => $business->address ?? 'N/A',
            'category' => $business->category?->name ?? 'General',
            'description' => $business->description ?? 'No description available',
            'custom_message' => $business->custom_thank_you_message,
        ];
    }


    private function parseReviewData(): array
    {
        if (!$this->feedback) {
            return [
                'rating' => 0,
                'comment' => 'No comment provided',
                'sentiment' => 'neutral',
                'customer_name' => 'Anonymous',
            ];
        }

        return [
            'rating' => $this->feedback->rating,
            'comment' => $this->feedback->comment ?? 'No comment provided',
            'sentiment' => $this->feedback->sentiment?->value ?? 'neutral',
            'customer_name' => $this->feedback->customer_name ?? 'Anonymous',
        ];
    }

    /**
     * Generate instructions using Blade template with business and review context.
     */
    public function instructions()
    {
        return view('agents.review-reply.instructions', [
            'business' => $this->businessData,
            'review' => $this->reviewData,
        ])->render();
    }

    /**
     * Pass through the message prompt.
     */
    public function prompt($message)
    {
        return $message;
    }

    public function promptMessage($prompt){
        return $this->respond($prompt);
    }

    public function getReplyPrompt(): string
    {
        if (!$this->feedback) {
            return 'Unable to generate reply: feedback not provided.';
        }

        return view('agents.review-reply.prompt', [
            'business' => $this->businessData,
            'review' => $this->reviewData,
        ])->render();
    }
}

