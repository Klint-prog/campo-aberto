<?php

namespace App\Http\Controllers;

use App\Http\Requests\OperationalCrudRequest;
use App\Models\Farm;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class OperationalCrudWebController extends Controller
{
    abstract protected function module(): string;

    protected function config(): array
    {
        return static::modules()[$this->module()];
    }

    public function index(Request $request): View
    {
        $query = $this->scopedQuery($request)
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $term = '%'.$request->string('q')->trim()->toString().'%';
                $query->where(function (Builder $query) use ($term): void {
                    foreach ($this->searchableColumns() as $column) {
                        $query->orWhere($column, 'ilike', $term);
                    }
                });
            });

        foreach (['farm_id', 'type', 'status'] as $filter) {
            $query->when($request->filled($filter) && $this->hasColumn($filter), fn (Builder $query) => $query->where($filter, $request->input($filter)));
        }

        if ($request->filled('active') && $this->hasColumn('is_active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        return view('master-data.index', [
            'module' => $this->module(),
            'config' => $this->config(),
            'columns' => $this->existingColumns($this->config()['columns']),
            'records' => $query->orderBy($this->hasColumn('name') ? 'name' : 'created_at')->paginate(15)->withQueryString(),
            'farms' => $this->farmOptions($request),
            'filters' => $request->only(['q', 'farm_id', 'type', 'status', 'active']),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizeAccess($request);

        return view('master-data.create', [
            'module' => $this->module(),
            'config' => $this->config(),
            'fields' => $this->formFields($request),
            'record' => null,
        ]);
    }

    public function store(OperationalCrudRequest $request): RedirectResponse
    {
        $record = new ($this->config()['model'])($this->payload($request));
        $record->save();

        return redirect()->route($this->module().'.show', $record)->with('status', $this->config()['singular'].' cadastrado com sucesso.');
    }

    public function show(Request $request, string $id): View
    {
        return view('master-data.show', [
            'module' => $this->module(),
            'config' => $this->config(),
            'columns' => $this->existingColumns($this->config()['columns']),
            'record' => $this->findScoped($request, $id),
        ]);
    }

    public function edit(Request $request, string $id): View
    {
        return view('master-data.edit', [
            'module' => $this->module(),
            'config' => $this->config(),
            'fields' => $this->formFields($request),
            'record' => $this->findScoped($request, $id),
        ]);
    }

    public function update(OperationalCrudRequest $request, string $id): RedirectResponse
    {
        $record = $this->findScoped($request, $id);
        $record->fill($this->payload($request));
        $record->save();

        return redirect()->route($this->module().'.show', $record)->with('status', $this->config()['singular'].' atualizado com sucesso.');
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        $record = $this->findScoped($request, $id);
        $record->delete();

        return redirect()->route($this->module().'.index')->with('status', $this->config()['singular'].' removido com sucesso.');
    }

    public function export(Request $request): StreamedResponse
    {
        $columns = $this->existingColumns($this->config()['columns']);
        $fileName = str_replace('.', '-', $this->module()).'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($columns, $request): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, array_values($columns));

            $this->scopedQuery($request)->chunk(200, function ($records) use ($handle, $columns): void {
                foreach ($records as $record) {
                    fputcsv($handle, collect(array_keys($columns))->map(fn (string $column) => $this->formatValue($record, $column))->all());
                }
            });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function validationRules(bool $updating = false): array
    {
        return $this->config()['rules'];
    }

    public function validationAttributes(): array
    {
        return collect($this->config()['fields'])->mapWithKeys(fn (array $field, string $name) => [$name => mb_strtolower($field['label'])])->all();
    }

    protected function scopedQuery(Request $request): Builder
    {
        $this->authorizeAccess($request);
        $modelClass = $this->config()['model'];
        $query = $modelClass::query();

        if ($this->hasColumn('tenant_id')) {
            $query->where('tenant_id', $request->user()->tenant_id);
        }

        if ($this->hasColumn('farm_id') && ! $request->user()->hasRole('admin')) {
            $query->whereIn('farm_id', $request->user()->farms()->pluck('farms.id'));
        }

        foreach (['farm', 'item', 'machine', 'account', 'category'] as $relation) {
            if (method_exists($modelClass, $relation)) {
                $query->with($relation);
            }
        }

        return $query;
    }

    protected function findScoped(Request $request, string $id): Model
    {
        return $this->scopedQuery($request)->whereKey($id)->firstOrFail();
    }

    protected function payload(OperationalCrudRequest $request): array
    {
        $payload = collect($request->validated())->filter(fn ($value): bool => $value !== '')->all();

        if ($this->hasColumn('tenant_id')) {
            $payload['tenant_id'] = $request->user()->tenant_id;
        }

        if ($this->hasColumn('is_active')) {
            $payload['is_active'] = $request->boolean('is_active', true);
        }

        if ($this->hasColumn('created_by') && $request->isMethod('post')) {
            $payload['created_by'] = $request->user()->id;
        }

        if (isset($payload['farm_id'])) {
            abort_unless($request->user()->canAccessFarm($payload['farm_id']), Response::HTTP_FORBIDDEN);
        }

        return collect($payload)->filter(fn ($value, string $column): bool => $this->hasColumn($column))->all();
    }

    protected function authorizeAccess(Request $request): void
    {
        abort_unless($request->user()?->tenant_id, Response::HTTP_FORBIDDEN);
    }

    protected function formFields(Request $request): array
    {
        $fields = $this->config()['fields'];

        foreach ($fields as &$field) {
            if (($field['type'] ?? null) === 'farm') {
                $field['options'] = $this->farmOptions($request);
            }
        }

        return $fields;
    }

    protected function farmOptions(Request $request): array
    {
        $query = Farm::query()->where('tenant_id', $request->user()->tenant_id);

        if (! $request->user()->hasRole('admin')) {
            $query->whereIn('id', $request->user()->farms()->pluck('farms.id'));
        }

        return $query->orderBy('name')->pluck('name', 'id')->all();
    }

    protected function existingColumns(array $columns): array
    {
        return collect($columns)->filter(fn (string $label, string $column): bool => $this->hasColumn($column))->all();
    }

    protected function searchableColumns(): array
    {
        return collect(array_keys($this->config()['columns']))->filter(fn (string $column): bool => in_array($column, ['name', 'code', 'sku', 'supplier', 'description', 'status', 'type'], true) && $this->hasColumn($column))->values()->all();
    }

    protected function hasColumn(string $column): bool
    {
        return Schema::hasColumn((new ($this->config()['model']))->getTable(), $column);
    }

    protected function formatValue(Model $record, string $column): string
    {
        $value = data_get($record, $column);

        if ($column === 'farm_id') {
            $value = $record->farm?->name ?? $value;
        }

        if (is_bool($value)) {
            return $value ? 'Sim' : 'Não';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return (string) ($value ?? '');
    }

    public static function modules(): array
    {
        return [
            'inventory' => [
                'title' => 'Estoque',
                'singular' => 'Item de estoque',
                'model' => \App\Models\InventoryItem::class,
                'columns' => ['name' => 'Nome', 'sku' => 'SKU', 'farm_id' => 'Fazenda', 'type' => 'Tipo', 'unit' => 'Unidade', 'current_quantity' => 'Saldo', 'minimum_quantity' => 'Mínimo', 'is_active' => 'Ativo'],
                'fields' => [
                    'farm_id' => ['label' => 'Fazenda', 'type' => 'farm'],
                    'name' => ['label' => 'Nome', 'type' => 'text'],
                    'sku' => ['label' => 'SKU', 'type' => 'text'],
                    'type' => ['label' => 'Tipo', 'type' => 'select', 'options' => ['input' => 'Insumo', 'feed' => 'Ração', 'fuel' => 'Combustível', 'medicine' => 'Medicamento', 'part' => 'Peça', 'other' => 'Outro']],
                    'unit' => ['label' => 'Unidade', 'type' => 'text'],
                    'current_quantity' => ['label' => 'Saldo atual', 'type' => 'number', 'step' => '0.0001'],
                    'minimum_quantity' => ['label' => 'Estoque mínimo', 'type' => 'number', 'step' => '0.0001'],
                    'unit_cost' => ['label' => 'Custo unitário', 'type' => 'number', 'step' => '0.0001'],
                    'supplier' => ['label' => 'Fornecedor', 'type' => 'text'],
                    'batch_number' => ['label' => 'Lote', 'type' => 'text'],
                    'expires_on' => ['label' => 'Validade', 'type' => 'date'],
                    'is_active' => ['label' => 'Ativo', 'type' => 'checkbox'],
                ],
                'rules' => ['farm_id' => ['required', 'uuid', 'exists:farms,id'], 'name' => ['required', 'string', 'max:160'], 'sku' => ['nullable', 'string', 'max:80'], 'type' => ['required', 'string', 'max:60'], 'unit' => ['required', 'string', 'max:30'], 'current_quantity' => ['nullable', 'numeric', 'min:0'], 'minimum_quantity' => ['nullable', 'numeric', 'min:0'], 'unit_cost' => ['nullable', 'numeric', 'min:0'], 'supplier' => ['nullable', 'string', 'max:160'], 'batch_number' => ['nullable', 'string', 'max:80'], 'expires_on' => ['nullable', 'date'], 'is_active' => ['nullable', 'boolean']],
            ],
            'machines' => [
                'title' => 'Máquinas e equipamentos',
                'singular' => 'Máquina',
                'model' => \App\Models\Machine::class,
                'columns' => ['name' => 'Nome', 'code' => 'Código', 'farm_id' => 'Fazenda', 'type' => 'Tipo', 'brand' => 'Marca', 'model' => 'Modelo', 'operational_status' => 'Status'],
                'fields' => [
                    'farm_id' => ['label' => 'Fazenda', 'type' => 'farm'],
                    'name' => ['label' => 'Nome', 'type' => 'text'],
                    'code' => ['label' => 'Código', 'type' => 'text'],
                    'type' => ['label' => 'Tipo', 'type' => 'text'],
                    'brand' => ['label' => 'Marca', 'type' => 'text'],
                    'model' => ['label' => 'Modelo', 'type' => 'text'],
                    'manufacture_year' => ['label' => 'Ano', 'type' => 'number', 'step' => '1'],
                    'hour_meter' => ['label' => 'Horímetro', 'type' => 'number', 'step' => '0.01'],
                    'odometer_km' => ['label' => 'Odômetro km', 'type' => 'number', 'step' => '0.01'],
                    'fuel_type' => ['label' => 'Combustível', 'type' => 'text'],
                    'operational_status' => ['label' => 'Status', 'type' => 'select', 'options' => ['available' => 'Disponível', 'in_use' => 'Em uso', 'maintenance' => 'Em manutenção', 'inactive' => 'Inativa']],
                ],
                'rules' => ['farm_id' => ['required', 'uuid', 'exists:farms,id'], 'name' => ['required', 'string', 'max:160'], 'code' => ['nullable', 'string', 'max:80'], 'type' => ['required', 'string', 'max:80'], 'brand' => ['nullable', 'string', 'max:120'], 'model' => ['nullable', 'string', 'max:120'], 'manufacture_year' => ['nullable', 'integer', 'min:1900'], 'hour_meter' => ['nullable', 'numeric', 'min:0'], 'odometer_km' => ['nullable', 'numeric', 'min:0'], 'fuel_type' => ['nullable', 'string', 'max:60'], 'operational_status' => ['required', 'string', 'max:60']],
            ],
            'finance.accounts' => [
                'title' => 'Contas financeiras',
                'singular' => 'Conta financeira',
                'model' => \App\Models\FinancialAccount::class,
                'columns' => ['name' => 'Nome', 'farm_id' => 'Fazenda', 'type' => 'Tipo', 'opening_balance' => 'Saldo inicial', 'is_active' => 'Ativa'],
                'fields' => ['farm_id' => ['label' => 'Fazenda', 'type' => 'farm'], 'name' => ['label' => 'Nome', 'type' => 'text'], 'type' => ['label' => 'Tipo', 'type' => 'select', 'options' => ['cash' => 'Caixa', 'bank' => 'Banco', 'credit' => 'Crédito', 'other' => 'Outra']], 'opening_balance' => ['label' => 'Saldo inicial', 'type' => 'number', 'step' => '0.01'], 'is_active' => ['label' => 'Ativa', 'type' => 'checkbox']],
                'rules' => ['farm_id' => ['nullable', 'uuid', 'exists:farms,id'], 'name' => ['required', 'string', 'max:160'], 'type' => ['required', 'string', 'max:60'], 'opening_balance' => ['nullable', 'numeric'], 'is_active' => ['nullable', 'boolean']],
            ],
            'finance.categories' => [
                'title' => 'Categorias financeiras',
                'singular' => 'Categoria financeira',
                'model' => \App\Models\FinancialCategory::class,
                'columns' => ['name' => 'Nome', 'type' => 'Tipo', 'is_active' => 'Ativa'],
                'fields' => ['name' => ['label' => 'Nome', 'type' => 'text'], 'type' => ['label' => 'Tipo', 'type' => 'select', 'options' => ['revenue' => 'Receita', 'expense' => 'Despesa']], 'is_active' => ['label' => 'Ativa', 'type' => 'checkbox']],
                'rules' => ['name' => ['required', 'string', 'max:160'], 'type' => ['required', 'string', 'in:revenue,expense'], 'is_active' => ['nullable', 'boolean']],
            ],
        ];
    }
}
