<?php

namespace App\AiAgents;

use LarAgent\Agent;

class FlagAgent extends Agent
{
    protected $model = 'llama3.1:latest';
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
        return <<<'INSTRUCTIONS'
You moderate customer reviews. Your ONLY job is to detect SPAM and ABUSE.

CRITICAL: Customer complaints and negative feedback are ALWAYS ALLOWED. Never flag reviews just because they complain or give low ratings.

ONLY FLAG if review contains:
1. SPAM: URLs, phone numbers, email addresses promoting other businesses
2. HATE SPEECH: Racial slurs, religious attacks, ethnic hatred
3. SEVERE PROFANITY: F-word, N-word, C-word and similar
4. THREATS: Violence or harm to people
5. EXPLICIT SEXUAL CONTENT: Pornographic language

NEVER FLAG these (they are NORMAL reviews):
- Price complaints: "overpriced", "too expensive", "not worth the money"
- Service complaints: "rude staff", "poor service", "terrible experience"
- Quality issues: "food was cold", "room was dirty", "product broke"
- Any honest negative feedback or criticism
- Low ratings (1-2 stars)
- Mild language: "stupid", "hate", "worst", "awful"

EXAMPLES OF SAFE REVIEWS (should_flag=false, confidence=0.0):

"They overpriced the meal." → should_flag=false, confidence=0.0, reason="Price complaint - normal feedback"
"Best hotel for sure." → should_flag=false, confidence=0.0, reason="Positive review"
"Worst experience ever, staff was incredibly rude." → should_flag=false, confidence=0.0, reason="Negative but legitimate feedback"
"I hate waiting 2 hours for food." → should_flag=false, confidence=0.0, reason="Complaint about service time"
"Terrible service, very disappointed." → should_flag=false, confidence=0.0, reason="Honest negative review"

EXAMPLES TO FLAG (should_flag=true, confidence=0.8-1.0):

"Visit cheapdeals.com for better prices!" → should_flag=true, confidence=1.0, reason="Spam URL"
"Call 555-1234 for real deals" → should_flag=true, confidence=1.0, reason="Promotional phone number"
"The [n-word] manager is trash" → should_flag=true, confidence=1.0, reason="Racial slur"
"I'll f***ing kill the owner" → should_flag=true, confidence=1.0, reason="Violent threat"

DEFAULT BEHAVIOR:
- If you're unsure → should_flag=false, confidence=0.0
- Complaints about price, service, quality → should_flag=false
- Words like "hate", "worst", "terrible", "awful" → should_flag=false (these are normal)

Output ONLY JSON: {"should_flag": false, "confidence": 0.0, "reason": "..."}
INSTRUCTIONS;
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

        $metadataContext = '';
        if (!empty($metadata)) {
            if (isset($metadata['rating'])) {
                $metadataContext .= "\nRating: {$metadata['rating']}/5";
            }
        }

        $concernsContext = '';
        if (!empty($concerns)) {
            $concernsContext = "\nDetected: " . implode(", ", $concerns);
        }

        $prompt = "Review: \"{$reviewText}\"{$metadataContext}{$concernsContext}";

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
