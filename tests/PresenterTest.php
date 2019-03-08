<?php

namespace Hemp\Presenter\Tests;

use BadMethodCallException;
use Hemp\Presenter\Presenter;
use Illuminate\Support\Carbon;
use Hemp\Presenter\Tests\Fixtures\User;
use Hemp\Presenter\Tests\Fixtures\UserProfilePresenter;
use Hemp\Presenter\Tests\Fixtures\UserWithDefaultPresenter;
use Hemp\Presenter\Tests\Fixtures\CamelCaseAttributesPresenter;

class PresenterTest extends IntegrationTest
{
    /** @test */
    public function you_can_get_the_original_model()
    {
        $user = factory(User::class)->create();
        $presenter = new class($user) extends Presenter {
        };
        $this->assertSame($user, $presenter->getModel());
    }

    /** @test */
    public function its_own_methods_take_priority()
    {
        $user = factory(User::class)->create();
        $presenter = new class($user) extends Presenter {
            public function middleName()
            {
                return 'Isles';
            }
        };

        $this->assertEquals('Isles', $presenter->middleName());
    }

    /** @test */
    public function it_delegates_undefined_method_calls_to_the_underlying_model_instance()
    {
        $user = factory(User::class)->create();
        $presenter = new class($user) extends Presenter {
        };
        $this->assertEquals('Hello from the Model!', $presenter->sayHello());
    }

    /** @test */
    public function it_delegates_magic_properties_to_the_presenter()
    {
        $user = factory(User::class)->create();
        $presenter = new class($user) extends Presenter {
            public function getSayHelloAttribute()
            {
                return 'Hello from the Presenter!';
            }
        };

        $this->assertEquals('Hello from the Presenter!', $presenter->say_hello);
    }

    /** @test */
    public function a_model_can_be_converted_to_an_array()
    {
        Carbon::setTestNow(Carbon::parse('Oct 14 2019'));

        $user = factory(User::class)->create([
            'name' => 'David Hemphill',
            'email' => 'david@laravel.com',
            'created_at' => Carbon::now()->subDays(4),
            'updated_at' => Carbon::now(),
        ]);

        $presenter = new class($user) extends Presenter {
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
    public function a_model_can_be_converted_to_json()
    {
        Carbon::setTestNow(Carbon::parse('Oct 14 2019'));

        $user = factory(User::class)->create([
            'name' => 'David Hemphill',
            'email' => 'david@laravel.com',
            'created_at' => Carbon::now()->subDays(4),
            'updated_at' => Carbon::now(),
        ]);

        $presenter = new class($user) extends Presenter {
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
    public function a_model_can_be_converted_to_a_string()
    {
        Carbon::setTestNow(Carbon::parse('Oct 14 2019'));

        $user = factory(User::class)->create([
            'name' => 'David Hemphill',
            'email' => 'david@laravel.com',
            'created_at' => Carbon::now()->subDays(4),
            'updated_at' => Carbon::now(),
        ]);

        $presenter = new class($user) extends Presenter {
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
    public function you_can_call_present_on_an_eloquent_model_using_the_trait()
    {
        $user = factory(User::class)->create()->present(UserProfilePresenter::class);
        $this->assertInstanceOf(UserProfilePresenter::class, $user);
    }

    /** @test */
    public function you_can_call_present_on_an_eloquent_model_using_the_trait_and_use_default_presenter()
    {
        $user = factory(UserWithDefaultPresenter::class)->create()->present();
        $this->assertInstanceOf(UserProfilePresenter::class, $user);
    }

    /** @test */
    public function it_throws_if_theres_no_default_presenter_and_none_is_passed_in()
    {
        $this->expectException(BadMethodCallException::class);
        factory(User::class)->create()->present(null);
    }

    /** @test */
    public function you_can_use_a_helper_function_to_decorate_a_model()
    {
        $user = present(factory(User::class)->create(), UserProfilePresenter::class);
        $this->assertInstanceOf(UserProfilePresenter::class, $user);
    }

    /** @test */
    public function you_can_present_a_model_using_a_closure()
    {
        $presenter = factory(User::class)->create(['name' => 'David'])->present(function ($user) {
            return ['name' => strtolower($user->name)];
        });

        $this->assertEquals('david', $presenter->name);
    }

    /** @test */
    public function you_can_present_a_collection_of_eloquent_models()
    {
        $user = factory(User::class)->create();
        $users = collect([$user])->present(UserProfilePresenter::class);

        $users->each(function ($user) {
            $this->assertInstanceOf(UserProfilePresenter::class, $user);
        });
    }

    /** @test */
    // public function you_can_access_a_collection_of_eloquent_models()
    // {
    // $user = factory(User::class)->create(['name' => 'David Hemphill']);
    // $users = collect([$user])->present(UserProfilePresenter::class);

    // $this->fail("I'm not sure why this test is here.");
    // dd($users->first()->pluck('name'));
    // dd($users);
    // dd($users->pluck('name'));
    // $this->assertEquals(
    // collect(['David']),
    // $users->pluck('name')
    // );

    // $this->assertEquals(
    //     collect(['David Lee Hemphill']),
    //     $users->pluck('full_name'));
    // }

    /** @test */
    public function you_can_present_a_collection_of_models_using_a_closure()
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
    public function it_can_camel_case_the_attributes_instead_of_snake_casing_them()
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
    public function you_can_paginate_a_presented_collection()
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
    public function a_presenter_can_specify_attributes_to_hide_from_json_or_array_output()
    {
        $this->fail('Not implemented');
    }

    /** @test */
    public function a_presenter_can_specify_attributes_to_show_in_json_or_array_output()
    {
        $this->fail('Not implemented');
    }

    /** @test */
    public function it_can_be_array_accessed()
    {
        $this->fail('Not implemented');
    }

    /** @test */
    public function it_cannot_be_written_to_via_array_access()
    {
        $this->fail('Not implemented');
    }
}
