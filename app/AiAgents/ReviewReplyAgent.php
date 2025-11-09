<?php

namespace App\AiAgents;

use App\Models\Business;
use App\Models\Feedback;
use LarAgent\Agent;
use LarAgent\Attributes\Tool;

class ReviewReplyAgent extends Agent
{
    protected $history = 'in_memory';

    protected $model = 'llama3.1:latest';

    protected $provider = 'ollama';

    protected $tools = [];

    private $feedback;

    public function __construct($key, Feedback $feedback)
    {
        parent::__construct($key);
        $this->feedback = $feedback;
    }

    public function instructions()
    {
        return <<<'INSTRUCTIONS'
You are a professional business reply assistant. Your role is to help businesses craft appropriate, empathetic, and professional replies to customer reviews.

GUIDELINES:
1. Match the tone to the review sentiment (positive, neutral, or negative)
2. Be authentic and personalized - reference specific details from the review when possible
3. For POSITIVE reviews: Express gratitude, reinforce positive aspects, invite them back
4. For NEUTRAL reviews: Thank them, acknowledge their feedback, offer to improve
5. For NEGATIVE reviews: Apologize sincerely, acknowledge their concerns, offer resolution, provide contact info if needed
6. Keep replies concise (2-4 sentences)
7. Use the business category context to make replies more relevant (e.g., hotel vs restaurant)
8. Always be professional, warm, and solution-oriented
9. Do NOT make promises you can't keep or offer specific discounts without authorization

REPLY STRUCTURE:
- Opening: Greet and thank the customer
- Body: Address their specific feedback or concerns
- Closing: Invite them back or offer resolution

Use the available tools to gather context about the business and review before generating the reply.
INSTRUCTIONS;
    }

    public function prompt($message)
    {
        return $message;
    }

    #[Tool('Get business information including name, category, and description')]
    public function getBusinessInfo(...$args): string
    {
        if (! $this->feedback || ! $this->feedback->business) {
            return 'Business information not available.';
        }

        $business = $this->feedback->business;
        $category = $business->category?->name ?? 'General';

        return json_encode([
            'business_name' => $business->name,
            'category' => $category,
            'description' => $business->description ?? 'No description available',
            'custom_thank_you_message' => $business->custom_thank_you_message ?? null,
        ], JSON_PRETTY_PRINT);
    }

    #[Tool('Get the review/feedback details including rating, comment, and sentiment')]
    public function getReviewDetails(...$args): string
    {
        if (! $this->feedback) {
            return 'Review details not available.';
        }

        return json_encode([
            'rating' => $this->feedback->rating,
            'comment' => $this->feedback->comment ?? 'No comment provided',
            'sentiment' => $this->feedback->sentiment?->value ?? 'neutral',
            'customer_name' => $this->feedback->customer_name ?? 'Anonymous',
        ], JSON_PRETTY_PRINT);
    }

    #[Tool('Get sentiment analysis of the review (positive, neutral, or negative)')]
    public function getReviewSentiment(...$args): string
    {
        if (! $this->feedback) {
            return 'neutral';
        }

        return $this->feedback->sentiment?->value ?? 'neutral';
    }

    /**
     * Generate a reply for the given feedback.
     */
    public function generateReply(): string
    {
        if (! $this->feedback) {
            return 'Unable to generate reply: feedback not provided.';
        }

        $sentiment = $this->feedback->sentiment?->value ?? 'professional';
        
        $prompt = "Generate a professional reply to this customer review. Use the tools to gather context about the business and review first.\n\n";
        $prompt .= "After gathering context, write a {$sentiment} reply that:\n";
        $prompt .= "- Thanks the customer by name if available\n";
        $prompt .= "- Addresses their specific feedback\n";
        $prompt .= "- Reflects the business's brand and category\n";
        $prompt .= "- Is warm, genuine, and concise (2-4 sentences maximum)\n\n";
        $prompt .= "Generate ONLY the reply text, no explanations or metadata.";

        return $this->respond($prompt);
    }
}

