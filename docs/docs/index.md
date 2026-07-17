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

$user->settings()->forget('timezone');
```

`get()` returns one effective value. `all()` returns an
`Illuminate\Support\Collection` containing defaults merged with overrides.

Defaults and overrides use the same four operations: `all()`, `get()`, `set()`, and `forget()`.

## Storage boundaries

Each row is identified by three values:

| Value | Meaning |
|-------|---------|
| `item_type` | Parent model morph class or morph-map alias |
| `item_id` | Parent primary key, or the reserved value `0` for class defaults |
| `key` | Setting name |

This makes defaults independent for each model class. A `User` default never becomes a `Post`
default, even when both classes use the same setting key.

## Supported models

The package supports Eloquent models with integer, UUID, or ULID primary keys. Models may also use a
Laravel morph map.

Per-model settings belong to persisted models. An unsaved model does not inherit defaults.

Payloads are stored as JSON. Without a configured cast, reads return decoded arrays or scalar
values. [Payload casts](payload-casts.md) can return application-specific objects instead.

## See Also

- [Getting Started](getting-started.md) — install the package and configure a model.
- [Working with Settings](settings.md) — manage defaults, overrides, keys, and values.
- [API Reference](api-reference.md) — check every public method and return type.
