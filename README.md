# Hemp Presenter

This package makes it fast, fun, and profitable to decorate your Eloquent models for presentation in views, responses, pdfs, csv files, or anywhere you want.

The idea for this package is explained in this post: [Presenters in Laravel](https://davidhemphill.com/presenters-in-laravel/).

### Key differences with other presenter packages

- Decorated models can still be converted to JSON/array with `toJson` and `toArray` or by simply returning it from a controller
- Supports accessors/magic getters like the ones used in Eloquent (e.g. `getFullNameAttribute`)

## Installation

Install the package via composer:

```
composer require hemp/presenter
```

Presenter adds a `present` Collection macro whichs allows you to present a group of models. To use this, add the Service Provider to the `providers` array in `config/app.php`:

```php
'providers' => [
    // ...

    Hemp\Presenter\PresenterServiceProvider::class,
],
```

In Laravel 5.5+, the `PresenterServiceProvider` should be auto-discovered, so you won't need to register it.

## Create a `Presenter`

```php
<?php

namespace App\Presenters;

use Hemp\Presenter\Presenter;

class ApiPresenter extends Presenter
{
    public function createdDate() {
        return $this->model->created_at->format('n/j/Y');
    }

    public function getFullNameAttribute()
    {
        return trim($this->model->first_name . ' ' . $this->model->last_name);
    }
}
```

## Presenting Single Models

Use the `make` factory method of the `Presenter` class:

```php
$user = User::first();
$presentedUser = Presenter::make($user, ApiPresenter::class);
```

You can also call the `make` method on any of your custom `Presenter` classes, without passing the second argument:

```php
$user = User::first();
$presentedUser = ApiPresenter::make($user);
```

Use the `present` helper:

```php
$user = User::first();
$presentedUser = present($user, ApiPresenter::class);
```

Or use the `Presentable` trait on your model and call `present` on it:

```php
$presentedUser = User::first()->present(ApiPresenter::class);
```

Or, when using the `Presentable` trait, specify a default presenter using the `defaultPresenter` attribute on the Model and call `present` to use it:

```php
$presentedUser = User::first()->present();
```

## Presenting Collections

You can use the static `collection` method on the `Presenter` class to present an array of `Model` objects:

```php
$users = User::all();
$presenter = Presenter::collection($users, ApiPresenter::class);
```

You can also use the static `collection` method on any of your custom `Presenter` classes directly without a second argument:

```php
$users = User::all();
$presenter = ApiPresenter::collection($users);
```

Use the `present` macro on a Collection object:

```php
$presentedUsers = User::all()->present(ApiPresenter::class);
```

Once you have a presented model instance, you can use magic getters like this:

```php
$presentedUser->full_name;
```

Or use regular old methods:

```php
$presentedUser->createdAt();
```

## Converting Presenters to JSON

Individual instances can be returned as JSON just like you can with plain Eloquent models, except the mutators you specify on your `Presenter` will also be serialized with the output.

```php
public function show($id)
{
    return User::findOrFail($id)->present(ApiPresenter::class);
}

/*
// Outputs something like this
{
    "id":1,
    "full_name":"David Lee Hemphill",
    "first_name":"David",
    "last_name":"Hemphill",
    "created_at":"2016-10-14 12:00:00",
    "updated_at":"2016-12-14 12:00:00"
}
*/
```

A collection of presented models can be converted to JSON and array format just like normal.

```php
public function index()
{
    return User::all()->present(ApiPresenter::class);
}

/*
// Outputs something like this
[{
    "id":1,
    "full_name":"David Lee Hemphill",
    "first_name":"David",
    "last_name":"Hemphill",
    "created_at":"2016-10-14 12:00:00",
    "updated_at":"2016-12-14 12:00:00"
},
{
    "id":1,
    "full_name":"Tess Rowlett",
    "first_name":"Tess",
    "last_name":"Rowlett",
    "created_at":"2016-10-14 12:00:00",
    "updated_at":"2016-12-14 12:00:00"
}]
*/
```

### Hiding Attributes from array/JSON output

You can also specify `$visible` and `$hidden` properties on your Presenters. Setting `$visible` acts as a whitelist of attributes you want to appear in the array/JSON output. Setting `$hidden` acts as a blacklist of attributes you wish to be hidden from the array/JSON output. This will also remove or show any attributes from the model itself.

Using our example from earlier:

```php
<?php

namespace App\Presenters;

use Hemp\Presenter\Presenter;

class ApiPresenter extends Presenter
{
    protected $hidden = ['first_name', 'last_name'];

    public function createdDate() {
        return $this->model->created_at->format('n/j/Y');
    }

    public function getFullNameAttribute()
    {
        return trim($this->model->first_name . ' ' . $this->model->last_name);
    }
}
```

This will output something like this. Notice how the `first_name` and `last_name` attributes have been removed:

```

/*
{
    "id":1,
    "full_name":"David Lee Hemphill",
    "created_at":"2016-10-14 12:00:00",
    "updated_at":"2016-12-14 12:00:00"
}
*/
```

## Support

If you're using this package, I would love to know about it!

If you're having trouble getting something to work when using this package, contact me on [Twitter](https://twitter.com/davidhemphill). I'd be glad to help.

If you believe you have found an bug, improvement, or other issue, please report it using the [GitHub issue tracker](https://github.com/davidhemphill/presenter/issues), or fork the repository and submit a pull request.
