<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isAdmin();
    }

    public function suspend(User $actor, User $target): bool
    {
        return $actor->isAdmin() && $actor->id !== $target->id;
    }
}
