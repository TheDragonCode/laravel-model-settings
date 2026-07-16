---
sidebar_position: 3
title: Working with Settings
description: Manage shared defaults, per-model overrides, setting keys, and values.
---

[← Getting Started](getting-started.md) · [Back to README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Eager Loading →](eager-loading.md)

# Working with Settings

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

`get()` and `all()` resolve values with the same precedence:

```php
$timezone = $user->settings()->get('timezone');
$settings = $user->settings()->all();
```

`all()` returns an `Illuminate\Support\Collection` keyed by setting key.

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

## Blank values

`set()` uses Laravel's `blank()` helper. A blank value removes the setting instead of storing it.

| Value | Result |
|-------|--------|
| `null` | Removed |
| `''` or whitespace-only string | Removed |
| `[]` | Removed |
| `0` | Stored |
| `false` | Stored |
| `'0'` | Stored |

The package cannot persist an intentionally blank value through `set()`.

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

Use the same key type when reading, replacing, or removing a setting.

## Model identifiers

Integer, UUID, and ULID primary keys are supported. The value `0` is reserved internally for shared
defaults and must not be used as a real model primary key.

## See Also

- [Eager Loading](eager-loading.md) — avoid one settings query per model.
- [Payload Casts](payload-casts.md) — return domain objects instead of decoded JSON.
- [API Reference](api-reference.md) — see method signatures and return values.
