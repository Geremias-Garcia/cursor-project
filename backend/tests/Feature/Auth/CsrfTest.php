<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CsrfTest extends TestCase
{
    use RefreshDatabase;

    public function test_mutating_request_without_csrf_token_returns_419(): void
    {
        $this->statefulApi()->post('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertStatus(419);
    }
}
