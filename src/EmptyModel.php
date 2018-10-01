<?php

namespace Hemp\Presenter;

use Illuminate\Database\Eloquent\Model;

class EmptyModel extends Model
{
    /**
     * Allow any attribute to be set.
     *
     * @var array
     */
    protected $guarded = [];
}
