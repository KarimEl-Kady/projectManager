<?php

return [
    'routes' => [
        'dashboard' => [
            'prefix' => env('DASHBOARD_PREFIX', 'dashboard'),
            'middleware' => ['web', 'auth'],
        ],
    ],
];
