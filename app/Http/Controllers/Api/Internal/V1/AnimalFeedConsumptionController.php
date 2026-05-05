<?php

namespace App\Http\Controllers\Api\Internal\V1;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\AnimalFeedConsumption;
use App\Models\AnimalLot;
use App\Models\Farm;
use App\Models\FeedStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnimalFeedConsumptionController extends Controller
{
    public function store(Request $request, Farm $farm, Animal $animal): JsonResponse
    {
        abort_unless($animal->tenant_id === $request->user()->tenant_id && $animal->farm_id === $farm->id && $request->user()->canAccessFarm($farm), 404);
        $record = $this->createConsumption($request, $farm, $animal, null);
        return response()->json(['success' => true, 'data' => $record], 201);
    }

    public function storeForLot(Request $request, Farm $farm, AnimalLot $animalLot): JsonResponse
    {
        abort_unless($animalLot->tenant_id === $request->user()->tenant_id && $animalLot->farm_id === $farm->id && $request->user()->canAccessFarm($farm), 404);
        $record = $this->createConsumption($request, $farm, null, $animalLot);
        return response()->json(['success' => true, 'data' => $record], 201);
    }

    private function createConsumption(Request $request, Farm $farm, ?Animal $animal, ?AnimalLot $animalLot): AnimalFeedConsumption
    {
        $data = $request->validate([
            'feed_stock_id' => ['nullable', 'uuid'],
            'consumed_on' => ['required', 'date'],
            'feed_name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'unit' => ['required', 'string', 'max:50'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);

        if (! empty($data['feed_stock_id'])) {
            FeedStock::where('tenant_id', $request->user()->tenant_id)->where('farm_id', $farm->id)->findOrFail($data['feed_stock_id'])->decrement('current_quantity', $data['quantity']);
        }

        $totalCost = isset($data['unit_cost']) ? (float) $data['unit_cost'] * (float) $data['quantity'] : null;

        return AnimalFeedConsumption::create($data + [
            'tenant_id' => $request->user()->tenant_id,
            'farm_id' => $farm->id,
            'animal_id' => $animal?->id,
            'animal_lot_id' => $animalLot?->id ?? $animal?->animal_lot_id,
            'total_cost' => $totalCost,
            'created_by' => $request->user()->id,
        ]);
    }
}
