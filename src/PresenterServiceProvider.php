<?php

namespace Hemp\Presenter;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class PresenterServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Collection::macro('present', function ($class) {
            return $this->map(function ($object) use ($class) {
                return present($object, $class);
            });
        });

        Collection::macro('presentTransformed', function ($class) {
            return $this->transform(function ($object) use ($class) {
                return present($object, $class);
            });
        });
    }

    public function register()
    {

    }
}