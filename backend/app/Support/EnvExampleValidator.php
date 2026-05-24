<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

class EnvExampleValidator
{
    /** @var list<string> */
    private const REQUIRED_KEYS = [
        'APP_NAME',
        'APP_ENV',
        'APP_KEY',
        'APP_URL',
        'DB_CONNECTION',
        'DB_HOST',
        'DB_PORT',
        'DB_DATABASE',
        'DB_USERNAME',
        'DB_PASSWORD',
        'SESSION_DRIVER',
        'SESSION_LIFETIME',
        'REDIS_HOST',
        'REDIS_PORT',
        'SANCTUM_STATEFUL_DOMAINS',
        'CORS_ALLOWED_ORIGINS',
    ];

    public function validateFile(string $path): void
    {
        if (! is_readable($path)) {
            throw new RuntimeException("Env example file is not readable: {$path}");
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException("Env example file could not be read: {$path}");
        }

        $missing = [];
        foreach (self::REQUIRED_KEYS as $key) {
            if (! preg_match('/^'.preg_quote($key, '/').'=/m', $contents)) {
                $missing[] = $key;
            }
        }

        if ($missing !== []) {
            throw new RuntimeException(
                'Missing keys in .env.example: '.implode(', ', $missing)
            );
        }
    }
}
