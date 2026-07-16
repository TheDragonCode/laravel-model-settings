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

## Supported models

The package supports Eloquent models with integer, UUID, or ULID primary keys. Models may also use a
Laravel morph map.

Per-model settings belong to persisted models. An unsaved model does not inherit defaults.

## See Also

- [Getting Started](getting-started.md) — install the package and configure a model.
- [Working with Settings](settings.md) — manage defaults, overrides, keys, and values.
- [API Reference](api-reference.md) — check every public method and return type.
