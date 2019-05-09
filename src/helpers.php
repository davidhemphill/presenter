<?php

use Hemp\Presenter\PresenterFactory;

if (! function_exists('present')) {
    function present($model, $presenter)
    {
        return (new PresenterFactory)($model, $presenter);
    }
}
