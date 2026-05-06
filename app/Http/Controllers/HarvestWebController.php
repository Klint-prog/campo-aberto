<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsAgricultureWebOptions;
use App\Http\Requests\StoreHarvestWebRequest;
use App\Models\Harvest;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HarvestWebController extends Controller
{
    use BuildsAgricultureWebOptions;

    public function index(Request $request): View
    {
        $harvests = $this->harvestQuery($request)
            ->with(['farm', 'plot', 'season', 'crop', 'cropVariety', 'activity'])
            ->orderByDesc('harvested_on')
            ->paginate(15)
            ->withQueryString();

        return view('harvests.index', $this->viewData($request) + [
            'harvests' => $harvests,
            'filters' => $request->only(['farm_id', 'season_id', 'crop_id', 'start_on', 'end_on']),
        ]);
    }

    public function create(Request $request): View
    {
        return view('harvests.create', $this->viewData($request) + ['harvest' => new Harvest]);
    }

    public function store(StoreHarvestWebRequest $request): RedirectResponse
    {
        $harvest = Harvest::create($request->validated() + [
            'tenant_id' => $request->user()->tenant_id,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('harvests.show', $harvest)->with('status', 'Colheita registrada com sucesso.');
    }

    public function show(Request $request, Harvest $harvest): View
    {
        $this->authorizeHarvest($request, $harvest);

        return view('harvests.show', $this->viewData($request) + [
            'harvest' => $harvest->load(['farm', 'plot', 'season', 'crop', 'cropVariety', 'activity']),
        ]);
    }

    public function edit(Request $request, Harvest $harvest): View
    {
        $this->authorizeHarvest($request, $harvest);

        return view('harvests.edit', $this->viewData($request) + ['harvest' => $harvest]);
    }

    public function update(StoreHarvestWebRequest $request, Harvest $harvest): RedirectResponse
    {
        $this->authorizeHarvest($request, $harvest);
        $harvest->fill($request->validated())->save();

        return redirect()->route('harvests.show', $harvest)->with('status', 'Colheita atualizada com sucesso.');
    }

    public function export(Request $request): StreamedResponse
    {
        $fileName = 'colheitas-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($request): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Data', 'Fazenda', 'Safra', 'Cultura', 'Talhão', 'Área colhida ha', 'Peso total kg', 'Produtividade kg/ha', 'Qualidade']);

            $this->harvestQuery($request)->with(['farm', 'season', 'crop', 'plot'])->chunk(200, function ($harvests) use ($handle): void {
                foreach ($harvests as $harvest) {
                    fputcsv($handle, [
                        optional($harvest->harvested_on)->format('Y-m-d'),
                        $harvest->farm?->name,
                        $harvest->season?->name,
                        $harvest->crop?->name,
                        $harvest->plot?->name,
                        $harvest->harvested_area_ha,
                        $harvest->total_weight_kg,
                        $harvest->productivity_kg_ha,
                        $harvest->quality_grade,
                    ]);
                }
            });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    protected function harvestQuery(Request $request): Builder
    {
        return Harvest::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->when(! $request->user()->hasRole('admin'), fn (Builder $query) => $query->whereIn('farm_id', $request->user()->farms()->pluck('farms.id')))
            ->when($request->filled('farm_id'), fn (Builder $query) => $query->where('farm_id', $request->input('farm_id')))
            ->when($request->filled('season_id'), fn (Builder $query) => $query->where('season_id', $request->input('season_id')))
            ->when($request->filled('crop_id'), fn (Builder $query) => $query->where('crop_id', $request->input('crop_id')))
            ->when($request->filled('start_on'), fn (Builder $query) => $query->whereDate('harvested_on', '>=', $request->input('start_on')))
            ->when($request->filled('end_on'), fn (Builder $query) => $query->whereDate('harvested_on', '<=', $request->input('end_on')));
    }

    protected function authorizeHarvest(Request $request, Harvest $harvest): void
    {
        abort_unless($harvest->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($harvest->farm_id), Response::HTTP_FORBIDDEN);
    }

    protected function viewData(Request $request): array
    {
        return [
            'farms' => $this->farmOptions($request),
            'plots' => $this->plotOptions($request),
            'seasons' => $this->seasonOptions($request),
            'crops' => $this->cropOptions($request),
            'cropVarieties' => $this->cropVarietyOptions($request),
            'activities' => $this->activityOptions($request),
        ];
    }
}
