<?php

use App\Jobs\SendMonthlyReports;
use App\Jobs\SendWeeklySummaries;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::call(function () {
    SendWeeklySummaries::dispatch();
})->weekly();

Schedule::call(function () {
    SendMonthlyReports::dispatch();
})->monthly();
