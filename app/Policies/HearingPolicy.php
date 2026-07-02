<?php

namespace App\Policies;

use App\Models\Hearing;
use App\Models\User;
use App\Policies\Concerns\HandlesRoleAuthorization;

class HearingPolicy
{
    use HandlesRoleAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        return $this->beforeSuperAdmin($user, $ability);
    }

    public function viewAny(User $user): bool
    {
        return $this->isStaff($user) || $user->isDean();
    }

    public function view(User $user, Hearing $hearing): bool
    {
        if ($this->isStaff($user)) {
            return true;
        }

        if ($user->isDean()) {
            $hearing->loadMissing('case.student');

            return $this->deanCanAccessStudent($user, $hearing->case->student);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function update(User $user, Hearing $hearing): bool
    {
        return $this->isStaff($user);
    }

    public function delete(User $user, Hearing $hearing): bool
    {
        return $this->isStaff($user);
    }

    public function start(User $user, Hearing $hearing): bool
    {
        return $this->isStaff($user);
    }

    public function complete(User $user, Hearing $hearing): bool
    {
        return $this->isStaff($user);
    }
}
