<?php

namespace App\Http\Controllers\Internal\V1;

use App\Http\Controllers\Controller;
use App\Services\Phase08\AccessScope;
use App\Services\Phase08\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AccessScope $accessScope,
        private readonly DashboardService $dashboards,
    ) {
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'dashboards' => $this->dashboards->available(),
        ]);
    }

    public function show(Request $request, string $dashboard): JsonResponse
    {
        $scope = $this->accessScope->fromRequest($request);
        $this->accessScope->assertPermission($scope, 'dashboards.view');

        return response()->json([
            'dashboard' => $dashboard,
            'metrics' => $this->dashboards->metrics($dashboard, $scope),
        ]);
    }
}
