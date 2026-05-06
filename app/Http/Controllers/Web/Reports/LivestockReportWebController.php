<?php

namespace App\Http\Controllers\Web\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LivestockReportWebController extends Controller
{
    public function index()
    {
        return view('reports.animals');
    }

    public function weights()
    {
        return view('reports.animal-weights');
    }

    public function health()
    {
        return view('reports.animal-health');
    }
}