<?php

use Hemp\Presenter\EmptyModel;

if (!function_exists('present')) {
    /**
     * Helper to Present Models
     * @param  string $model
     * @param  \Closure|string $presenter
     * @return object
     */
    function present($model, $presenter)
    {
        if ($presenter instanceof \Closure) {
            return new EmptyModel($presenter($model));
        }

        return new $presenter($model);
    }
}
