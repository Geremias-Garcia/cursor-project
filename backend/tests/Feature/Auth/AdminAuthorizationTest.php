<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_returns_403_for_regular_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/v1/admin/dashboard')
            ->assertForbidden();
    }

    public function test_admin_dashboard_is_accessible_for_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/dashboard')
            ->assertOk()
            ->assertJsonPath('data.message', 'Admin dashboard placeholder');
    }
}
