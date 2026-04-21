<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardCountsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardCountsService $dashboardCountsService)
    {
    }

    public function counts(Request $request): JsonResponse
    {
        $this->authorize('view-dashboard');

        return response()->json([
            'data' => $this->dashboardCountsService->build($request->user()),
        ]);
    }
}
