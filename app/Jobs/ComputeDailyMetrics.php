<?php

namespace App\Jobs;

use App\Models\Business;
use App\Models\BusinessMetric;
use App\Models\Enums\ModerationStatus;
use App\Models\Enums\Sentiments;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ComputeDailyMetrics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?int $businessId = null,
        public ?string $date = null
    ) {
        $this->date = $date ?? now()->subDay()->format('Y-m-d');
    }

    public function handle(): void
    {


        $businesses = $this->businessId 
            ? Business::where('id', $this->businessId)->get() 
            : Business::all();

        foreach ($businesses as $business) {
            $this->computeForBusiness($business);
        }
    }

    protected function computeForBusiness(Business $business): void
    {
        $positiveValue = Sentiments::POSITIVE->value;
        $neutralValue = Sentiments::NEUTRAL->value;
        $negativeValue = Sentiments::NEGATIVE->value;
        $notDeterminedValue = Sentiments::NOT_DETERMINED->value;

        $flagged_status = ModerationStatus::FLAGGED->value;

        $metrics = DB::table('feedback')
            ->where('business_id', $business->id)
            ->whereDate('created_at', $this->date)
            ->where('moderation_status', '!=', $flagged_status) 
            ->selectRaw('
                COUNT(*) as total_feedback,
                ROUND(AVG(rating), 2) as avg_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as promoters,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as passives,
                SUM(CASE WHEN rating <= 3 THEN 1 ELSE 0 END) as detractors,
                SUM(CASE WHEN sentiment = ? THEN 1 ELSE 0 END) as positive_count,
                SUM(CASE WHEN sentiment = ? THEN 1 ELSE 0 END) as neutral_count,
                SUM(CASE WHEN sentiment = ? THEN 1 ELSE 0 END) as negative_count,
                SUM(CASE WHEN sentiment = ? THEN 1 ELSE 0 END) as not_determined_count
            ', [
                $positiveValue,
                $neutralValue,
                $negativeValue,
                $notDeterminedValue,
            ])
            ->first();


        if (!$metrics || $metrics->total_feedback == 0) {
            Log::info('No feedback found for business', [
                'business_id' => $business->id,
                'date' => $this->date,
            ]);
            return;
        }

        // Calculate NPS: ((promoters - detractors) / total) * 100
        $nps = 0;
        if ($metrics->total_feedback > 0) {
            $nps = round((($metrics->promoters - $metrics->detractors) / $metrics->total_feedback) * 100);
        }

        // Extract keywords
        $topKeywords = $this->extractKeywords($business->id, $this->date);

        $metricData = [
            'business_id' => $business->id,
            'metric_date' => $this->date, 
            'total_feedback' => $metrics->total_feedback,
            'avg_rating' => $metrics->avg_rating,
            'promoters' => $metrics->promoters,
            'passives' => $metrics->passives,
            'detractors' => $metrics->detractors,
            'nps' => $nps,
            'positive_count' => $metrics->positive_count,
            'neutral_count' => $metrics->neutral_count,
            'negative_count' => $metrics->negative_count,
            'not_determined_count' => $metrics->not_determined_count,
            'top_keywords' => json_encode($topKeywords),
            'updated_at' => now(),
        ];

        $exists = DB::table('business_metrics')
            ->where('business_id', $business->id)
            ->where('metric_date', $this->date)
            ->exists();

        if ($exists) {
            Log::info('Updating existing business metric', [
                'business_id' => $business->id,
                'date' => $this->date,
            ]);
            
            DB::table('business_metrics')
                ->where('business_id', $business->id)
                ->where('metric_date', $this->date)
                ->update($metricData);
        } else {
            $metricData['created_at'] = now();
            DB::table('business_metrics')->insert($metricData);
        }
    }

    protected function extractKeywords(int $businessId, string $date): array
    {
        $feedback = DB::table('feedback')
            ->where('business_id', $businessId)
            ->whereDate('created_at', $date)
            ->where('moderation_status', '!=', 'flagged') // Exclude flagged feedback
            ->whereNotNull('comment')
            ->pluck('comment');

        if ($feedback->isEmpty()) {
            return [];
        }

        $stopWords = [
            'the', 'a', 'an', 'and', 'or', 'but', 'is', 'was', 'were', 'are', 'be', 'been',
            'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could',
            'should', 'may', 'might', 'can', 'to', 'of', 'in', 'for', 'on', 'with', 'at',
            'by', 'from', 'as', 'this', 'that', 'these', 'those', 'it', 'its', 'i', 'you',
            'he', 'she', 'they', 'we', 'my', 'your', 'his', 'her', 'their', 'our', 'very',
            'really', 'just', 'so', 'too', 'also', 'here', 'there', 'where', 'when', 'why',
            'how', 'what', 'which', 'who', 'whom', 'whose', 'than', 'then', 'not', 'no',
            'yes', 'all', 'some', 'any', 'many', 'much', 'more', 'most', 'such', 'only',
            'own', 'same', 'other', 'each', 'every', 'both', 'few', 'if', 'about', 'into',
            'through', 'during', 'before', 'after', 'above', 'below', 'between', 'under',
            'again', 'further', 'once', 'me', 'him', 'them', 'us'
        ];

        $wordFrequency = [];

        foreach ($feedback as $text) {
            // Basic tokenization
            $words = str_word_count(strtolower((string) $text), 1);
            
            foreach ($words as $word) {
                // Filter out short words and stop words
                if (strlen($word) >= 3 && !in_array($word, $stopWords)) {
                    $wordFrequency[$word] = ($wordFrequency[$word] ?? 0) + 1;
                }
            }
        }

        // Sort by frequency and get top 10
        arsort($wordFrequency);
        
        return array_map(
            fn($word, $count) => ['word' => $word, 'count' => $count],
            array_keys(array_slice($wordFrequency, 0, 10)),
            array_slice($wordFrequency, 0, 10)
        );
    }
}
