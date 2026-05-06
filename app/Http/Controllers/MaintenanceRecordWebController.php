<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRecord;
use App\Models\Machine;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MaintenanceRecordWebController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->tenant_id, 403);

        $records = MaintenanceRecord::query()
            ->with(['farm', 'machine'])
            ->where('tenant_id', $request->user()->tenant_id)
            ->when(! $request->user()->hasRole('admin'), fn ($query) => $query->whereIn('farm_id', $request->user()->farms()->pluck('farms.id')))
            ->when($request->filled('farm_id'), fn ($query) => $query->where('farm_id', $request->input('farm_id')))
            ->when($request->filled('machine_id'), fn ($query) => $query->where('machine_id', $request->input('machine_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('scheduled_on', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('scheduled_on', '<=', $request->input('to')))
            ->latest('scheduled_on')
            ->paginate(20)
            ->withQueryString();

        return view('maintenance.index', ['records' => $records, 'filters' => $request->only(['farm_id', 'machine_id', 'status', 'from', 'to'])]);
    }

    public function store(Request $request, Machine $machine): RedirectResponse
    {
        abort_unless($request->user()?->tenant_id === $machine->tenant_id && $request->user()->canAccessFarm($machine->farm_id), 403);

        $data = $request->validate([
            'type' => ['required', 'string', 'max:80'],
            'status' => ['required', 'string', 'max:60'],
            'scheduled_on' => ['nullable', 'date'],
            'performed_on' => ['nullable', 'date'],
            'description' => ['required', 'string', 'max:2000'],
            'hour_meter' => ['nullable', 'numeric', 'min:0'],
            'odometer_km' => ['nullable', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'supplier' => ['nullable', 'string', 'max:160'],
        ]);

        MaintenanceRecord::create($data + [
            'tenant_id' => $machine->tenant_id,
            'farm_id' => $machine->farm_id,
            'machine_id' => $machine->id,
            'created_by' => $request->user()->id,
        ]);

        if ($data['status'] === 'performed') {
            $machine->forceFill(['operational_status' => 'available'])->save();
        }

        return redirect()->route('machines.show', $machine)->with('status', 'Manutenção registrada com sucesso.');
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->tenant_id, 403);
        $query = MaintenanceRecord::query()->with(['farm', 'machine'])->where('tenant_id', $request->user()->tenant_id);

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Fazenda', 'Máquina', 'Tipo', 'Status', 'Agendada', 'Realizada', 'Custo', 'Fornecedor']);
            $query->chunk(200, function ($records) use ($handle): void {
                foreach ($records as $record) {
                    fputcsv($handle, [$record->farm?->name, $record->machine?->name, $record->type, $record->status, optional($record->scheduled_on)->format('Y-m-d'), optional($record->performed_on)->format('Y-m-d'), $record->cost, $record->supplier]);
                }
            });
            fclose($handle);
        }, 'maintenance-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
