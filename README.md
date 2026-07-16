# Laravel Model Settings

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://banners.beyondco.de/Laravel%20Model%20Settings.png?pattern=topography&style=style_2&fontSize=100px&md=1&showWatermark=1&theme=dark&packageManager=composer+require&packageName=dragon-code%2Flaravel-model-settings&description=Model+Settings+for+your+Laravel+application&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg">
    <img src="https://banners.beyondco.de/Laravel%20Model%20Settings.png?pattern=topography&style=style_2&fontSize=100px&md=1&showWatermark=1&theme=light&packageManager=composer+require&packageName=dragon-code%2Flaravel-model-settings&description=Model+Settings+for+your+Laravel+application&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg" alt="Laravel Model Settings">
</picture>

[![Stable Version][badge_stable]][link_packagist]
[![Total Downloads][badge_downloads]][link_packagist]
[![License][badge_license]][link_license]

> Persist shared defaults and per-model overrides for Eloquent models.

## Requirements

- PHP 8.3+
- Laravel 12+

## Installation

Install the package via [Composer](https://getcomposer.org):

```bash
composer require dragon-code/laravel-model-settings
```

Publish the config and migration, then run the migration:

```bash
php artisan vendor:publish --tag="model_settings"
php artisan migrate
```

## Usage

Add the trait to an Eloquent model:

```php
use DragonCode\LaravelModelSettings\Concerns\HasSettings;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasSettings;
}
```

Set shared defaults and override them for a saved model:

```php
(new User)->defaultSettings()->set('timezone', 'UTC');

$user = User::query()->findOrFail(123);
$user->settings()->set('timezone', 'Europe/Paris');
$user->settings()->set('notifications', ['email' => true]);

$timezone = $user->settings()->get('timezone');
$notifications = $user->settings()->get('notifications');

$user->settings()->forget('timezone');
```

`get()` returns the model override, its default, or `null`. `all()` returns a collection of defaults merged with model
overrides. After the override above is forgotten, `timezone` resolves to `UTC` again.

`set()` removes a setting when Laravel's `blank()` helper considers its value blank. Values such as `0` and `false` are
stored. Setting keys accept strings, integers, and PHP enums. Model keys may be integers, UUIDs, or ULIDs.

## Eager Loading

Eager load `modelSettings` when reading settings for multiple models:

```php
$users = User::query()->with('modelSettings')->get();

$settings = $users->map(
    fn (User $user) => $user->settings()->all()
);
```

The relation includes inherited defaults and model-specific overrides, avoiding one settings query per model.

## API

| Method                                          | Returns      | Description                           |
|-------------------------------------------------|--------------|---------------------------------------|
| `all()`                                         | `Collection` | Defaults merged with model overrides. |
| `get(UnitEnum\|string\|int $key)`               | `mixed`      | Override, default, or `null`.         |
| `set(UnitEnum\|string\|int $key, mixed $value)` | `void`       | Stores or removes a setting.          |
| `forget(UnitEnum\|string\|int $key)`            | `void`       | Removes a setting.                    |

## Payload Casts

Without a custom cast, payloads are decoded to arrays, scalar values, or `null`. Configure casts by parent model in
`config/model_settings.php`:

```php
'casts' => [
    App\Models\User::class => App\Data\UserSettingsData::class,
],
```

A cast may implement Laravel's `CastsAttributes` contract. [Spatie Laravel Data](https://spatie.be/docs/laravel-data)
classes are also supported when that package is installed.

The published config lets you replace the settings model, database connection, table, and payload casts. Use
`MODEL_SETTINGS_DATABASE_CONNECTION` and `MODEL_SETTINGS_DATABASE_TABLE` to override database defaults.

## Development

- Tests: `composer test`
- Coverage: `composer test:coverage`
- Contributions: [contribution guide](https://github.com/TheDragonCode/.github/blob/main/CONTRIBUTING.md)
- Security: [helldar@dragon-code.pro](mailto:helldar@dragon-code.pro)
- Credits: [Andrey Helldar](https://github.com/andrey-helldar)
  and [all contributors](https://github.com/TheDragonCode/laravel-model-settings/graphs/contributors)

## License

The MIT License (MIT). See [License File](LICENSE).

[badge_downloads]: https://img.shields.io/packagist/dt/dragon-code/laravel-model-settings.svg?style=flat-square

[badge_license]: https://img.shields.io/packagist/l/dragon-code/laravel-model-settings.svg?style=flat-square

[badge_stable]: https://img.shields.io/github/v/release/TheDragonCode/laravel-model-settings?label=packagist&style=flat-square

[link_license]: LICENSE

[link_packagist]: https://packagist.org/packages/dragon-code/laravel-model-settings
