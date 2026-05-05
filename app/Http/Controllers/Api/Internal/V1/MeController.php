<?php

namespace App\Http\Controllers\Api\Internal\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user()->load(['tenant', 'farms', 'roles.permissions']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => $user->is_active,
                'tenant' => $user->tenant,
                'farms' => $user->farms,
                'roles' => $user->roles,
            ],
            'meta' => [],
        ]);
    }
}
