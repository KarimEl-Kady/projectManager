<?php

use App\Modules\Project\Controllers\Api\ProjectController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->apiResource('projects', ProjectController::class);
