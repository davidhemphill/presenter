<?php

namespace Hemp\Presenter\Tests\Fixtures;

use Hemp\Presenter\Presenter;

class VisibleAttributesPresenter extends Presenter
{
    public $visible = ['id', 'email'];
}
