<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockMovementWebRequest;
use App\Models\InventoryItem;
use App\Models\StockMovement;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockMovementWebController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->tenant_id, 403);

        $query = StockMovement::query()
            ->with(['farm', 'item'])
            ->where('tenant_id', $request->user()->tenant_id)
            ->when(! $request->user()->hasRole('admin'), fn ($query) => $query->whereIn('farm_id', $request->user()->farms()->pluck('farms.id')))
            ->when($request->filled('farm_id'), fn ($query) => $query->where('farm_id', $request->input('farm_id')))
            ->when($request->filled('direction'), fn ($query) => $query->where('direction', $request->input('direction')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('moved_on', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('moved_on', '<=', $request->input('to')))
            ->latest('moved_on');

        return view('stock-movements.index', [
            'movements' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['farm_id', 'direction', 'from', 'to']),
        ]);
    }

    public function store(StockMovementWebRequest $request, InventoryItem $item): RedirectResponse
    {
        $data = $request->validated();
        $quantity = (float) $data['quantity'];
        $unitCost = isset($data['unit_cost']) ? (float) $data['unit_cost'] : null;

        if ($data['direction'] === 'out' && (float) $item->current_quantity < $quantity) {
            return back()->withErrors(['quantity' => 'Saída bloqueada: saldo insuficiente para este item.'])->withInput();
        }

        DB::transaction(function () use ($item, $data, $quantity, $unitCost, $request): void {
            StockMovement::create([
                'tenant_id' => $item->tenant_id,
                'farm_id' => $item->farm_id,
                'inventory_item_id' => $item->id,
                'direction' => $data['direction'] === 'adjustment' ? 'in' : $data['direction'],
                'reason' => $data['reason'],
                'quantity' => $quantity,
                'unit' => $item->unit,
                'unit_cost' => $unitCost,
                'total_cost' => $unitCost === null ? null : $unitCost * $quantity,
                'moved_on' => $data['moved_on'],
                'metadata' => ['origin' => 'web', 'movement_type' => $data['direction']],
                'created_by' => $request->user()->id,
            ]);

            $item->current_quantity = match ($data['direction']) {
                'out' => (float) $item->current_quantity - $quantity,
                'adjustment' => $quantity,
                default => (float) $item->current_quantity + $quantity,
            };
            $item->save();
        });

        return redirect()->route('inventory.show', $item)->with('status', 'Movimentação registrada com sucesso.');
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->tenant_id, 403);

        $query = StockMovement::query()->with(['farm', 'item'])->where('tenant_id', $request->user()->tenant_id);

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Fazenda', 'Item', 'Direção', 'Motivo', 'Quantidade', 'Unidade', 'Custo total', 'Data']);
            $query->chunk(200, function ($records) use ($handle): void {
                foreach ($records as $record) {
                    fputcsv($handle, [$record->farm?->name, $record->item?->name, $record->direction, $record->reason, $record->quantity, $record->unit, $record->total_cost, optional($record->moved_on)->format('Y-m-d')]);
                }
            });
            fclose($handle);
        }, 'stock-movements-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
