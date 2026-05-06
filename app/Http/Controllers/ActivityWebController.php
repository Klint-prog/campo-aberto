<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsAgricultureWebOptions;
use App\Http\Requests\StoreActivityWebRequest;
use App\Http\Requests\UpdateActivityWebRequest;
use App\Models\Activity;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityWebController extends Controller
{
    use BuildsAgricultureWebOptions;

    public function index(Request $request): View
    {
        $activities = $this->activityQuery($request)
            ->with(['farm', 'plot', 'season', 'crop'])
            ->orderByDesc('planned_start_on')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('activities.index', $this->viewData($request) + [
            'activities' => $activities,
            'filters' => $request->only(['farm_id', 'season_id', 'crop_id', 'status', 'type', 'start_on', 'end_on']),
        ]);
    }

    public function create(Request $request): View
    {
        return view('activities.create', $this->viewData($request) + ['activity' => new Activity]);
    }

    public function store(StoreActivityWebRequest $request): RedirectResponse
    {
        $activity = Activity::create($request->validated() + [
            'tenant_id' => $request->user()->tenant_id,
            'status' => Activity::STATUS_PLANNED,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        $activity->recordDomainEvent('activity.planned', $activity->eventPayload());

        return redirect()->route('activities.show', $activity)->with('status', 'Atividade planejada com sucesso.');
    }

    public function show(Request $request, Activity $activity): View
    {
        $this->authorizeActivity($request, $activity);

        return view('activities.show', $this->viewData($request) + [
            'activity' => $activity->load(['farm', 'plot', 'season', 'crop', 'cropVariety', 'inputs', 'harvests']),
        ]);
    }

    public function edit(Request $request, Activity $activity): View
    {
        $this->authorizeActivity($request, $activity);

        return view('activities.edit', $this->viewData($request) + ['activity' => $activity]);
    }

    public function update(UpdateActivityWebRequest $request, Activity $activity): RedirectResponse
    {
        $this->authorizeActivity($request, $activity);

        $activity->fill($request->validated() + ['updated_by' => $request->user()->id])->save();

        return redirect()->route('activities.show', $activity)->with('status', 'Atividade atualizada com sucesso.');
    }

    public function complete(Request $request, Activity $activity): RedirectResponse
    {
        $this->authorizeActivity($request, $activity);

        $data = $request->validate([
            'actual_area_ha' => ['nullable', 'numeric', 'min:0'],
            'completed_at' => ['nullable', 'date'],
        ]);

        $activity->fill([
            'actual_area_ha' => $data['actual_area_ha'] ?? $activity->actual_area_ha,
            'updated_by' => $request->user()->id,
        ])->save();

        $activity->markCompleted(isset($data['completed_at']) ? Carbon::parse($data['completed_at']) : null);

        return redirect()->route('activities.show', $activity)->with('status', 'Atividade concluída com sucesso.');
    }

    public function cancel(Request $request, Activity $activity): RedirectResponse
    {
        $this->authorizeActivity($request, $activity);

        $data = $request->validate(['reason' => ['nullable', 'string', 'max:1000']]);
        $activity->cancel($data['reason'] ?? null);

        return redirect()->route('activities.show', $activity)->with('status', 'Atividade cancelada com sucesso.');
    }

    public function export(Request $request): StreamedResponse
    {
        $fileName = 'atividades-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($request): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Título', 'Tipo', 'Status', 'Fazenda', 'Safra', 'Cultura', 'Início planejado', 'Fim planejado', 'Área planejada ha', 'Área executada ha']);

            $this->activityQuery($request)->with(['farm', 'season', 'crop'])->chunk(200, function ($activities) use ($handle): void {
                foreach ($activities as $activity) {
                    fputcsv($handle, [
                        $activity->title,
                        $this->activityTypes()[$activity->type] ?? $activity->type,
                        $this->activityStatuses()[$activity->status] ?? $activity->status,
                        $activity->farm?->name,
                        $activity->season?->name,
                        $activity->crop?->name,
                        optional($activity->planned_start_on)->format('Y-m-d'),
                        optional($activity->planned_end_on)->format('Y-m-d'),
                        $activity->planned_area_ha,
                        $activity->actual_area_ha,
                    ]);
                }
            });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    protected function activityQuery(Request $request): Builder
    {
        return Activity::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->when(! $request->user()->hasRole('admin'), fn (Builder $query) => $query->whereIn('farm_id', $request->user()->farms()->pluck('farms.id')))
            ->when($request->filled('farm_id'), fn (Builder $query) => $query->where('farm_id', $request->input('farm_id')))
            ->when($request->filled('season_id'), fn (Builder $query) => $query->where('season_id', $request->input('season_id')))
            ->when($request->filled('crop_id'), fn (Builder $query) => $query->where('crop_id', $request->input('crop_id')))
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->input('status')))
            ->when($request->filled('type'), fn (Builder $query) => $query->where('type', $request->input('type')))
            ->when($request->filled('start_on'), fn (Builder $query) => $query->whereDate('planned_start_on', '>=', $request->input('start_on')))
            ->when($request->filled('end_on'), fn (Builder $query) => $query->whereDate('planned_start_on', '<=', $request->input('end_on')));
    }

    protected function authorizeActivity(Request $request, Activity $activity): void
    {
        abort_unless($activity->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($activity->farm_id), Response::HTTP_FORBIDDEN);
    }

    protected function viewData(Request $request): array
    {
        return [
            'farms' => $this->farmOptions($request),
            'plots' => $this->plotOptions($request),
            'seasons' => $this->seasonOptions($request),
            'crops' => $this->cropOptions($request),
            'cropVarieties' => $this->cropVarietyOptions($request),
            'types' => $this->activityTypes(),
            'statuses' => $this->activityStatuses(),
        ];
    }
}
