<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\FuelEntryController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\ReminderController;
use App\Http\Controllers\Api\ReportController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/dashboard/metrics', App\Http\Controllers\Api\DashboardController::class);
    Route::get('/reports', [ReportController::class, 'index']);
    Route::get('/export/{type}', [ReportController::class, 'export']);

    Route::apiResource('vehicles', VehicleController::class);
    Route::apiResource('fuel-entries', FuelEntryController::class);
    Route::apiResource('maintenances', MaintenanceController::class);
    Route::apiResource('expenses', ExpenseController::class);
    Route::apiResource('reminders', ReminderController::class)->except(['show']);

    Route::put('/profile', [App\Http\Controllers\Api\ProfileController::class, 'update']);
    Route::put('/profile/password', [App\Http\Controllers\Api\ProfileController::class, 'updatePassword']);
});
