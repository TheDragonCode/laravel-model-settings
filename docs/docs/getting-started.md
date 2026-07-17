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

The `model_settings` tag publishes both `config/model_settings.php` and the package migration. The
default migration creates a `settings` table on the application's default database connection.

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

The trait adds this public surface:

| Member | Use |
|--------|-----|
| `settings()` | Read or change effective settings for one saved model |
| `defaultSettings()` | Read or change defaults for the model class |
| `modelSettings()` | Eloquent relation used for eager loading |

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

Read all effective settings as a collection keyed by setting name:

```php
$settings = $user->settings()->all();

assert($settings->get('timezone') === 'Europe/Paris');
```

Remove the override to fall back to `UTC`:

```php
$user->settings()->forget('timezone');

assert($user->settings()->get('timezone') === 'UTC');
```

## Persist models first

Use `settings()->set()` only after the parent model has been saved. An unsaved model has no primary
key. Its `settings()->get()` returns `null`, and `settings()->all()` returns an empty collection even
when class defaults exist.

## See Also

- [Working with Settings](settings.md) — learn precedence, deletion, keys, and values.
- [Configuration](configuration.md) — select the connection, table, or storage model.
- [Eager Loading](eager-loading.md) — load settings efficiently for model collections.
