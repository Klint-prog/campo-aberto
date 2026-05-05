<?php

namespace App\Http\Controllers\Api\Internal\V1;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\FinancialTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancialTransactionController extends Controller
{
    public function index(Request $request, Farm $farm): JsonResponse
    {
        abort_unless($farm->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($farm), 403);

        $transactions = FinancialTransaction::query()
            ->with(['category', 'account'])
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('farm_id', $farm->id)
            ->latest()
            ->paginate((int) $request->integer('per_page', 20));

        return response()->json(['success' => true, 'data' => $transactions]);
    }

    public function store(Request $request, Farm $farm): JsonResponse
    {
        abort_unless($farm->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($farm), 403);

        $data = $request->validate([
            'type' => ['required', 'in:revenue,expense'],
            'status' => ['nullable', 'in:paid,pending,overdue,cancelled'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_on' => ['nullable', 'date'],
            'paid_on' => ['nullable', 'date'],
            'financial_account_id' => ['nullable', 'uuid'],
            'financial_category_id' => ['nullable', 'uuid'],
            'season_id' => ['nullable', 'uuid'],
            'plot_id' => ['nullable', 'uuid'],
            'animal_lot_id' => ['nullable', 'uuid'],
            'machine_id' => ['nullable', 'uuid'],
            'activity_id' => ['nullable', 'uuid'],
            'metadata' => ['nullable', 'array'],
        ]);

        $transaction = FinancialTransaction::create($data + [
            'tenant_id' => $request->user()->tenant_id,
            'farm_id' => $farm->id,
            'status' => $data['status'] ?? 'pending',
            'created_by' => $request->user()->id,
        ]);

        return response()->json(['success' => true, 'data' => $transaction], 201);
    }
}
