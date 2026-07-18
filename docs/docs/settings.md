---
sidebar_position: 3
title: Working with Settings
description: Manage shared defaults, per-model overrides, setting keys, and values.
---

[← Getting Started](getting-started.md) · [Back to README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Eager Loading →](eager-loading.md)

# Working with Settings

The same service handles defaults and model values. The entry point determines which scope is read
or changed:

| Entry point | Scope |
|-------------|-------|
| `(new User)->defaultSettings()` | Defaults shared by saved `User` models |
| `$user->settings()` | Effective settings for one saved user |

## Shared defaults

Defaults apply to every saved model with the same Eloquent morph class:

```php
$defaults = (new User)->defaultSettings();

$defaults->set('timezone', 'UTC');
$defaults->set('notifications', ['email' => true]);
```

Read or remove defaults through the same service:

```php
$timezone = $defaults->get('timezone');
$all = $defaults->all();

$defaults->forget('timezone');
```

Defaults are independent for each model class.

## Per-model overrides

`set()` creates a setting or replaces its existing value:

```php
$user->settings()->set('timezone', 'Europe/Paris');
$user->settings()->set('timezone', 'America/Toronto');
```

Only the setting for that model is changed. Other models continue to use their own override or the
shared default.

`get()`, `has()`, and `all()` resolve values with the same precedence:

```php
$timezone = $user->settings()->get('timezone');
$hasTimezone = $user->settings()->has('timezone');
$settings = $user->settings()->all();
```

`all()` returns an `Illuminate\Support\Collection` keyed by setting key.

`get()` accepts only the key. It returns the model override, then the persistent class default, then
`null`. It does not accept a caller-supplied fallback value. `has()` distinguishes a missing key
from a stored JSON `null`:

```php
if ($user->settings()->has('timezone')) {
    $timezone = $user->settings()->get('timezone');
}
```

For example, one override replaces only the matching default:

```php
(new User)->defaultSettings()->set('timezone', 'UTC');
(new User)->defaultSettings()->set('locale', 'en');

$user->settings()->set('timezone', 'Europe/Paris');

assert($user->settings()->all()->sortKeys()->all() === [
    'locale' => 'en',
    'timezone' => 'Europe/Paris',
]);
```

## Remove a value

Removing a model override reveals the default:

```php
(new User)->defaultSettings()->set('timezone', 'UTC');

$user->settings()->set('timezone', 'Europe/Paris');
$user->settings()->forget('timezone');

assert($user->settings()->get('timezone') === 'UTC');
```

To remove the default itself, call `forget()` through `defaultSettings()`:

```php
(new User)->defaultSettings()->forget('timezone');
```

Calling `forget()` for a missing key has no effect.

## Bulk mutations

Use `setMany()` and `forgetMany()` when one scope needs several changes:

```php
$user->settings()->setMany([
    'timezone' => 'Europe/Paris',
    'locale' => 'fr',
]);

$user->settings()->forgetMany(['timezone', 'locale']);
```

Both methods accept any iterable. `setMany()` normalizes every key before writing. When multiple
input keys normalize to the same stored key, the last value wins. Every value is stored; only
`forget()` and `forgetMany()` delete rows.

A non-empty `setMany()` batch uses one database-native upsert inside a transaction. `forgetMany()`
uses one delete for all listed keys. Query count is bounded by the operation type, not the number of
keys.

Use `purge()` to remove the complete current scope:

```php
$user->settings()->purge();
```

For `settings()`, `purge()` deletes only that owner's overrides and exposes any persistent defaults
again. For `defaultSettings()`, it deletes the defaults for that model class without deleting model
overrides. All three bulk methods return `void`.

## JSON values

`set()` and `setMany()` preserve JSON values exactly:

| Value | Result |
|-------|--------|
| `null` | Stored |
| `''` or whitespace-only string | Stored |
| `[]` | Stored |
| `0` | Stored |
| `false` | Stored |
| `'0'` | Stored |

A stored `null` is still present. `has($key)` returns `true`, while `get($key)` returns `null`. A
model override containing `null` also hides a filled class default until the override is removed
with `forget()`.

## Setting keys

Keys may be strings, integers, or PHP enums implementing `UnitEnum`:

```php
enum SettingKey: string
{
    case Timezone = 'timezone';
}

$user->settings()->set(SettingKey::Timezone, 'Europe/Paris');

$timezone = $user->settings()->get(SettingKey::Timezone);
```

Laravel stores a backed enum by its backing value and a pure unit enum by its case name. Use the same
key or enum case when reading, replacing, or removing a setting.

Empty and whitespace-only keys throw
`DragonCode\LaravelModelSettings\Exceptions\InvalidSettingKey`. Validation runs after integers and
enums are normalized to their stored string form. The exception never includes the rejected key or
payload.

Dots are literal characters. The key `mail.from.address` is one opaque setting key and never means a
nested path:

```php
$user->settings()->set('mail.from.address', 'noreply@example.com');

$address = $user->settings()->get('mail.from.address');
```

## Model identifiers

Integer, string, UUID, and ULID primary keys are supported.

Per-model mutations require a persisted owner with a non-null key. For an unsaved model, `get()`
returns `null`, `has()` returns `false`, and `all()` returns an empty collection without querying
model overrides. Its `set()`, `setMany()`, `forget()`, `forgetMany()`, and `purge()` methods throw
`InvalidSettingsOwnerException` before a storage query or iterable consumption occurs.

Persisted models with integer `0` or string `'0'` identifiers support the same reads and mutations
as every other saved owner. The storage discriminator keeps their overrides separate from class
defaults even though both rows retain `item_id = '0'`. Other string keys, including `'00'`, remain
valid.

Settings are stored against the model's current morph class. Introducing or changing a morph-map
alias after settings have been written requires updating existing `item_type` values.

## See Also

- [Eager Loading](eager-loading.md) — avoid one settings query per model.
- [Payload Casts](payload-casts.md) — return domain objects instead of decoded JSON.
- [API Reference](api-reference.md) — see method signatures and return values.
