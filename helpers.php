<?php

if (!function_exists('present')) {
    /**
     * Helper to Present Models
     * @param  string $model
     * @param  string $presenter
     * @return object
     */
    function present($model, $presenter)
    {
        return new $presenter($model);
    }
}
