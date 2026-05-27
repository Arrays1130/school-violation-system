<?php

namespace App\Policies;

use App\Models\StudentCase;
use App\Models\User;
use App\Policies\Concerns\HandlesRoleAuthorization;

class StudentCasePolicy
{
    use HandlesRoleAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        return $this->beforeSuperAdmin($user, $ability);
    }

    public function viewAny(User $user): bool
    {
        return $user->isDean() || $this->isStaff($user);
    }

    public function view(User $user, StudentCase $case): bool
    {
        $case->loadMissing('student');

        if ($this->isStaff($user)) {
            return true;
        }

        return $case->student && $this->deanCanAccessStudent($user, $case->student);
    }

    public function create(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function update(User $user, StudentCase $case): bool
    {
        return $this->isStaff($user);
    }

    public function delete(User $user, StudentCase $case): bool
    {
        return $this->isStaff($user);
    }

    public function restore(User $user, StudentCase $case): bool
    {
        return $this->isStaff($user);
    }

    public function forceDelete(User $user, StudentCase $case): bool
    {
        return $user->isSuperAdmin();
    }

    public function close(User $user, StudentCase $case): bool
    {
        return $this->isStaff($user);
    }

    public function recordAction(User $user, StudentCase $case): bool
    {
        return $this->isStaff($user);
    }

    public function endorse(User $user, StudentCase $case): bool
    {
        return $this->isStaff($user);
    }
}
