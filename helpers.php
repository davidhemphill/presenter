<?php

use Hemp\Presenter\EmptyModel;

if (!function_exists('present')) {
    /**
     * Helper to Present Models
     * @param  string $model
     * @param  \Closure|string $presentationStrategy
     * @return object
     */
    function present($model, $presentationStrategy)
    {
        if ($presentationStrategy instanceof \Closure) {
            return new EmptyModel($presentationStrategy($model));
        }

        return new $presentationStrategy($model);
    }
}
