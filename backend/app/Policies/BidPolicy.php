<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class BidPolicy
{
    public function create(User $user): bool
    {
        return true;
    }
}
