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

When parent models are fetched and their settings are then read, lazy loading and eager loading have
the same cost for one model. For a collection, the difference is visible:

| Loaded parent models | Lazy loading | Eager loading |
|----------------------|--------------|---------------|
| 1 | 2 queries | 2 queries |
| N | 1 + N queries | 2 queries |

The eager-loading path uses:

1. One query for the parent models.
2. One query for their defaults and overrides.

The settings query includes the class defaults and every requested model identifier. The relation
then copies inherited defaults into each model's loaded result and replaces matching keys with that
model's overrides.

This behavior is covered for integer, UUID, and ULID primary keys.

## Changes after eager loading

`set()` and `forget()` clear the loaded `modelSettings` relation on that model. The next read reloads
the relation, so it does not return the stale value.

Mutation still performs its own write queries. Eager loading only changes subsequent reads.

## See Also

- [Working with Settings](settings.md) — understand how defaults and overrides are merged.
- [API Reference](api-reference.md) — distinguish the service methods from the relation.
- [Configuration](configuration.md) — configure the settings connection and model.
