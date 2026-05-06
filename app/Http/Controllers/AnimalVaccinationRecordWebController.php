<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\AnimalVaccinationRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AnimalVaccinationRecordWebController extends Controller
{
    public function store(Request $request, Animal $animal): RedirectResponse
    {
        $this->authorizeAnimal($request, $animal);

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
        ]);

        AnimalVaccinationRecord::create($data + [
            'tenant_id' => $animal->tenant_id,
            'farm_id' => $animal->farm_id,
            'animal_id' => $animal->id,
            'animal_lot_id' => $animal->animal_lot_id,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('animals.show', $animal)->with('status', 'Vacinação registrada com sucesso.');
    }

    protected function authorizeAnimal(Request $request, Animal $animal): void
    {
        abort_unless($animal->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($animal->farm_id), Response::HTTP_FORBIDDEN);
    }
}
