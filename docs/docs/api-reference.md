---
sidebar_position: 7
title: API Reference
description: Public trait, service, and relation methods provided by Laravel Model Settings.
---

[← Payload Casts](payload-casts.md) · [Back to README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Development →](development.md)

# API Reference

## HasSettings trait

| Method | Returns | Purpose |
|--------|---------|---------|
| `settings()` | `SettingsService` | Access effective settings for this model |
| `defaultSettings()` | `SettingsService` | Access shared defaults for this model class |
| `modelSettings()` | Eloquent `Relation` | Load defaults and overrides as a relation |

Use the `modelSettings` relation only for `with()`, `load()`, or `loadMissing()` and as the resulting
loaded property. Do not use its relation query as an alternative read or CRUD API. Use the two
service methods to read or mutate values. At runtime, the relation is a package `SettingsRelation`
based on Laravel's `MorphMany` relation.

## SettingsService

| Method | Returns | Behavior |
|--------|---------|----------|
| `all()` | `Collection` | Returns defaults merged with model overrides |
| `get(int\|string\|UnitEnum $key)` | `mixed` | Returns an override, its default, or `null` |
| `has(int\|string\|UnitEnum $key)` | `bool` | Reports whether an effective key exists, including stored `null` |
| `set(int\|string\|UnitEnum $key, mixed $value)` | `void` | Creates or replaces a setting with the exact JSON value |
| `setMany(iterable $values)` | `void` | Upserts every value in one bounded transactional batch |
| `forget(int\|string\|UnitEnum $key)` | `void` | Removes a setting if it exists |
| `forgetMany(iterable $keys)` | `void` | Removes the listed keys from the current scope |
| `purge()` | `void` | Removes every setting stored in the current scope |

The keyed methods accept backed and pure unit enums. Laravel converts backed enums to their backing
value and pure unit enums to their case name.

`SettingsService` has no caller-supplied fallback parameter on `get()`. Use `has($key)` to
distinguish a missing effective key from a stored JSON `null`.

## Resolution matrix

| Model override | Class default | `get()` result | `has()` result | Included by `all()` |
|----------------|---------------|----------------|----------------|---------------------|
| Present | Present | Override, including `null` | `true` | Override |
| Present | Missing | Override, including `null` | `true` | Override |
| Missing | Present | Default, including `null` | `true` | Default |
| Missing | Missing | `null` | `false` | No entry |

For an unsaved model, `get()` returns `null`, `has()` returns `false`, and `all()` returns an empty
collection. Class defaults are only inherited by persisted models.

## all

```php
$settings = $user->settings()->all();

$timezone = $settings->get('timezone');
```

The result is an `Illuminate\Support\Collection` keyed by setting key. For model settings, overrides
replace defaults with the same key.

## get

```php
$timezone = $user->settings()->get('timezone');
```

The result is the effective decoded or cast value. A missing override falls back to the default. A
missing override and default returns `null`. The signature intentionally accepts no second fallback
argument.

## has

```php
$hasTimezone = $user->settings()->has('timezone');
```

The method returns `true` when either the model override or class default row exists. A stored JSON
`null` returns `true`; a missing key returns `false`. Lazy and eager-loaded services use the same
precedence, and the eager path performs no additional settings query.

## set

```php
$user->settings()->set('timezone', 'Europe/Paris');
```

The method validates the owner and normalized key, then uses an update-or-create operation for the
model type, model identifier, scope discriminator, and key. Every JSON value is stored, including
`null`, empty strings, whitespace strings, empty arrays, zero, and `false`. After a successful write,
the loaded `modelSettings` relation is cleared so the next read cannot reuse stale data.

## setMany

```php
$user->settings()->setMany([
    'timezone' => 'Europe/Paris',
    'locale' => 'fr',
    'obsolete' => null,
]);
```

The iterable keys use the same normalization as `set()`. If multiple input keys normalize to the
same string, the last value wins. Every value uses one database-native upsert inside a transaction.
The method validates the owner before consuming the iterable and clears `modelSettings` once after
success. Use `forgetMany()` for deletion.

## forget

```php
$user->settings()->forget('timezone');
```

For a valid owner, the method is safe when the key does not exist. Removing an override does not
remove its shared default. The loaded relation is cleared after the delete.

## forgetMany

```php
$user->settings()->forgetMany(['timezone', 'locale']);
```

The method normalizes and de-duplicates the iterable, then removes only those keys from the current
scope with one delete. Missing keys have no effect. It returns `void` and clears the loaded relation
after a successful call, including an empty iterable.

## purge

```php
$user->settings()->purge();
```

For `settings()`, the method deletes every override belonging to that saved owner. It never deletes
class defaults or another owner's overrides. For `defaultSettings()`, it deletes every default for
that model class and leaves model overrides intact. It returns `void` and clears a loaded relation
after success.

## defaultSettings

The service returned by `defaultSettings()` has the same eight methods:

```php
$defaults = (new User)->defaultSettings();

$defaults->set('timezone', 'UTC');
$defaults->setMany(['timezone' => 'UTC', 'locale' => 'en']);
$timezone = $defaults->get('timezone');
$hasTimezone = $defaults->has('timezone');
$all = $defaults->all();
$defaults->forget('timezone');
$defaults->forgetMany(['timezone', 'locale']);
$defaults->purge();
```

## Exceptions

`DragonCode\LaravelModelSettings\Exceptions\InvalidSettingsOwnerException` extends PHP's
`DomainException`. Every mutation through `settings()` throws it before a storage query when the
owner model is unsaved, including an unsaved model with a preassigned key.

This validation also happens before a bulk iterable is consumed. Mutations through
`defaultSettings()` remain valid because that service selects the class-default scope explicitly.
Read-only access stays deterministic: an unsaved owner returns `null` or an empty collection without
querying overrides, and `has()` returns `false`. A persisted owner with integer `0` or string `'0'`
can read and mutate its model overrides; `is_default` keeps those rows separate from class defaults.

`DragonCode\LaravelModelSettings\Exceptions\InvalidPayloadCast` is thrown when a configured
model-wide or key-aware cast is missing, has an invalid type, implements no supported contract, or
cannot be resolved through the Laravel container. Its message may identify the parent model, setting
key, and cast class, but never the payload.

`DragonCode\LaravelModelSettings\Exceptions\InvalidSettingKey` is thrown after normalization when a
key is empty or contains only whitespace. Its message and package logs never contain the rejected
key or payload.

If a non-empty `setMany()` operation fails, the transaction rolls back its batch work. The exception
is rethrown, and the existing loaded `modelSettings` relation is not cleared.

## See Also

- [Working with Settings](settings.md) — learn the behavior behind each operation.
- [Eager Loading](eager-loading.md) — use `modelSettings` without N+1 queries.
- [Payload Casts](payload-casts.md) — control the values returned by `get()` and `all()`.
