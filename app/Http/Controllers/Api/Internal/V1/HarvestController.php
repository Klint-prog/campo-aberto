<?php

namespace App\Http\Controllers\Api\Internal\V1;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\Harvest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HarvestController extends Controller
{
    public function index(Request $request, Farm $farm): JsonResponse
    {
        abort_unless($farm->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($farm), 403);

        $harvests = Harvest::query()
            ->with(['plot', 'season', 'crop', 'cropVariety', 'activity'])
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('farm_id', $farm->id)
            ->latest('harvested_on')
            ->paginate((int) $request->integer('per_page', 20));

        return response()->json(['success' => true, 'data' => $harvests]);
    }

    public function store(Request $request, Farm $farm): JsonResponse
    {
        abort_unless($farm->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($farm), 403);

        $data = $request->validate([
            'plot_id' => ['nullable', 'uuid', Rule::exists('plots', 'id')->where('tenant_id', $request->user()->tenant_id)],
            'season_id' => ['nullable', 'uuid', Rule::exists('seasons', 'id')->where('tenant_id', $request->user()->tenant_id)],
            'crop_id' => ['required', 'uuid', Rule::exists('crops', 'id')->where('tenant_id', $request->user()->tenant_id)],
            'crop_variety_id' => ['nullable', 'uuid', Rule::exists('crop_varieties', 'id')->where('tenant_id', $request->user()->tenant_id)],
            'activity_id' => ['nullable', 'uuid', Rule::exists('activities', 'id')->where('tenant_id', $request->user()->tenant_id)],
            'harvested_on' => ['required', 'date'],
            'harvested_area_ha' => ['required', 'numeric', 'min:0.0001'],
            'total_weight_kg' => ['required', 'numeric', 'min:0'],
            'quality_grade' => ['nullable', 'string', 'max:120'],
            'metadata' => ['nullable', 'array'],
        ]);

        $harvest = Harvest::create($data + [
            'tenant_id' => $request->user()->tenant_id,
            'farm_id' => $farm->id,
            'created_by' => $request->user()->id,
        ]);

        return response()->json(['success' => true, 'data' => $harvest->load(['plot', 'season', 'crop', 'cropVariety', 'activity'])], 201);
    }
}
