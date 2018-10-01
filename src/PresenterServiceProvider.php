<?php

namespace Hemp\Presenter;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class PresenterServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $factory = new PresenterFactory();

        Collection::macro('present', function ($class) use ($factory) {
            return $this->map(function ($object) use ($class, $factory) {
                return $factory($object, $class);
            });
        });

        Collection::macro('presentTransformed', function ($class) use ($factory) {
            return $this->transform(function ($object) use ($class, $factory) {
                return $factory($object, $class);
            });
        });
    }

    public function register()
    {
    }
}
