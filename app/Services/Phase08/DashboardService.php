<?php

namespace App\Services\Phase08;

use App\Models\DashboardSnapshot;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DashboardService
{
    public const DASHBOARDS = [
        'executivo' => 'Executivo',
        'agricola' => 'Agrícola',
        'pecuario' => 'Pecuário',
        'financeiro' => 'Financeiro',
        'operacional' => 'Operacional',
    ];

    public function available(): array
    {
        return collect(self::DASHBOARDS)
            ->map(fn (string $label, string $key): array => ['key' => $key, 'name' => $label])
            ->values()
            ->all();
    }

    public function metrics(string $dashboardKey, array $scope): array
    {
        if (! array_key_exists($dashboardKey, self::DASHBOARDS)) {
            throw new InvalidArgumentException('Dashboard desconhecido.');
        }

        $base = [
            'dashboard' => self::DASHBOARDS[$dashboardKey],
            'tenant_id' => $scope['tenant_id'],
            'farm_id' => $scope['farm_id'] ?? null,
            'reports_generated' => $this->countByScope('report_exports', $scope),
            'ai_consultations' => $this->countByScope('ai_consultations', $scope),
            'critical_ai_recommendations_pending_confirmation' => DB::table('ai_recommendations')
                ->where('tenant_id', $scope['tenant_id'])
                ->when($scope['farm_id'] ?? null, fn ($query, $farmId) => $query->where('farm_id', $farmId))
                ->where('critical_action', true)
                ->where('requires_confirmation', true)
                ->count(),
        ];

        $lastSnapshot = DashboardSnapshot::query()
            ->where('tenant_id', $scope['tenant_id'])
            ->when($scope['farm_id'] ?? null, fn ($query, $farmId) => $query->where('farm_id', $farmId))
            ->where('dashboard_key', $dashboardKey)
            ->latest('calculated_at')
            ->first();

        return [
            ...$base,
            'snapshot' => $lastSnapshot?->metrics ?? [],
            'calculated_at' => now()->toISOString(),
        ];
    }

    private function countByScope(string $table, array $scope): int
    {
        if (! SchemaProbe::tableExists($table)) {
            return 0;
        }

        return DB::table($table)
            ->where('tenant_id', $scope['tenant_id'])
            ->when($scope['farm_id'] ?? null, fn ($query, $farmId) => $query->where('farm_id', $farmId))
            ->count();
    }
}
