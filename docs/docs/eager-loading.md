---
sidebar_position: 4
title: Eager Loading
description: Avoid N+1 queries when reading settings for Eloquent model collections.
---

[← Working with Settings](settings.md) · [Back to README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Configuration →](configuration.md)

# Eager Loading

## Load settings with the models

Reading settings lazily loads the `modelSettings` relation. For a collection, that produces one
additional settings query per model.

Eager load the relation when the result contains multiple models:

```php
$users = User::query()
    ->with('modelSettings')
    ->get();

$timezones = $users->map(
    fn (User $user) => $user->settings()->get('timezone')
);
```

The eager-loaded relation contains each model's overrides plus any defaults it inherits. Subsequent
`get()` and `all()` calls use the loaded relation.

## Load settings after the query

Use `loadMissing()` when models are already available:

```php
$users->loadMissing('modelSettings');

$settings = $users->map(
    fn (User $user) => $user->settings()->all()
);
```

## Query behavior

For a single model, lazy loading is usually sufficient. For a collection, eager loading changes the
common query pattern from one model query plus one settings query per model to:

1. One query for the parent models.
2. One query for their defaults and overrides.

The package resolves inherited values in the relation results, including for integer, UUID, and ULID
primary keys.

## Changes after eager loading

`set()` and `forget()` clear the loaded `modelSettings` relation on that model. The next read reloads
the relation, so it does not return the stale value.

## See Also

- [Working with Settings](settings.md) — understand how defaults and overrides are merged.
- [API Reference](api-reference.md) — distinguish the service methods from the relation.
- [Configuration](configuration.md) — configure the settings connection and model.
