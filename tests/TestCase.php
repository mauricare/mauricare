<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        if (file_exists(dirname(__DIR__).'/bootstrap/cache/config.php')) {
            throw new RuntimeException(
                'Tests aborted: Laravel configuration is cached. Run "php artisan config:clear" before testing.',
            );
        }

        parent::setUp();

        if (
            ! $this->app->environment('testing')
            || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:'
        ) {
            throw new RuntimeException(
                sprintf(
                    'Tests aborted: expected testing/sqlite/:memory:, got %s/%s/%s.',
                    $this->app->environment(),
                    config('database.default'),
                    config('database.connections.sqlite.database'),
                ),
            );
        }
    }
}
