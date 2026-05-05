<?php

namespace App\Http\Controllers\Api\Internal\V1;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\AnimalReproductionRecord;
use App\Models\Farm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnimalReproductionRecordController extends Controller
{
    public function store(Request $request, Farm $farm, Animal $animal): JsonResponse
    {
        abort_unless($animal->tenant_id === $request->user()->tenant_id && $animal->farm_id === $farm->id && $request->user()->canAccessFarm($farm), 404);

        $data = $request->validate([
            'male_animal_id' => ['nullable', 'uuid'],
            'type' => ['required', 'in:breeding,insemination,pregnancy_check,birth'],
            'event_on' => ['required', 'date'],
            'expected_birth_on' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:100'],
            'pregnancy_confirmed' => ['nullable', 'boolean'],
            'pregnancy_checked_on' => ['nullable', 'date'],
            'offspring_animal_id' => ['nullable', 'uuid'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);

        $record = AnimalReproductionRecord::create($data + [
            'tenant_id' => $animal->tenant_id,
            'farm_id' => $animal->farm_id,
            'female_animal_id' => $animal->id,
            'created_by' => $request->user()->id,
        ]);

        return response()->json(['success' => true, 'data' => $record], 201);
    }
}
