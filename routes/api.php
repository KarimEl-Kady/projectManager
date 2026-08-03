<?php

use Illuminate\Support\Facades\Route;

// Application API routes are loaded from app/Modules/*/Routes/api.php.

Route::get('/', fn () => response()->json([
    'name' => config('app.name'),
    'documentation_url' => url('/docs'),
    'health_url' => url('/up'),
]));
