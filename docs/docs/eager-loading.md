---
sidebar_position: 4
title: Eager Loading
description: Avoid N+1 queries when reading settings for Eloquent model collections.
---

[← Working with Settings](settings.md) · [Back to README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Configuration →](configuration.md)

# Eager Loading

## Load settings with the models

Without eager loading, each `settings()->get()`, `settings()->has()`, or `settings()->all()` call
performs a settings query. These service reads do not load `modelSettings` as a side effect.

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
`get()`, `has()`, and `all()` calls use the loaded relation.

## Load settings after the query

Use `loadMissing()` when models are already available:

```php
$users->loadMissing('modelSettings');

$settings = $users->map(
    fn (User $user) => $user->settings()->all()
);
```

## Relation boundary

Use `modelSettings` only with `with()`, `load()`, or `loadMissing()` and as the loaded relation
property. It is a read optimization, not an alternative query or CRUD API. Read and mutate values
through `settings()` or `defaultSettings()`.

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

This behavior is covered for integer, string, UUID, and ULID primary keys.

## Changes after eager loading

After a successful `set()`, `setMany()`, `forget()`, `forgetMany()`, or `purge()`, the package clears
the loaded `modelSettings` relation on that model exactly once. The next service read queries the
current effective value, so it does not return stale data. A failed bulk mutation keeps the existing
loaded relation and rolls back the transactional batch.

Explicitly load the relation again before another batch read:

```php
$user->settings()->setMany([
    'timezone' => 'Europe/Paris',
    'locale' => 'fr',
]);

$user->load('modelSettings');
```

Mutation still performs its own write queries. Eager loading only changes subsequent reads.

## See Also

- [Working with Settings](settings.md) — understand how defaults and overrides are merged.
- [API Reference](api-reference.md) — distinguish the service methods from the relation.
- [Configuration](configuration.md) — configure the settings connection and model.
