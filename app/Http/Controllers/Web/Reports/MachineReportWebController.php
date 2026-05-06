<?php

namespace App\Http\Controllers\Web\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MachineReportWebController extends Controller
{
    public function index()
    {
        return view('reports.machines');
    }
}