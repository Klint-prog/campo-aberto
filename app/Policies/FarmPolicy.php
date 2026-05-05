<?php

namespace App\Policies;

use App\Models\Farm;
use App\Models\User;

class FarmPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('farms.view') || $user->hasRole('admin') || $user->hasRole('tenant_admin');
    }

    public function view(User $user, Farm $farm): bool
    {
        return $user->tenant_id === $farm->tenant_id && $user->canAccessFarm($farm);
    }
}
