<?php

namespace Hemp\Presenter\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

abstract class IntegrationTest extends TestCase
{
    /**
     * Setup the test case.
     *
     * @return void
     */
    public function setUp() : void
    {
        parent::setUp();

        Hash::driver('bcrypt')->setRounds(4);

        $this->withFactories(__DIR__.'/Factories');

        $this->loadMigrations();
        $this->registerRoutes();
    }

    protected function loadMigrations()
    {
        $this->loadMigrationsFrom([
            '--database' => 'sqlite',
            '--realpath' => realpath(__DIR__.'/Migrations'),
        ]);
    }

    /**
     * Register the package's routes for testing resources.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        Route::get('/users', function () {
            return \Hemp\Presenter\Tests\Fixtures\User::all()->present(function ($user) {
                return ['full_name' => $user->name];
            });
        });

        Route::get('/paginated', function () {
            return \Hemp\Presenter\Tests\Fixtures\User::paginate(1)->present(function ($user) {
                return ['full_name' => $user->name];
            });
        });
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function getPackageAliases($app)
    {
        return [
            // 'Presenter' => 'Hemp\Presenter\Facades\Presenter'
        ];
    }

    protected function getPackageProviders($app)
    {
        return [\Hemp\Presenter\PresenterServiceProvider::class];
    }
}
