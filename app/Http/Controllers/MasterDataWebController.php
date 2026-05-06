<?php

namespace App\Http\Controllers;

use App\Http\Requests\MasterDataRequest;
use App\Models\Farm;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class MasterDataWebController extends Controller
{
    abstract protected function module(): string;

    protected function config(): array
    {
        return static::modules()[$this->module()];
    }

    public function index(Request $request): View
    {
        $this->authorizeModuleAccess($request);

        $query = $this->scopedQuery($request)
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $term = '%'.$request->string('q')->trim()->toString().'%';
                $query->where(function (Builder $query) use ($term): void {
                    foreach ($this->searchableColumns() as $column) {
                        $query->orWhere($column, 'ilike', $term);
                    }
                });
            })
            ->when($request->filled('farm_id') && $this->hasColumn('farm_id'), fn (Builder $query) => $query->where('farm_id', $request->input('farm_id')))
            ->when($request->filled('status') && $this->hasColumn('status'), fn (Builder $query) => $query->where('status', $request->input('status')))
            ->when($request->filled('active') && $this->hasColumn('is_active'), fn (Builder $query) => $query->where('is_active', $request->boolean('active')));

        $orderColumn = $this->hasColumn('name') ? 'name' : 'created_at';

        return view('master-data.index', [
            'module' => $this->module(),
            'config' => $this->config(),
            'columns' => $this->existingColumns($this->config()['columns']),
            'records' => $query->orderBy($orderColumn)->paginate(15)->withQueryString(),
            'farms' => $this->farmOptions($request),
            'filters' => $request->only(['q', 'farm_id', 'status', 'active']),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizeModuleAccess($request);

        return view('master-data.create', [
            'module' => $this->module(),
            'config' => $this->config(),
            'fields' => $this->formFields($request),
            'record' => null,
        ]);
    }

    public function store(MasterDataRequest $request): RedirectResponse
    {
        $this->authorizeModuleAccess($request);

        $payload = $this->payload($request);
        $modelClass = $this->config()['model'];
        $record = new $modelClass($payload);
        $record->save();

        return redirect()
            ->route($this->module().'.show', $record)
            ->with('status', $this->config()['singular'].' cadastrado com sucesso.');
    }

    public function show(Request $request, string $id): View
    {
        $this->authorizeModuleAccess($request);
        $record = $this->findScoped($request, $id);

        return view('master-data.show', [
            'module' => $this->module(),
            'config' => $this->config(),
            'columns' => $this->existingColumns($this->config()['columns']),
            'record' => $record,
        ]);
    }

    public function edit(Request $request, string $id): View
    {
        $this->authorizeModuleAccess($request);
        $record = $this->findScoped($request, $id);

        return view('master-data.edit', [
            'module' => $this->module(),
            'config' => $this->config(),
            'fields' => $this->formFields($request),
            'record' => $record,
        ]);
    }

    public function update(MasterDataRequest $request, string $id): RedirectResponse
    {
        $this->authorizeModuleAccess($request);
        $record = $this->findScoped($request, $id);
        $record->fill($this->payload($request));
        $record->save();

        return redirect()
            ->route($this->module().'.show', $record)
            ->with('status', $this->config()['singular'].' atualizado com sucesso.');
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        $this->authorizeModuleAccess($request);
        $record = $this->findScoped($request, $id);
        $record->delete();

        return redirect()
            ->route($this->module().'.index')
            ->with('status', $this->config()['singular'].' removido com sucesso.');
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorizeModuleAccess($request);

        $columns = $this->existingColumns($this->config()['columns']);
        $fileName = $this->module().'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($columns, $request): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, array_values($columns));

            $this->scopedQuery($request)->orderBy($this->hasColumn('name') ? 'name' : 'created_at')
                ->chunk(200, function ($records) use ($handle, $columns): void {
                    foreach ($records as $record) {
                        fputcsv($handle, collect(array_keys($columns))->map(fn (string $column) => $this->formatValue($record->{$column} ?? null))->all());
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
        return collect($this->config()['fields'])
            ->mapWithKeys(fn (array $field, string $name) => [$name => mb_strtolower($field['label'])])
            ->all();
    }

    protected function scopedQuery(Request $request): Builder
    {
        $modelClass = $this->config()['model'];

        /** @var Builder $query */
        $query = $modelClass::query();

        if ($this->hasColumn('tenant_id')) {
            $query->where('tenant_id', $request->user()->tenant_id);
        }

        if ($this->hasColumn('farm_id') && ! $request->user()->hasRole('admin')) {
            $query->whereIn('farm_id', $request->user()->farms()->pluck('farms.id'));
        }

        foreach (['farm', 'crop'] as $relation) {
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

    protected function payload(MasterDataRequest $request): array
    {
        $payload = collect($request->validated())
            ->filter(fn ($value): bool => $value !== '')
            ->all();

        if ($this->hasColumn('tenant_id')) {
            $payload['tenant_id'] = $request->user()->tenant_id;
        }

        if ($this->hasColumn('is_active') && array_key_exists('is_active', $this->config()['fields'])) {
            $payload['is_active'] = $request->boolean('is_active');
        }

        if ($this->hasColumn('created_by') && $request->isMethod('post')) {
            $payload['created_by'] = $request->user()->id;
        }

        if ($this->hasColumn('updated_by')) {
            $payload['updated_by'] = $request->user()->id;
        }

        if (isset($payload['farm_id'])) {
            abort_unless($request->user()->canAccessFarm($payload['farm_id']), Response::HTTP_FORBIDDEN);
        }

        return collect($payload)
            ->filter(fn ($value, string $column): bool => $this->hasColumn($column))
            ->all();
    }

    protected function authorizeModuleAccess(Request $request): void
    {
        abort_unless($request->user()?->tenant_id, Response::HTTP_FORBIDDEN);

        if ($this->module() === 'farms') {
            $request->user()->can('viewAny', Farm::class) ?: abort(Response::HTTP_FORBIDDEN);
        }
    }

    protected function formFields(Request $request): array
    {
        $fields = $this->config()['fields'];

        foreach ($fields as &$field) {
            if (($field['type'] ?? null) === 'farm') {
                $field['options'] = $this->farmOptions($request);
            }

            if (($field['type'] ?? null) === 'crop') {
                $field['options'] = \App\Models\Crop::query()
                    ->where('tenant_id', $request->user()->tenant_id)
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->all();
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
        return collect($columns)
            ->filter(fn (string $label, string $column): bool => $this->hasColumn($column))
            ->all();
    }

    protected function searchableColumns(): array
    {
        return collect(array_keys($this->config()['columns']))
            ->filter(fn (string $column): bool => in_array($column, ['name', 'code', 'city', 'state', 'scientific_name', 'cultivar_code', 'status'], true) && $this->hasColumn($column))
            ->values()
            ->all();
    }

    protected function hasColumn(string $column): bool
    {
        return Schema::hasColumn((new ($this->config()['model']))->getTable(), $column);
    }

    protected function formatValue(mixed $value): string
    {
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
            'farms' => [
                'title' => 'Fazendas',
                'singular' => 'Fazenda',
                'model' => \App\Models\Farm::class,
                'columns' => ['name' => 'Nome', 'code' => 'Código', 'city' => 'Cidade', 'state' => 'UF', 'total_area_ha' => 'Área ha'],
                'fields' => [
                    'name' => ['label' => 'Nome', 'type' => 'text'],
                    'code' => ['label' => 'Código', 'type' => 'text'],
                    'city' => ['label' => 'Cidade', 'type' => 'text'],
                    'state' => ['label' => 'UF', 'type' => 'text'],
                    'total_area_ha' => ['label' => 'Área total (ha)', 'type' => 'number', 'step' => '0.01'],
                ],
                'rules' => ['name' => ['required', 'string', 'max:160'], 'code' => ['nullable', 'string', 'max:60'], 'city' => ['nullable', 'string', 'max:120'], 'state' => ['nullable', 'string', 'size:2'], 'total_area_ha' => ['nullable', 'numeric', 'min:0']],
            ],
            'fields' => [
                'title' => 'Talhões',
                'singular' => 'Talhão',
                'model' => \App\Models\Field::class,
                'columns' => ['name' => 'Nome', 'code' => 'Código', 'farm_id' => 'Fazenda', 'area_ha' => 'Área ha', 'perimeter_m' => 'Perímetro m'],
                'fields' => [
                    'farm_id' => ['label' => 'Fazenda', 'type' => 'farm'],
                    'name' => ['label' => 'Nome', 'type' => 'text'],
                    'code' => ['label' => 'Código', 'type' => 'text'],
                    'area_ha' => ['label' => 'Área (ha)', 'type' => 'number', 'step' => '0.0001'],
                    'perimeter_m' => ['label' => 'Perímetro (m)', 'type' => 'number', 'step' => '0.01'],
                ],
                'rules' => ['farm_id' => ['required', 'uuid', 'exists:farms,id'], 'name' => ['required', 'string', 'max:160'], 'code' => ['nullable', 'string', 'max:60'], 'area_ha' => ['nullable', 'numeric', 'min:0'], 'perimeter_m' => ['nullable', 'numeric', 'min:0']],
            ],
            'pastures' => [
                'title' => 'Pastagens',
                'singular' => 'Pastagem',
                'model' => \App\Models\Pasture::class,
                'columns' => ['name' => 'Nome', 'code' => 'Código', 'farm_id' => 'Fazenda', 'area_ha' => 'Área ha', 'perimeter_m' => 'Perímetro m'],
                'fields' => [
                    'farm_id' => ['label' => 'Fazenda', 'type' => 'farm'],
                    'name' => ['label' => 'Nome', 'type' => 'text'],
                    'code' => ['label' => 'Código', 'type' => 'text'],
                    'area_ha' => ['label' => 'Área (ha)', 'type' => 'number', 'step' => '0.0001'],
                    'perimeter_m' => ['label' => 'Perímetro (m)', 'type' => 'number', 'step' => '0.01'],
                ],
                'rules' => ['farm_id' => ['required', 'uuid', 'exists:farms,id'], 'name' => ['required', 'string', 'max:160'], 'code' => ['nullable', 'string', 'max:60'], 'area_ha' => ['nullable', 'numeric', 'min:0'], 'perimeter_m' => ['nullable', 'numeric', 'min:0']],
            ],
            'crops' => [
                'title' => 'Culturas',
                'singular' => 'Cultura',
                'model' => \App\Models\Crop::class,
                'columns' => ['name' => 'Nome', 'scientific_name' => 'Nome científico', 'cycle_type' => 'Ciclo', 'is_active' => 'Ativo'],
                'fields' => [
                    'name' => ['label' => 'Nome', 'type' => 'text'],
                    'scientific_name' => ['label' => 'Nome científico', 'type' => 'text'],
                    'cycle_type' => ['label' => 'Ciclo', 'type' => 'select', 'options' => ['annual' => 'Anual', 'perennial' => 'Perene', 'semi_perennial' => 'Semiperene']],
                    'description' => ['label' => 'Descrição', 'type' => 'textarea'],
                    'is_active' => ['label' => 'Ativa', 'type' => 'checkbox'],
                ],
                'rules' => ['name' => ['required', 'string', 'max:160'], 'scientific_name' => ['nullable', 'string', 'max:160'], 'cycle_type' => ['nullable', 'string', 'max:60'], 'description' => ['nullable', 'string', 'max:2000'], 'is_active' => ['nullable', 'boolean']],
            ],
            'crop-varieties' => [
                'title' => 'Variedades',
                'singular' => 'Variedade',
                'model' => \App\Models\CropVariety::class,
                'columns' => ['name' => 'Nome', 'crop_id' => 'Cultura', 'cultivar_code' => 'Cultivar', 'cycle_days' => 'Ciclo dias', 'expected_yield_kg_ha' => 'Produtividade esperada kg/ha', 'is_active' => 'Ativa'],
                'fields' => [
                    'crop_id' => ['label' => 'Cultura', 'type' => 'crop'],
                    'name' => ['label' => 'Nome', 'type' => 'text'],
                    'cultivar_code' => ['label' => 'Código cultivar', 'type' => 'text'],
                    'cycle_days' => ['label' => 'Ciclo em dias', 'type' => 'number', 'step' => '1'],
                    'expected_yield_kg_ha' => ['label' => 'Produtividade esperada (kg/ha)', 'type' => 'number', 'step' => '0.01'],
                    'is_active' => ['label' => 'Ativa', 'type' => 'checkbox'],
                ],
                'rules' => ['crop_id' => ['required', 'uuid', 'exists:crops,id'], 'name' => ['required', 'string', 'max:160'], 'cultivar_code' => ['nullable', 'string', 'max:80'], 'cycle_days' => ['nullable', 'integer', 'min:0'], 'expected_yield_kg_ha' => ['nullable', 'numeric', 'min:0'], 'is_active' => ['nullable', 'boolean']],
            ],
            'seasons' => [
                'title' => 'Safras',
                'singular' => 'Safra',
                'model' => \App\Models\Season::class,
                'columns' => ['name' => 'Nome', 'farm_id' => 'Fazenda', 'starts_on' => 'Início', 'ends_on' => 'Fim', 'status' => 'Status'],
                'fields' => [
                    'farm_id' => ['label' => 'Fazenda', 'type' => 'farm'],
                    'name' => ['label' => 'Nome', 'type' => 'text'],
                    'starts_on' => ['label' => 'Início', 'type' => 'date'],
                    'ends_on' => ['label' => 'Fim', 'type' => 'date'],
                    'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['planned' => 'Planejada', 'active' => 'Ativa', 'closed' => 'Encerrada', 'cancelled' => 'Cancelada']],
                    'notes' => ['label' => 'Observações', 'type' => 'textarea'],
                ],
                'rules' => ['farm_id' => ['required', 'uuid', 'exists:farms,id'], 'name' => ['required', 'string', 'max:160'], 'starts_on' => ['nullable', 'date'], 'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'], 'status' => ['required', 'string', 'max:60'], 'notes' => ['nullable', 'string', 'max:2000']],
            ],
        ];
    }
}
