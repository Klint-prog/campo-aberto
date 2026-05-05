<?php

namespace App\Http\Controllers\Internal\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlertController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'integer'],
            'farm_id' => ['nullable', 'integer'],
            'type' => ['required', 'string', 'max:80'],
            'severity' => ['required', 'string', 'max:20'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'context' => ['nullable', 'array'],
            'due_at' => ['nullable', 'date'],
        ]);

        $alertId = DB::table('internal_alerts')->insertGetId([
            'tenant_id' => $validated['tenant_id'],
            'farm_id' => $validated['farm_id'] ?? null,
            'type' => $validated['type'],
            'severity' => $validated['severity'],
            'title' => $validated['title'],
            'message' => $validated['message'],
            'context' => isset($validated['context']) ? json_encode($validated['context']) : null,
            'due_at' => $validated['due_at'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('notifications')->insert([
            'tenant_id' => $validated['tenant_id'],
            'farm_id' => $validated['farm_id'] ?? null,
            'internal_alert_id' => $alertId,
            'channel' => 'internal',
            'status' => 'pending',
            'title' => $validated['title'],
            'message' => $validated['message'],
            'payload' => json_encode(['alert_id' => $alertId, 'type' => $validated['type']]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'data' => DB::table('internal_alerts')->find($alertId),
        ], 201);
    }

    public function notifications(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'integer'],
            'farm_id' => ['nullable', 'integer'],
        ]);

        $items = DB::table('notifications')
            ->where('tenant_id', $validated['tenant_id'])
            ->when(array_key_exists('farm_id', $validated), fn ($query) => $query->where('farm_id', $validated['farm_id']))
            ->orderByDesc('id')
            ->paginate(15);

        return response()->json([
            'data' => $items,
        ]);
    }
}
