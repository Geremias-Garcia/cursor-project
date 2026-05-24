<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Support\EnvExampleValidator;
use Tests\TestCase;

class EnvExampleTest extends TestCase
{
    public function test_env_example_contains_required_keys(): void
    {
        $validator = new EnvExampleValidator;

        $validator->validateFile(base_path('.env.example'));

        $this->addToAssertionCount(1);
    }
}
