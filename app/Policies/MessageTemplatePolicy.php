<?php

namespace App\Policies;

use App\Models\MessageTemplate;
use App\Models\User;

class MessageTemplatePolicy
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
        return $user->isDean();
    }

    public function view(User $user, MessageTemplate $messageTemplate): bool
    {
        return $user->isDean();
    }

    public function create(User $user): bool
    {
        return $user->isDean();
    }

    public function update(User $user, MessageTemplate $messageTemplate): bool
    {
        return $user->isDean();
    }

    public function delete(User $user, MessageTemplate $messageTemplate): bool
    {
        return $user->isDean();
    }
}
