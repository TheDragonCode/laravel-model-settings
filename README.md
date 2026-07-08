# Laravel Model Settings

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://banners.beyondco.de/Laravel%20Model%20Settings.png?pattern=topography&style=style_2&fontSize=100px&md=1&showWatermark=1&theme=dark&packageManager=composer+require&packageName=dragon-code%2Flaravel-model-settings&description=Model+Settings+for+your+Laravel+application&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg">
    <img src="https://banners.beyondco.de/Laravel%20Model%20Settings.png?pattern=topography&style=style_2&fontSize=100px&md=1&showWatermark=1&theme=light&packageManager=composer+require&packageName=dragon-code%2Flaravel-model-settings&description=Model+Settings+for+your+Laravel+application&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg" alt="Laravel Model Settings">
</picture>

[![Stable Version][badge_stable]][link_packagist]
[![Total Downloads][badge_downloads]][link_packagist]
[![License][badge_license]][link_license]

> Store settings for individual Eloquent models, with optional defaults shared by models of the same type.

Use this package when each model needs its own settings, but should fall back to shared values when a model value is
missing.

## Installation

Install the package via [Composer](https://getcomposer.org):

```bash
composer require dragon-code/laravel-model-settings
```

Publish the config and migration, then run migrations:

```bash
php artisan vendor:publish --tag="model_settings"
php artisan migrate
```

## Quick Start

Add the `HasSettings` trait to a model:

```php
use DragonCode\LaravelModelSettings\Concerns\HasSettings;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasSettings;
}
```

Use settings on a saved model:

```php
$user = User::query()->findOrFail(123);

$user->settings()->set('timezone', 'UTC');
$user->settings()->set('notifications', ['email' => true]);

$user->settings()->get('timezone');      // 'UTC'
$user->settings()->get('notifications'); // ['email' => true]
$user->settings()->get('missing');       // null

$user->settings()->all();                // Illuminate\Support\Collection

$user->settings()->forget('timezone');
$user->settings()->get('timezone');      // null
```

Calling `set()` with a blank value removes the model setting. Blank values include `null`, an empty string, and an
empty array.

## Default Settings

Default settings are fallback values shared by models of the same type:

```php
(new User)->defaultSettings()->set('timezone', 'UTC');

$user->settings()->get('timezone'); // 'UTC'

$user->settings()->set('timezone', 'Europe/Paris');
$user->settings()->get('timezone'); // 'Europe/Paris'

$user->settings()->forget('timezone');
$user->settings()->get('timezone'); // 'UTC'
```

Default settings are stored with the model morph class and `item_id = 0`.

## API

| Method                                          | Returns      | Description                                                    |
|-------------------------------------------------|--------------|----------------------------------------------------------------|
| `all()`                                         | `Collection` | Returns defaults merged with model settings. Model values win. |
| `get(UnitEnum\|string\|int $key)`               | `mixed`      | Returns the model value, then the default value, then `null`.  |
| `set(UnitEnum\|string\|int $key, mixed $value)` | `void`       | Creates, updates, or removes a model setting.                  |
| `forget(UnitEnum\|string\|int $key)`            | `void`       | Removes a model setting.                                       |

## Setting Keys

Keys can be strings, integers, or PHP enums:

```php
enum UserSetting: string
{
    case Timezone = 'timezone';
}

$user->settings()->set(UserSetting::Timezone, 'UTC');
$user->settings()->get(UserSetting::Timezone); // 'UTC'
```

Backed enums, unit enums, strings, and integers are supported.

## Configuration

After publishing, edit `config/model_settings.php`:

| Option       | Environment variable                 | Default                                           |
|--------------|--------------------------------------|---------------------------------------------------|
| `model`      | -                                    | `DragonCode\LaravelModelSettings\Models\Settings` |
| `connection` | `MODEL_SETTINGS_DATABASE_CONNECTION` | `env('DATABASE_CONNECTION')`                      |
| `table`      | `MODEL_SETTINGS_DATABASE_TABLE`      | `settings`                                        |

The migration stores settings in one table with `item_type`, string `item_id`, `key`, and JSONB `payload`. Each setting
is unique by `item_type`, `item_id`, and `key`. The string `item_id` column supports integer and UUID model keys.

## Testing

```bash
composer test
composer test:coverage
```

## Contributing

Please see [CONTRIBUTING](https://github.com/TheDragonCode/.github/blob/main/CONTRIBUTING.md) for details.

## Security

If you've found a security bug, mail [helldar@dragon-code.pro](mailto:helldar@dragon-code.pro) instead of using the
issue tracker.

## Credits

- [Andrey Helldar](https://github.com/andrey-helldar)
- [All Contributors](../../graphs/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

[badge_downloads]: https://img.shields.io/packagist/dt/dragon-code/laravel-model-settings.svg?style=flat-square

[badge_license]: https://img.shields.io/packagist/l/dragon-code/laravel-model-settings.svg?style=flat-square

[badge_stable]: https://img.shields.io/github/v/release/TheDragonCode/laravel-model-settings?label=packagist&style=flat-square

[link_license]: LICENSE

[link_packagist]: https://packagist.org/packages/dragon-code/laravel-model-settings
