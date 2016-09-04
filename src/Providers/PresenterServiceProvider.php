<?php

namespace Hemp\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class PresenterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Collection::macro('present', function ($class) {
            return $this->map(function ($object) use ($class) {
                return present($object, $class);
            });
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
