<?php

namespace App\Services\Financial;

use App\Models\FinancialCategory;
use App\Models\FinancialTransaction;

class FinancialTransactionService
{
    public function createFromEvent(string $type, string $categoryName, array $data): FinancialTransaction
    {
        if (! empty($data['source_event_id'])) {
            $existing = FinancialTransaction::where('source_event_id', $data['source_event_id'])->first();
            if ($existing) {
                return $existing;
            }
        }

        $category = FinancialCategory::firstOrCreate([
            'tenant_id' => $data['tenant_id'],
            'type' => $type,
            'name' => $categoryName,
        ], ['is_active' => true]);

        return FinancialTransaction::create($data + [
            'type' => $type,
            'financial_category_id' => $category->id,
            'status' => $data['paid_on'] ?? false ? 'paid' : 'pending',
        ]);
    }
}
