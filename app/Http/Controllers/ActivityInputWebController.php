<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActivityInputWebRequest;
use App\Models\Activity;
use App\Models\ActivityInput;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ActivityInputWebController extends Controller
{
    public function store(StoreActivityInputWebRequest $request, Activity $activity): RedirectResponse
    {
        $data = $request->validated();

        if (isset($data['unit_cost'])) {
            $data['total_cost'] = round((float) $data['quantity'] * (float) $data['unit_cost'], 4);
        }

        ActivityInput::create($data + [
            'tenant_id' => $request->user()->tenant_id,
            'farm_id' => $activity->farm_id,
            'activity_id' => $activity->id,
        ]);

        return redirect()->route('activities.show', $activity)->with('status', 'Consumo de insumo registrado com sucesso.');
    }

    public function destroy(Request $request, Activity $activity, ActivityInput $input): RedirectResponse
    {
        abort_unless($activity->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($activity->farm_id), Response::HTTP_FORBIDDEN);
        abort_unless($input->tenant_id === $request->user()->tenant_id && $input->activity_id === $activity->id, Response::HTTP_NOT_FOUND);

        $input->delete();

        return redirect()->route('activities.show', $activity)->with('status', 'Consumo de insumo removido com sucesso.');
    }
}
