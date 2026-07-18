---
sidebar_position: 6
title: Преобразования данных
description: Декодирование данных настроек в массивы, пользовательские значения или объекты Spatie Laravel Data.
---

[← Конфигурация](configuration.md) · [Вернуться к README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Справочник API →](api-reference.md)

# Преобразования данных

## Стандартные JSON-значения

Без пользовательского преобразования пакет кодирует каждое значение в JSON при записи и возвращает
точное декодированное JSON-значение при чтении. Это относится к `null`, пустым строкам, строкам из
пробелов, пустым массивам, нулю и `false`.

```php
$user->settings()->set('notifications', [
    'email' => true,
    'push' => false,
]);

$notifications = $user->settings()->get('notifications');
```

Значения должны поддерживать сериализацию в JSON. Ошибки кодирования JSON не подавляются.

## Выбор преобразования

Устаревшая форма для всей модели применяет одно преобразование ко всем настройкам класса
родительской модели:

```php
'casts' => [
    App\Models\User::class => App\Casts\UserSettingsPayloadCast::class,
],
```

Используйте карту по ключам, если специальная обработка нужна только точным ключам настроек:

```php
'casts' => [
    App\Models\User::class => [
        'profile' => App\Data\ProfileData::class,
        'billing.credentials' => App\Casts\EncryptedSettingPayload::class,
    ],
],
```

Перед выбором преобразования псевдонимы Laravel morph map разрешаются обратно в класс родительской
модели. Сопоставление использует сохранённый ключ настройки, а не имя Eloquent-атрибута `payload`.
Точки считаются обычными символами, поэтому `billing.credentials` — один ключ. Отсутствующие в карте
ключи используют обычный JSON.

Настроенный класс должен реализовывать `CastsAttributes` или расширять `Spatie\LaravelData\Data`.
Неверный, отсутствующий, неподдерживаемый или неразрешимый контейнером класс выбрасывает
`InvalidPayloadCast`; для настроенной записи пакет не переключается на обычный JSON без ошибки.

## Жизненный цикл преобразования

Для реализации `CastsAttributes` пакет выполняет следующую последовательность:

| Направление | Последовательность |
|-------------|--------------------|
| Запись | Вызвать пользовательский `set()`, затем закодировать результат в JSON |
| Чтение | Передать сохранённую JSON-строку в пользовательский `get()` |

Аргумент `$model` содержит настроенную модель хранения настроек, а не родительскую модель `User` или
`Post`. Реализации `CastsAttributes` разрешаются через контейнер Laravel, поэтому зависимости
конструктора могут использовать обычные привязки контейнера. Пользовательский `set()` получает все
входные значения, включая значения, которые Laravel считает пустыми.

## Преобразование атрибута Eloquent

Преобразование может реализовывать контракт Laravel `CastsAttributes`:

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

Результат пользовательского `set()` должен поддерживать сериализацию в JSON. Ошибки кодирования
JSON не подавляются.

## Шифрование отдельного ключа

Шифрование следует реализовать в преобразовании приложения, потому что схема пакета не определяет
метаданные шифрования и контракт ротации ключей. Это преобразование шифрует одну настройку, а
остальные ключи оставляет на стандартном пути JSON:

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

Зарегистрируйте его для точного литерального ключа:

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

Не записывайте значение в журнал до или после преобразования. Если ключи шифрования могут
измениться, определите и протестируйте политику ротации приложения до сохранения производственных
данных. Не добавляйте столбцы метаданных в таблицу пакета без отдельного контракта хранения,
определяющего версионирование и ротацию.

## Spatie Laravel Data

Если установлен `spatie/laravel-data`, класс `Data` можно использовать напрямую:

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

Передайте в `set()` данные, принимаемые классом, или экземпляр `Data`. `get()` возвращает экземпляр
данных, а `all()` — коллекцию с экземплярами данных.

```php
$preferences = UserSettingsData::from([
    'timezone' => 'Europe/Paris',
    'notifications' => true,
]);

$user->settings()->set('preferences', $preferences);

$preferences = $user->settings()->get('preferences');
```

Другие ключи той же модели продолжают использовать обычный JSON. Применяйте устаревшую форму для
всей модели только тогда, когда данные каждой настройки этого владельца подходят настроенному классу
данных.

## Ошибки преобразования

`DragonCode\LaravelModelSettings\Exceptions\InvalidPayloadCast` указывает класс родительской модели,
ключ настройки и настроенное преобразование при ошибке разрешения. Исключение никогда не содержит
данные настройки. Оно выбрасывается для одиночных и групповых записей, а также при чтении
сохранённого значения через такую настроенную запись.

## См. также

- [Конфигурация](configuration.md) — регистрация преобразований и замена модели хранения.
- [Работа с настройками](settings.md) — сохранение и удаление точных JSON-значений.
- [Справочник API](api-reference.md) — возвращаемые типы `get()` и `all()`.
