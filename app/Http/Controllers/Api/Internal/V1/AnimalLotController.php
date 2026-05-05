<?php

namespace App\Http\Controllers\Api\Internal\V1;

use App\Http\Controllers\Controller;
use App\Models\AnimalLot;
use App\Models\Farm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnimalLotController extends Controller
{
    public function index(Request $request, Farm $farm): JsonResponse
    {
        abort_unless($farm->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($farm), 403);
        $lots = AnimalLot::query()->withCount('animals')->where('tenant_id', $request->user()->tenant_id)->where('farm_id', $farm->id)->latest()->paginate((int) $request->integer('per_page', 20));
        return response()->json(['success' => true, 'data' => $lots]);
    }

    public function store(Request $request, Farm $farm): JsonResponse
    {
        abort_unless($farm->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($farm), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('animal_lots')->where('tenant_id', $request->user()->tenant_id)->where('farm_id', $farm->id)],
            'code' => ['nullable', 'string', 'max:255'],
            'pasture_id' => ['nullable', 'uuid'],
            'species' => ['required', 'string', 'max:100'],
            'purpose' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:50'],
            'started_on' => ['nullable', 'date'],
            'closed_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);
        $lot = AnimalLot::create($data + ['tenant_id' => $request->user()->tenant_id, 'farm_id' => $farm->id]);
        return response()->json(['success' => true, 'data' => $lot], 201);
    }
}
