<?php

namespace Hemp\Presenter;

use Hemp\Presenter\PresenterFactory;

trait Presentable
{
    /**
     * Present this instance using the provided Presenter class
     * @param  string $presenter
     * @return Hemp\Presenter
     */
    public function present($presenter)
    {
        $factory = new PresenterFactory;
        return $factory($this, $presenter);
    }
}
