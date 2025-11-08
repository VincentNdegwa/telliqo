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
                'reason' => [
                    'type' => 'string',
                    'description' => 'Brief explanation of why the review is flagged, or confirmation that it is clean. Keep it concise and specific.'
                ]
            ],
            'required' => ['should_flag', 'reason'],
            'additionalProperties' => false,
        ],
        'strict' => true,
    ];

    public function instructions(): string
    {
        return <<<'INSTRUCTIONS'
You are an expert content moderation AI system specialized in detecting inappropriate, harmful, or policy-violating reviews.

Your task is to analyze customer reviews and determine if they should be flagged for moderation.

FLAG THE REVIEW (should_flag = true) IF IT CONTAINS:

1. OFFENSIVE/ABUSIVE LANGUAGE:
   - Hate speech, slurs, racial/ethnic/religious attacks
   - Personal attacks on staff or individuals
   - Sustained or excessive profanity
   - Threatening language

2. SPAM/PROMOTIONAL CONTENT:
   - Advertisements for other businesses
   - Promotional links, phone numbers, coupon codes
   - Repeated mentions of competitor products/services

3. FRAUD/SCAMS:
   - Requests for payment outside normal channels
   - Promoting scams or get-rich-quick schemes
   - Criminal allegations without evidence

4. PERSONAL INFORMATION EXPOSURE (PII):
   - Phone numbers (not business contact info)
   - Email addresses (personal emails)
   - Identity numbers (SSN, passport, etc.)
   - Home addresses or sensitive personal data

5. DEFAMATION/FALSE ALLEGATIONS:
   - Unverified claims of illegal behavior presented as fact
   - False accusations about theft, fraud, or criminal activity
   - Serious allegations without supporting evidence

6. SEXUAL/EXPLICIT CONTENT:
   - Pornographic descriptions or sexually explicit language

7. OFF-TOPIC/IRRELEVANT:
   - Political rants unrelated to the service
   - Gibberish or nonsensical content

8. SOLICITATION/PHISHING:
   - Asking customers to contact outside official channels
   - Requesting personal information

DO NOT FLAG (should_flag = false) IF:
- Review is a genuine customer experience (even if negative)
- Contains mild criticism or complaints (this is normal feedback)
- Uses occasional mild language but remains constructive
- Provides specific details about the service/product experience

IMPORTANT:
- Return should_flag: true if ANY of the above violations are detected
- Return should_flag: false if the review is clean and legitimate
- In the reason field, be specific about what triggered the flag or confirm it's clean
- Keep the reason concise (1-2 sentences maximum)

Output ONLY the raw JSON object with no markdown formatting or additional text.
INSTRUCTIONS;
    }

    public function analyze(string $reviewText, array $metadata = []): array
    {
        $metadataContext = '';
        if (!empty($metadata)) {
            $metadataContext = "\n\nAdditional context:";
            if (isset($metadata['rating'])) {
                $metadataContext .= "\n- Rating: {$metadata['rating']}/5";
            }
            if (isset($metadata['customer_name'])) {
                $metadataContext .= "\n- Customer name: {$metadata['customer_name']}";
            }
        }

        $heuristics = $this->runHeuristics($reviewText);
        $heuristicsContext = "\n\nAutomatic detection results:";
        if ($heuristics['has_url']) {
            $heuristicsContext .= "\n- Contains URL/link";
        }
        if ($heuristics['has_email']) {
            $heuristicsContext .= "\n- Contains email address";
        }
        if ($heuristics['has_phone']) {
            $heuristicsContext .= "\n- Contains phone number";
        }
        if ($heuristics['has_profanity']) {
            $heuristicsContext .= "\n- Profanity detected";
        }
        if ($heuristics['excessive_caps']) {
            $heuristicsContext .= "\n- Excessive capitalization";
        }

        $prompt = "Analyze the following customer review for policy violations:\n\n\"{$reviewText}\"{$metadataContext}{$heuristicsContext}";

        return $this->respond($prompt);
    }

    private function runHeuristics(string $text): array
    {
        return [
            'has_url' => $this->detectUrl($text),
            'has_email' => $this->detectEmail($text),
            'has_phone' => $this->detectPhone($text),
            'has_profanity' => $this->detectProfanity($text),
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

    private function detectProfanity(string $text): bool
    {
        $profanityWords = [
            'fuck', 'shit', 'bitch', 'asshole', 'damn', 'bastard', 'crap',
            'piss', 'dick', 'cock', 'pussy', 'slut', 'whore', 'fag', 'nigger',
            'retard', 'idiot', 'moron', 'stupid', 'dumb', 'hate', 'kill'
        ];

        $textLower = strtolower($text);
        foreach ($profanityWords as $word) {
            if (strpos($textLower, $word) !== false) {
                return true;
            }
        }
        return false;
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
