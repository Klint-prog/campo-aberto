<?php

namespace App\Http\Controllers\Internal\V1;

use App\Http\Controllers\Controller;
use App\Models\ReportExport;
use App\Services\Phase08\AccessScope;
use App\Services\Phase08\AuditLogger;
use App\Services\Phase08\ReportCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ReportController extends Controller
{
    public function __construct(
        private readonly AccessScope $accessScope,
        private readonly AuditLogger $auditLogger,
        private readonly ReportCatalog $catalog,
    ) {
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'reports' => $this->catalog->available(),
            'formats' => ReportCatalog::FORMATS,
        ]);
    }

    public function show(Request $request, string $report): JsonResponse
    {
        $scope = $this->accessScope->fromRequest($request);
        $this->accessScope->assertPermission($scope, 'reports.view');

        return response()->json([
            'report' => $report,
            'filters' => [
                'tenant_id' => $scope['tenant_id'],
                'farm_id' => $scope['farm_id'],
            ],
            'data' => $this->catalog->dataset($report, $scope, $request->query()),
        ]);
    }

    public function export(Request $request, string $report): JsonResponse
    {
        $scope = $this->accessScope->fromRequest($request);
        $this->accessScope->assertPermission($scope, 'reports.export');

        $format = strtolower((string) $request->input('format', 'csv'));

        if (! in_array($format, ReportCatalog::FORMATS, true)) {
            throw new BadRequestHttpException('Formato inválido. Use pdf, csv ou excel.');
        }

        $data = $this->catalog->dataset($report, $scope, $request->all());

        $export = ReportExport::create([
            'tenant_id' => $scope['tenant_id'],
            'farm_id' => $scope['farm_id'],
            'user_id' => $scope['user_id'],
            'report_key' => $report,
            'format' => $format,
            'filters' => $request->except(['permissions', 'allowed_farm_ids']),
            'status' => 'generated',
            'file_path' => 'storage/app/reports/'.$scope['tenant_id'].'/'.$report.'.'.$format,
            'row_count' => count($data),
            'generated_at' => now(),
        ]);

        $this->auditLogger->record('report.exported', $scope, [
            'report_key' => $report,
            'format' => $format,
            'report_export_id' => $export->id,
        ], $request, ReportExport::class, $export->id);

        return response()->json([
            'export' => $export,
            'message' => 'Exportação registrada. Geração física de PDF/CSV/Excel pode ser processada por fila em produção.',
        ], 201);
    }
}
