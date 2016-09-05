<?php

namespace Hemp\Presenter;

trait Presentable
{
    /**
     * Present this instance using the provided Presenter class
     * @param  string $presenter
     * @return Hemp\Presenter
     */
    public function present($presenter)
    {
        return present($this, $presenter);
    }
}