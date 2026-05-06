<?php

namespace App\Http\Controllers\Web\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class CsvExportController extends Controller
{
    public function exportFarms() { return Response::make('id,nome\n', 200); }
    public function exportActivities() { return Response::make('id,atividade\n', 200); }
    public function exportActivityInputs() { return Response::make('id,insumo\n', 200); }
    public function exportHarvests() { return Response::make('id,colheita\n', 200); }
    public function exportAnimals() { return Response::make('id,animal\n', 200); }
    public function exportAnimalWeights() { return Response::make('id,peso\n', 200); }
    public function exportAnimalHealth() { return Response::make('id,status\n', 200); }
    public function exportInventory() { return Response::make('id,item,quantidade\n', 200); }
    public function exportMachines() { return Response::make('id,maquina\n', 200); }
    public function exportFinance() { return Response::make('id,valor\n', 200); }
    public function exportDomainEvents() { return Response::make('id,event\n', 200); }
}