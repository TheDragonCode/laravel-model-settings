---
sidebar_position: 5
title: Configuration
description: Configure the settings model, database connection, table, and payload casts.
---

[← Eager Loading](eager-loading.md) · [Back to README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Payload Casts →](payload-casts.md)

# Configuration

## Publish the configuration

```bash
php artisan vendor:publish --tag="model_settings"
```

This publishes `config/model_settings.php` and the package migration.

## Available options

| Option | Default | Purpose |
|--------|---------|---------|
| `model` | `DragonCode\LaravelModelSettings\Models\Settings` | Eloquent model used for stored settings |
| `connection` | Application default | Database connection used by the model and migration |
| `table` | `settings` | Database table used by the model and migration |
| `casts` | `[]` | Payload casts selected by parent model class and optionally setting key |

The package reads these environment variables:

| Variable | Default |
|----------|---------|
| `MODEL_SETTINGS_DATABASE_CONNECTION` | `DATABASE_CONNECTION`, then Laravel's default connection |
| `MODEL_SETTINGS_DATABASE_TABLE` | `settings` |

Set the connection and table before running the migration:

```dotenv
MODEL_SETTINGS_DATABASE_CONNECTION=mysql
MODEL_SETTINGS_DATABASE_TABLE=model_settings
```

Changing either value later does not move existing records.

## Payload cast configuration

The legacy model-wide form remains supported. One cast handles every payload owned by the model
class:

```php
'casts' => [
    App\Models\User::class => App\Casts\UserSettingsPayloadCast::class,
],
```

Use a key-aware map when different keys need different types or handling:

```php
'casts' => [
    App\Models\User::class => [
        'profile' => App\Data\ProfileData::class,
        'billing.credentials' => App\Casts\EncryptedSettingPayload::class,
    ],
],
```

Key matching is exact. Dots have no nested-path meaning, and a key missing from the map uses the
default JSON cast. Each model entry is either a model-wide class string or a key-aware map; there is
no wildcard entry inside a key-aware map. See [Payload Casts](payload-casts.md) for supported cast
contracts and an encryption recipe.

## Storage schema

The published migration creates these columns:

| Column | Purpose |
|--------|---------|
| `id` | Settings row primary key |
| `item_type` | Parent model morph class or alias |
| `item_id` | Parent identifier, stored as a string up to 36 characters |
| `key` | Setting key |
| `payload` | Payload declared by the migration as `jsonb` |
| `created_at` and `updated_at` | Laravel timestamps |

The combination of `item_type`, `item_id`, and `key` is unique.

Class defaults and model overrides share this table. The package does not create a second defaults
table or add encryption metadata columns.

The default `item_id` column stores at most 36 characters. Integer, string, UUID, and ULID
identifiers fit this schema when their string form is no longer than 36 characters. A longer custom
primary key requires a matching migration change.

The value `0` is reserved in `item_id` for class defaults. In 1.x, every mutation through
`settings()` rejects a persisted owner whose key is integer `0` or string `'0'` with
`InvalidSettingsOwnerException` before querying this table. Changing the database connection, table
name, or morph-map aliases after data exists requires moving or updating the existing rows yourself.

## Replace the storage model

The built-in settings model is final. Configure a replacement instead of extending it:

```php
namespace App\Models;

use DragonCode\LaravelModelSettings\Casts\PayloadCast;
use Illuminate\Database\Eloquent\Model;

final class ApplicationSetting extends Model
{
    protected $fillable = [
        'item_type',
        'item_id',
        'key',
        'payload',
    ];

    public function __construct(array $attributes = [])
    {
        $this->setConnection(config('model_settings.connection'));
        $this->setTable(config('model_settings.table'));

        parent::__construct($attributes);
    }

    protected function casts(): array
    {
        return [
            'item_id' => 'string',
            'payload' => PayloadCast::class,
        ];
    }
}
```

Then update the config:

```php
'model' => App\Models\ApplicationSetting::class,
```

The replacement must remain compatible with the published schema. Keep the fillable attributes and
the `PayloadCast` unless the replacement implements equivalent serialization.

At minimum, the replacement model must preserve these behaviors:

| Requirement | Reason |
|-------------|--------|
| Fill `item_type`, `item_id`, `key`, and `payload` | `updateOrCreate()` writes these attributes |
| Use the configured connection and table | The migration and repository must address the same rows |
| Cast `item_id` to `string` | Integer, string, UUID, and ULID identifiers share one column |
| Cast `payload` with `PayloadCast` or an equivalent | Reads and writes must preserve JSON behavior |

## See Also

- [Getting Started](getting-started.md) — publish the config and migration.
- [Payload Casts](payload-casts.md) — configure application-specific payload types.
- [API Reference](api-reference.md) — see the public package surface.
