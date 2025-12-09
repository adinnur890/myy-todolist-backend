<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PremiumController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/profile', [AuthController::class, 'updateProfile']);
    Route::apiResource('todos', TodoController::class);
    
    Route::get('/subtasks', [SubtaskController::class, 'index']);
    Route::post('/subtasks', [SubtaskController::class, 'store']);
    Route::put('/subtasks/{subtask}', [SubtaskController::class, 'update']);
    Route::delete('/subtasks/{subtask}', [SubtaskController::class, 'destroy']);
    Route::post('/subtasks/change-status', [SubtaskController::class, 'changeStatus']);
    
    Route::get('/plans', [PaymentController::class, 'getPlans']);
    Route::post('/orders', [PaymentController::class, 'createOrder']);
    Route::post('/payments', [PaymentController::class, 'createPayment']);
    
    Route::get('/premium/packages', [PremiumController::class, 'getPackages']);
    Route::post('/premium/purchase', [PremiumController::class, 'purchase']);
    Route::post('/vouchers/validate', [PremiumController::class, 'validateVoucher']);
    Route::post('/voucher/validate', [PremiumController::class, 'validateVoucher']);
    Route::post('/vouchers/check', [PremiumController::class, 'validateVoucher']);
    Route::get('/user/subscription', [PremiumController::class, 'getUserSubscription']);
    
    Route::post('/admin/activate-premium', [AdminController::class, 'activatePremium']);
    Route::get('/admin/premium-count', [AdminController::class, 'premiumCount']);
    Route::get('/admin/users', [AdminController::class, 'listUsers']);
});

Route::post('/midtrans/callback', [PaymentController::class, 'midtransCallback']);
