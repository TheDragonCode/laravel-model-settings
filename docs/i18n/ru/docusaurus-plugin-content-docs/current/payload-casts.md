---
sidebar_position: 6
title: Преобразования данных
description: Декодирование данных настроек в массивы, пользовательские значения или объекты Spatie Laravel Data.
---

[← Конфигурация](configuration.md) · [Вернуться к README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Справочник API →](api-reference.md)

# Преобразования данных

## Стандартные JSON-значения

Без пользовательского преобразования пакет кодирует непустые значения в JSON при записи и
возвращает декодированные массивы или скалярные значения при чтении.

```php
$user->settings()->set('notifications', [
    'email' => true,
    'push' => false,
]);

$notifications = $user->settings()->get('notifications');
```

Значения должны поддерживать сериализацию в JSON. Ошибки кодирования JSON не подавляются.

## Выбор преобразования

Пользовательские преобразования настраиваются по классу родительской модели:

```php
'casts' => [
    App\Models\User::class => App\Casts\UserSettingsPayloadCast::class,
],
```

Одно настроенное преобразование обрабатывает данные всех настроек этого класса родительской модели.
Перед выбором преобразования псевдонимы Laravel morph map разрешаются обратно в класс модели.

Настроенный класс должен реализовывать `CastsAttributes` или расширять `Spatie\LaravelData\Data`.
Для других классов специальная обработка не применяется, и значения используют стандартный путь JSON.

## Жизненный цикл преобразования

Для реализации `CastsAttributes` пакет выполняет следующую последовательность:

| Направление | Последовательность |
|-------------|--------------------|
| Запись | Вызвать пользовательский `set()`, затем закодировать результат в JSON |
| Чтение | Передать сохранённую JSON-строку в пользовательский `get()` |

Аргумент `$model` содержит настроенную модель хранения настроек, а не родительскую модель `User` или
`Post`. Пакет создаёт экземпляр преобразования без аргументов конструктора.

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

## Spatie Laravel Data

Если установлен `spatie/laravel-data`, класс `Data` можно использовать напрямую:

```bash
composer require spatie/laravel-data:^4.23
```

```php
'casts' => [
    App\Models\User::class => App\Data\UserSettingsData::class,
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

Преобразование выбирается для класса родительской модели, а не для отдельного ключа. Поэтому данные
каждой настройки этой модели должны быть допустимым входом для настроенного преобразования.

## См. также

- [Конфигурация](configuration.md) — регистрация преобразований и замена модели хранения.
- [Работа с настройками](settings.md) — какие значения считаются пустыми и удаляются.
- [Справочник API](api-reference.md) — возвращаемые типы `get()` и `all()`.
