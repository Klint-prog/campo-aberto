<?php

namespace App\Http\Controllers\Api\Internal\V1;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\InventoryItem;
use App\Models\StockMovement;
use App\Services\Inventory\StockMovementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request, Farm $farm): JsonResponse
    {
        abort_unless($farm->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($farm), 403);

        $movements = StockMovement::query()
            ->with('item')
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('farm_id', $farm->id)
            ->latest()
            ->paginate((int) $request->integer('per_page', 20));

        return response()->json(['success' => true, 'data' => $movements]);
    }

    public function store(Request $request, Farm $farm, StockMovementService $service): JsonResponse
    {
        abort_unless($farm->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($farm), 403);

        $data = $request->validate([
            'inventory_item_id' => ['required', 'uuid'],
            'direction' => ['required', 'in:in,out'],
            'reason' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'moved_on' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
        ]);

        $item = InventoryItem::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('farm_id', $farm->id)
            ->findOrFail($data['inventory_item_id']);

        $movement = $service->move($item, $data + ['created_by' => $request->user()->id]);

        return response()->json(['success' => true, 'data' => $movement->load('item')], 201);
    }
}
