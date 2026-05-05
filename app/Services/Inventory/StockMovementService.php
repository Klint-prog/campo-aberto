<?php

namespace App\Services\Inventory;

use App\Models\InventoryItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockMovementService
{
    public function move(InventoryItem $item, array $data): StockMovement
    {
        return DB::transaction(function () use ($item, $data): StockMovement {
            if (! empty($data['source_event_id'])) {
                $existing = StockMovement::where('source_event_id', $data['source_event_id'])->first();
                if ($existing) {
                    return $existing;
                }
            }

            $lockedItem = InventoryItem::whereKey($item->id)->lockForUpdate()->firstOrFail();
            $quantity = (float) $data['quantity'];
            $direction = $data['direction'];

            if ($direction === StockMovement::DIRECTION_OUT && (float) $lockedItem->current_quantity < $quantity) {
                throw ValidationException::withMessages(['quantity' => 'Saldo insuficiente para saída de estoque.']);
            }

            $lockedItem->forceFill([
                'current_quantity' => $direction === StockMovement::DIRECTION_IN
                    ? (float) $lockedItem->current_quantity + $quantity
                    : (float) $lockedItem->current_quantity - $quantity,
            ])->save();

            return StockMovement::create($data + [
                'tenant_id' => $lockedItem->tenant_id,
                'farm_id' => $lockedItem->farm_id,
                'inventory_item_id' => $lockedItem->id,
                'unit' => $lockedItem->unit,
                'unit_cost' => $data['unit_cost'] ?? $lockedItem->unit_cost,
                'total_cost' => $data['total_cost'] ?? (($data['unit_cost'] ?? $lockedItem->unit_cost) !== null ? $quantity * (float) ($data['unit_cost'] ?? $lockedItem->unit_cost) : null),
                'moved_on' => $data['moved_on'] ?? now()->toDateString(),
            ]);
        });
    }
}
