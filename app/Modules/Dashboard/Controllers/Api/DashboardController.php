<?php

namespace App\Modules\Dashboard\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $service) {}

    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->service->metricsFor($request->user()),
        ]);
    }
}
