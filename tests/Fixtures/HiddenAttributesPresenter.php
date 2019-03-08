<?php

namespace Hemp\Presenter\Tests\Fixtures;

use Hemp\Presenter\Presenter;

class HiddenAttributesPresenter extends Presenter
{
    public $hidden = ['id', 'created_at', 'updated_at'];
}
