<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('guest')->group(function() {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/transactions', [TransactionController::class, 'index']);

    Route::post('/transactions', [TransactionController::class, 'store']);

    Route::get('/transactions/{id}', [TransactionController::class, 'show']);

    Route::put('/transactions/{id}', [TransactionController::class, 'update']);

    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);

    Route::post('/logout', [AuthController::class, 'logout']);
});