<?php

namespace App\Http\Controllers\Api\Internal\V1;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Services\GeoJsonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FarmGeoJsonController extends Controller
{
    public function __invoke(Request $request, Farm $farm, GeoJsonService $geoJsonService): JsonResponse
    {
        abort_unless($farm->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($farm), 403);

        return response()->json($geoJsonService->farmFeatureCollection($farm));
    }
}
