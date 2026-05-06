<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHarvestWebRequest extends FormRequest
{
    public function authorize(): bool
    {
        $farmId = $this->input('farm_id');

        return $this->user()?->tenant_id !== null
            && $farmId
            && $this->user()->canAccessFarm((string) $farmId);
    }

    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'farm_id' => ['required', 'uuid', Rule::exists('farms', 'id')->where('tenant_id', $tenantId)],
            'plot_id' => ['nullable', 'uuid', Rule::exists('plots', 'id')->where('tenant_id', $tenantId)],
            'season_id' => ['nullable', 'uuid', Rule::exists('seasons', 'id')->where('tenant_id', $tenantId)],
            'crop_id' => ['required', 'uuid', Rule::exists('crops', 'id')->where('tenant_id', $tenantId)],
            'crop_variety_id' => ['nullable', 'uuid', Rule::exists('crop_varieties', 'id')->where('tenant_id', $tenantId)],
            'activity_id' => ['nullable', 'uuid', Rule::exists('activities', 'id')->where('tenant_id', $tenantId)],
            'harvested_on' => ['required', 'date'],
            'harvested_area_ha' => ['required', 'numeric', 'min:0.0001'],
            'total_weight_kg' => ['required', 'numeric', 'min:0'],
            'quality_grade' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'farm_id' => 'fazenda',
            'plot_id' => 'talhão',
            'season_id' => 'safra',
            'crop_id' => 'cultura',
            'crop_variety_id' => 'variedade',
            'activity_id' => 'atividade',
            'harvested_on' => 'data da colheita',
            'harvested_area_ha' => 'área colhida',
            'total_weight_kg' => 'peso total',
            'quality_grade' => 'classificação de qualidade',
        ];
    }
}
