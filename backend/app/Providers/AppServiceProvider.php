<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define('admin', fn (User $user): bool => $user->isAdmin());

        RateLimiter::for('login', function (Request $request): Limit {
            $email = (string) $request->input('email');

            return Limit::perMinute(5)->by($request->ip().'|'.$email);
        });
    }
}
