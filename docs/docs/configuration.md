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
| `casts` | `[]` | Payload cast selected by parent model class |

The package reads these environment variables:

| Variable | Default |
|----------|---------|
| `MODEL_SETTINGS_DATABASE_CONNECTION` | `DATABASE_CONNECTION`, then the application default |
| `MODEL_SETTINGS_DATABASE_TABLE` | `settings` |

Set the connection and table before running the migration:

```dotenv
MODEL_SETTINGS_DATABASE_CONNECTION=mysql
MODEL_SETTINGS_DATABASE_TABLE=model_settings
```

Changing either value later does not move existing records.

## Storage schema

The published migration creates these columns:

| Column | Purpose |
|--------|---------|
| `id` | Settings row primary key |
| `item_type` | Parent model morph class or alias |
| `item_id` | Parent identifier, stored as a string up to 36 characters |
| `key` | Setting key |
| `payload` | JSON payload |
| `created_at` and `updated_at` | Laravel timestamps |

The combination of `item_type`, `item_id`, and `key` is unique.

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

## See Also

- [Getting Started](getting-started.md) — publish the config and migration.
- [Payload Casts](payload-casts.md) — configure application-specific payload types.
- [API Reference](api-reference.md) — see the public package surface.
