<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class FarmMapController extends Controller
{
    public function __invoke(Request $request, Farm $farm): View
    {
        abort_unless($farm->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($farm), 403);

        return view('farms.map', [
            'farm' => $farm,
        ]);
    }
}
