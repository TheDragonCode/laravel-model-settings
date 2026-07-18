---
sidebar_position: 6
title: Пераўтварэнні даных
description: Дэкадаванне даных налад у масівы, карыстальніцкія значэнні або аб’екты Spatie Laravel Data.
---

[← Канфігурацыя](configuration.md) · [Вярнуцца да README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Даведнік API →](api-reference.md)

# Пераўтварэнні даных

## Стандартныя JSON-значэнні

Без карыстальніцкага пераўтварэння пакет кадзіруе кожнае значэнне ў JSON пры запісе і вяртае
дакладнае дэкадаванае JSON-значэнне пры чытанні. Гэта ўключае `null`, пустыя радкі, радкі з прабелаў,
пустыя масівы, нуль і `false`.

```php
$user->settings()->set('notifications', [
    'email' => true,
    'push' => false,
]);

$notifications = $user->settings()->get('notifications');
```

Значэнні павінны падтрымліваць серыялізацыю ў JSON. Памылкі кадавання JSON не хаваюцца.

## Выбар пераўтварэння

Сумяшчальная форма для ўсёй мадэлі прымяняе адно пераўтварэнне да ўсіх налад класа бацькоўскай
мадэлі:

```php
'casts' => [
    App\Models\User::class => App\Casts\UserSettingsPayloadCast::class,
],
```

Выкарыстоўвайце карту па ключах, калі спецыяльная апрацоўка патрэбна толькі дакладным ключам налад:

```php
'casts' => [
    App\Models\User::class => [
        'profile' => App\Data\ProfileData::class,
        'billing.credentials' => App\Casts\EncryptedSettingPayload::class,
    ],
],
```

Перад выбарам пераўтварэння псеўданімы Laravel morph map вызначаюцца назад у клас бацькоўскай
мадэлі. Супастаўленне выкарыстоўвае захаваны ключ налады, а не назву Eloquent-атрыбута `payload`.
Кропкі з’яўляюцца звычайнымі сімваламі, таму `billing.credentials` — адзін ключ. Адсутныя ў карце
ключы выкарыстоўваюць звычайны JSON.

Наладжаны клас павінен рэалізоўваць `CastsAttributes` або пашыраць `Spatie\LaravelData\Data`.
Няправільны, адсутны, непадтрыманы або невырашальны кантэйнерам клас выклікае
`InvalidPayloadCast`; для наладжанага запісу пакет не пераходзіць да звычайнага JSON без памылкі.

## Жыццёвы цыкл пераўтварэння

Для рэалізацыі `CastsAttributes` пакет выконвае наступную паслядоўнасць:

| Напрамак | Паслядоўнасць |
|----------|---------------|
| Запіс | Выклікаць карыстальніцкі `set()`, затым закадзіраваць вынік у JSON |
| Чытанне | Перадаць захаваны JSON-радок у карыстальніцкі `get()` |

Аргумент `$model` змяшчае наладжаную мадэль захоўвання налад, а не бацькоўскую мадэль `User` або
`Post`. Рэалізацыі `CastsAttributes` вырашаюцца праз кантэйнер Laravel, таму залежнасці канструктара
могуць выкарыстоўваць звычайныя прывязкі кантэйнера. Карыстальніцкі `set()` атрымлівае кожнае
ўваходнае значэнне, у тым ліку значэнні, якія Laravel лічыць пустымі.

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

## Шыфраванне асобнага ключа

Шыфраванне трэба рэалізаваць у пераўтварэнні праграмы, бо схема пакета не вызначае метаданыя
шыфравання і кантракт ратацыі ключоў. Гэта пераўтварэнне шыфруе адзін ключ, а астатнія пакідае на
стандартным шляху JSON:

```php
namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

final class EncryptedSettingPayload implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $ciphertext = Json::decode($value);

        return Json::decode(Crypt::decryptString((string) $ciphertext));
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return Crypt::encryptString(Json::encode($value));
    }
}
```

Зарэгіструйце яго для дакладнага літаральнага ключа:

```php
'casts' => [
    App\Models\User::class => [
        'billing.credentials' => App\Casts\EncryptedSettingPayload::class,
    ],
],
```

```php
$user->settings()->set('billing.credentials', $credentials);

$credentials = $user->settings()->get('billing.credentials');
```

Не запісвайце значэнне ў журнал да або пасля пераўтварэння. Калі ключы шыфравання могуць змяняцца,
вызначце і праверце палітыку ратацыі праграмы да захоўвання production-даных. Не дадавайце слупкі
метаданых у табліцу пакета без асобнага кантракта захоўвання, які вызначае версіянаванне і ратацыю.

## Spatie Laravel Data

Калі ўсталяваны `spatie/laravel-data`, клас `Data` можна выкарыстоўваць непасрэдна:

```bash
composer require spatie/laravel-data:^4.23
```

```php
'casts' => [
    App\Models\User::class => [
        'preferences' => App\Data\UserSettingsData::class,
    ],
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

Іншыя ключы той жа мадэлі працягваюць выкарыстоўваць звычайны JSON. Выкарыстоўвайце сумяшчальную
форму для ўсёй мадэлі толькі тады, калі даныя кожнай налады гэтага ўладальніка падыходзяць
наладжанаму класу даных.

## Памылкі пераўтварэння

`DragonCode\LaravelModelSettings\Exceptions\InvalidPayloadCast` паказвае клас бацькоўскай мадэлі,
ключ налады і наладжанае пераўтварэнне пры памылцы вырашэння. Выключэнне ніколі не змяшчае даныя
налады. Яно выклікаецца для адзіночных і групавых запісаў, а таксама пры чытанні захаванага значэння
праз такі наладжаны запіс.

## Гл. таксама

- [Канфігурацыя](configuration.md) — рэгістрацыя пераўтварэнняў і замена мадэлі захоўвання.
- [Праца з наладамі](settings.md) — як дакладныя JSON-значэнні захоўваюцца і выдаляюцца.
- [Даведнік API](api-reference.md) — тыпы, якія вяртаюць `get()` і `all()`.
