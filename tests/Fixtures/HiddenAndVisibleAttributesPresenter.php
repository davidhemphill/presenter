<?php

namespace Hemp\Presenter\Tests\Fixtures;

use Hemp\Presenter\Presenter;

class HiddenAndVisibleAttributesPresenter extends Presenter
{
    public $hidden = ['id', 'created_at', 'name', 'updated_at'];

    public $visible = ['name'];
}
