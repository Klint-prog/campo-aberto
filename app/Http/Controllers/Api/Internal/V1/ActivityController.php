<?php

namespace App\Http\Controllers\Api\Internal\V1;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Farm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class ActivityController extends Controller
{
    public function index(Request $request, Farm $farm): JsonResponse
    {
        abort_unless($farm->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($farm), 403);

        $activities = Activity::query()
            ->with(['plot', 'season', 'crop', 'cropVariety', 'inputs'])
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('farm_id', $farm->id)
            ->latest()
            ->paginate((int) $request->integer('per_page', 20));

        return response()->json(['success' => true, 'data' => $activities]);
    }

    public function store(Request $request, Farm $farm): JsonResponse
    {
        abort_unless($farm->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($farm), 403);

        $data = $this->validatedActivity($request);

        $activity = Activity::create($data + [
            'tenant_id' => $request->user()->tenant_id,
            'farm_id' => $farm->id,
            'status' => Activity::STATUS_PLANNED,
            'created_by' => $request->user()->id,
        ]);

        $activity->recordDomainEvent('activity.planned', $activity->eventPayload());

        return response()->json(['success' => true, 'data' => $activity->load(['plot', 'season', 'crop', 'cropVariety'])], 201);
    }

    public function complete(Request $request, Farm $farm, Activity $activity): JsonResponse
    {
        $this->authorizeFarmActivity($request, $farm, $activity);

        $data = $request->validate([
            'actual_area_ha' => ['nullable', 'numeric', 'min:0'],
            'completed_at' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
        ]);

        $activity->fill([
            'actual_area_ha' => $data['actual_area_ha'] ?? $activity->actual_area_ha,
            'metadata' => array_merge($activity->metadata ?? [], $data['metadata'] ?? []),
            'updated_by' => $request->user()->id,
        ])->save();

        $activity->markCompleted(isset($data['completed_at']) ? Carbon::parse($data['completed_at']) : null);

        return response()->json(['success' => true, 'data' => $activity->fresh(['inputs'])]);
    }

    public function cancel(Request $request, Farm $farm, Activity $activity): JsonResponse
    {
        $this->authorizeFarmActivity($request, $farm, $activity);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $activity->cancel($data['reason'] ?? null);

        return response()->json(['success' => true, 'data' => $activity->fresh()]);
    }

    private function validatedActivity(Request $request): array
    {
        return $request->validate([
            'plot_id' => ['nullable', 'uuid', Rule::exists('plots', 'id')->where('tenant_id', $request->user()->tenant_id)],
            'season_id' => ['nullable', 'uuid', Rule::exists('seasons', 'id')->where('tenant_id', $request->user()->tenant_id)],
            'crop_id' => ['nullable', 'uuid', Rule::exists('crops', 'id')->where('tenant_id', $request->user()->tenant_id)],
            'crop_variety_id' => ['nullable', 'uuid', Rule::exists('crop_varieties', 'id')->where('tenant_id', $request->user()->tenant_id)],
            'type' => ['required', 'string', 'in:soil_preparation,planting,fertilization,spraying,irrigation,pest_control,harvest,transport,technical_visit,other'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'planned_start_on' => ['nullable', 'date'],
            'planned_end_on' => ['nullable', 'date', 'after_or_equal:planned_start_on'],
            'planned_area_ha' => ['nullable', 'numeric', 'min:0'],
            'estimated_productivity_kg_ha' => ['nullable', 'numeric', 'min:0'],
            'metadata' => ['nullable', 'array'],
        ]);
    }

    private function authorizeFarmActivity(Request $request, Farm $farm, Activity $activity): void
    {
        abort_unless($farm->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($farm), 403);
        abort_unless($activity->tenant_id === $request->user()->tenant_id && $activity->farm_id === $farm->id, 404);
    }
}
