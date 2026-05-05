<?php

namespace App\Http\Controllers\Api\Internal\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FarmController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $farms = $request->user()
            ->farms()
            ->where('farms.tenant_id', $request->user()->tenant_id)
            ->wherePivot('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $farms,
            'meta' => ['count' => $farms->count()],
        ]);
    }
}
