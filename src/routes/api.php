<?php

use App\Http\Controllers\Api\BalanceController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'create']);
Route::post('/deposit', [TransactionController::class, 'deposit']);
Route::post('/withdraw', [TransactionController::class, 'withdraw']);;
Route::post('/transfer', [TransactionController::class, 'transfer']);
Route::get('/balance/{user_id}', [BalanceController::class, 'show']);
