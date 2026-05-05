<?php

namespace App\Http\Controllers\Api\Internal\V1;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\AnimalMovement;
use App\Models\Farm;
use App\Support\LivestockDomainEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnimalController extends Controller
{
    public function index(Request $request, Farm $farm): JsonResponse
    {
        abort_unless($farm->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($farm), 403);
        $animals = Animal::query()->with('lot')->where('tenant_id', $request->user()->tenant_id)->where('farm_id', $farm->id)->latest()->paginate((int) $request->integer('per_page', 20));
        return response()->json(['success' => true, 'data' => $animals]);
    }

    public function store(Request $request, Farm $farm): JsonResponse
    {
        abort_unless($farm->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($farm), 403);
        $data = $request->validate(['animal_lot_id' => ['nullable', 'uuid'], 'internal_code' => ['required', 'string', 'max:255'], 'ear_tag' => ['nullable', 'string'], 'rfid' => ['nullable', 'string'], 'name' => ['nullable', 'string'], 'species' => ['required', 'string'], 'breed' => ['nullable', 'string'], 'sex' => ['required', 'in:male,female,unknown'], 'birth_date' => ['nullable', 'date'], 'birth_weight_kg' => ['nullable', 'numeric'], 'acquired_on' => ['nullable', 'date'], 'purchase_price' => ['nullable', 'numeric'], 'purchase_document' => ['nullable', 'string'], 'origin' => ['nullable', 'string'], 'metadata' => ['nullable', 'array']]);
        $animal = Animal::create($data + ['tenant_id' => $request->user()->tenant_id, 'farm_id' => $farm->id, 'status' => Animal::STATUS_ACTIVE, 'created_by' => $request->user()->id]);
        if (array_key_exists('purchase_price', $data)) {
            $animal->movements()->create(['tenant_id' => $animal->tenant_id, 'farm_id' => $animal->farm_id, 'to_animal_lot_id' => $animal->animal_lot_id, 'type' => AnimalMovement::TYPE_PURCHASE, 'moved_on' => $animal->acquired_on ?? now()->toDateString(), 'amount' => $animal->purchase_price, 'document' => $animal->purchase_document, 'counterparty' => $animal->origin, 'created_by' => $request->user()->id]);
            $animal->recordDomainEvent(LivestockDomainEvent::ANIMAL_PURCHASED, $animal->eventPayload(['purchase_price' => $animal->purchase_price, 'document' => $animal->purchase_document, 'origin' => $animal->origin]));
        }
        return response()->json(['success' => true, 'data' => $animal->load('lot')], 201);
    }

    public function sell(Request $request, Farm $farm, Animal $animal): JsonResponse
    {
        abort_unless($animal->tenant_id === $request->user()->tenant_id && $animal->farm_id === $farm->id && $request->user()->canAccessFarm($farm), 404);
        $data = $request->validate(['sale_price' => ['required', 'numeric', 'min:0'], 'sale_document' => ['nullable', 'string'], 'counterparty' => ['nullable', 'string']]);
        $animal->sell($data['sale_price'], $data['sale_document'] ?? null, $data['counterparty'] ?? null);
        return response()->json(['success' => true, 'data' => $animal->fresh('movements')]);
    }

    public function die(Request $request, Farm $farm, Animal $animal): JsonResponse
    {
        abort_unless($animal->tenant_id === $request->user()->tenant_id && $animal->farm_id === $farm->id && $request->user()->canAccessFarm($farm), 404);
        $data = $request->validate(['death_cause' => ['nullable', 'string']]);
        $animal->die($data['death_cause'] ?? null);
        return response()->json(['success' => true, 'data' => $animal->fresh()]);
    }
}
