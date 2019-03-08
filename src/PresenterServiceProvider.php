<?php

namespace Hemp\Presenter;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class PresenterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        Collection::macro('present', function ($class) {
            return $this->transform(function ($object) use ($class) {
                return (new PresenterFactory)($object, $class);
            });
        });
    }
}
