<?php

namespace App\Http\Controllers\Api\Internal\V1;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Models\CropVariety;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CropVarietyController extends Controller
{
    public function store(Request $request, Crop $crop): JsonResponse
    {
        abort_unless($crop->tenant_id === $request->user()->tenant_id, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cultivar_code' => ['nullable', 'string', 'max:120'],
            'cycle_days' => ['nullable', 'integer', 'min:1'],
            'expected_yield_kg_ha' => ['nullable', 'numeric', 'min:0'],
            'metadata' => ['nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $variety = CropVariety::create($data + [
            'tenant_id' => $request->user()->tenant_id,
            'crop_id' => $crop->id,
        ]);

        return response()->json(['success' => true, 'data' => $variety], 201);
    }
}
