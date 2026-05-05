<?php

namespace App\Http\Controllers\Api\Internal\V1;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\AnimalHealthRecord;
use App\Models\Farm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnimalHealthRecordController extends Controller
{
    public function store(Request $request, Farm $farm, Animal $animal): JsonResponse
    {
        abort_unless($animal->tenant_id === $request->user()->tenant_id && $animal->farm_id === $farm->id && $request->user()->canAccessFarm($farm), 404);

        $data = $request->validate([
            'type' => ['required', 'in:treatment,disease,vermifuge,clinical_exam,other'],
            'recorded_on' => ['required', 'date'],
            'diagnosis' => ['nullable', 'string'],
            'medicine_name' => ['nullable', 'string'],
            'medicine_reference' => ['nullable', 'string'],
            'dosage' => ['nullable', 'numeric', 'min:0'],
            'dosage_unit' => ['nullable', 'string'],
            'quantity_consumed' => ['nullable', 'numeric', 'min:0'],
            'quantity_unit' => ['nullable', 'string'],
            'withdrawal_period' => ['nullable', 'string'],
            'responsible' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);

        $record = AnimalHealthRecord::create($data + [
            'tenant_id' => $animal->tenant_id,
            'farm_id' => $animal->farm_id,
            'animal_id' => $animal->id,
            'animal_lot_id' => $animal->animal_lot_id,
            'created_by' => $request->user()->id,
        ]);

        return response()->json(['success' => true, 'data' => $record], 201);
    }
}
