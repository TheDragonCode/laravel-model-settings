---
sidebar_position: 6
title: Payload Casts
description: Decode setting payloads as arrays, custom cast values, or Spatie Laravel Data objects.
---

[← Configuration](configuration.md) · [Back to README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [API Reference →](api-reference.md)

# Payload Casts

## Default JSON values

Without a custom cast, the package JSON-encodes every value when writing and returns the exact
decoded JSON value when reading. This includes `null`, empty strings, whitespace strings, empty
arrays, zero, and `false`.

```php
$user->settings()->set('notifications', [
    'email' => true,
    'push' => false,
]);

$notifications = $user->settings()->get('notifications');
```

Values must be JSON-serializable. JSON encoding errors are not suppressed.

## Cast selection

The legacy model-wide form applies one cast to every setting owned by a parent model class:

```php
'casts' => [
    App\Models\User::class => App\Casts\UserSettingsPayloadCast::class,
],
```

Use a key-aware map when only exact setting keys need custom handling:

```php
'casts' => [
    App\Models\User::class => [
        'profile' => App\Data\ProfileData::class,
        'billing.credentials' => App\Casts\EncryptedSettingPayload::class,
    ],
],
```

Laravel morph-map aliases are resolved back to the parent model class before selection. Key matching
uses the stored setting key, not the Eloquent attribute name `payload`. Dots are literal, so
`billing.credentials` is one key. Keys missing from a key-aware map use normal JSON.

A configured class must implement `CastsAttributes` or extend `Spatie\LaravelData\Data`. Invalid,
missing, unsupported, or container-unresolvable configured classes throw `InvalidPayloadCast`; the
package does not silently fall back to plain JSON for a configured entry.

## Cast lifecycle

For a `CastsAttributes` implementation, the package runs this sequence:

| Direction | Sequence |
|-----------|----------|
| Write | Call the custom `set()`, then JSON-encode its result |
| Read | Pass the stored JSON string to the custom `get()` |

The `$model` argument is the configured settings storage model, not the parent `User` or `Post`.
The package resolves `CastsAttributes` implementations through Laravel's container, so constructor
dependencies may use normal container bindings. Custom `set()` casts receive every input value,
including values that Laravel considers blank.

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

The custom `set()` result must remain JSON-serializable. JSON encoding errors are not suppressed.

## Per-key encryption

Encryption belongs in an application cast because the package schema has no encryption metadata or
key-rotation contract. This cast encrypts one setting key while leaving all other keys on the normal
JSON path:

```php
namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

final class EncryptedSettingPayload implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $ciphertext = Json::decode($value);

        return Json::decode(Crypt::decryptString((string) $ciphertext));
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return Crypt::encryptString(Json::encode($value));
    }
}
```

Register it for an exact literal key:

```php
'casts' => [
    App\Models\User::class => [
        'billing.credentials' => App\Casts\EncryptedSettingPayload::class,
    ],
],
```

```php
$user->settings()->set('billing.credentials', $credentials);

$credentials = $user->settings()->get('billing.credentials');
```

Do not log the value before or after the cast. If encryption keys may change, define and test an
application-level rotation policy before storing production data. Do not add metadata columns to the
package table without a separate storage contract that defines versioning and rotation.

## Spatie Laravel Data

When `spatie/laravel-data` is installed, a `Data` class can be used directly:

```bash
composer require spatie/laravel-data:^4.23
```

```php
'casts' => [
    App\Models\User::class => [
        'preferences' => App\Data\UserSettingsData::class,
    ],
],
```

Pass either data accepted by the class or a `Data` instance to `set()`. `get()` returns a data
instance, and `all()` returns a collection containing data instances.

```php
$preferences = UserSettingsData::from([
    'timezone' => 'Europe/Paris',
    'notifications' => true,
]);

$user->settings()->set('preferences', $preferences);

$preferences = $user->settings()->get('preferences');
```

Other keys for the same model continue to use normal JSON. Use the legacy model-wide form only when
every payload for that parent model is valid input for the configured data class.

## Cast errors

`DragonCode\LaravelModelSettings\Exceptions\InvalidPayloadCast` identifies the parent model class,
setting key, and configured cast when resolution fails. It never includes the payload. The exception
is thrown for both single and bulk writes, and for reads of persisted values using that configured
entry.

## See Also

- [Configuration](configuration.md) — register casts and replace the storage model.
- [Working with Settings](settings.md) — see how exact JSON values are stored and removed.
- [API Reference](api-reference.md) — check the return types of `get()` and `all()`.
