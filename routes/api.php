<?php

use App\Http\Controllers\API\v1\AuthController;
use App\Http\Controllers\API\v1\TravelRequestController;
use Illuminate\Support\Facades\Route;

Route::get("/", function () {
    return view('welcome');
});
Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);

        Route::group(['middleware' => ['auth:sanctum']], function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
        });
    });

    Route::middleware(['api.auth'])->group(function () {
        Route::prefix('travel-requests')->group(function () {
            Route::get('', [TravelRequestController::class, 'index']);
            Route::post('', [TravelRequestController::class, 'store']);
            Route::get('{id}', [TravelRequestController::class, 'show']);
            Route::put('{id}', [TravelRequestController::class, 'updateStatus']);
            Route::post('{id}/initiate-cancellation', [TravelRequestController::class, 'initiateCancellation']);
            Route::get('{id}/confirm-cancellation', [TravelRequestController::class, 'confirmCancellation'])->name('travel-requests.confirm-cancellation');
        });

        Route::prefix('admin')->group(function () {
            Route::get('travel-requests/pending-cancellations', [TravelRequestController::class, 'pendingCancellations']);
            Route::get('travel-requests/{id}/cancellation/review', [TravelRequestController::class, 'reviewCancellation'])->name('admin.travel-requests.cancellation.review');
            Route::post('travel-requests/{id}/approve-cancellation', [TravelRequestController::class, 'approveCancellation']);
            Route::post('travel-requests/{id}/reject-cancellation', [TravelRequestController::class, 'rejectCancellation']);        
        });
    });
});
