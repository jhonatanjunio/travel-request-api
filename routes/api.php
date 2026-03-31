<?php

use App\Http\Controllers\API\v1\AuthController;
use App\Http\Controllers\API\v1\TravelRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');
        Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1');

        Route::middleware('auth:api')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
            Route::post('refresh', [AuthController::class, 'refresh']);
        });
    });

    Route::middleware('auth:api')->group(function () {
        Route::prefix('travel-requests')->group(function () {
            Route::get('', [TravelRequestController::class, 'index']);
            Route::post('', [TravelRequestController::class, 'store']);
            Route::get('{travelRequest}', [TravelRequestController::class, 'show']);
            Route::patch('{travelRequest}', [TravelRequestController::class, 'updateStatus']);
            Route::post('{travelRequest}/cancel', [TravelRequestController::class, 'cancel']);

            // Enhanced cancellation flow for approved requests
            Route::post('{travelRequest}/request-cancellation', [TravelRequestController::class, 'requestCancellation']);
            Route::post('{travelRequest}/confirm-cancellation', [TravelRequestController::class, 'confirmCancellation'])
                ->name('travel-requests.confirm-cancellation');
        });

        // Admin cancellation management
        Route::prefix('admin/travel-requests')->group(function () {
            Route::post('{travelRequest}/approve-cancellation', [TravelRequestController::class, 'approveCancellation']);
            Route::post('{travelRequest}/reject-cancellation', [TravelRequestController::class, 'rejectCancellation']);
        });
    });
});
