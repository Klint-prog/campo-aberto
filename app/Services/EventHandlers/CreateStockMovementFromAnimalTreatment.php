<?php

namespace App\Services\EventHandlers;

use App\Models\DomainEvent;
use App\Models\InventoryItem;
use App\Models\StockMovement;
use App\Services\Inventory\StockMovementService;

class CreateStockMovementFromAnimalTreatment
{
    public function __construct(private readonly StockMovementService $stockMovementService) {}

    public function handle(DomainEvent $event): ?StockMovement
    {
        if ($event->event_name !== 'animal.treatment_recorded') {
            return null;
        }

        $payload = $event->payload ?? [];
        if (empty($payload['medicine_reference']) && empty($payload['medicine_name'])) {
            return null;
        }

        $item = $this->resolveItem($event, $payload['medicine_reference'] ?? null, $payload['medicine_name'] ?? 'Medicamento', $payload['quantity_unit'] ?? 'unit');

        return $this->stockMovementService->move($item, [
            'source_event_id' => $event->id,
            'direction' => StockMovement::DIRECTION_OUT,
            'reason' => 'animal_treatment',
            'quantity' => $payload['quantity_consumed'] ?? 0,
            'moved_on' => $event->occurred_at?->toDateString() ?? now()->toDateString(),
            'movable_type' => $event->aggregate_type,
            'movable_id' => $event->aggregate_id,
            'metadata' => ['event_name' => $event->event_name, 'payload' => $payload],
        ]);
    }

    private function resolveItem(DomainEvent $event, ?string $sku, string $name, string $unit): InventoryItem
    {
        $query = InventoryItem::where('tenant_id', $event->tenant_id)->where('farm_id', $event->farm_id);
        if ($sku && ($found = (clone $query)->where('sku', $sku)->first())) {
            return $found;
        }

        return InventoryItem::firstOrCreate([
            'tenant_id' => $event->tenant_id,
            'farm_id' => $event->farm_id,
            'name' => $name,
        ], ['type' => 'medicine', 'sku' => $sku, 'unit' => $unit, 'current_quantity' => 0]);
    }
}
