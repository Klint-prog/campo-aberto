<?php

namespace App\Http\Controllers\Web\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DomainEventReportWebController extends Controller
{
    public function index()
    {
        return view('reports.domain-events');
    }
}