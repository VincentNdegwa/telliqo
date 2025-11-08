<?php

use App\Http\Controllers\BusinessSettingsController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\QRCodeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('/review/{business:slug}', [FeedbackController::class, 'show'])->name('feedback.submit');
Route::post('/review/{business:slug}', [FeedbackController::class, 'store'])->name('feedback.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
});

Route::middleware(['auth', 'verified', 'business.onboarded'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Feedback management routes
    Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');

    // QR Code routes
    Route::get('/qr-code', [QRCodeController::class, 'index'])->name('qr-code.index');
    Route::get('/qr-code/download', [QRCodeController::class, 'download'])->name('qr-code.download');
    Route::post('/qr-code/regenerate', [QRCodeController::class, 'regenerate'])->name('qr-code.regenerate');

    // Business Settings routes
    Route::get('/business/settings', [BusinessSettingsController::class, 'edit'])->name('business.settings');
    Route::put('/business/settings', [BusinessSettingsController::class, 'update'])->name('business.settings.update');
});

require __DIR__.'/settings.php';
