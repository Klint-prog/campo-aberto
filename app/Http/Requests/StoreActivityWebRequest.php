<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreActivityWebRequest extends FormRequest
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
            'crop_id' => ['nullable', 'uuid', Rule::exists('crops', 'id')->where('tenant_id', $tenantId)],
            'crop_variety_id' => ['nullable', 'uuid', Rule::exists('crop_varieties', 'id')->where('tenant_id', $tenantId)],
            'type' => ['required', 'string', Rule::in(['soil_preparation', 'planting', 'fertilization', 'spraying', 'irrigation', 'pest_control', 'harvest', 'transport', 'technical_visit', 'other'])],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'planned_start_on' => ['nullable', 'date'],
            'planned_end_on' => ['nullable', 'date', 'after_or_equal:planned_start_on'],
            'planned_area_ha' => ['nullable', 'numeric', 'min:0'],
            'estimated_productivity_kg_ha' => ['nullable', 'numeric', 'min:0'],
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
            'planned_start_on' => 'início planejado',
            'planned_end_on' => 'fim planejado',
            'planned_area_ha' => 'área planejada',
            'estimated_productivity_kg_ha' => 'produtividade estimada',
        ];
    }
}
