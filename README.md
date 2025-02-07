ry (Database, Redis, File, ...)
and use them through an application without hassle.

## Installation

You can install the package via [Composer](https://getcomposer.org):

```bash
composer require dragon-code/laravel-model-settings
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="model-settings-migrations"

php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="model-settings-config"
```

## Usage

```php
/*
 * Settings for examples:
 * 
 * [
 *   'settings' => [
 *     'users' => [
 *       'foo' => 'Foo Value',
 *     ],
 *   ],
 * ]
 */
```

### Model

This method allows you to maintain individual settings for each model.

General settings for all models can be set in the configuration file.

```php
use DragonCode\LaravelModelSettings\Concerns\HasSettings;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasSettings; // Attach this trait to your model
}

$user = User::find(123);

$user->settings()->set('bar', 'Bar Value');

$user->settings()->get('foo'); // Foo Value
$user->settings()->get('bar'); // Bar Value
$user->settings()->get('baz'); // null

$user->settings()->get('foo', 'default value'); // Foo Value
$user->settings()->get('bar', 'default value'); // Bar Value
$user->settings()->get('baz', 'default value'); // default value
```

#### Model Casting

You can also use your own caste for the data object. For example:

```php
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class MyData extends Data
{
    public Optional|string $foo;
    public Optional|SomeData $content;
}

class SomeData extends Data
{
    public Optional|string $title;
}

// config/model-settings.php
return [
    'repositories' => [
        'database' => [
            'cast' => MyData::class,
        ],
    ],
];

// Usage
$user = User::find(123);

$user->settings()->get('content.title'); // null
$user->settings()->set('content.title', 'The best title!');
$user->settings()->get('content.title'); // The best title!

$settings = $user->settings()->all();

return $settings->content->title; // The best title!
```

### Application Settings

This method allows you to refer to the general settings of the application.

```php
settings()->set('bar', 'Bar Value');

settings()->get('foo'); // Foo Value
settings()->get('bar'); // Bar Value
settings()->get('baz'); // null

settings()->get('foo', 'default value'); // Foo Value
settings()->get('bar', 'default value'); // Bar Value
settings()->get('baz', 'default value'); // default value
```

### Available Methods

| Method                 | Description                                                                                             |
|------------------------|---------------------------------------------------------------------------------------------------------|
| `settings()->all()`    | Returns all the settings for the model                                                                  |
| `settings()->get()`    | Returns the value of the settings along the specified path                                              |
| `settings()->set()`    | Sets the value of the settings along the specified path                                                 |
| `settings()->has()`    | Checks the existence of the value along the specified path                                              |
| `settings()->apply()`  | Saves settings to the repository                                                                        |
| `settings()->forget()` | Removes the value of the settings along the specified path.<br/>The default settings cannot be deleted. |
| `settings()->clear()`  | Removes model settings from the repository                                                              |

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
