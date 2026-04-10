<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MeasurementController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\VisitController;

Route::post('/login', [AuthController::class, 'login']);

Route::get('/devices', [DeviceController::class, 'index']);
Route::get('/visits', [VisitController::class, 'index']);
Route::get('/visits/{id}', [VisitController::class, 'show']);
Route::post('/measurements', [MeasurementController::class, 'store']); // Moved out for prototype

// Dashboard & History
Route::get('/dashboard/summary', [\App\Http\Controllers\Api\DashboardController::class, 'summary']);
Route::get('/measurements', [\App\Http\Controllers\Api\DashboardController::class, 'measurements']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
});
