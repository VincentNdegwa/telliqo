<?php

use App\Http\Controllers\BusinessSettingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PublicBusinessController;
use App\Http\Controllers\QRCodeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('/b/{business:slug}', [PublicBusinessController::class, 'show'])->name('business.public');

Route::get('/review/{business:slug}', [FeedbackController::class, 'show'])->name('feedback.submit');
Route::post('/review/{business:slug}', [FeedbackController::class, 'store'])->name('feedback.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
});

Route::middleware(['auth', 'verified', 'business.onboarded'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Feedback management routes
    Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::post('/feedback/{feedback}/reply', [FeedbackController::class, 'reply'])->name('feedback.reply');
    Route::post('/feedback/{feedback}/flag', [FeedbackController::class, 'flag'])->name('feedback.flag');

    // AI endpoints
    Route::get('/ai/reply-suggestion', [\App\Http\Controllers\AiController::class, 'replySuggestion'])->name('ai.reply-suggestion');

    // QR Code routes
    Route::get('/qr-code', [QRCodeController::class, 'index'])->name('qr-code.index');
    Route::post('/qr-code/preview', [QRCodeController::class, 'preview'])->name('qr-code.preview');
    Route::post('/qr-code/preview-poster', [QRCodeController::class, 'previewPoster'])->name('qr-code.preview-poster');
    Route::match(['get', 'post'], '/qr-code/download', [QRCodeController::class, 'download'])->name('qr-code.download');

    // Business Settings routes
    Route::get('/business/settings', [BusinessSettingsController::class, 'edit'])->name('business.settings');
    Route::post('/business/settings', [BusinessSettingsController::class, 'update'])->name('business.settings.update');
    Route::delete('/business/settings/remove-logo', [BusinessSettingsController::class, 'removeLogo'])->name('business.settings.remove-logo');

    Route::get('/business/settings/notifications', [BusinessSettingsController::class, 'notifications'])->name('business.settings.notifications');
    Route::post('/business/settings/notifications', [BusinessSettingsController::class, 'updateNotifications'])->name('business.settings.notifications.update');
    
    Route::get('/business/settings/display', [BusinessSettingsController::class, 'display'])->name('business.settings.display');
    Route::post('/business/settings/display', [BusinessSettingsController::class, 'updateDisplay'])->name('business.settings.display.update');
    
    Route::get('/business/settings/moderation', [BusinessSettingsController::class, 'moderation'])->name('business.settings.moderation');
    Route::post('/business/settings/moderation', [BusinessSettingsController::class, 'updateModeration'])->name('business.settings.moderation.update');
    
    Route::get('/business/settings/feedback', [BusinessSettingsController::class, 'feedbackSettings'])->name('business.settings.feedback');
    Route::post('/business/settings/feedback', [BusinessSettingsController::class, 'updateFeedbackSettings'])->name('business.settings.feedback.update');
});

require __DIR__.'/settings.php';
