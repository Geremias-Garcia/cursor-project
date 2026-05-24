<?php

declare(strict_types=1);

namespace App\Services\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutUserService
{
    public function logout(Request $request): void
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
