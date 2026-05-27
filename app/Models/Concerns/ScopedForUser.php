<?php

namespace App\Models\Concerns;

use App\Models\User;
use App\Support\DepartmentResolver;
use Illuminate\Database\Eloquent\Builder;

trait ScopedForUser
{
    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return $query;
        }

        if ($user->isDean()) {
            $department = DepartmentResolver::shortcutToLong($user->department);

            return $query->whereRaw('TRIM(department) = ?', [trim((string) $department)]);
        }

        return $query->whereRaw('0 = 1');
    }
}
