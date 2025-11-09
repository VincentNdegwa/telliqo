<?php

namespace App\AiAgents;

use LarAgent\Agent;

class ReviewAgent extends Agent
{
    protected $model = 'llama3.1:latest';
    protected $provider = 'ollama';

    protected $responseSchema = [
        'name' => 'review_analysis_data',
        'schema' => [
            'type' => 'object',
            'properties' => [
                'sentiment' => [
                    'type' => 'string',
                    'enum' => ['positive', 'negative', 'neutral'],
                    'description' => 'The overall sentiment of the review. Must be one of the enum values.'
                ],
                'summary' => [
                    'type' => 'string',
                    'description' => 'A brief, one-sentence summary of the review\'s main point.'
                ],
                'category' => [
                    'type' => 'string',
                    'description' => 'The main topic of the review (e.g., "Product Quality", "Customer Service", "Shipping Time", "Value for Money").'
                ],
                'is_urgent' => [
                    'type' => 'boolean',
                    'description' => 'True if the review indicates an urgent problem or high-priority issue (e.g., bug, safety concern, or delivery failure).'
                ]
            ],
            'required' => ['sentiment', 'summary', 'category', 'is_urgent'],
            'additionalProperties' => false,
        ],
        'strict' => true,
    ];

    public function instructions(): string
    {
        return "You are an expert review analysis system. Your sole task is to analyze the provided customer review text and strictly generate a JSON object that adheres to the defined schema. DO NOT output any text or markdown formatting (e.g., ```json) other than the raw JSON object itself.";
    }


    public function analyze(string $reviewText): array
    {
        $prompt = "Analyze the following customer review: \"{$reviewText}\"";
        return $this->respond($prompt);
    }
}
