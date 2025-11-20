<?php

use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\BusinessSettingsController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PublicBusinessController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\ReviewRequestsController;
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

Route::get('/r/{token}', [ReviewRequestsController::class, 'publicShow'])->name('review-request.show');
Route::post('/r/{token}', [ReviewRequestsController::class, 'submitReview'])->name('review-request.submit');
Route::get('/r/{token}/opt-out', [ReviewRequestsController::class, 'optOut'])->name('review-request.opt-out');

Route::middleware(['auth', 'verified', 'redirect.if.super.admin'])->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
});

Route::middleware(['auth', 'verified', 'business.onboarded'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/addons/request', [BillingController::class, 'requestAddon'])->name('billing.addons.request');
    Route::post('/billing/subscriptions/local', [BillingController::class, 'createLocalSubscription'])->name('billing.subscriptions.local.store');
    Route::post('/billing/subscriptions/local/{subscription}/cancel', [BillingController::class, 'cancelLocalSubscription'])->name('billing.subscriptions.local.cancel');
    Route::post('/billing/subscriptions/paddle/start', [BillingController::class, 'startPaddleSubscription'])->name('billing.subscriptions.paddle.start');
    Route::post('/billing/subscriptions/paypal/start', [BillingController::class, 'startPaypalSubscription'])->name('billing.subscriptions.paypal.start');

    Route::resource('customers', CustomersController::class);
    
    Route::resource('review-requests', ReviewRequestsController::class)->except(['edit', 'update']);
    Route::post('/review-requests/{reviewRequest}/remind', [ReviewRequestsController::class, 'sendReminder'])->name('review-requests.remind');

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
    
    // API Keys routes
    Route::get('/settings/api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
    Route::post('/settings/api-keys', [ApiKeyController::class, 'store'])->name('api-keys.store');
    Route::put('/settings/api-keys/{apiKey}', [ApiKeyController::class, 'update'])->name('api-keys.update');
    Route::post('/settings/api-keys/{apiKey}/revoke', [ApiKeyController::class, 'revoke'])->name('api-keys.revoke');
    Route::delete('/settings/api-keys/{apiKey}', [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');
    
    // Team Management routes
    Route::get('/team/users', [\App\Http\Controllers\TeamController::class, 'index'])->name('team.users.index');
    Route::post('/team/users', [\App\Http\Controllers\TeamController::class, 'store'])->name('team.users.store');
    Route::put('/team/users/{user}', [\App\Http\Controllers\TeamController::class, 'update'])->name('team.users.update');
    Route::delete('/team/users/{user}', [\App\Http\Controllers\TeamController::class, 'destroy'])->name('team.users.destroy');
    
    // Role Management routes
    Route::get('/team/roles', [\App\Http\Controllers\RoleController::class, 'index'])->name('team.roles.index');
    Route::get('/team/roles/create', [\App\Http\Controllers\RoleController::class, 'create'])->name('team.roles.create');
    Route::post('/team/roles', [\App\Http\Controllers\RoleController::class, 'store'])->name('team.roles.store');
    Route::get('/team/roles/{role}/edit', [\App\Http\Controllers\RoleController::class, 'edit'])->name('team.roles.edit');
    Route::put('/team/roles/{role}', [\App\Http\Controllers\RoleController::class, 'update'])->name('team.roles.update');
    Route::delete('/team/roles/{role}', [\App\Http\Controllers\RoleController::class, 'destroy'])->name('team.roles.destroy');
});

require __DIR__.'/settings.php';
