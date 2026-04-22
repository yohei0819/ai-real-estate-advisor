<?php

use App\Http\Controllers\AdminReportsController;
use App\Http\Controllers\AdminStatsController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\InvestmentReportController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::prefix('api/v1')->group(function (): void {
    Route::post('/investment/report', InvestmentReportController::class);

    Route::prefix('admin')->group(function (): void {
        Route::get('/stats',   AdminStatsController::class);
        Route::get('/reports', AdminReportsController::class);
    });
});
