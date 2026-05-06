<?php

namespace App\Http\Requests;

use App\Http\Controllers\OperationalCrudWebController;
use Illuminate\Foundation\Http\FormRequest;

class OperationalCrudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->tenant_id !== null;
    }

    public function rules(): array
    {
        $controller = $this->route()?->getController();

        if ($controller instanceof OperationalCrudWebController) {
            return $controller->validationRules($this->isMethod('put') || $this->isMethod('patch'));
        }

        return [];
    }

    public function attributes(): array
    {
        $controller = $this->route()?->getController();

        if ($controller instanceof OperationalCrudWebController) {
            return $controller->validationAttributes();
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'string' => 'O campo :attribute deve ser texto.',
            'max' => 'O campo :attribute deve ter no máximo :max caracteres.',
            'numeric' => 'O campo :attribute deve ser numérico.',
            'integer' => 'O campo :attribute deve ser um número inteiro.',
            'min' => 'O campo :attribute deve ser maior ou igual a :min.',
            'date' => 'O campo :attribute deve ser uma data válida.',
            'uuid' => 'O campo :attribute deve ser um UUID válido.',
            'exists' => 'O registro informado em :attribute não foi encontrado.',
            'boolean' => 'O campo :attribute deve ser verdadeiro ou falso.',
            'in' => 'O campo :attribute possui valor inválido.',
        ];
    }
}
