<?php

namespace Hemp\Presenter;

class PresenterFactory
{
    /**
     * Create and return a new Presenter instance.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param Closure|string $presenter
     * @return void
     */
    public function __invoke($model, $presenter)
    {
        if ($presenter instanceof \Closure) {
            return new class($presenter($model)) {
                public function __construct($attributes)
                {
                    $this->attributes = $attributes;
                }

                public function __get($attribute)
                {
                    return $this->attributes[$attribute];
                }
            };
        }

        return new $presenter($model);
    }
}
