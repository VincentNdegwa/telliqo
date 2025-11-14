<?php

use App\Http\Controllers\Api\ReviewRequestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('review-requests')->group(function () {
    Route::get('/', [ReviewRequestController::class, 'index'])
        ->middleware('api.key:review-requests.read');
    
    Route::post('/', [ReviewRequestController::class, 'store'])
        ->middleware('api.key:review-requests.create');
    
    Route::get('/{reviewRequest}', [ReviewRequestController::class, 'show'])
        ->middleware('api.key:review-requests.read');
    
    Route::put('/{reviewRequest}', [ReviewRequestController::class, 'update'])
        ->middleware('api.key:review-requests.update');
    
    Route::delete('/{reviewRequest}', [ReviewRequestController::class, 'destroy'])
        ->middleware('api.key:review-requests.delete');
    
    Route::post('/{reviewRequest}/send', [ReviewRequestController::class, 'send'])
        ->middleware('api.key:review-requests.create')
        ->name('api.review-requests.send');
});
