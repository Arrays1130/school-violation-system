<?php

namespace App\Policies;

use App\Models\Handbook;
use App\Models\User;
use App\Policies\Concerns\HandlesRoleAuthorization;

class HandbookPolicy
{
    use HandlesRoleAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        return $this->beforeSuperAdmin($user, $ability);
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Handbook $handbook): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function update(User $user, Handbook $handbook): bool
    {
        return $this->isStaff($user);
    }

    public function delete(User $user, Handbook $handbook): bool
    {
        return $user->isSuperAdmin();
    }
}
