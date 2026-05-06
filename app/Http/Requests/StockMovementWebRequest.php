<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockMovementWebRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route('item');

        return $this->user() !== null
            && $this->user()->tenant_id !== null
            && $item !== null
            && $item->tenant_id === $this->user()->tenant_id
            && $this->user()->canAccessFarm($item->farm_id);
    }

    public function rules(): array
    {
        return [
            'direction' => ['required', 'string', 'in:in,out,adjustment'],
            'reason' => ['required', 'string', 'max:120'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'moved_on' => ['required', 'date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'direction' => 'movimento',
            'reason' => 'motivo',
            'quantity' => 'quantidade',
            'unit_cost' => 'custo unitário',
            'moved_on' => 'data da movimentação',
        ];
    }
}
