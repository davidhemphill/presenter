# Hemp Presenter

[![Codeship Status for davidhemphill/presenter](https://app.codeship.com/projects/2ef68e40-fcaa-0136-86ee-2eee2779cdfb/status?branch=master)](https://app.codeship.com/projects/322407)

This package makes it fast, fun, and profitable to decorate your Eloquent models for presentation in views, PDFs, CSV files, or anywhere else in your project.

For a little primer on the problems presenters solve, take a look at this article: [Presenters in Laravel](https://davidhemphill.com/presenters-in-laravel/).


## Installation

Install the package via [Composer](https://getcomposer.org/):

```
composer require hemp/presenter
```

Then register the Service Provider by adding it to the `providers` array in `config/app.php`:

```php
'providers' => [
    // ...
    Hemp\Presenter\PresenterServiceProvider::class,
],
```

**Note**: In Laravel 5.5+, the `PresenterServiceProvider` should be auto-discovered, so you won't need to register it.

## Creating `Presenter` Classes

You can easily generate a `Presenter` class by calling the `make:presenter` Artisan command:

```sh
php artisan make:presenter ApiPresenter
```

This will generate an empty `Presenter` class inside of `app/Presenters`.

## Customizing `Presenter` Classes

At their core, presenters are simple classes designed to encapsulate complex or repetitive view logic. What makes `hemp/presenter` nice is it allows you to attach methods and magic accessors to these `Presenter` objects. For example, take this `ApiPresenter` class:

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

This class has a custom method (`createdDate`) that can be called wherever this `Presenter` is used. It also has a magic accessor `getFullNameAttribute` that will be accessible via the `Presenter` like so: `$user->full_name`. This works exactly like Eloquent's magic accessors...when the `Presenter` is serialized into a response (like for a view or API response), these magic accessors will be called an added to the rendered output.

This `Presenter` might output something like this:

```json
{
    "id": 1, 
    "first_name": "David",
    "last_name": "Hemphill",
    "created_at": "2016-10-14 12:00:00",
    "updated_at": "2016-12-14 12:00:00",
    "full_name": "David Lee Hemphill" // The magic accessor
}
```

Once you have a presented model instance (like inside a Blade view), you can use magic accessors like this:

```php
$presentedUser->full_name;
```

Or use the methods available on the `Presenter` itself:

```php
$presentedUser->createdAt();
```

When outputting the `Presenter` to and `array` or JSON, if you'd like each of the rendered attributes to use `camelCase` formatting instead of the default `snake_case` formatting, you can set the `snakeCase` property on your `Presenter` to `false`:

```php
class ApiPresenter extends Presenter
{
    public $snakeCase = false;
}
```

This will cause the rendered output to look like this:

```json
{
    "id": 1, 
    "firstName": "David",
    "lastName": "Hemphill",
    "createdAt": "2016-10-14 12:00:00",
    "updatedAt": "2016-12-14 12:00:00",
    "fullName": "David Lee Hemphill"
}
```

You might like this option if your front-end JavaScript style guide uses mostly camelCased variables.

## Presenting Single Models

There are a number of different ways you can present your `Model` objects, depending on your personal preferences. For instance, you can use the `make` factory method of the `Presenter` class:

```php
$user = User::first();
$presentedUser = Presenter::make($user, ApiPresenter::class);
```

You can also call the `make` method on any of your custom `Presenter` classes, without passing the second argument:

```php
$user = User::first();
$presentedUser = ApiPresenter::make($user);
```

You may also use the `present` global function, if that's your jam:

```php
$user = User::first();
$presentedUser = present($user, ApiPresenter::class); 
```

Or you can use the `Presentable` trait on your `Model`. This will allow you to call `present` on it directly:

```php
$presentedUser = User::first()->present(ApiPresenter::class);
```

Also, when using the `Presentable` trait, you can specify a default presenter using the `defaultPresenter` attribute on the Model and call `present` to use it:

```php
class User extends \Illuminate\Database\Eloquent\Model
{
    public $defaultPresenter = App\Presenters\ApiPresenter::class,
}

$presentedUser = User::first()->present();
```

## Presenting Collections

You can also create a collection of presented `Model` objects. One way is to use the static `collection` method on the `Presenter` class to present an array of `Model` objects:

```php
$users = User::all();
$presenter = Presenter::collection($users, ApiPresenter::class);
```

You can also use the static `collection` method on any of your custom `Presenter` classes directly without passing the second argument:

```php
$users = User::all();
$presenter = ApiPresenter::collection($users);
```

You may also use the `present` macro on a Collection object:

```php
$presentedUsers = User::all()->present(ApiPresenter::class);
```

## Hiding Model Attributes From Output

There are times you may wish to keep certain keys from being rendered inside your `Presenter`. You can use the `hide` property on the `Presenter` to keep any default `Model` attributes from being used in the output:

```php
<?php

namespace App\Presenters;

use Hemp\Presenter\Presenter;

class ApiPresenter extends Presenter
{
    protected $hidden = ['first_name', 'last_name'];
}
```

This will keep the underlying `Model` instance's `first_name` and `last_name` attributes from showing in the final output.

You may also specify the `visible` property on the `Presenter` to act as a whitelist of attributes that should be shown in the output. 

```php
<?php

namespace App\Presenters;

use Hemp\Presenter\Presenter;

class ApiPresenter extends Presenter
{
    protected $visible = ['subscribed'];
}
```

**Note**: If a key is specified in both the `hidden` and `visible` properties, then it will be assumed that you want it to be visible in the rendered output.

## Support

If you're using this package, I would love to know about it!

If you're having trouble getting something to work when using this package, contact me on [Twitter](https://twitter.com/davidhemphill). I'd be glad to help.

If you believe you have found an bug, improvement, or other issue, please report it using the [GitHub issue tracker](https://github.com/davidhemphill/presenter/issues), or fork the repository and submit a pull request.