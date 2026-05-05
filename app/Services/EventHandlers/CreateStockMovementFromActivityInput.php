<?php

namespace App\Services\EventHandlers;

use App\Models\DomainEvent;
use App\Models\InventoryItem;
use App\Models\StockMovement;
use App\Services\Inventory\StockMovementService;

class CreateStockMovementFromActivityInput
{
    public function __construct(private readonly StockMovementService $stockMovementService) {}

    public function handle(DomainEvent $event): ?StockMovement
    {
        if ($event->event_name !== 'input.consumed_by_activity') {
            return null;
        }

        $payload = $event->payload ?? [];
        $item = $this->resolveItem($event, $payload['input_reference'] ?? null, $payload['input_name'] ?? null, $payload['unit'] ?? 'unit');

        return $this->stockMovementService->move($item, [
            'source_event_id' => $event->id,
            'direction' => StockMovement::DIRECTION_OUT,
            'reason' => 'activity_input',
            'quantity' => $payload['quantity'] ?? 0,
            'unit_cost' => $payload['unit_cost'] ?? null,
            'total_cost' => $payload['total_cost'] ?? null,
            'moved_on' => $event->occurred_at?->toDateString() ?? now()->toDateString(),
            'movable_type' => $event->aggregate_type,
            'movable_id' => $event->aggregate_id,
            'metadata' => ['event_name' => $event->event_name, 'payload' => $payload],
        ]);
    }

    private function resolveItem(DomainEvent $event, ?string $sku, ?string $name, string $unit): InventoryItem
    {
        $query = InventoryItem::where('tenant_id', $event->tenant_id)->where('farm_id', $event->farm_id);
        if ($sku) {
            $found = (clone $query)->where('sku', $sku)->first();
            if ($found) {
                return $found;
            }
        }

        return InventoryItem::firstOrCreate([
            'tenant_id' => $event->tenant_id,
            'farm_id' => $event->farm_id,
            'name' => $name ?: 'Insumo agrícola',
        ], ['type' => 'input', 'sku' => $sku, 'unit' => $unit, 'current_quantity' => 0]);
    }
}
