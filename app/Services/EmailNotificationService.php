<?php

namespace App\Services;

use App\Mail\LowRatingAlert;
use App\Mail\MonthlyReport;
use App\Mail\NewFeedbackNotification;
use App\Mail\WeeklySummary;
use App\Models\Business;
use App\Models\Feedback;
use Illuminate\Support\Facades\Mail;

class EmailNotificationService
{
    protected $featureService;
    protected $business;
    public function __construct(
    ) {
        $this->featureService = new FeatureService();
    }

    public function sendNewFeedbackNotification(Feedback $feedback): void
    {
        $this->business = $feedback->business;
        $settings = $this->business->getSetting('notification_settings', []);

        if (!$this->shouldSendEmail($settings, 'new_feedback_email')) {
            return;
        }

        $recipients = $this->getNotificationRecipients($this->business, $settings);

        foreach ($recipients as $email) {
            Mail::to($email)->send(new NewFeedbackNotification($feedback, $this->business));
        }
    }

    public function sendLowRatingAlert(Feedback $feedback): void
    {
        $this->business = $feedback->business;
        $settings = $this->business->getSetting('notification_settings', []);

        if (!$this->shouldSendEmail($settings, 'low_rating_alert')) {
            return;
        }

        $threshold = $settings['low_rating_threshold'] ?? 2;
        
        if ($feedback->rating > $threshold) {
            return;
        }

        $recipients = $this->getNotificationRecipients($this->business, $settings);

        foreach ($recipients as $email) {
            Mail::to($email)->send(new LowRatingAlert($feedback, $this->business, $threshold));
        }
    }

    public function sendWeeklySummary(Business $business, array $summaryData): void
    {
        $settings = $business->getSetting('notification_settings', []);

        if (!$this->shouldSendEmail($settings, 'weekly_summary')) {
            return;
        }

        $recipients = $this->getNotificationRecipients($business, $settings);

        foreach ($recipients as $email) {
            Mail::to($email)->send(new WeeklySummary($business, $summaryData));
        }
    }

    public function sendMonthlyReport(Business $business, array $reportData): void
    {
        $settings = $business->getSetting('notification_settings', []);

        if (!$this->shouldSendEmail($settings, 'monthly_report')) {
            return;
        }

        $recipients = $this->getNotificationRecipients($business, $settings);

        foreach ($recipients as $email) {
            Mail::to($email)->send(new MonthlyReport($business, $reportData));
        }
    }

    private function shouldSendEmail(array $settings, string $key): bool
    {
        if (!($settings['email_notifications_enabled'] ?? false)) {
            return false;
        }

        if ($key == "weekly_summary" || $key == 'monthly_report') {
          $canSendSummary = $this->featureService->hasFeature($this->business, 'summary_reports');
          if (!$canSendSummary) {
            return false;
          }
        }


        return $settings[$key] ?? false;
    }

    private function getNotificationRecipients(Business $business, array $settings): array
    {
        $recipients = [];

        if ($business->email) {
            $recipients[] = $business->email;
        }

        // if ($settings['team_notifications'] ?? false) {
        //     $teamEmails = $business->users()
        //         ->wherePivot('is_active', true)
        //         ->pluck('email')
        //         ->filter()
        //         ->toArray();
            
        //     $recipients = array_merge($recipients, $teamEmails);
        // }

        return array_unique($recipients);
    }
}
