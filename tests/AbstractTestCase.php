<?php

namespace Hemp\Tests\Presentable;

use GrahamCampbell\TestBench\AbstractPackageTestCase;
use Hemp\Presenter\PresenterServiceProvider;

abstract class AbstractTestCase extends AbstractPackageTestCase
{
    /**
     * Get the service provider class.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return string
     */
    protected function getServiceProviderClass($app)
    {
        return PresenterServiceProvider::class;
    }
}
