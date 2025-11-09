<?php

namespace App\AiAgents;

use LarAgent\Agent;

class FlagAgent extends Agent
{
    protected $model = 'llama3.2:1b';
    protected $provider = 'ollama';

    protected $responseSchema = [
        'name' => 'review_flag_check',
        'schema' => [
            'type' => 'object',
            'properties' => [
                'should_flag' => [
                    'type' => 'boolean',
                    'description' => 'Whether this review should be flagged for moderation (true) or published normally (false).'
                ],
                'confidence' => [
                    'type' => 'number',
                    'description' => 'Confidence score between 0.0 and 1.0, where 1.0 means absolutely certain the review should be flagged, and 0.0 means definitely safe to publish.',
                    'minimum' => 0.0,
                    'maximum' => 1.0
                ],
                'reason' => [
                    'type' => 'string',
                    'description' => 'Brief explanation of why the review is flagged, or confirmation that it is clean. Keep it concise and specific.'
                ]
            ],
            'required' => ['should_flag', 'confidence', 'reason'],
            'additionalProperties' => false,
        ],
        'strict' => true,
    ];

    public function instructions(): string
    {
        return view('agents.flag-review.instructions')->render();
    }

    public function analyze(string $reviewText, array $metadata = []): array
    {
        $heuristics = $this->runHeuristics($reviewText);
        $rating = $metadata['rating'] ?? null;
        
        $concerns = [];
        if ($heuristics['has_url']) {
            $concerns[] = "Contains URL/link";
        }
        if ($heuristics['has_email']) {
            $concerns[] = "Contains email address";
        }
        if ($heuristics['has_phone']) {
            $concerns[] = "Contains phone number";
        }
        if ($heuristics['profanity']['severity'] === 'high') {
            $concerns[] = "High-severity profanity detected";
        }

        $prompt = view('agents.flag-review.prompt', [
            'reviewText' => $reviewText,
            'rating' => $rating,
            'concerns' => $concerns,
        ])->render();

        $aiResult = $this->respond($prompt);
        
        $shouldFlag = $aiResult['should_flag'] ?? false;
        $confidence = $aiResult['confidence'] ?? 0.0;
        $reason = $aiResult['reason'] ?? 'No reason provided';
        
        // Check if high-severity heuristics were triggered
        $highSeverityDetected = ($heuristics['has_url'] ?? false) 
            || ($heuristics['has_email'] ?? false)
            || ($heuristics['has_phone'] ?? false)
            || (($heuristics['profanity']['severity'] ?? 'none') === 'high');

        // Determine moderation status based on confidence and context
        $moderationStatus = 'published';
        
        if ($shouldFlag) {
            // For positive ratings (4-5 stars), require higher confidence
            $hardFlagThreshold = ($rating >= 4 && !$highSeverityDetected) ? 0.9 : 0.7;
            $softFlagThreshold = 0.5;
            
            if ($confidence >= $hardFlagThreshold) {
                $moderationStatus = 'flagged'; // Hard flag - hide from public
            } elseif ($confidence >= $softFlagThreshold) {
                $moderationStatus = 'soft_flagged'; // Soft flag - needs review but stays visible
            }
            // else: confidence < 0.5, treat as false positive, keep published
        }
        
        return [
            'moderation_status' => $moderationStatus,
            'confidence' => $confidence,
            'reason' => $reason,
            'should_flag' => $shouldFlag,
            'heuristics' => $heuristics,
        ];
    }

    private function runHeuristics(string $text): array
    {
        $profanityCheck = $this->detectProfanity($text);
        
        return [
            'has_url' => $this->detectUrl($text),
            'has_email' => $this->detectEmail($text),
            'has_phone' => $this->detectPhone($text),
            'profanity' => $profanityCheck,
            'excessive_caps' => $this->detectExcessiveCaps($text),
        ];
    }

    private function detectUrl(string $text): bool
    {
        return (bool) preg_match('/https?:\/\/|www\.|\.com|\.net|\.org/i', $text);
    }

    private function detectEmail(string $text): bool
    {
        return (bool) preg_match('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i', $text);
    }

    private function detectPhone(string $text): bool
    {
        return (bool) preg_match('/\+?\d{1,4}?[-.\s]?\(?\d{1,3}?\)?[-.\s]?\d{1,4}[-.\s]?\d{1,4}[-.\s]?\d{1,9}/', $text);
    }

    private function detectProfanity(string $text): array
    {
        $highSeverityWords = [
            'fuck', 'shit', 'bitch', 'asshole', 'bastard',
            'dick', 'cock', 'pussy', 'slut', 'whore', 'cunt',
            'fag', 'faggot', 'nigger', 'nigga', 'kike', 'chink'
        ];

        $mediumSeverityWords = [
            'crap', 'piss', 'damn', 'hell'
        ];

        $textLower = strtolower($text);
        $highSeverityCount = 0;
        $mediumSeverityCount = 0;

        // Check high severity with word boundaries
        foreach ($highSeverityWords as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/i', $text)) {
                $highSeverityCount++;
            }
        }

        // Check medium severity with word boundaries
        foreach ($mediumSeverityWords as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/i', $text)) {
                $mediumSeverityCount++;
            }
        }

        return [
            'has_profanity' => $highSeverityCount > 0 || $mediumSeverityCount > 2,
            'severity' => $highSeverityCount > 0 ? 'high' : ($mediumSeverityCount > 0 ? 'medium' : 'none'),
            'high_count' => $highSeverityCount,
            'medium_count' => $mediumSeverityCount
        ];
    }

    private function detectExcessiveCaps(string $text): bool
    {
        $words = preg_split('/\s+/', $text);
        $capsCount = 0;
        $totalWords = count($words);

        if ($totalWords < 5) {
            return false;
        }

        foreach ($words as $word) {
            if (strlen($word) > 2 && $word === strtoupper($word) && preg_match('/[A-Z]/', $word)) {
                $capsCount++;
            }
        }

        return ($capsCount / $totalWords) > 0.3;
    }
}
