<?php

return [
    'routes' => [
        'api' => [
            'prefix' => env('API_PREFIX', 'api'),
        ],
        'dashboard' => [
            'prefix' => env('DASHBOARD_PREFIX', 'dashboard'),
            'middleware' => ['web', 'auth'],
        ],
    ],
];
