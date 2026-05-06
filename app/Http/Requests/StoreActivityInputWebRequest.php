<?php

namespace App\Http\Requests;

use App\Models\Activity;
use Illuminate\Foundation\Http\FormRequest;

class StoreActivityInputWebRequest extends FormRequest
{
    public function authorize(): bool
    {
        $activity = $this->route('activity');

        return $this->user()?->tenant_id !== null
            && $activity instanceof Activity
            && $activity->tenant_id === $this->user()->tenant_id
            && $this->user()->canAccessFarm($activity->farm_id);
    }

    public function rules(): array
    {
        return [
            'input_name' => ['required', 'string', 'max:255'],
            'input_type' => ['nullable', 'string', 'max:120'],
            'input_reference' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'unit' => ['required', 'string', 'max:40'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input_name' => 'insumo',
            'input_type' => 'tipo de insumo',
            'input_reference' => 'referência',
            'unit_cost' => 'custo unitário',
        ];
    }
}
