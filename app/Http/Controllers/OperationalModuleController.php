<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OperationalModuleController extends Controller
{
    private const MODULES = [
        'farms' => ['title' => 'Fazendas', 'table' => 'farms', 'columns' => ['name' => 'Nome', 'code' => 'Código', 'city' => 'Cidade', 'state' => 'UF', 'total_area_ha' => 'Área ha']],
        'fields' => ['title' => 'Talhões', 'table' => 'fields', 'columns' => ['name' => 'Nome', 'code' => 'Código', 'area_ha' => 'Área ha', 'perimeter_m' => 'Perímetro m']],
        'pastures' => ['title' => 'Pastagens', 'table' => 'pastures', 'columns' => ['name' => 'Nome', 'code' => 'Código', 'area_ha' => 'Área ha', 'perimeter_m' => 'Perímetro m']],
        'crops' => ['title' => 'Culturas', 'table' => 'crops', 'columns' => ['name' => 'Nome', 'scientific_name' => 'Nome científico', 'cycle_type' => 'Ciclo', 'is_active' => 'Ativo']],
        'seasons' => ['title' => 'Safras', 'table' => 'seasons', 'columns' => ['name' => 'Nome', 'starts_on' => 'Início', 'ends_on' => 'Fim', 'status' => 'Status']],
        'activities' => ['title' => 'Atividades', 'table' => 'activities', 'columns' => ['title' => 'Título', 'type' => 'Tipo', 'status' => 'Status', 'planned_start_on' => 'Início planejado']],
        'animals' => ['title' => 'Animais', 'table' => 'animals', 'columns' => ['internal_code' => 'Código interno', 'ear_tag' => 'Brinco', 'name' => 'Nome', 'species' => 'Espécie', 'status' => 'Status']],
        'animal-groups' => ['title' => 'Lotes de animais', 'table' => 'animal_lots', 'columns' => ['name' => 'Nome', 'code' => 'Código', 'species' => 'Espécie', 'purpose' => 'Finalidade', 'status' => 'Status']],
        'inventory' => ['title' => 'Estoque', 'table' => 'inventory_items', 'columns' => ['name' => 'Item', 'type' => 'Tipo', 'sku' => 'SKU', 'unit' => 'Unidade', 'current_quantity' => 'Quantidade']],
        'machines' => ['title' => 'Máquinas', 'table' => 'machines', 'columns' => ['name' => 'Nome', 'code' => 'Código', 'type' => 'Tipo', 'brand' => 'Marca', 'operational_status' => 'Status']],
        'finance' => ['title' => 'Financeiro', 'table' => 'financial_transactions', 'columns' => ['description' => 'Descrição', 'type' => 'Tipo', 'status' => 'Status', 'amount' => 'Valor', 'due_on' => 'Vencimento']],
        'reports' => ['title' => 'Relatórios', 'table' => 'reports', 'columns' => ['name' => 'Nome', 'type' => 'Tipo', 'created_at' => 'Criado em']],
    ];

    public function index(Request $request, string $module): View
    {
        $config = self::MODULES[$module] ?? null;

        if (! $config || ! Schema::hasTable($config['table'])) {
            return view('shared.under-construction', [
                'module' => $module,
                'title' => $config['title'] ?? str($module)->replace('-', ' ')->title()->toString(),
            ]);
        }

        $query = DB::table($config['table']);

        if (Schema::hasColumn($config['table'], 'tenant_id')) {
            $query->where('tenant_id', $request->user()->tenant_id);
        }

        if (Schema::hasColumn($config['table'], 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        $orderColumn = Schema::hasColumn($config['table'], 'name') ? 'name' : 'created_at';
        if (Schema::hasColumn($config['table'], $orderColumn)) {
            $query->orderBy($orderColumn);
        }

        return view('modules.index', [
            'module' => $module,
            'title' => $config['title'],
            'table' => $config['table'],
            'columns' => $this->existingColumns($config['table'], $config['columns']),
            'records' => $query->paginate(20),
        ]);
    }

    public function map(Request $request): RedirectResponse|View
    {
        if (! Schema::hasTable('farms')) {
            return view('shared.under-construction', ['module' => 'map', 'title' => 'Mapa operacional']);
        }

        $farm = Farm::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->orderBy('name')
            ->first();

        if (! $farm) {
            return view('map.empty');
        }

        return redirect()->route('farms.map', $farm);
    }

    private function existingColumns(string $table, array $columns): array
    {
        return collect($columns)
            ->filter(fn (string $label, string $column): bool => Schema::hasColumn($table, $column))
            ->all();
    }
}
