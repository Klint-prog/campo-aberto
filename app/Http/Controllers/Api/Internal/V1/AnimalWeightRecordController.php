<?php

namespace App\Http\Controllers\Api\Internal\V1;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\AnimalWeightRecord;
use App\Models\Farm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnimalWeightRecordController extends Controller
{
    public function store(Request $request, Farm $farm, Animal $animal): JsonResponse
    {
        abort_unless($animal->tenant_id === $request->user()->tenant_id && $animal->farm_id === $farm->id && $request->user()->canAccessFarm($farm), 404);

        $data = $request->validate([
            'weighed_on' => ['required', 'date'],
            'weight_kg' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);

        $record = AnimalWeightRecord::create($data + [
            'tenant_id' => $animal->tenant_id,
            'farm_id' => $animal->farm_id,
            'animal_id' => $animal->id,
            'created_by' => $request->user()->id,
        ]);

        return response()->json(['success' => true, 'data' => $record], 201);
    }
}
