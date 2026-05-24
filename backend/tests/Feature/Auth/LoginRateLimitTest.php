<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_rate_limited_after_five_attempts_per_ip_and_email(): void
    {
        User::factory()->create([
            'email' => 'limited@example.com',
            'password' => 'password',
        ]);

        $csrf = $this->withCsrfToken();

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $csrf->post('/api/v1/login', [
                'email' => 'limited@example.com',
                'password' => 'wrong-password',
            ])->assertUnprocessable();
        }

        $csrf->post('/api/v1/login', [
            'email' => 'limited@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }
}
