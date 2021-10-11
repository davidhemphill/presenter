<?php

namespace Hemp\Presenter\Tests;

use BadMethodCallException;
use Hemp\Presenter\Presenter;
use Hemp\Presenter\Tests\Fixtures\CamelCaseAttributesPresenter;
use Hemp\Presenter\Tests\Fixtures\HiddenAndVisibleAttributesPresenter;
use Hemp\Presenter\Tests\Fixtures\HiddenAttributesPresenter;
use Hemp\Presenter\Tests\Fixtures\User;
use Hemp\Presenter\Tests\Fixtures\UserProfilePresenter;
use Hemp\Presenter\Tests\Fixtures\UserWithDefaultPresenter;
use Hemp\Presenter\Tests\Fixtures\VisibleAttributesPresenter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PresenterTest extends IntegrationTest
{
    /** @test */
    public function can_get_the_original_model()
    {
        $user = factory(User::class)->create();
        $presenter = new class($user) extends Presenter
        {
        };
        $this->assertSame($user, $presenter->getModel());
    }

    /** @test */
    public function its_own_methods_take_priority()
    {
        $user = factory(User::class)->create();
        $presenter = new class($user) extends Presenter
        {
            public function middleName()
            {
                return 'Isles';
            }
        };

        $this->assertEquals('Isles', $presenter->middleName());
    }

    /** @test */
    public function delegates_undefined_method_calls_to_the_underlying_model_instance()
    {
        $user = factory(User::class)->create();
        $presenter = new class($user) extends Presenter
        {
        };
        $this->assertEquals('Hello from the Model!', $presenter->sayHello());
    }

    /** @test */
    public function delegates_magic_properties_to_the_presenter()
    {
        $user = factory(User::class)->create();
        $presenter = new class($user) extends Presenter
        {
            public function getSayHelloAttribute()
            {
                return 'Hello from the Presenter!';
            }
        };

        $this->assertEquals('Hello from the Presenter!', $presenter->say_hello);
    }

    /** @test */
    public function can_be_converted_to_an_array()
    {
        Carbon::setTestNow(Carbon::parse('Oct 14 2019'));

        $user = factory(User::class)->create([
            'name' => 'David Hemphill',
            'email' => 'david@laravel.com',
            'created_at' => Carbon::now()->subDays(4),
            'updated_at' => Carbon::now(),
        ]);

        $presenter = new class($user) extends Presenter
        {
            public function getCreatedAtAttribute($model)
            {
                return $model->created_at->format('M j, Y');
            }

            public function getUpdatedAtAttribute($model)
            {
                return $model->updated_at->format('M j, Y');
            }
        };

        $this->assertEquals([
            'id' => $user->getKey(),
            'name' => 'David Hemphill',
            'email' => 'david@laravel.com',
            'created_at' => 'Oct 10, 2019',
            'updated_at' => 'Oct 14, 2019',
        ], $presenter->toArray());
    }

    /** @test */
    public function can_be_converted_to_json()
    {
        Carbon::setTestNow(Carbon::parse('Oct 14 2019'));

        $user = factory(User::class)->create([
            'name' => 'David Hemphill',
            'email' => 'david@laravel.com',
            'created_at' => Carbon::now()->subDays(4),
            'updated_at' => Carbon::now(),
        ]);

        $presenter = new class($user) extends Presenter
        {
            public function getCreatedAtAttribute($model)
            {
                return $model->created_at->format('M j, Y');
            }

            public function getUpdatedAtAttribute($model)
            {
                return $model->updated_at->format('M j, Y');
            }
        };

        $this->assertEquals(json_encode([
            'name' => 'David Hemphill',
            'email' => 'david@laravel.com',
            'created_at' => 'Oct 10, 2019',
            'updated_at' => 'Oct 14, 2019',
            'id' => $user->getKey(), // The ID is always the last key
        ]), $presenter->toJson());
    }

    /** @test */
    public function can_be_converted_to_a_string()
    {
        Carbon::setTestNow(Carbon::parse('Oct 14 2019'));

        $user = factory(User::class)->create([
            'name' => 'David Hemphill',
            'email' => 'david@laravel.com',
            'created_at' => Carbon::now()->subDays(4),
            'updated_at' => Carbon::now(),
        ]);

        $presenter = new class($user) extends Presenter
        {
            public function getCreatedAtAttribute($model)
            {
                return $model->created_at->format('M j, Y');
            }

            public function getUpdatedAtAttribute($model)
            {
                return $model->updated_at->format('M j, Y');
            }
        };

        $this->assertEquals(json_encode([
            'name' => 'David Hemphill',
            'email' => 'david@laravel.com',
            'created_at' => 'Oct 10, 2019',
            'updated_at' => 'Oct 14, 2019',
            'id' => $user->getKey(), // The ID is always the last key
        ]), (string) $presenter);
    }

    /** @test */
    public function can_call_present_on_an_eloquent_model_using_the_trait()
    {
        $user = factory(User::class)->create()->present(UserProfilePresenter::class);
        $this->assertInstanceOf(UserProfilePresenter::class, $user);
    }

    /** @test */
    public function can_call_present_on_an_eloquent_model_using_the_trait_and_use_default_presenter()
    {
        $user = factory(UserWithDefaultPresenter::class)->create()->present();
        $this->assertInstanceOf(UserProfilePresenter::class, $user);
    }

    /** @test */
    public function throws_if_theres_no_default_presenter_and_none_is_passed_in()
    {
        $this->expectException(BadMethodCallException::class);
        factory(User::class)->create()->present(null);
    }

    /** @test */
    public function can_use_a_helper_function_to_decorate_a_model()
    {
        $user = present(factory(User::class)->create(), UserProfilePresenter::class);
        $this->assertInstanceOf(UserProfilePresenter::class, $user);
    }

    /** @test */
    public function can_present_a_model_using_a_closure()
    {
        $presenter = factory(User::class)->create(['name' => 'David'])->present(function ($user) {
            return ['name' => strtolower($user->name)];
        });

        $this->assertEquals('david', $presenter->name);
    }

    /** @test */
    public function can_present_an_object_that_is_not_a_model()
    {
        $notAModel = new class
        {
            public $name = 'david';

            public function fullName()
            {
                return 'David Hemphill';
            }
        };

        $presenter = new class($notAModel) extends Presenter
        {
        };

        $this->assertEquals('david', $presenter->name);
        $this->assertEquals('David Hemphill', $presenter->fullName());
    }

    /** @test */
    public function can_present_a_collection_of_eloquent_models()
    {
        $user = factory(User::class)->create();
        $users = collect([$user])->present(UserProfilePresenter::class);

        $users->each(function ($user) {
            $this->assertInstanceOf(UserProfilePresenter::class, $user);
        });
    }

    /** @test */
    public function can_present_a_collection_of_models_using_a_closure()
    {
        $user = factory(User::class)->create(['name' => 'David Hemphill']);
        $users = collect([$user])->present(function ($user) {
            return ['name' => strtolower($user->name)];
        });

        $firstUser = $users->first();
        $this->assertNotNull($firstUser);
        $this->assertEquals('david hemphill', $firstUser->name);
    }

    /** @test */
    public function can_create_presenters_using_the_make_method()
    {
        $user = factory(User::class)->create(['name' => 'David Hemphill']);
        $presenter = Presenter::make($user, UserProfilePresenter::class);

        $this->assertInstanceOf(UserProfilePresenter::class, $presenter);
    }

    /** @test */
    public function can_call_make_on_the_presenter_itself()
    {
        $user = factory(User::class)->create(['name' => 'David Hemphill']);
        $presenter = UserProfilePresenter::make($user);

        $this->assertInstanceOf(UserProfilePresenter::class, $presenter);
    }

    /** @test */
    public function can_present_a_collection_of_models_using_collection_method()
    {
        $user1 = factory(User::class)->create(['name' => 'David Hemphill']);
        $user2 = factory(User::class)->create(['name' => 'David Hemphill']);

        $collection = Presenter::collection([$user1, $user2], UserProfilePresenter::class);

        $this->assertInstanceOf(Collection::class, $collection);
    }

    /** @test */
    public function can_present_a_collection_of_models_using_collection_method_on_the_presenter_itself()
    {
        $user1 = factory(User::class)->create(['name' => 'David Hemphill']);
        $user2 = factory(User::class)->create(['name' => 'David Hemphill']);

        $collection = UserProfilePresenter::collection([$user1, $user2]);

        $this->assertInstanceOf(Collection::class, $collection);
    }

    /** @test */
    public function can_camel_case_the_attributes_instead_of_snake_casing_them()
    {
        Carbon::setTestNow(Carbon::parse('Oct 14 2019'));

        $presenter = factory(User::class)
            ->create(['name' => 'David Hemphill', 'email' => 'david@laravel.com'])
            ->present(CamelCaseAttributesPresenter::class);

        $this->assertEquals([
            'firstName' => 'David',
            'lastName' => 'Hemphill',
            'name' => 'David Hemphill',
            'email' => 'david@laravel.com',
            'id' => 1,
            'updatedAt' => '2019-10-14 00:00:00',
            'createdAt' => '2019-10-14 00:00:00',
        ], $presenter->toArray());
    }

    /** @test */
    public function can_set_the_casing_strategy_at_runtime()
    {
        $presenter = factory(User::class)
            ->create(['name' => 'David Hemphill', 'email' => 'david@laravel.com'])
            ->present(UserProfilePresenter::class);

        $this->assertTrue($presenter->snakeCase);

        $presenter->camelCase();

        $this->assertFalse($presenter->snakeCase);

        $this->assertEquals(
            ['name', 'email', 'updatedAt', 'createdAt', 'id'],
            array_keys($presenter->toArray())
        );

        $presenter->snakeCase();

        $this->assertTrue($presenter->snakeCase);

        $this->assertEquals(
            ['name', 'email', 'updated_at', 'created_at', 'id'],
            array_keys($presenter->toArray())
        );
    }

    /** @test */
    public function a_collection_of_presented_eloquent_models_will_still_return_json()
    {
        factory(User::class)->create(['name' => 'David Hemphill']);

        $response = $this
            ->withoutExceptionHandling()
            ->json('GET', '/users')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json');

        $this->assertEquals('David Hemphill', $response->original[0]->full_name);
    }

    /** @test */
    public function can_paginate_a_presented_collection()
    {
        factory(User::class)->create(['name' => 'David Hemphill']);
        factory(User::class)->create(['name' => 'Taylor Otwell']);

        $response = $this
            ->withoutExceptionHandling()
            ->json('GET', '/paginated?page=2')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json');

        $this->assertEquals('Taylor Otwell', $response->original[0]->full_name);
    }

    /** @test */
    public function presenter_removes_hidden_model_attributes_from_output()
    {
        $presenter = factory(User::class)
            ->create(['name' => 'David Hemphill', 'email' => 'david@laravel.com'])
            ->present(HiddenAttributesPresenter::class);

        $this->assertEquals([
            'name' => 'David Hemphill',
            'email' => 'david@laravel.com',
        ], $presenter->toArray());
    }

    /** @test */
    public function presenter_removes_hidden_attributes_and_leaves_visible_model_attributes_in_output()
    {
        $presenter = factory(User::class)
            ->create(['name' => 'David Hemphill', 'email' => 'david@laravel.com'])
            ->present(HiddenAndVisibleAttributesPresenter::class);

        $this->assertEquals([
            'name' => 'David Hemphill',
        ], $presenter->toArray());
    }

    /** @test */
    public function supports_offset_exists_via_array_access()
    {
        $presenter = factory(User::class)
            ->create(['name' => 'David Hemphill', 'email' => 'david@laravel.com'])
            ->present(HiddenAndVisibleAttributesPresenter::class);

        $this->assertTrue(isset($presenter['name']));
    }

    /** @test */
    public function presenter_leaves_visible_model_attributes_in_output()
    {
        $presenter = factory(User::class)
            ->create(['name' => 'David Hemphill', 'email' => 'david@laravel.com'])
            ->present(VisibleAttributesPresenter::class);

        $this->assertEquals([
            'id' => 1,
            'email' => 'david@laravel.com',
        ], $presenter->toArray());
    }

    /** @test */
    public function can_be_array_accessed()
    {
        $presenter = factory(User::class)
            ->create(['name' => 'David Hemphill', 'email' => 'david@laravel.com'])
            ->present(UserProfilePresenter::class);

        $this->assertEquals('David Hemphill', $presenter['name']);
    }

    /** @test */
    public function cannot_be_written_to_via_array_access()
    {
        $this->expectException(BadMethodCallException::class);

        $presenter = factory(User::class)
            ->create(['name' => 'David Hemphill', 'email' => 'david@laravel.com'])
            ->present(HiddenAndVisibleAttributesPresenter::class);

        $presenter['email'] = 'david@monarkee.com';
    }

    /** @test */
    public function output_keys_cannot_be_unset_via_array_access()
    {
        $this->expectException(BadMethodCallException::class);

        $presenter = factory(User::class)
            ->create(['name' => 'David Hemphill', 'email' => 'david@laravel.com'])
            ->present(HiddenAndVisibleAttributesPresenter::class);

        unset($presenter['email']);
    }
}
