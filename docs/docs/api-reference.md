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

Use the `modelSettings` relation for `with()`, `load()`, or `loadMissing()`. Use the two service
methods to read or mutate values. At runtime, the relation is a package `SettingsRelation` based on
Laravel's `MorphMany` relation.

## SettingsService

| Method | Returns | Behavior |
|--------|---------|----------|
| `all()` | `Collection` | Returns defaults merged with model overrides |
| `get(int\|string\|UnitEnum $key)` | `mixed` | Returns an override, its default, or `null` |
| `set(int\|string\|UnitEnum $key, mixed $value)` | `void` | Creates, replaces, or removes a blank setting |
| `forget(int\|string\|UnitEnum $key)` | `void` | Removes a setting if it exists |

The keyed methods accept backed and pure unit enums. Laravel converts backed enums to their backing
value and pure unit enums to their case name.

## Resolution matrix

| Model override | Class default | `get()` result | Included by `all()` |
|----------------|---------------|----------------|---------------------|
| Present | Present | Override | Override |
| Present | Missing | Override | Override |
| Missing | Present | Default | Default |
| Missing | Missing | `null` | No entry |

For an unsaved model, `get()` returns `null` and `all()` returns an empty collection. Class defaults
are only inherited by persisted models.

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
missing override and default returns `null`.

## set

```php
$user->settings()->set('timezone', 'Europe/Paris');
```

The method uses an update-or-create operation for the model type, model identifier, and key. Passing
a value considered blank by Laravel removes the row. After either path, the loaded `modelSettings`
relation is cleared so the next read cannot reuse stale data.

## forget

```php
$user->settings()->forget('timezone');
```

The method is safe when the key does not exist. Removing an override does not remove its shared
default. The loaded relation is cleared after the delete.

## defaultSettings

The service returned by `defaultSettings()` has the same four methods:

```php
$defaults = (new User)->defaultSettings();

$defaults->set('timezone', 'UTC');
$timezone = $defaults->get('timezone');
$all = $defaults->all();
$defaults->forget('timezone');
```

## See Also

- [Working with Settings](settings.md) — learn the behavior behind each operation.
- [Eager Loading](eager-loading.md) — use `modelSettings` without N+1 queries.
- [Payload Casts](payload-casts.md) — control the values returned by `get()` and `all()`.
