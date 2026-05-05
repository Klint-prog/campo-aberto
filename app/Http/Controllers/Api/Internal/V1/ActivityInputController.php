<?php

namespace App\Http\Controllers\Api\Internal\V1;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityInput;
use App\Models\Farm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityInputController extends Controller
{
    public function store(Request $request, Farm $farm, Activity $activity): JsonResponse
    {
        abort_unless($farm->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($farm), 403);
        abort_unless($activity->tenant_id === $request->user()->tenant_id && $activity->farm_id === $farm->id, 404);

        $data = $request->validate([
            'input_name' => ['required', 'string', 'max:255'],
            'input_type' => ['nullable', 'string', 'max:120'],
            'input_reference' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'unit' => ['required', 'string', 'max:40'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'metadata' => ['nullable', 'array'],
        ]);

        if (isset($data['unit_cost'])) {
            $data['total_cost'] = round((float) $data['quantity'] * (float) $data['unit_cost'], 4);
        }

        $input = ActivityInput::create($data + [
            'tenant_id' => $request->user()->tenant_id,
            'farm_id' => $farm->id,
            'activity_id' => $activity->id,
        ]);

        return response()->json(['success' => true, 'data' => $input], 201);
    }
}
