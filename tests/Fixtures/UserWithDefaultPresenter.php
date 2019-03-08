<?php

namespace Hemp\Presenter\Tests\Fixtures;

class UserWithDefaultPresenter extends User
{
    protected $table = 'users';

    public $defaultPresenter = UserProfilePresenter::class;
}
