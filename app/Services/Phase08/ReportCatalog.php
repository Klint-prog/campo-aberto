<?php

namespace App\Services\Phase08;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReportCatalog
{
    public const REPORTS = [
        'atividades' => 'Atividades',
        'estoque' => 'Estoque',
        'financeiro' => 'Financeiro',
        'safras' => 'Safras',
        'culturas' => 'Culturas',
        'talhoes' => 'Talhões',
        'maquinas' => 'Máquinas',
        'manutencao' => 'Manutenção',
        'produtividade' => 'Produtividade',
        'pecuaria' => 'Pecuária',
        'sanitario_animal' => 'Sanitário animal',
        'pastagens' => 'Pastagens',
        'climatico' => 'Climático',
        'bi_gerencial' => 'BI gerencial',
        'gerado_por_ia' => 'Gerado por IA',
    ];

    public const FORMATS = ['pdf', 'csv', 'excel'];

    public function available(): array
    {
        return collect(self::REPORTS)
            ->map(fn (string $label, string $key): array => ['key' => $key, 'name' => $label])
            ->values()
            ->all();
    }

    public function dataset(string $reportKey, array $scope, array $filters = []): array
    {
        if (! array_key_exists($reportKey, self::REPORTS)) {
            throw new InvalidArgumentException('Relatório desconhecido.');
        }

        $rows = DB::table('report_exports')
            ->select(['id', 'report_key', 'format', 'row_count', 'generated_at', 'created_at'])
            ->where('tenant_id', $scope['tenant_id'])
            ->when($scope['farm_id'] ?? null, fn ($query, $farmId) => $query->where('farm_id', $farmId))
            ->where('report_key', $reportKey)
            ->latest('id')
            ->limit((int) ($filters['limit'] ?? 50))
            ->get()
            ->map(fn ($row): array => (array) $row)
            ->all();

        if ($rows === []) {
            $rows[] = [
                'report_key' => $reportKey,
                'report_name' => self::REPORTS[$reportKey],
                'tenant_id' => $scope['tenant_id'],
                'farm_id' => $scope['farm_id'] ?? null,
                'message' => 'Sem dados consolidados ainda. Execute operações dos módulos anteriores para popular este relatório.',
            ];
        }

        return $rows;
    }
}
