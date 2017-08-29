<?php

namespace Hemp\Presenter;

use Hemp\Presenter\EmptyModel;

class PresenterFactory
{
    public function __invoke($model, $presenter)
    {
        if ($presenter instanceof \Closure) {
            return new EmptyModel($presenter($model));
        }

        return new $presenter($model);
    }
}
