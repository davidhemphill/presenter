# Hemp Presenter

This package makes it fast, fun, and profitable to decorate your Eloquent models for presentation in views, responses, pdfs, csv files, or anywhere you want.

## Installation

Install the package via composer:

```
composer require hemp/presenter
```

Add this macro in a Service Provider somewhere in your project:

```php
Collection::macro('present', function ($class) {
    return $this->map(function ($object) use ($class) {
        return present($object, $class);
    });
});
```

## Usage

Use the `present` helper:

```php
$user = User::first();
$presentedUser = present($user, ApiPresenter::class);
```

Or use the `Presentable` trait on your model and call `present` on it

```php
$presentedUser = User::first()->present(ApiPresenter::class);
```

Use the `present` macro on the Collection class:

```php
$presentedUsers = User::all()->present(ApiPresenter::class);
```

## Other
Presented models can be converted to JSON and array format just like the Eloquent models they wrap.

```php
$presentedUsersJson = User::all()->present(ApiPresenter::class)->toJson();
```

