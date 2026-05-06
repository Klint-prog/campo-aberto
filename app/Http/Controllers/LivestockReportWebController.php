<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\AnimalFeedConsumption;
use App\Models\AnimalHealthRecord;
use App\Models\AnimalVaccinationRecord;
use App\Models\AnimalWeightRecord;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LivestockReportWebController extends Controller
{
    public function __invoke(Request $request): View
    {
        $animalBase = $this->tenantScoped(Animal::query(), $request);

        return view('livestock.reports.index', [
            'statusTotals' => (clone $animalBase)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'speciesTotals' => (clone $animalBase)->selectRaw('species, count(*) as total')->groupBy('species')->pluck('total', 'species'),
            'weightsCount' => $this->tenantScoped(AnimalWeightRecord::query(), $request)->count(),
            'vaccinationsApplied' => $this->tenantScoped(AnimalVaccinationRecord::query(), $request)->whereDate('vaccinated_on', '<=', now())->count(),
            'vaccinationsPending' => $this->tenantScoped(AnimalVaccinationRecord::query(), $request)->whereDate('next_due_on', '>=', now())->count(),
            'healthCount' => $this->tenantScoped(AnimalHealthRecord::query(), $request)->count(),
            'feedTotal' => $this->tenantScoped(AnimalFeedConsumption::query(), $request)->sum('quantity'),
            'feedCost' => $this->tenantScoped(AnimalFeedConsumption::query(), $request)->sum('total_cost'),
        ]);
    }

    public function exportWeights(Request $request): StreamedResponse
    {
        return response()->streamDownload(function () use ($request): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Animal', 'Brinco', 'Fazenda', 'Data', 'Peso kg', 'GMD kg', 'Observações']);
            $this->tenantScoped(AnimalWeightRecord::query(), $request)->with(['animal', 'farm'])->chunk(200, function ($records) use ($handle): void {
                foreach ($records as $record) {
                    fputcsv($handle, [$record->animal?->internal_code, $record->animal?->ear_tag, $record->farm?->name, optional($record->weighed_on)->format('Y-m-d'), $record->weight_kg, $record->average_daily_gain_kg, $record->notes]);
                }
            });
            fclose($handle);
        }, 'pesagens-animais-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportHealth(Request $request): StreamedResponse
    {
        return response()->streamDownload(function () use ($request): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tipo', 'Animal', 'Lote', 'Fazenda', 'Data', 'Diagnóstico', 'Medicamento', 'Quantidade', 'Responsável']);
            $this->tenantScoped(AnimalHealthRecord::query(), $request)->with(['animal', 'lot', 'farm'])->chunk(200, function ($records) use ($handle): void {
                foreach ($records as $record) {
                    fputcsv($handle, [$record->type, $record->animal?->internal_code, $record->lot?->name, $record->farm?->name, optional($record->recorded_on)->format('Y-m-d'), $record->diagnosis, $record->medicine_name, trim(($record->quantity_consumed ?? '').' '.($record->quantity_unit ?? '')), $record->responsible]);
                }
            });
            fclose($handle);
        }, 'sanidade-animais-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    protected function tenantScoped(Builder $query, Request $request): Builder
    {
        return $query
            ->where('tenant_id', $request->user()->tenant_id)
            ->when(! $request->user()->hasRole('admin'), fn (Builder $builder) => $builder->whereIn('farm_id', $request->user()->farms()->pluck('farms.id')));
    }
}
