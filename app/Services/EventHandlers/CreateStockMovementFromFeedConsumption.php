<?php

namespace App\Services\EventHandlers;

use App\Models\DomainEvent;
use App\Models\InventoryItem;
use App\Models\StockMovement;
use App\Services\Inventory\StockMovementService;

class CreateStockMovementFromFeedConsumption
{
    public function __construct(private readonly StockMovementService $stockMovementService) {}

    public function handle(DomainEvent $event): ?StockMovement
    {
        if ($event->event_name !== 'animal.feed_consumed') {
            return null;
        }

        $payload = $event->payload ?? [];
        $item = InventoryItem::firstOrCreate([
            'tenant_id' => $event->tenant_id,
            'farm_id' => $event->farm_id,
            'name' => $payload['feed_name'] ?? 'Alimento animal',
        ], ['type' => 'feed', 'unit' => $payload['unit'] ?? 'kg', 'current_quantity' => 0]);

        return $this->stockMovementService->move($item, [
            'source_event_id' => $event->id,
            'direction' => StockMovement::DIRECTION_OUT,
            'reason' => 'animal_feed_consumption',
            'quantity' => $payload['quantity'] ?? 0,
            'total_cost' => $payload['total_cost'] ?? null,
            'moved_on' => $event->occurred_at?->toDateString() ?? now()->toDateString(),
            'movable_type' => $event->aggregate_type,
            'movable_id' => $event->aggregate_id,
            'metadata' => ['event_name' => $event->event_name, 'payload' => $payload],
        ]);
    }
}
