<?php

namespace App\Services\EventHandlers;

use App\Models\DomainEvent;
use App\Models\FinancialTransaction;
use App\Services\Financial\FinancialTransactionService;

class CreateFinancialRevenueFromAnimalSale
{
    public function __construct(private readonly FinancialTransactionService $financialTransactionService) {}

    public function handle(DomainEvent $event): ?FinancialTransaction
    {
        if ($event->event_name !== 'animal.sold') {
            return null;
        }

        $payload = $event->payload ?? [];
        $amount = $payload['sale_price'] ?? null;
        if ($amount === null) {
            return null;
        }

        return $this->financialTransactionService->createFromEvent(FinancialTransaction::TYPE_REVENUE, 'Venda de animais', [
            'tenant_id' => $event->tenant_id,
            'farm_id' => $event->farm_id,
            'source_event_id' => $event->id,
            'description' => 'Receita gerada por venda de animal',
            'amount' => $amount,
            'due_on' => $event->occurred_at?->toDateString() ?? now()->toDateString(),
            'paid_on' => $event->occurred_at?->toDateString() ?? now()->toDateString(),
            'transactionable_type' => $event->aggregate_type,
            'transactionable_id' => $event->aggregate_id,
            'animal_lot_id' => $payload['animal_lot_id'] ?? null,
            'metadata' => ['event_name' => $event->event_name, 'payload' => $payload],
        ]);
    }
}
