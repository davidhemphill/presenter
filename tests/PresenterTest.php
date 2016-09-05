<?php

use Hemp\Presenter;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager;

class PresenterTest extends PHPUnit_Framework_TestCase
{
    function setupDatabase()
    {
        $capsule = new Manager;

        $capsule->addConnection([
            'driver'    => 'sqlite',
            'host'      => 'localhost',
            'database'  => ':memory:',
            'username'  => '',
            'password'  => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        Manager::schema()->create('test_models', function($table) {
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->timestamps();
        });
    }

    function registerCollectionMacro()
    {
        Collection::macro('present', function ($class) {
            return $this->map(function ($object) use ($class) {
                return present($object, $class);
            });
        });
    }

    function setUp()
    {
        parent::setUp();
        $this->setUpDatabase();
        $this->registerCollectionMacro();
    }

    function createModel()
    {
        return new TestModel([
            'first_name' => 'David',
            'last_name' => 'Hemphill',
        ]);
    }

    /** @test */
    function it_decorates_objects()
    {
        $sampleModel = $this->createModel();
        $presenter = new SamplePresenter($sampleModel);
    }

    /** @test */
    function you_can_get_the_decorated_model()
    {
        $sampleModel = $this->createModel();
        $presenter = new SamplePresenter($sampleModel);

        $this->assertSame($sampleModel, $presenter->getModel());
    }

    /** @test */
    function it_can_have_its_own_methods()
    {
        $sampleModel = $this->createModel();
        $presenter = new SamplePresenter($sampleModel);

        $this->assertEquals('David Hemphill', $presenter->name());
    }

    /** @test */
    function it_can_call_the_decorated_objects_methods()
    {
        $sampleModel = $this->createModel();
        $presenter = new SamplePresenter($sampleModel);

        $this->assertEquals(90210, $presenter->timeStamp());
    }

    /** @test */
    function it_can_return_the_decorated_objects_properties()
    {
        $sampleModel = $this->createModel();
        $presenter = new SamplePresenter($sampleModel);

        $this->assertEquals('David', $presenter->first_name);
    }

    /** @test */
    function it_can_overload_the_decorated_objects_methods()
    {
        $sampleModel = $this->createModel();
        $presenter = new SamplePresenter($sampleModel);

        $this->assertEquals('This is your decorator speaking', $presenter->overloadedMethod());
    }

    /** @test */
    function it_can_have_its_own_magic_properties()
    {
        $sampleModel = $this->createModel();
        $presenter = new SamplePresenter($sampleModel);

        $this->assertEquals('David Lee Hemphill', $presenter->full_name);
    }

    /** @test */
    function you_can_wrap_a_collection_of_eloquent_models()
    {
        $sampleModel = $this->createModel();

        $users = collect([$sampleModel])->present(SamplePresenter::class);

        $firstUser = $users->first();

        $this->assertNotNull($firstUser);
        $this->assertEquals('David Hemphill', $firstUser->name());
        $this->assertEquals('David Lee Hemphill', $firstUser->full_name);
    }
}

class TestModel extends Model
{
    protected $guarded = [];

    public function overloadedMethod()
    {
        return 'This is the original method!';
    }

    public function timeStamp()
    {
        return 90210;
    }
}

class SamplePresenter extends Presenter
{
    public function overloadedMethod()
    {
        return 'This is your decorator speaking';
    }

    public function name()
    {
        return $this->model->first_name . ' ' . $this->model->last_name;
    }

    public function getFullNameAttribute()
    {
        return $this->model->first_name . ' Lee ' . $this->model->last_name;
    }
}
