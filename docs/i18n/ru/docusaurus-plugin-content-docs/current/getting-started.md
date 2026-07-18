---
sidebar_position: 2
title: Начало работы
description: Установка Laravel Model Settings и сохранение первого значения по умолчанию и переопределения.
---

[← Обзор](index.md) · [Вернуться к README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Работа с настройками →](settings.md)

# Начало работы

## Требования

- PHP 8.3 или новее.
- Laravel 12 или 13.

## Установка пакета

```bash
composer require dragon-code/laravel-model-settings
```

Laravel автоматически обнаруживает сервис-провайдер пакета.

Опубликуйте конфигурацию и миграцию, затем создайте таблицу настроек:

```bash
php artisan vendor:publish --tag="model_settings"
php artisan migrate
```

Тег `model_settings` публикует `config/model_settings.php` и миграцию пакета. По умолчанию миграция
создаёт таблицу `settings`, используя стандартное подключение приложения к базе данных.

## Добавление трейта

Добавьте `HasSettings` в каждую Eloquent-модель, которой нужны настройки:

```php
namespace App\Models;

use DragonCode\LaravelModelSettings\Concerns\HasSettings;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasSettings;
}
```

Трейт добавляет следующие публичные методы:

| Метод | Назначение |
|-------|------------|
| `settings()` | Чтение или изменение итоговых настроек одной сохранённой модели |
| `defaultSettings()` | Чтение или изменение значений по умолчанию для класса модели |
| `modelSettings()` | Eloquent-связь для предварительной загрузки |

## Сохранение первой настройки

Создайте значение по умолчанию для всех сохранённых моделей `User`:

```php
(new User)->defaultSettings()->set('timezone', 'UTC');
```

Переопределите это значение для одного сохранённого пользователя:

```php
$user = User::query()->firstOrFail();

$user->settings()->set('timezone', 'Europe/Paris');

assert($user->settings()->get('timezone') === 'Europe/Paris');
```

Прочитайте все итоговые настройки как коллекцию с ключами по именам настроек:

```php
$settings = $user->settings()->all();

assert($settings->get('timezone') === 'Europe/Paris');
```

Удалите переопределение, чтобы снова использовать `UTC`:

```php
$user->settings()->forget('timezone');

assert($user->settings()->get('timezone') === 'UTC');
```

## Сначала сохраняйте модели

Вызывайте `settings()->set()`, `setMany()`, `forget()`, `forgetMany()` и `purge()` только после
сохранения родительской модели. Для несохранённой модели `settings()->get()` возвращает `null`, а
`settings()->all()` — пустую коллекцию, даже если для класса есть значения по умолчанию. Каждый
изменяющий метод выбрасывает
`DragonCode\LaravelModelSettings\Exceptions\InvalidSettingsOwnerException` до выполнения запроса к
хранилищу.

## См. также

- [Работа с настройками](settings.md) — приоритеты, удаление, ключи и значения.
- [Конфигурация](configuration.md) — выбор подключения, таблицы и модели хранения.
- [Предварительная загрузка](eager-loading.md) — эффективная загрузка настроек для коллекций моделей.
