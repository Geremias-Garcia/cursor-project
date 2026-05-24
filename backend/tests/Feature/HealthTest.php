<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Redis;
use Tests\TestCase;
use Throwable;

class HealthTest extends TestCase
{
    public function test_health_returns_ok_without_dependencies(): void
    {
        $this->get('/health')
            ->assertOk()
            ->assertJson(['status' => 'ok']);
    }

    public function test_ready_returns_ready_when_database_and_redis_are_reachable(): void
    {
        try {
            Redis::connection()->ping();
        } catch (Throwable $exception) {
            $this->markTestSkipped('Redis is not available: '.$exception->getMessage());
        }

        $this->get('/ready')
            ->assertOk()
            ->assertJson(['status' => 'ready']);
    }
}
