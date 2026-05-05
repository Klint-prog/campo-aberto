<?php

namespace App\Services\EventHandlers;

use App\Models\DomainEvent;
use Illuminate\Support\Collection;

class ProcessDomainEventIntegrations
{
    public function __construct(
        private readonly CreateStockMovementFromActivityInput $activityInputHandler,
        private readonly CreateStockMovementFromAnimalTreatment $animalTreatmentHandler,
        private readonly CreateStockMovementFromFeedConsumption $feedConsumptionHandler,
        private readonly CreateFinancialRevenueFromAnimalSale $animalSaleHandler,
        private readonly CreateFinancialExpenseFromAnimalPurchase $animalPurchaseHandler,
        private readonly CreateFinancialExpenseFromMaintenance $maintenanceHandler,
    ) {}

    public function process(DomainEvent $event): Collection
    {
        $results = collect([
            $this->activityInputHandler->handle($event),
            $this->animalTreatmentHandler->handle($event),
            $this->feedConsumptionHandler->handle($event),
            $this->animalSaleHandler->handle($event),
            $this->animalPurchaseHandler->handle($event),
            $this->maintenanceHandler->handle($event),
        ])->filter()->values();

        if ($results->isNotEmpty()) {
            $event->forceFill(['processed_at' => now()])->save();
        }

        return $results;
    }
}
