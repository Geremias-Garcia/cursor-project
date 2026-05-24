<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginUserService
{
    /**
     * @param  array{email: string, password: string}  $credentials
     */
    public function login(Request $request, array $credentials, bool $remember): User
    {
        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
