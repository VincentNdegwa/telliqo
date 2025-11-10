<?php

namespace App\Jobs;

use App\Models\Business;
use App\Services\EmailNotificationService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendMonthlyReports implements ShouldQueue
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
        $now = Carbon::now();

        $start = $now->copy()->startOfMonth();
        $end = $now->copy()->endOfMonth();

        $businesses = Business::query()->get();

        foreach ($businesses as $business) {
            $settings = $business->getSetting('notification_settings', []);

            if (!($settings['email_notifications_enabled'] ?? false)) {
                continue;
            }

            if (!($settings['monthly_report'] ?? false)) {
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

            // Calculate best day if we have feedback
            $bestDay = 'N/A';
            $bestDayCount = 0;
            if ($total > 0) {
                $dayGroups = $feedbacks->groupBy(fn($f) => Carbon::parse($f->submitted_at)->format('l'));
                $bestDayData = $dayGroups->sortByDesc(fn($group) => $group->count())->first();
                if ($bestDayData) {
                    $bestDay = Carbon::parse($bestDayData->first()->submitted_at)->format('l');
                    $bestDayCount = $bestDayData->count();
                }
            }

            // Calculate peak hour if we have feedback
            $peakHour = 'N/A';
            if ($total > 0) {
                $hourGroups = $feedbacks->groupBy(fn($f) => Carbon::parse($f->submitted_at)->format('H'));
                $peakHourData = $hourGroups->sortByDesc(fn($group) => $group->count())->first();
                if ($peakHourData) {
                    $peakHour = (int) Carbon::parse($peakHourData->first()->submitted_at)->format('H');
                }
            }

            $reportData = [
                'month_name' => $start->format('F'),
                'year' => $start->format('Y'),
                'total_count' => $total,
                'average_rating' => $average,
                'rating_breakdown' => $ratingBreakdown,
                'response_rate' => $responseRate,
                'growth_percentage' => 0,
                'best_day' => $bestDay,
                'best_day_count' => $bestDayCount,
                'peak_hour' => $peakHour,
                'sentiment_breakdown' => [],
                'top_keywords' => [],
                'improvement_areas' => [],
            ];

            try {
                $emails->sendMonthlyReport($business, $reportData);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
