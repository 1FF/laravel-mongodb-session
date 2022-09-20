<?php

namespace ForFit\Session;

use ForFit\Session\Console\Commands\MongodbSessionDropIndex;
use ForFit\Session\Console\Commands\MongodbSessionIndex;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider as ParentServiceProvider;

class SessionServiceProvider extends ParentServiceProvider
{
    /**
     * Register any application services.
     *
     * @throws \Exception
     * @return void
     *
     */
    public function boot()
    {
        if (config('session.driver') !== 'mongodb') {
            return;
        }

        Session::extend('mongodb', function ($app) {
            $configs = $app['config']->get('session');
            $connection = $app['db']->connection($configs['connection'] ?? null);

            return new MongoDbSessionHandler($connection, $configs['table'], $configs['lifetime']);
        });

        // register the collection indexing commands and migrations if running in cli
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
            $this->commands([
                MongodbSessionDropIndex::class,
                MongodbSessionIndex::class,
            ]);
        }
    }
}
