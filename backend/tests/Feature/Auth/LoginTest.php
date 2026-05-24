<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_sets_session_and_returns_current_user(): void
    {
        $user = User::factory()->create([
            'email' => 'bidder@example.com',
            'password' => 'password',
        ]);

        $this->withCsrfToken()->post('/api/v1/login', [
            'email' => 'bidder@example.com',
            'password' => 'password',
        ])->assertOk()
            ->assertJsonPath('data.email', 'bidder@example.com')
            ->assertJsonPath('data.uuid', $user->uuid);

        $this->actingAs($user)
            ->getJson('/api/v1/user')
            ->assertOk()
            ->assertJsonPath('data.uuid', $user->uuid);
    }
}
