<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsAgricultureWebOptions;
use App\Models\Activity;
use App\Models\ActivityInput;
use App\Models\Harvest;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AgricultureReportWebController extends Controller
{
    use BuildsAgricultureWebOptions;

    public function __invoke(Request $request): View
    {
        $farmIds = $this->authorizedFarmIds($request);

        $activities = Activity::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->whereIn('farm_id', $farmIds)
            ->when($request->filled('farm_id'), fn (Builder $query) => $query->where('farm_id', $request->input('farm_id')))
            ->when($request->filled('season_id'), fn (Builder $query) => $query->where('season_id', $request->input('season_id')))
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->input('status')));

        $harvests = Harvest::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->whereIn('farm_id', $farmIds)
            ->when($request->filled('farm_id'), fn (Builder $query) => $query->where('farm_id', $request->input('farm_id')))
            ->when($request->filled('season_id'), fn (Builder $query) => $query->where('season_id', $request->input('season_id')))
            ->when($request->filled('crop_id'), fn (Builder $query) => $query->where('crop_id', $request->input('crop_id')));

        $inputs = ActivityInput::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->whereIn('farm_id', $farmIds)
            ->with('activity')
            ->latest()
            ->limit(25)
            ->get();

        return view('reports.agriculture', [
            'farms' => $this->farmOptions($request),
            'seasons' => $this->seasonOptions($request),
            'crops' => $this->cropOptions($request),
            'statuses' => $this->activityStatuses(),
            'filters' => $request->only(['farm_id', 'season_id', 'crop_id', 'status']),
            'activityTotals' => [
                'total' => (clone $activities)->count(),
                'planned' => (clone $activities)->where('status', Activity::STATUS_PLANNED)->count(),
                'completed' => (clone $activities)->where('status', Activity::STATUS_COMPLETED)->count(),
                'cancelled' => (clone $activities)->where('status', Activity::STATUS_CANCELLED)->count(),
                'late' => (clone $activities)->where('status', Activity::STATUS_PLANNED)->whereDate('planned_end_on', '<', now()->toDateString())->count(),
            ],
            'inputTotals' => [
                'count' => $inputs->count(),
                'cost' => $inputs->sum(fn (ActivityInput $input): float => (float) $input->total_cost),
            ],
            'harvestTotals' => [
                'count' => (clone $harvests)->count(),
                'area' => (clone $harvests)->sum('harvested_area_ha'),
                'weight' => (clone $harvests)->sum('total_weight_kg'),
            ],
            'recentInputs' => $inputs,
            'recentHarvests' => (clone $harvests)->with(['farm', 'season', 'crop', 'plot'])->latest('harvested_on')->limit(25)->get(),
        ]);
    }

    private function authorizedFarmIds(Request $request): array
    {
        $query = \App\Models\Farm::query()->where('tenant_id', $request->user()->tenant_id);

        if (! $request->user()->hasRole('admin')) {
            $query->whereIn('id', $request->user()->farms()->pluck('farms.id'));
        }

        return $query->pluck('id')->all();
    }
}
