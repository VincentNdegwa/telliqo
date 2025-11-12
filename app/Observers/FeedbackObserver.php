<?php

namespace App\Observers;

use App\Jobs\ComputeDailyMetrics;
use App\Models\Feedback;
use App\Services\MetricsService;

class FeedbackObserver
{
    public function __construct(
        protected MetricsService $metricsService
    ) {}

    /**
     * Handle the Feedback "created" event.
     */
    public function created(Feedback $feedback): void
    {
        // Run synchronously (no queue worker needed)
        (new ComputeDailyMetrics($feedback->business_id, now()->format('Y-m-d')))->handle();
        
        $this->metricsService->clearCache($feedback->business_id);
    }

    /**
     * Handle the Feedback "updated" event.
     */
    public function updated(Feedback $feedback): void
    {
        // Run synchronously
        (new ComputeDailyMetrics(
            $feedback->business_id, 
            $feedback->created_at->format('Y-m-d')
        ))->handle();
        
        $this->metricsService->clearCache($feedback->business_id);
    }

    /**
     * Handle the Feedback "deleted" event.
     */
    public function deleted(Feedback $feedback): void
    {
        // Run synchronously
        (new ComputeDailyMetrics(
            $feedback->business_id, 
            $feedback->created_at->format('Y-m-d')
        ))->handle();
        
        $this->metricsService->clearCache($feedback->business_id);
    }
}
