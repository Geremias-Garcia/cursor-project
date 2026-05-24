<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * ADR-004: CSRF must be enforced in feature tests, not bypassed in PHPUnit.
     */
    protected function runningUnitTests(): bool
    {
        return false;
    }
}
