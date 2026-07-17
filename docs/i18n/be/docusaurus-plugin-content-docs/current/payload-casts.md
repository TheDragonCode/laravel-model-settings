---
sidebar_position: 6
title: Пераўтварэнні даных
description: Дэкадаванне даных налад у масівы, карыстальніцкія значэнні або аб’екты Spatie Laravel Data.
---

[← Канфігурацыя](configuration.md) · [Вярнуцца да README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Даведнік API →](api-reference.md)

# Пераўтварэнні даных

## Стандартныя JSON-значэнні

Без карыстальніцкага пераўтварэння пакет кадзіруе непустыя значэнні ў JSON пры запісе і вяртае
дэкадаваныя масівы або скалярныя значэнні пры чытанні.

```php
$user->settings()->set('notifications', [
    'email' => true,
    'push' => false,
]);

$notifications = $user->settings()->get('notifications');
```

Значэнні павінны падтрымліваць серыялізацыю ў JSON. Памылкі кадавання JSON не хаваюцца.

## Выбар пераўтварэння

Карыстальніцкія пераўтварэнні наладжваюцца па класе бацькоўскай мадэлі:

```php
'casts' => [
    App\Models\User::class => App\Casts\UserSettingsPayloadCast::class,
],
```

Адно наладжанае пераўтварэнне апрацоўвае даныя ўсіх налад гэтага класа бацькоўскай мадэлі. Перад
выбарам пераўтварэння псеўданімы Laravel morph map вызначаюцца назад у клас мадэлі.

Наладжаны клас павінен рэалізоўваць `CastsAttributes` або пашыраць `Spatie\LaravelData\Data`. Для
іншых класаў спецыяльная апрацоўка не ўжываецца, і значэнні выкарыстоўваюць стандартны шлях JSON.

## Жыццёвы цыкл пераўтварэння

Для рэалізацыі `CastsAttributes` пакет выконвае наступную паслядоўнасць:

| Напрамак | Паслядоўнасць |
|----------|---------------|
| Запіс | Выклікаць карыстальніцкі `set()`, затым закадзіраваць вынік у JSON |
| Чытанне | Перадаць захаваны JSON-радок у карыстальніцкі `get()` |

Аргумент `$model` змяшчае наладжаную мадэль захоўвання налад, а не бацькоўскую мадэль `User` або
`Post`. Пакет стварае асобнік пераўтварэння без аргументаў канструктара.

## Пераўтварэнне атрыбута Eloquent

Пераўтварэнне можа рэалізоўваць кантракт Laravel `CastsAttributes`:

```php
namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

final class UserSettingsPayloadCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        return (array) $value;
    }
}
```

Вынік карыстальніцкага `set()` павінен падтрымліваць серыялізацыю ў JSON. Памылкі кадавання JSON не
хаваюцца.

## Spatie Laravel Data

Калі ўсталяваны `spatie/laravel-data`, клас `Data` можна выкарыстоўваць непасрэдна:

```bash
composer require spatie/laravel-data:^4.23
```

```php
'casts' => [
    App\Models\User::class => App\Data\UserSettingsData::class,
],
```

Перадайце ў `set()` даныя, якія прымае клас, або асобнік `Data`. `get()` вяртае асобнік даных, а
`all()` — калекцыю з асобнікамі даных.

```php
$preferences = UserSettingsData::from([
    'timezone' => 'Europe/Paris',
    'notifications' => true,
]);

$user->settings()->set('preferences', $preferences);

$preferences = $user->settings()->get('preferences');
```

Пераўтварэнне выбіраецца для класа бацькоўскай мадэлі, а не для асобнага ключа. Таму даныя кожнай
налады гэтай мадэлі павінны быць дапушчальным уваходам для наладжанага пераўтварэння.

## Гл. таксама

- [Канфігурацыя](configuration.md) — рэгістрацыя пераўтварэнняў і замена мадэлі захоўвання.
- [Праца з наладамі](settings.md) — якія значэнні лічацца пустымі і выдаляюцца.
- [Даведнік API](api-reference.md) — тыпы, якія вяртаюць `get()` і `all()`.
