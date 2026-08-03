<?php

use App\Modules\Dashboard\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', DashboardController::class)
    ->middleware('auth:sanctum')
    ->name('dashboard');
