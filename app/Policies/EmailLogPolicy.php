<?php

namespace App\Policies;

use App\Models\EmailLog;
use App\Models\User;

class EmailLogPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function delete(User $user, EmailLog $emailLog): bool
    {
        return false;
    }
}
