<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait ScopedForUser
{
    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return $query;
        }

        if ($user->isDean()) {
            return $query->forDeanDepartment($user->department);
        }

        return $query->whereRaw('0 = 1');
    }
}
