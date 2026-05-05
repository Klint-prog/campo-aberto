<?php

namespace App\Http\Controllers\Api\Internal\V1;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CropController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $crops = Crop::query()
            ->with('varieties')
            ->where('tenant_id', $request->user()->tenant_id)
            ->orderBy('name')
            ->get();

        return response()->json(['success' => true, 'data' => $crops]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'scientific_name' => ['nullable', 'string', 'max:255'],
            'cycle_type' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $crop = Crop::create($data + ['tenant_id' => $request->user()->tenant_id]);

        return response()->json(['success' => true, 'data' => $crop], 201);
    }

    public function update(Request $request, Crop $crop): JsonResponse
    {
        abort_unless($crop->tenant_id === $request->user()->tenant_id, 403);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('crops')->where('tenant_id', $request->user()->tenant_id)->ignore($crop->id)],
            'scientific_name' => ['nullable', 'string', 'max:255'],
            'cycle_type' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $crop->update($data);

        return response()->json(['success' => true, 'data' => $crop->fresh('varieties')]);
    }
}
