---
sidebar_position: 6
title: Перетворення даних
description: Декодування даних налаштувань у масиви, користувацькі значення або об’єкти Spatie Laravel Data.
---

[← Конфігурація](configuration.md) · [Повернутися до README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Довідник API →](api-reference.md)

# Перетворення даних

## Стандартні JSON-значення

Без користувацького перетворення пакет кодує непорожні значення у JSON під час запису й повертає
декодовані масиви або скалярні значення під час читання.

```php
$user->settings()->set('notifications', [
    'email' => true,
    'push' => false,
]);

$notifications = $user->settings()->get('notifications');
```

Значення мають підтримувати серіалізацію в JSON. Помилки кодування JSON не приховуються.

## Вибір перетворення

Сумісна форма для всієї моделі застосовує одне перетворення до всіх налаштувань класу
батьківської моделі:

```php
'casts' => [
    App\Models\User::class => App\Casts\UserSettingsPayloadCast::class,
],
```

Використовуйте мапу за ключами, якщо спеціальна обробка потрібна лише точним ключам налаштувань:

```php
'casts' => [
    App\Models\User::class => [
        'profile' => App\Data\ProfileData::class,
        'billing.credentials' => App\Casts\EncryptedSettingPayload::class,
    ],
],
```

Перед вибором перетворення псевдоніми Laravel morph map розв’язуються назад у клас батьківської
моделі. Зіставлення використовує збережений ключ налаштування, а не назву Eloquent-атрибута
`payload`. Крапки є звичайними символами, тому `billing.credentials` — один ключ. Відсутні в мапі
ключі використовують звичайний JSON.

Налаштований клас має реалізовувати `CastsAttributes` або розширювати `Spatie\LaravelData\Data`.
Неправильний, відсутній, непідтримуваний або нерозв’язний контейнером клас викидає
`InvalidPayloadCast`; для налаштованого запису пакет не переходить до звичайного JSON без помилки.

## Життєвий цикл перетворення

Для реалізації `CastsAttributes` пакет виконує таку послідовність:

| Напрямок | Послідовність |
|----------|---------------|
| Запис | Викликати користувацький `set()`, потім закодувати результат у JSON |
| Читання | Передати збережений JSON-рядок у користувацький `get()` |

Аргумент `$model` містить налаштовану модель зберігання налаштувань, а не батьківську модель `User`
чи `Post`. Реалізації `CastsAttributes` розв’язуються через контейнер Laravel, тому залежності
конструктора можуть використовувати звичайні прив’язки контейнера.

## Перетворення атрибута Eloquent

Перетворення може реалізовувати контракт Laravel `CastsAttributes`:

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

Результат користувацького `set()` має підтримувати серіалізацію в JSON. Помилки кодування JSON не
приховуються.

## Шифрування окремого ключа

Шифрування слід реалізувати в перетворенні застосунку, оскільки схема пакета не визначає метадані
шифрування та контракт ротації ключів. Це перетворення шифрує один ключ, а решту залишає на
стандартному шляху JSON:

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

Зареєструйте його для точного літерального ключа:

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

Не записуйте значення в журнал до або після перетворення. Якщо ключі шифрування можуть змінюватися,
визначте й перевірте політику ротації застосунку до зберігання production-даних. Не додавайте стовпці
метаданих до таблиці пакета без окремого контракту зберігання, що визначає версіонування та ротацію.

## Spatie Laravel Data

Якщо встановлено `spatie/laravel-data`, клас `Data` можна використовувати безпосередньо:

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

Передайте в `set()` дані, які приймає клас, або екземпляр `Data`. `get()` повертає екземпляр даних,
а `all()` — колекцію з екземплярами даних.

```php
$preferences = UserSettingsData::from([
    'timezone' => 'Europe/Paris',
    'notifications' => true,
]);

$user->settings()->set('preferences', $preferences);

$preferences = $user->settings()->get('preferences');
```

Інші ключі тієї самої моделі продовжують використовувати звичайний JSON. Використовуйте сумісну форму
для всієї моделі лише тоді, коли дані кожного налаштування цього власника підходять налаштованому
класу даних.

## Помилки перетворення

`DragonCode\LaravelModelSettings\Exceptions\InvalidPayloadCast` указує клас батьківської моделі, ключ
налаштування та налаштоване перетворення в разі помилки розв’язання. Виняток ніколи не містить дані
налаштування. Він викидається для одиночних і групових записів, а також під час читання збереженого
значення через такий налаштований запис.

## Див. також

- [Конфігурація](configuration.md) — реєстрація перетворень і заміна моделі зберігання.
- [Робота з налаштуваннями](settings.md) — які значення вважаються порожніми й видаляються.
- [Довідник API](api-reference.md) — типи, які повертають `get()` і `all()`.
