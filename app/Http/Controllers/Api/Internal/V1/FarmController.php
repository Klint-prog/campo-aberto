<?php

namespace App\Http\Controllers\Api\Internal\V1;

use App\Http\Controllers\Controller;
use App\Models\Farm;
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

    public function show(Request $request, Farm $farm): JsonResponse
    {
        abort_unless($farm->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($farm), 403);

        $farm->loadCount(['fields', 'plots', 'pastures', 'mapFeatures']);

        return response()->json([
            'success' => true,
            'data' => $farm,
        ]);
    }
}
