<?php

namespace App\Policies\Concerns;

use App\Models\Student;
use App\Models\User;
use App\Support\DepartmentResolver;

trait HandlesRoleAuthorization
{
    protected function beforeSuperAdmin(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    protected function isStaff(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    protected function deanCanAccessStudent(User $user, Student $student): bool
    {
        if (! $user->isDean()) {
            return false;
        }

        $deanDept = DepartmentResolver::shortcutToLong($user->department);

        return strcasecmp(trim((string) $student->department), trim((string) $deanDept)) === 0;
    }
}
