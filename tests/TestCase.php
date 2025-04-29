<?php

namespace ForFit\Session\Tests;

use ForFit\Session\SessionServiceProvider;
use MongoDB\Laravel\MongoDBServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->artisan('mongodb:session:index');
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function defineEnvironment($app): void
    {
        // Setup default database to use MongoDB
        $app['config']->set('database.default', 'mongodb');
        $app['config']->set('database.connections.mongodb', [
            'driver' => 'mongodb',
            'host' => env('MONGODB_HOST', '127.0.0.1'),
            'port' => env('MONGODB_PORT', 27017),
            'database' => env('MONGODB_DATABASE', 'laravel_session_test'),
            'username' => env('MONGODB_USERNAME', ''),
            'password' => env('MONGODB_PASSWORD', ''),
            'options' => [
                'database' => env('MONGODB_AUTHENTICATION_DATABASE', 'admin'),
            ],
        ]);

        // Configure session to use MongoDB driver
        $app['config']->set('session.driver', 'mongodb');
        $app['config']->set('session.lifetime', 120);
        $app['config']->set('session.table', 'sessions');
    }

    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            MongoDBServiceProvider::class,
            SessionServiceProvider::class,
        ];
    }
} 