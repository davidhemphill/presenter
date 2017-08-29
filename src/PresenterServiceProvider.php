<?php

namespace Hemp\Presenter;

use Illuminate\Support\Collection;
use Hemp\Presenter\PresenterFactory;
use Illuminate\Support\ServiceProvider;

class PresenterServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Collection::macro('present', function ($class) {
            return $this->map(function ($object) use ($class) {
                return (new PresenterFactory)($object, $class);
            });
        });

        Collection::macro('presentTransformed', function ($class) {
            return $this->transform(function ($object) use ($class) {
                return (new PresenterFactory)($object, $class);
            });
        });
    }

    public function register()
    {
    }
}
