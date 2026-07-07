# Laravel Model Settings

![model settings](https://banners.beyondco.de/Laravel%20Model%20Settings.png?theme=light&packageManager=composer+require&packageName=dragon-code%2Flaravel-model-settings&pattern=topography&style=style_2&description=by+The+Dragon+Code&md=1&showWatermark=1&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg)

[![Stable Version][badge_stable]][link_packagist]
[![Total Downloads][badge_downloads]][link_packagist]
[![License][badge_license]][link_license]

> [!TIP]
>
> Store settings for individual Eloquent models, with optional defaults shared by all models.
>
> Use this package when each model needs its own settings, but should fall back to shared values when a model value is
> missing.

## Installation

You can install the package via [Composer](https://getcomposer.org):

```bash
composer require dragon-code/laravel-model-settings

php artisan vendor:publish --tag="model-settings"

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

$user->settings()->all();                // array of all resolved settings

$user->settings()->forget('timezone');
$user->settings()->get('timezone');      // null
```

## Default Settings

Default settings are shared fallback values:

```php
use DragonCode\LaravelModelSettings\Storages\DefaultStorage;

$defaults = app(DefaultStorage::class);

$defaults->set('timezone', 'UTC');
$defaults->set('locale', 'en');

$defaults->get('timezone'); // 'UTC'
$defaults->all();           // Illuminate\Support\Collection
$defaults->forget('locale');
```

Model values override defaults. Removing the model value exposes the default again:

```php
use DragonCode\LaravelModelSettings\Storages\DefaultStorage;

app(DefaultStorage::class)->set('timezone', 'UTC');

$user->settings()->get('timezone'); // 'UTC'

$user->settings()->set('timezone', 'Europe/Paris');
$user->settings()->get('timezone'); // 'Europe/Paris'

$user->settings()->forget('timezone');
$user->settings()->get('timezone'); // 'UTC'
```

## Methods

| Method                                     | Returns      | Description                                                    |
|--------------------------------------------|--------------|----------------------------------------------------------------|
| `all()`                                    | `Collection` | Returns defaults merged with model settings. Model values win. |
| `get(UnitEnum\|string $key)`               | `mixed`      | Returns the model value, then the default value, then `null`.  |
| `set(UnitEnum\|string $key, mixed $value)` | `void`       | Creates or updates a model setting.                            |
| `forget(UnitEnum\|string $key)`            | `void`       | Removes a model setting.                                       |

If you cannot use the trait, resolve `SettingsService` from the container with `['model' => $user]`.

## Setting Keys

Keys can be strings or PHP enums:

```php
enum UserSetting: string
{
    case Timezone = 'timezone';
}

$user->settings()->set(UserSetting::Timezone, 'UTC');
$user->settings()->get(UserSetting::Timezone); // 'UTC'
```

A blank model value is treated as missing by `get()`. For example: `null`, an empty string, or an empty array.

## Configuration

After publishing, edit `config/model-settings.php`:

| Option       | Environment variable                 | Default               |
|--------------|--------------------------------------|-----------------------|
| `connection` | `MODEL_SETTINGS_DATABASE_CONNECTION` | `DATABASE_CONNECTION` |
| `table`      | `MODEL_SETTINGS_DATABASE_TABLE`      | `settings`            |

The migration stores values in one table with `item_type`, `item_id`, `key`, and JSON `payload`.

Default settings use `item_type = '_default'` and `item_id = 0`.

Each setting is unique by `item_type`, `item_id`, and `key`.

## Contributing

Please see [CONTRIBUTING](https://github.com/TheDragonCode/.github/blob/main/CONTRIBUTING.md) for details.

## Security

If you've found a bug regarding security please mail [helldar@dragon-code.pro](mailto:helldar@dragon-code.pro) instead
of using the issue tracker.

## Credits

- [Andrey Helldar](https://github.com/andrey-helldar)
- [All Contributors](../../graphs/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

[badge_downloads]:      https://img.shields.io/packagist/dt/dragon-code/laravel-model-settings.svg?style=flat-square

[badge_license]:        https://img.shields.io/packagist/l/dragon-code/laravel-model-settings.svg?style=flat-square

[badge_stable]:         https://img.shields.io/github/v/release/TheDragonCode/laravel-model-settings?label=packagist&style=flat-square

[link_license]:         LICENSE

[link_packagist]:       https://packagist.org/packages/dragon-code/laravel-deploy-operations
