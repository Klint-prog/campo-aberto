<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        $cards = collect([
            ['title' => 'Usuários', 'table' => 'users', 'route' => 'users.index', 'icon' => '👥'],
            ['title' => 'Fazendas', 'table' => 'farms', 'route' => 'farms.index', 'icon' => '🏡'],
            ['title' => 'Talhões', 'table' => 'fields', 'route' => 'fields.index', 'icon' => '🌱'],
            ['title' => 'Pastagens', 'table' => 'pastures', 'route' => 'pastures.index', 'icon' => '🌾'],
            ['title' => 'Culturas', 'table' => 'crops', 'route' => 'crops.index', 'icon' => '🌽'],
            ['title' => 'Safras', 'table' => 'seasons', 'route' => 'seasons.index', 'icon' => '📅'],
            ['title' => 'Atividades', 'table' => 'activities', 'route' => 'activities.index', 'icon' => '✅'],
            ['title' => 'Animais', 'table' => 'animals', 'route' => 'animals.index', 'icon' => '🐄'],
            ['title' => 'Estoque', 'table' => 'inventory_items', 'route' => 'inventory.index', 'icon' => '📦'],
            ['title' => 'Financeiro', 'table' => 'financial_transactions', 'route' => 'finance.index', 'icon' => '💰'],
        ])->map(function (array $card) use ($tenantId): array {
            return array_merge($card, [
                'available' => Schema::hasTable($card['table']),
                'count' => $this->safeCount($card['table'], $tenantId),
            ]);
        });

        $quickLinks = [
            ['label' => 'Cadastrar usuário', 'route' => 'users.create'],
            ['label' => 'Ver mapa operacional', 'route' => 'map.index'],
            ['label' => 'Abrir fazendas', 'route' => 'farms.index'],
            ['label' => 'Consultar relatórios', 'route' => 'reports.index'],
        ];

        return view('dashboard', [
            'cards' => $cards,
            'quickLinks' => $quickLinks,
        ]);
    }

    private function safeCount(string $table, string $tenantId): ?int
    {
        if (! Schema::hasTable($table)) {
            return null;
        }

        $query = DB::table($table);

        if (Schema::hasColumn($table, 'tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->count();
    }
}
