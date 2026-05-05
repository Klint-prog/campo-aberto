<?php

namespace App\Http\Controllers\Api\Internal\V1;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\Machine;
use App\Models\MaintenanceRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceRecordController extends Controller
{
    public function store(Request $request, Farm $farm, Machine $machine): JsonResponse
    {
        abort_unless($machine->tenant_id === $request->user()->tenant_id && $machine->farm_id === $farm->id && $request->user()->canAccessFarm($farm), 404);

        $data = $request->validate([
            'type' => ['required', 'in:preventive,corrective'],
            'status' => ['nullable', 'string', 'max:100'],
            'scheduled_on' => ['nullable', 'date'],
            'performed_on' => ['nullable', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'hour_meter' => ['nullable', 'numeric', 'min:0'],
            'odometer_km' => ['nullable', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
        ]);

        $record = MaintenanceRecord::create($data + [
            'tenant_id' => $machine->tenant_id,
            'farm_id' => $machine->farm_id,
            'machine_id' => $machine->id,
            'status' => $data['status'] ?? 'performed',
            'created_by' => $request->user()->id,
        ]);

        return response()->json(['success' => true, 'data' => $record], 201);
    }
}
