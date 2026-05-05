<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('users.view') || $user->hasRole('admin') || $user->hasRole('tenant_admin');
    }

    public function view(User $user, User $target): bool
    {
        return $user->tenant_id === $target->tenant_id && $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('users.manage') || $user->hasRole('admin') || $user->hasRole('tenant_admin');
    }

    public function update(User $user, User $target): bool
    {
        return $user->tenant_id === $target->tenant_id && $this->create($user);
    }

    public function delete(User $user, User $target): bool
    {
        return $user->id !== $target->id
            && $user->tenant_id === $target->tenant_id
            && $this->create($user);
    }
}
