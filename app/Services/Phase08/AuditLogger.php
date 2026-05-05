<?php

namespace App\Services\Phase08;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuditLogger
{
    public function record(string $event, array $scope, array $metadata = [], ?Request $request = null, ?string $type = null, int|string|null $id = null): AuditLog
    {
        return AuditLog::create([
            'tenant_id' => $scope['tenant_id'] ?? null,
            'farm_id' => $scope['farm_id'] ?? null,
            'user_id' => $scope['user_id'] ?? null,
            'action' => $event,
            'event' => $event,
            'auditable_type' => $type,
            'auditable_id' => $this->uuidOrNull($id),
            'old_values' => [],
            'new_values' => $metadata,
            'metadata' => $metadata,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'created_at' => now(),
        ]);
    }

    private function uuidOrNull(int|string|null $id): ?string
    {
        if ($id === null) {
            return null;
        }

        $value = (string) $id;

        return Str::isUuid($value) ? $value : null;
    }
}
