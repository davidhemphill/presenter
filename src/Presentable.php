<?php

namespace Hemp\Presenter;

trait Presentable
{
    /**
     * Present this instance using the provided Presenter class
     *
     * @param  string $presenter
     * @return Hemp\Presenter
     */
    public function present($presenter = null)
    {
        if (!$presenter) {
            if (!$this->defaultPresenter) {
                throw new \BadMethodCallException('No presenter or default presenter passed to present()');
            }
            $presenter = $this->defaultPresenter;
        }

        $factory = new PresenterFactory();

        return $factory($this, $presenter);
    }
}
