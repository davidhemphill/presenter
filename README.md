# Hemp Presenter

This package makes it fast, fun, and profitable to decorate your Eloquent models for presentation in views, responses, pdfs, csv files, or anywhere you want.

## Installation

1. Install the package via composer:

`composer require hemp/presenter`

2. Add this macro in a Service Provider somewhere in your project

```php
Collection::macro('present', function ($class) {
    return $this->map(function ($object) use ($class) {
        return present($object, $class);
    });
});
```

### Usage

1. Use the `present` helper
```php
$user = User::first();
$presentedUser = present($user, ApiPresenter::class);
```

2. Use the `present` macro on the Collection class

```php
$presentedUsers = User::all()->present(ApiPresenter::class);
```