<?php

namespace Hemp\Presenter;

use BadMethodCallException;

trait Presentable
{
    /**
     * Present this instance using the provided Presenter class, defaulting
     * to the Presenter defined on the Model instance.
     *
     * @param string|null $presenter
     * @return \Hemp\Presenter
     */
    public function present($presenter = null)
    {
        $presenter = $presenter ?? $this->defaultPresenter;

        if (is_null($presenter)) {
            throw new BadMethodCallException('No presenter or default presenter passed to present()');
        }

        return (new PresenterFactory)($this, $presenter);
    }
}
