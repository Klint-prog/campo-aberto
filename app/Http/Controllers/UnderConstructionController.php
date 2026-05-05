<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class UnderConstructionController extends Controller
{
    public function __invoke(?string $module = null): View
    {
        $labels = [
            'roles' => 'Papéis e permissões',
            'harvests' => 'Colheitas',
            'animal-groups' => 'Lotes de animais',
            'animal-weights' => 'Pesagens',
            'animal-health' => 'Sanidade animal',
            'maintenance' => 'Manutenção',
            'reports' => 'Relatórios',
            'bi' => 'Dashboards BI',
            'pwa' => 'PWA',
            'mobile' => 'Aplicativo mobile',
            'inpe-ndvi' => 'INPE/NDVI',
            'iot-rfid' => 'IoT/RFID',
            'saas' => 'SaaS',
        ];

        return view('shared.under-construction', [
            'module' => $module,
            'title' => $labels[$module] ?? str((string) $module)->replace('-', ' ')->title()->toString(),
        ]);
    }
}
