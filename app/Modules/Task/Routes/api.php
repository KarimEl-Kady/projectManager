<?php

use App\Modules\Task\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->apiResource('projects.tasks', TaskController::class);
