---
sidebar_position: 2
title: Getting Started
description: Install Laravel Model Settings and store the first default and override.
---

[← Overview](index.md) · [Back to README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Working with Settings →](settings.md)

# Getting Started

## Requirements

- PHP 8.3 or newer.
- Laravel 12 or 13.

## Install the package

```bash
composer require dragon-code/laravel-model-settings
```

Laravel discovers the package service provider automatically.

Publish the configuration and migration, then create the settings table:

```bash
php artisan vendor:publish --tag="model_settings"
php artisan migrate
```

The default migration creates a `settings` table on the application's default database connection.

## Add the trait

Add `HasSettings` to every Eloquent model that needs settings:

```php
namespace App\Models;

use DragonCode\LaravelModelSettings\Concerns\HasSettings;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasSettings;
}
```

The trait adds `settings()`, `defaultSettings()`, and the `modelSettings` Eloquent relation.

## Store the first setting

Create a default for all saved `User` models:

```php
(new User)->defaultSettings()->set('timezone', 'UTC');
```

Override that value for one saved user:

```php
$user = User::query()->firstOrFail();

$user->settings()->set('timezone', 'Europe/Paris');

assert($user->settings()->get('timezone') === 'Europe/Paris');
```

Remove the override to fall back to `UTC`:

```php
$user->settings()->forget('timezone');

assert($user->settings()->get('timezone') === 'UTC');
```

## Persist models first

Use `settings()->set()` only after the parent model has been saved. An unsaved model has no primary
key, does not inherit defaults, and returns an empty collection from `settings()->all()`.

## See Also

- [Working with Settings](settings.md) — learn precedence, deletion, keys, and values.
- [Configuration](configuration.md) — select the connection, table, or storage model.
- [Eager Loading](eager-loading.md) — load settings efficiently for model collections.
