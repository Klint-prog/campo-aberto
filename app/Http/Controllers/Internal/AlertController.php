<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\InternalAlert;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tenant_id' => ['required', 'integer', 'min:1'],
            'farm_id' => ['sometimes', 'integer', 'min:1'],
            'type' => ['sometimes', 'string', 'max:80'],
            'unresolved' => ['sometimes', 'boolean'],
        ]);

        $query = InternalAlert::query()
            ->where('tenant_id', $data['tenant_id'])
            ->orderByDesc('created_at');

        if (! empty($data['farm_id'])) {
            $query->where('farm_id', $data['farm_id']);
        }

        if (! empty($data['type'])) {
            $query->where('type', $data['type']);
        }

        if ((bool) ($data['unresolved'] ?? false)) {
            $query->whereNull('resolved_at');
        }

        return response()->json(['data' => $query->paginate(30)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tenant_id' => ['required', 'integer', 'min:1'],
            'farm_id' => ['nullable', 'integer', 'min:1'],
            'type' => ['required', 'string', 'max:80'],
            'severity' => ['required', 'in:info,warning,critical'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:4000'],
            'context' => ['nullable', 'array'],
            'due_at' => ['nullable', 'date'],
        ]);

        $alert = InternalAlert::create($data);

        $notification = Notification::create([
            'tenant_id' => $alert->tenant_id,
            'farm_id' => $alert->farm_id,
            'internal_alert_id' => $alert->id,
            'channel' => 'internal',
            'status' => 'pending',
            'title' => $alert->title,
            'message' => $alert->message,
            'payload' => $alert->context,
        ]);

        return response()->json(['data' => $alert, 'notification' => $notification], 201);
    }

    public function notifications(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tenant_id' => ['required', 'integer', 'min:1'],
            'farm_id' => ['sometimes', 'integer', 'min:1'],
            'status' => ['sometimes', 'string', 'max:30'],
        ]);

        $query = Notification::query()
            ->where('tenant_id', $data['tenant_id'])
            ->orderByDesc('created_at');

        if (! empty($data['farm_id'])) {
            $query->where('farm_id', $data['farm_id']);
        }

        if (! empty($data['status'])) {
            $query->where('status', $data['status']);
        }

        return response()->json(['data' => $query->paginate(30)]);
    }
}
