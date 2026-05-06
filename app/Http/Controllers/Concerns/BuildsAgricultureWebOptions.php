<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Activity;
use App\Models\Crop;
use App\Models\CropVariety;
use App\Models\Farm;
use App\Models\Plot;
use App\Models\Season;
use Illuminate\Http\Request;

trait BuildsAgricultureWebOptions
{
    protected function farmOptions(Request $request): array
    {
        $query = Farm::query()->where('tenant_id', $request->user()->tenant_id);

        if (! $request->user()->hasRole('admin')) {
            $query->whereIn('id', $request->user()->farms()->pluck('farms.id'));
        }

        return $query->orderBy('name')->pluck('name', 'id')->all();
    }

    protected function plotOptions(Request $request): array
    {
        return Plot::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->when(! $request->user()->hasRole('admin'), fn ($query) => $query->whereIn('farm_id', $request->user()->farms()->pluck('farms.id')))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    protected function seasonOptions(Request $request): array
    {
        return Season::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->when(! $request->user()->hasRole('admin'), fn ($query) => $query->whereIn('farm_id', $request->user()->farms()->pluck('farms.id')))
            ->orderByDesc('starts_on')
            ->pluck('name', 'id')
            ->all();
    }

    protected function cropOptions(Request $request): array
    {
        return Crop::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    protected function cropVarietyOptions(Request $request): array
    {
        return CropVariety::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    protected function activityOptions(Request $request): array
    {
        return Activity::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->when(! $request->user()->hasRole('admin'), fn ($query) => $query->whereIn('farm_id', $request->user()->farms()->pluck('farms.id')))
            ->orderByDesc('planned_start_on')
            ->pluck('title', 'id')
            ->all();
    }

    protected function activityTypes(): array
    {
        return [
            'soil_preparation' => 'Preparo de solo',
            'planting' => 'Plantio',
            'fertilization' => 'Adubação',
            'spraying' => 'Pulverização',
            'irrigation' => 'Irrigação',
            'pest_control' => 'Controle de pragas',
            'harvest' => 'Colheita',
            'transport' => 'Transporte',
            'technical_visit' => 'Visita técnica',
            'other' => 'Outra',
        ];
    }

    protected function activityStatuses(): array
    {
        return [
            Activity::STATUS_PLANNED => 'Planejada',
            Activity::STATUS_COMPLETED => 'Concluída',
            Activity::STATUS_CANCELLED => 'Cancelada',
        ];
    }
}
