---
sidebar_position: 1
slug: /
title: Laravel Model Settings
description: Shared defaults and per-model setting overrides for Laravel Eloquent models.
---

[Back to README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Getting Started →](getting-started.md)

# Laravel Model Settings

Laravel Model Settings stores shared defaults and per-model overrides in a separate database table.
Use it when every model should start with the same value but individual records may override it.

The package does not add setting columns to parent tables. Settings remain independent from the
model schema and are grouped by the model's Eloquent morph class.

## When it fits

| Requirement | Package behavior |
|-------------|------------------|
| Give every saved model the same initial value | Store one class-level default |
| Change the value for one model | Store one per-model override |
| Remove an override | Expose the class default again |
| Read many models | Eager load one relation for defaults and overrides |

## Resolution order

When you read a setting, the package returns the first available value:

1. The override for the saved model.
2. The default for that model class.
3. `null`.

| Source | `timezone` |
|--------|------------|
| `User` default | `UTC` |
| User 123 override | `Europe/Paris` |
| Effective value for User 123 | `Europe/Paris` |
| Effective value for another saved user | `UTC` |

Removing an override exposes the default again. It does not delete the default.

## Core operations

```php
(new User)->defaultSettings()->set('timezone', 'UTC');

$user->settings()->set('timezone', 'Europe/Paris');

$timezone = $user->settings()->get('timezone');
$settings = $user->settings()->all();
$hasTimezone = $settings->has('timezone');

$user->settings()->setMany([
    'locale' => 'fr',
    'notifications.email' => true,
]);
$user->settings()->forgetMany(['timezone', 'locale']);
```

`get()` returns one effective value. `all()` returns an
`Illuminate\Support\Collection` containing defaults merged with overrides.
Use the collection's `has()` method when effective-key existence matters. `get()` intentionally has
no caller-supplied fallback argument: the persistent class default is its only fallback, followed by
`null` when neither scope contains the key.

Defaults and overrides use the same operations: `all()`, `get()`, `set()`, `setMany()`, `forget()`,
`forgetMany()`, and `purge()`.

## Focused package boundaries

Laravel Model Settings is a focused Eloquent package, not a general application-settings
framework.

| Boundary | Intentional behavior |
|----------|----------------------|
| Storage | One database table; no Redis backend or parent-model field storage |
| Defaults | Reserved rows in the same table; no second defaults table |
| Registration | No repository registry, typed global settings classes, or class discovery |
| Migrations | No per-key settings migration runner |
| Caching | No mandatory cross-request cache; eager loading only reuses a loaded relation |

Applications that need those features should compose them outside this package rather than treating
`modelSettings` or the internal repository as an extension API.

## Storage boundaries

Each row is identified by four values:

| Value | Meaning |
|-------|---------|
| `item_type` | Parent model morph class or morph-map alias |
| `item_id` | Parent primary key; class defaults keep the physical value `0` |
| `is_default` | `true` for a class default, `false` for a model override |
| `key` | Setting name |

This makes defaults independent for each model class. A `User` default never becomes a `Post`
default, even when both classes use the same setting key.

## Supported models

The package supports Eloquent models with integer, string, UUID, or ULID primary keys. Persisted
models with integer `0` or string `'0'` identifiers can store overrides without colliding with class
defaults. Models may also use a Laravel morph map.

Per-model settings belong to persisted models. An unsaved model does not inherit defaults:
`get()` returns `null`, and `all()` returns an empty collection. Calling `set()`, `setMany()`,
`forget()`, `forgetMany()`, or `purge()` for an unsaved owner throws
`InvalidSettingsOwnerException` before a storage query runs.

Payloads are stored as JSON. Without a configured cast, reads return decoded arrays or scalar
values. [Payload casts](payload-casts.md) can return application-specific objects instead.

## See Also

- [Getting Started](getting-started.md) — install the package and configure a model.
- [Working with Settings](settings.md) — manage defaults, overrides, keys, and values.
- [API Reference](api-reference.md) — check every public method and return type.
