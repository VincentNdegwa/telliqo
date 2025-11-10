<?php

namespace App\Jobs;

use App\Models\Business;
use App\Services\EmailNotificationService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendWeeklySummaries implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(EmailNotificationService $emails): void
    {
        $end = Carbon::now();
        $start = $end->copy()->subDays(7);

        $businesses = Business::query()->get();

        foreach ($businesses as $business) {
            $settings = $business->getSetting('notification_settings', []);

            if (!($settings['email_notifications_enabled'] ?? false)) {
                continue;
            }

            if (!($settings['weekly_summary'] ?? false)) {
                continue;
            }

            $feedbacks = $business->feedback()
                ->whereBetween('submitted_at', [$start->toDateTimeString(), $end->toDateTimeString()])
                ->get();

            $total = $feedbacks->count();
            $average = $total ? round($feedbacks->avg('rating'), 2) : 0;

            $ratingBreakdown = [];
            for ($i = 5; $i >= 1; $i--) {
                $ratingBreakdown[$i] = $feedbacks->where('rating', $i)->count();
            }

            $responseRate = $total ? round(($feedbacks->whereNotNull('replied_at')->count() / $total) * 100, 1) : 0;

            $summaryData = [
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'total_count' => $total,
                'average_rating' => $average,
                'rating_breakdown' => $ratingBreakdown,
                'response_rate' => $responseRate,
                'sentiment_breakdown' => [],
                'top_keywords' => [],
            ];

            try {
                $emails->sendWeeklySummary($business, $summaryData);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
