<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\AnimalHealthRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AnimalHealthRecordWebController extends Controller
{
    public function store(Request $request, Animal $animal): RedirectResponse
    {
        $this->authorizeAnimal($request, $animal);

        $data = $request->validate([
            'type' => ['required', 'string', 'max:100'],
            'recorded_on' => ['required', 'date'],
            'diagnosis' => ['nullable', 'string', 'max:255'],
            'medicine_name' => ['nullable', 'string', 'max:255'],
            'medicine_reference' => ['nullable', 'string', 'max:255'],
            'dosage' => ['nullable', 'numeric', 'min:0'],
            'dosage_unit' => ['nullable', 'string', 'max:50'],
            'quantity_consumed' => ['nullable', 'numeric', 'min:0'],
            'quantity_unit' => ['nullable', 'string', 'max:50'],
            'withdrawal_period' => ['nullable', 'string', 'max:100'],
            'responsible' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        AnimalHealthRecord::create($data + [
            'tenant_id' => $animal->tenant_id,
            'farm_id' => $animal->farm_id,
            'animal_id' => $animal->id,
            'animal_lot_id' => $animal->animal_lot_id,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('animals.show', $animal)->with('status', 'Tratamento/sanidade registrado com sucesso.');
    }

    protected function authorizeAnimal(Request $request, Animal $animal): void
    {
        abort_unless($animal->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($animal->farm_id), Response::HTTP_FORBIDDEN);
    }
}
