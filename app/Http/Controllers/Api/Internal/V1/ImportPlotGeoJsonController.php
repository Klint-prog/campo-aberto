<?php

namespace App\Http\Controllers\Api\Internal\V1;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Services\GeoJsonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImportPlotGeoJsonController extends Controller
{
    public function __invoke(Request $request, Farm $farm, GeoJsonService $geoJsonService): JsonResponse
    {
        abort_unless($farm->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($farm), 403);

        $data = $request->validate([
            'geojson' => ['nullable', 'array'],
            'file' => ['nullable', 'file', 'mimes:json,geojson,txt'],
        ]);

        if (! isset($data['geojson']) && ! $request->hasFile('file')) {
            return response()->json([
                'success' => false,
                'message' => 'Envie geojson no corpo da requisição ou um arquivo GeoJSON.',
            ], 422);
        }

        $geojson = $request->hasFile('file')
            ? $geoJsonService->readUploadedGeoJson($request->file('file'))
            : $data['geojson'];

        $count = $geoJsonService->importPlots($farm, $geojson, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'GeoJSON importado com sucesso.',
            'data' => ['imported' => $count],
        ], 201);
    }
}
