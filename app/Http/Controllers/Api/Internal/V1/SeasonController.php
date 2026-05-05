<?php

namespace App\Http\Controllers\Api\Internal\V1;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\Season;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeasonController extends Controller
{
    public function index(Request $request, Farm $farm): JsonResponse
    {
        abort_unless($farm->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($farm), 403);

        $seasons = Season::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('farm_id', $farm->id)
            ->orderByDesc('starts_on')
            ->get();

        return response()->json(['success' => true, 'data' => $seasons]);
    }

    public function store(Request $request, Farm $farm): JsonResponse
    {
        abort_unless($farm->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($farm), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'status' => ['sometimes', 'string', 'in:planned,active,closed,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        $season = Season::create($data + [
            'tenant_id' => $request->user()->tenant_id,
            'farm_id' => $farm->id,
        ]);

        return response()->json(['success' => true, 'data' => $season], 201);
    }
}
