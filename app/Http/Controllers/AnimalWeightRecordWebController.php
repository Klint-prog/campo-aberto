<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\AnimalWeightRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AnimalWeightRecordWebController extends Controller
{
    public function store(Request $request, Animal $animal): RedirectResponse
    {
        $this->authorizeAnimal($request, $animal);

        $data = $request->validate([
            'weighed_on' => ['required', 'date'],
            'weight_kg' => ['required', 'numeric', 'min:0'],
            'average_daily_gain_kg' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string'],
        ]);

        AnimalWeightRecord::create($data + [
            'tenant_id' => $animal->tenant_id,
            'farm_id' => $animal->farm_id,
            'animal_id' => $animal->id,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('animals.show', $animal)->with('status', 'Pesagem registrada com sucesso.');
    }

    protected function authorizeAnimal(Request $request, Animal $animal): void
    {
        abort_unless($animal->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($animal->farm_id), Response::HTTP_FORBIDDEN);
    }
}
