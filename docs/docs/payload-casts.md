---
sidebar_position: 6
title: Payload Casts
description: Decode setting payloads as arrays, custom cast values, or Spatie Laravel Data objects.
---

[← Configuration](configuration.md) · [Back to README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [API Reference →](api-reference.md)

# Payload Casts

## Default JSON values

Without a custom cast, the package JSON-encodes non-blank values when writing and returns decoded
arrays or scalar values when reading.

```php
$user->settings()->set('notifications', [
    'email' => true,
    'push' => false,
]);

$notifications = $user->settings()->get('notifications');
```

Values must be JSON-serializable. JSON encoding errors are not suppressed.

## Cast selection

Custom casts are configured by parent model class:

```php
'casts' => [
    App\Models\User::class => App\Casts\UserSettingsPayloadCast::class,
],
```

One configured cast handles every setting payload owned by that parent model class. Laravel morph
map aliases are resolved back to the model class before the cast is selected.

## Eloquent attribute cast

The cast may implement Laravel's `CastsAttributes` contract:

```php
namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

final class UserSettingsPayloadCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        return (array) $value;
    }
}
```

The package creates this cast without constructor arguments. Its `set()` result must remain
JSON-serializable.

## Spatie Laravel Data

When `spatie/laravel-data` is installed, a `Data` class can be used directly:

```bash
composer require spatie/laravel-data:^4.23
```

```php
'casts' => [
    App\Models\User::class => App\Data\UserSettingsData::class,
],
```

Pass either data accepted by the class or a `Data` instance to `set()`. `get()` and `all()` then
return instances created by that data class.

```php
$preferences = UserSettingsData::from([
    'timezone' => 'Europe/Paris',
    'notifications' => true,
]);

$user->settings()->set('preferences', $preferences);

$preferences = $user->settings()->get('preferences');
```

Because a cast is selected per parent model class rather than per key, every payload for that model
must be valid input for the configured cast.

## See Also

- [Configuration](configuration.md) — register casts and replace the storage model.
- [Working with Settings](settings.md) — see which values are removed as blank.
- [API Reference](api-reference.md) — check the return types of `get()` and `all()`.
