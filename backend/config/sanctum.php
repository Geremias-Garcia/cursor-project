<?php

declare(strict_types=1);
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Laravel\Sanctum\Http\Middleware\AuthenticateSession;

return [
    'stateful' => explode(',', (string) env(
        'SANCTUM_STATEFUL_DOMAINS',
        'localhost,localhost:5173,localhost:8080,127.0.0.1,127.0.0.1:5173,127.0.0.1:8080'
    )),
    'guard' => ['web'],
    'expiration' => null,
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
    'middleware' => [
        'authenticate_session' => AuthenticateSession::class,
        'encrypt_cookies' => EncryptCookies::class,
        'validate_csrf_token' => VerifyCsrfToken::class,
    ],
];
