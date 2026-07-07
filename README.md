# Laravel Model Settings

![model settings](https://banners.beyondco.de/Laravel%20Model%20Settings.png?theme=light&packageManager=composer+require&packageName=dragon-code%2Flaravel-model-settings&pattern=topography&style=style_2&description=by+The+Dragon+Code&md=1&showWatermark=1&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg)

## Installation

You can install the package via [Composer](https://getcomposer.org):

```bash
composer require dragon-code/laravel-model-settings
```

You can publish the config file and the migrations with:

```bash
php artisan vendor:publish --tag="model-settings"
```

## Usage

This method allows you to maintain individual settings for each model.

General settings for all models can be set in the default record in the `settings` table name.

```php
use DragonCode\LaravelModelSettings\Concerns\HasSettings;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasSettings; // Attach to your model
}

$user = User::find(123);

$user->settings()->set('bar', 'Bar');

$user->settings()->get('foo'); // Foo is a default value
$user->settings()->get('bar'); // Bar
$user->settings()->get('baz'); // null
```

### Default Settings

```php
use DragonCode\LaravelModelSettings\Storages\DefaultStorage;

class Some
{
    public function __construct(
        protected DefaultStorage $storage,
    ) {}
    
    public function all(): void
    {
      $this->storage->all();
      $this->storage->get('key');
      $this->storage->set('key', 'value');
      $this->storage->forget('key');
    }
}
```

### Available Methods

| Method                                                 | Description                                                 |
|--------------------------------------------------------|-------------------------------------------------------------|
| `settings()->all()`                                    | Returns all the settings for the model                      |
| `settings()->get(UnitEnum\|string $key)`               | Returns the value of the settings along the specified path  |
| `settings()->set(UnitEnum\|string $key, mixed $value)` | Sets the value of the settings along the specified path     |
| `settings()->forget(UnitEnum\|string $key)`            | Removes the value of the settings along the specified path. |

## Testing

```bash
composer test
```

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

[badge_build]:          https://img.shields.io/github/actions/workflow/status/TheDragonCode/laravel-model-settings/tests.yml?style=flat-square

[badge_downloads]:      https://img.shields.io/packagist/dt/dragon-code/laravel-model-settings.svg?style=flat-square

[badge_license]:        https://img.shields.io/packagist/l/dragon-code/laravel-model-settings.svg?style=flat-square

[badge_stable]:         https://img.shields.io/github/v/release/TheDragonCode/laravel-model-settings?label=packagist&style=flat-square

[link_build]:           https://github.com/TheDragonCode/laravel-model-settings/actions

[link_license]:         LICENSE

[link_packagist]:       https://packagist.org/packages/dragon-code/laravel-deploy-operations
