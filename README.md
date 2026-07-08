# Laravel Model Settings

![model settings](https://banners.beyondco.de/Laravel%20Model%20Settings.png?theme=light&packageManager=composer+require&packageName=dragon-code%2Flaravel-model-settings&pattern=topography&style=style_2&description=by+The+Dragon+Code&md=1&showWatermark=1&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg)

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

## Typed Settings Schema

Instead of scattering keys, types, and defaults across the code and the database, a model can declare a **schema** — a
plain object whose promoted properties describe the available settings, their types, and their code-level defaults:

```php
final class UserSettings
{
    public function __construct(
        public string $localization_code = 'ru',
        public int $default_agreement = 3,
        public bool $order_card_payment = false,
        public ?int $ttb_command_index = null,
    ) {}
}
```

Opt in by overriding `settingsSchema()` on the model:

```php
class User extends Model
{
    use HasSettings;

    public function settingsSchema(): ?string
    {
        return UserSettings::class;
    }
}
```

Now values resolve through three layers — **model value → database default → schema default → `null`** — so a value
always exists without seeding the database, and `schema()` returns a fully typed object:

```php
$user->settings()->get('localization_code'); // 'ru' — from the schema, nothing stored yet

// Pass the schema class to get IDE autocomplete and static analysis on the result:
$settings = $user->settings()->schema(UserSettings::class);
$settings->localization_code;                 // string — autocompleted, PHPStan/Psalm friendly
$settings->order_card_payment;                // bool

// Or omit it to hydrate the model's declared schema (typed as a generic object):
$user->settings()->schema();
```

The `schema(UserSettings::class)` form uses a `class-string<T>` → `T` generic, so the IDE resolves the concrete
schema type and its properties — the same pattern as `app(UserSettings::class)`.

### Property access

Settings can also be read and written as properties, which is the most natural form when a schema is declared:

```php
$user->settings()->ttb_command_index;        // read — resolves through the value layers
$user->settings()->ttb_command_index = null; // write — stores (or, for a blank value, removes) the setting
```

`SettingsService` is generic over the schema (`@mixin TSchema`), so annotating the model's `settings()` method makes
the IDE autocomplete and type-check these properties:

```php
use DragonCode\LaravelModelSettings\Services\SettingsService;

/**
 * @method SettingsService<UserSettings> settings()
 */
class User extends Model
{
    use HasSettings;

    public function settingsSchema(): ?string
    {
        return UserSettings::class;
    }
}
```

The feature is entirely opt-in: models without a `settingsSchema()` behave exactly as before. Database defaults set via
`defaultSettings()` still override schema defaults, letting an admin change a value for a whole model type at runtime
without a deploy.

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

After publishing, edit `config/model-settings.php`:

| Option       | Environment variable                 | Default                                                  |
|--------------|--------------------------------------|----------------------------------------------------------|
| `model`      | -                                    | `DragonCode\LaravelModelSettings\Models\Settings::class` |
| `connection` | `MODEL_SETTINGS_DATABASE_CONNECTION` | `env('DATABASE_CONNECTION')`                             |
| `table`      | `MODEL_SETTINGS_DATABASE_TABLE`      | `settings`                                               |

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
