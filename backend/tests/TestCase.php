<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function statefulApi(): static
    {
        return $this->withHeaders([
            'Origin' => 'http://localhost:5173',
            'Referer' => 'http://localhost:5173/',
            'Accept' => 'application/json',
        ]);
    }

    protected function withCsrfToken(): static
    {
        $response = $this->statefulApi()->get('/sanctum/csrf-cookie');

        $xsrfToken = collect($response->headers->getCookies())
            ->first(fn ($cookie) => $cookie->getName() === 'XSRF-TOKEN')
            ?->getValue();

        return $this->withHeader('X-XSRF-TOKEN', (string) $xsrfToken);
    }
}
