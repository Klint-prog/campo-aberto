<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\AnimalFeedConsumption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AnimalFeedConsumptionWebController extends Controller
{
    public function store(Request $request, Animal $animal): RedirectResponse
    {
        $this->authorizeAnimal($request, $animal);

        $data = $request->validate([
            'feed_stock_id' => ['nullable', 'uuid'],
            'consumed_on' => ['required', 'date'],
            'feed_name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $totalCost = isset($data['unit_cost']) ? ((float) $data['quantity'] * (float) $data['unit_cost']) : null;

        AnimalFeedConsumption::create($data + [
            'tenant_id' => $animal->tenant_id,
            'farm_id' => $animal->farm_id,
            'animal_id' => $animal->id,
            'animal_lot_id' => $animal->animal_lot_id,
            'total_cost' => $totalCost,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('animals.show', $animal)->with('status', 'Consumo de alimentação registrado com sucesso.');
    }

    protected function authorizeAnimal(Request $request, Animal $animal): void
    {
        abort_unless($animal->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($animal->farm_id), Response::HTTP_FORBIDDEN);
    }
}
