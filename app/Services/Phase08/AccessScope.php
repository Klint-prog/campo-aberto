<?php

namespace App\Services\Phase08;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AccessScope
{
    public function fromRequest(Request $request, bool $requireFarm = false): array
    {
        $tenantId = $request->integer('tenant_id') ?: $request->headers->get('X-Tenant-Id');
        $farmId = $request->integer('farm_id') ?: $request->headers->get('X-Farm-Id');
        $userId = $request->integer('user_id') ?: $request->headers->get('X-User-Id');
        $permissions = $this->permissions($request);
        $allowedFarmIds = $this->allowedFarmIds($request);

        if (! $tenantId) {
            throw new AccessDeniedHttpException('tenant_id é obrigatório para relatórios, dashboards e IA.');
        }

        if ($requireFarm && ! $farmId) {
            throw new AccessDeniedHttpException('farm_id é obrigatório para esta operação.');
        }

        if ($farmId && $allowedFarmIds !== ['*'] && ! in_array((int) $farmId, $allowedFarmIds, true)) {
            throw new AccessDeniedHttpException('Fazenda não autorizada para este usuário.');
        }

        return [
            'tenant_id' => (int) $tenantId,
            'farm_id' => $farmId ? (int) $farmId : null,
            'user_id' => $userId ? (int) $userId : null,
            'permissions' => $permissions,
            'allowed_farm_ids' => $allowedFarmIds,
        ];
    }

    public function assertPermission(array $scope, string $permission): void
    {
        if (! in_array('*', $scope['permissions'], true) && ! in_array($permission, $scope['permissions'], true)) {
            throw new AccessDeniedHttpException('Permissão insuficiente: '.$permission.'.');
        }
    }

    private function permissions(Request $request): array
    {
        $value = $request->headers->get('X-Permissions', (string) $request->input('permissions', ''));

        if (is_array($request->input('permissions'))) {
            return array_values(array_filter($request->input('permissions')));
        }

        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }

    private function allowedFarmIds(Request $request): array
    {
        $value = $request->headers->get('X-Allowed-Farm-Ids', (string) $request->input('allowed_farm_ids', ''));

        if ($value === '*') {
            return ['*'];
        }

        if (is_array($request->input('allowed_farm_ids'))) {
            return array_map('intval', array_filter($request->input('allowed_farm_ids')));
        }

        return array_map('intval', array_filter(array_map('trim', explode(',', $value))));
    }
}
