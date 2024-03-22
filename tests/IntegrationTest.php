<?php

namespace Hemp\Presenter\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;

abstract class IntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function setUpDatabase()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

    }

    protected function getPackageProviders($app)
    {
        return [
            \Hemp\Presenter\PresenterServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function defineWebRoutes($router)
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
}
