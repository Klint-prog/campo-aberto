<?php

namespace App\Http\Controllers\Api\Internal\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $permissions = $request->user()
            ->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('slug')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $permissions,
            'meta' => ['count' => $permissions->count()],
        ]);
    }
}
