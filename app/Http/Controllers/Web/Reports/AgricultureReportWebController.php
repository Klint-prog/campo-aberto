<?php

namespace App\Http\Controllers\Web\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AgricultureReportWebController extends Controller
{
    public function index()
    {
        return view('reports.activities');
    }

    public function inputs()
    {
        return view('reports.activity-inputs');
    }

    public function harvests()
    {
        return view('reports.harvests');
    }
}