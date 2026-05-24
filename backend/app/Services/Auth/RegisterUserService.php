<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Enums\UserRole;
use App\Models\User;

class RegisterUserService
{
    /**
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function register(array $data): User
    {
        return User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => UserRole::User,
        ]);
    }
}
