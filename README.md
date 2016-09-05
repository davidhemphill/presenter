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
// You can use magic getters like this:
$presentedUser->full_name;
// Or use regular old methods:
$presentedUser->createdAt();
```

Or use the `Presentable` trait on your model and call `present` on it:

```php
$presentedUser = User::first()->present(ApiPresenter::class);
```

Use the `present` macro on a Collection object:

```php
$presentedUsers = User::all()->present(ApiPresenter::class);
```

## Other
Presented models can be converted to JSON and array format just like the Eloquent models they wrap.

```php
$presentedUsersJson = User::all()->present(ApiPresenter::class)->toJson();
```
