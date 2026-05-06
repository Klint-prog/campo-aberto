<?php

namespace App\Http\Controllers\Web\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportCenterWebController extends Controller
{
    public function index()
    {
        // Lista todos os relatórios disponíveis
        $reports = [
            'farms','activities','activity-inputs','harvests','animals','animal-weights','animal-health','inventory','machines','finance','domain-events'
        ];
        return view('reports.index', compact('reports'));
    }
}