<?php

namespace App\Http\Controllers\Api\Internal\V1;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\AnimalVaccinationRecord;
use App\Models\Farm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnimalVaccinationRecordController extends Controller
{
    public function store(Request $request, Farm $farm, Animal $animal): JsonResponse
    {
        abort_unless($animal->tenant_id === $request->user()->tenant_id && $animal->farm_id === $farm->id && $request->user()->canAccessFarm($farm), 404);

        $data = $request->validate([
            'vaccinated_on' => ['required', 'date'],
            'vaccine_name' => ['required', 'string', 'max:255'],
            'vaccine_reference' => ['nullable', 'string', 'max:255'],
            'batch_number' => ['nullable', 'string', 'max:255'],
            'dose' => ['nullable', 'numeric', 'min:0'],
            'dose_unit' => ['nullable', 'string', 'max:50'],
            'next_due_on' => ['nullable', 'date'],
            'responsible' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);

        $record = AnimalVaccinationRecord::create($data + [
            'tenant_id' => $animal->tenant_id,
            'farm_id' => $animal->farm_id,
            'animal_id' => $animal->id,
            'animal_lot_id' => $animal->animal_lot_id,
            'created_by' => $request->user()->id,
        ]);

        return response()->json(['success' => true, 'data' => $record], 201);
    }
}
