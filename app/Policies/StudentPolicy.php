<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use App\Policies\Concerns\HandlesRoleAuthorization;

class StudentPolicy
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

    public function view(User $user, Student $student): bool
    {
        if ($this->isStaff($user)) {
            return true;
        }

        return $this->deanCanAccessStudent($user, $student);
    }

    public function create(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function update(User $user, Student $student): bool
    {
        return $this->isStaff($user);
    }

    public function delete(User $user, Student $student): bool
    {
        return $this->isStaff($user);
    }

    public function restore(User $user, Student $student): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Student $student): bool
    {
        return $user->isSuperAdmin();
    }

    public function import(User $user): bool
    {
        return $this->isStaff($user);
    }
}
