# Laravel Model Settings

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://banners.beyondco.de/Laravel%20Model%20Settings.png?pattern=topography&style=style_2&fontSize=100px&md=1&showWatermark=1&theme=dark&packageManager=composer+require&packageName=dragon-code%2Flaravel-model-settings&description=Model+Settings+for+your+Laravel+application&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg">
    <img src="https://banners.beyondco.de/Laravel%20Model%20Settings.png?pattern=topography&style=style_2&fontSize=100px&md=1&showWatermark=1&theme=light&packageManager=composer+require&packageName=dragon-code%2Flaravel-model-settings&description=Model+Settings+for+your+Laravel+application&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg" alt="Laravel Model Settings">
</picture>

[![Stable Version][badge_stable]][link_packagist]
[![Total Downloads][badge_downloads]][link_packagist]
[![License][badge_license]][link_license]

> Persist shared defaults and per-model overrides for Eloquent models.

Laravel Model Settings keeps configurable values outside your model tables. Define a default once,
override it for individual records, and read the effective value through one API.

Requires PHP 8.3+ and Laravel 12 or 13.

## Key Features

- Shared defaults are isolated by Eloquent model class.
- Per-model values override defaults without changing other records.
- `get()` and `all()` resolve the effective values automatically.
- Eager loading avoids one settings query per model in a collection.
- Integer, string, UUID, and ULID primary keys work with or without a Laravel morph map.
- Payloads use JSON by default and may use custom value objects.

## Quick Start

```bash
composer require dragon-code/laravel-model-settings

php artisan vendor:publish --tag="model_settings"
php artisan migrate
```

## Example

Add the trait to an Eloquent model:

```php
use DragonCode\LaravelModelSettings\Concerns\HasSettings;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasSettings;
}
```

Set a shared default, override it for a saved model, and read the effective value:

```php
(new User)->defaultSettings()->set('timezone', 'UTC');

$user->settings()->set('timezone', 'Europe/Paris');

assert($user->settings()->get('timezone') === 'Europe/Paris');

$user->settings()->forget('timezone');

assert($user->settings()->get('timezone') === 'UTC');
```

## Documentation

Read the [documentation site](https://model-settings.dragon-code.pro) or open a guide in the repository:

| Guide | Description |
|-------|-------------|
| [Overview](docs/docs/index.md) | Resolution rules and supported models |
| [Getting Started](docs/docs/getting-started.md) | Installation and first setting |
| [Working with Settings](docs/docs/settings.md) | Defaults, owners, keys, and blank values |
| [Eager Loading](docs/docs/eager-loading.md) | Avoiding settings N+1 queries |
| [Configuration](docs/docs/configuration.md) | Connection, schema, and storage model |
| [Payload Casts](docs/docs/payload-casts.md) | JSON, custom casts, and data objects |
| [API Reference](docs/docs/api-reference.md) | Public methods and return values |
| [Development](docs/docs/development.md) | Tests, documentation, and security |

## License

The MIT License (MIT). See [License File](LICENSE).

[badge_downloads]: https://img.shields.io/packagist/dt/dragon-code/laravel-model-settings.svg?style=flat-square

[badge_license]: https://img.shields.io/packagist/l/dragon-code/laravel-model-settings.svg?style=flat-square

[badge_stable]: https://img.shields.io/github/v/release/TheDragonCode/laravel-model-settings?label=packagist&style=flat-square

[link_license]: LICENSE

[link_packagist]: https://packagist.org/packages/dragon-code/laravel-model-settings
