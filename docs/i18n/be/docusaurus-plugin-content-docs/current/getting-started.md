---
sidebar_position: 2
title: Пачатак працы
description: Усталяванне Laravel Model Settings і захаванне першага значэння па змаўчанні і перавызначэння.
---

[← Агляд](index.md) · [Вярнуцца да README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Праца з наладамі →](settings.md)

# Пачатак працы

## Патрабаванні

- PHP 8.3 або навейшы.
- Laravel 12 або 13.

## Усталяванне пакета

```bash
composer require dragon-code/laravel-model-settings
```

Laravel аўтаматычна знаходзіць сэрвіс-правайдар пакета.

Апублікуйце канфігурацыю і міграцыю, затым стварыце табліцу налад:

```bash
php artisan vendor:publish --tag="model_settings"
php artisan migrate
```

Тэг `model_settings` публікуе `config/model_settings.php` і міграцыю пакета. Па змаўчанні міграцыя
стварае табліцу `settings`, выкарыстоўваючы стандартнае падключэнне праграмы да базы даных.

## Дадаванне трэіта

Дадайце `HasSettings` у кожную Eloquent-мадэль, якой патрэбныя налады:

```php
namespace App\Models;

use DragonCode\LaravelModelSettings\Concerns\HasSettings;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasSettings;
}
```

Трэіт дадае наступныя публічныя метады:

| Метад | Прызначэнне |
|-------|-------------|
| `settings()` | Чытанне або змяненне выніковых налад адной захаванай мадэлі |
| `defaultSettings()` | Чытанне або змяненне значэнняў па змаўчанні для класа мадэлі |
| `modelSettings()` | Eloquent-сувязь для папярэдняй загрузкі |

## Захаванне першай налады

Стварыце значэнне па змаўчанні для ўсіх захаваных мадэляў `User`:

```php
(new User)->defaultSettings()->set('timezone', 'UTC');
```

Перавызначце гэта значэнне для аднаго захаванага карыстальніка:

```php
$user = User::query()->firstOrFail();

$user->settings()->set('timezone', 'Europe/Paris');

assert($user->settings()->get('timezone') === 'Europe/Paris');
```

Прачытайце ўсе выніковыя налады як калекцыю з ключамі па назвах налад:

```php
$settings = $user->settings()->all();

assert($settings->get('timezone') === 'Europe/Paris');
```

Выдаліце перавызначэнне, каб зноў выкарыстоўваць `UTC`:

```php
$user->settings()->forget('timezone');

assert($user->settings()->get('timezone') === 'UTC');
```

## Спачатку захоўвайце мадэлі

Выклікайце `settings()->set()` толькі пасля захавання бацькоўскай мадэлі. Незахаваная мадэль не мае
першаснага ключа. Яе `settings()->get()` вяртае `null`, а `settings()->all()` — пустую калекцыю,
нават калі для класа ёсць значэнні па змаўчанні.

## Гл. таксама

- [Праца з наладамі](settings.md) — прыярытэты, выдаленне, ключы і значэнні.
- [Канфігурацыя](configuration.md) — выбар падключэння, табліцы і мадэлі захоўвання.
- [Папярэдняя загрузка](eager-loading.md) — эфектыўная загрузка налад для калекцый мадэляў.
