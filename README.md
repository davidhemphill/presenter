# Hemp Presenter

This package makes it fast, fun, and profitable to decorate your Eloquent models for presentation in views, responses, pdfs, csv files, or anywhere you want.

The idea for this package is explained in this post: [Presenters in Laravel](http://davidhemphill.com/blog/2016/09/06/presenters-in-laravel/).

### Key differences with other presenter packages

- Supports multiple decorators/presenters
- Decorated models can still be converted to JSON/array with `toJson` and `toArray` or by simply returning it from a controller
- Supports mutators/magic getters like the ones used in Eloquent (e.g. getFullNameAttribute())

## Installation

Install the package via composer:

```
composer require hemp/presenter
```

Add this macro to the `register` method of a Service Provider anywhere in your project:

```php
public function register()
{
    Collection::macro('present', function ($class) {
        return $this->map(function ($object) use ($class) {
            return present($object, $class);
        });
    });
}
```

Or add the Service Provider to the `providers` array in `config/app.php`:

```php
'providers' => [
    // ...

    Hemp\Presenter\PresenterServiceProvider::class,
],
```

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

## Usage

Use the `present` helper:

```php
$user = User::first();
$presentedUser = present($user, ApiPresenter::class);
```

Or use the `Presentable` trait on your model and call `present` on it:

```php
$presentedUser = User::first()->present(ApiPresenter::class);
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

## Other

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
