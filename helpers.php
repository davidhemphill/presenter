<?php

use Hemp\Presenter\PresenterFactory;

if (! function_exists('present')) {
    /**
     * Helper to Present Models.
     *
     * @param string          $model
     * @param \Closure|string $presenter
     *
     * @return object
     */
    function present($model, $presenter)
    {
        $factory = new PresenterFactory();

        return $factory($model, $presenter);
    }
}
