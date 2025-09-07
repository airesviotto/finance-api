<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function() {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['auth:sanctum'])->group(function () {

    //USERS
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::put('/user/change-password', [UserController::class, 'changePassword']);
    Route::delete('/user/delete-account', [UserController::class, 'deleteProfile']);
    Route::post('/user/avatar', [UserController::class, 'updateAvatar']);
    Route::delete('/user/avatar', [UserController::class, 'deleteAvatar']);

    //DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'summary']);
    Route::get('/dashboard/advanced', [DashboardController::class, 'advancedSummary']);

    //NOTIFICATIONS
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::get('/notifications/unread/count', [NotificationController::class, 'unreadCount']);

    //TRANSACTIONS
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    //EXPORT DATA OR FILE
    Route::get('/transactions/export', [TransactionController::class, 'exportFile']);
    Route::get('/transactions/export-data', [TransactionController::class, 'exportData']);
    Route::post('/transactions/generate-report', [TransactionController::class, 'generateReport']);
    
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);
    Route::put('/transactions/{id}', [TransactionController::class, 'update']);
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);

    //CATEGORIES
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    //EXCHANGE API EXTERNAL
    Route::get('/exchange', [ExchangeRateController::class, 'allCurrency']);
    Route::get('/exchange/convert', [ExchangeRateController::class, 'convert']);
    Route::post('/exchange/transactions', [ExchangeRateController::class, 'convertTransactions']);
    Route::post('/exchange/convert-batch', [ExchangeRateController::class, 'convertBatch']);

    //REPORTS
    Route::get('/report/monthly-average', [ReportController::class, 'monthlyAverage']);
    Route::get('/report/category-comparison', [ReportController::class, 'categoryComparison']);
    Route::get('/report/top-expenses', [ReportController::class, 'topExpenses']);

    //LOGOUT
    Route::post('/logout', [AuthController::class, 'logout']);
});
