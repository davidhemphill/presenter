<?php

namespace Hemp\Presenter\Tests\Fixtures;

use Hemp\Presenter\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserWithDefaultPresenter extends User
{
    use HasFactory;

    protected $table = 'users';

    public $defaultPresenter = UserProfilePresenter::class;

    protected static function newFactory()
    {
        return UserWithDefaultPresenterFactory::new();
    }
}
