<?php

namespace Hemp\Presenter\Tests\Fixtures;

use Hemp\Presenter\Presenter;

class CamelCaseAttributesPresenter extends Presenter
{
    public $snakeCase = false;

    public function getFirstNameAttribute()
    {
        return explode(' ', $this->name)[0];
    }

    public function getLastNameAttribute()
    {
        return explode(' ', $this->name)[1];
    }
}
