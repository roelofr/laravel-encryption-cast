<?php

declare(strict_types=1);

namespace Tests\Roelofr\EncryptionCast;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

// phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
class TestCase extends OrchestraTestCase
{
    use DatabaseMigrations;

    /**
     * Purges the mongo collection after each test
     * @tearDown
     */
    public function deleteCollectionAfterRun()
    {
        // Ensure app exists
        $this->ensureAppExists();
    }

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        // Forward first
        parent::setUp();

        // Test-only migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Ensures an app exists
     * @return void
     */
    protected function ensureAppExists(): void
    {
        if (!$this->app) {
            $this->refreshApplication();
        }
    }

    /**
     * Get package providers.
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [];
    }

    /**
     * Define environment setup.
     * @param  \Illuminate\Foundation\Application   $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Configure app
        $app['config']->set('app.url', 'http://test.local');
        $app['config']->set('app.env', 'local');
        $app['config']->set('app.debug', true);
        $app['config']->set('app.timezone', 'UTC');

        // Configure queue and events
        $app['config']->set('queue.default', 'sync');
    }
}
