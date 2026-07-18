---
sidebar_position: 2
title: Початок роботи
description: Встановлення Laravel Model Settings і збереження першого значення за замовчуванням та перевизначення.
---

[← Огляд](index.md) · [Повернутися до README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Робота з налаштуваннями →](settings.md)

# Початок роботи

## Вимоги

- PHP 8.3 або новіша версія.
- Laravel 12 або 13.

## Встановлення пакета

```bash
composer require dragon-code/laravel-model-settings
```

Laravel автоматично виявляє сервіс-провайдер пакета.

Опублікуйте конфігурацію та міграцію, потім створіть таблицю налаштувань:

```bash
php artisan vendor:publish --tag="model_settings"
php artisan migrate
```

Тег `model_settings` публікує `config/model_settings.php` і міграцію пакета. За замовчуванням міграція
створює таблицю `settings`, використовуючи стандартне підключення застосунку до бази даних.

## Додавання трейта

Додайте `HasSettings` до кожної Eloquent-моделі, якій потрібні налаштування:

```php
namespace App\Models;

use DragonCode\LaravelModelSettings\Concerns\HasSettings;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasSettings;
}
```

Трейт додає такі публічні методи:

| Метод | Призначення |
|-------|-------------|
| `settings()` | Читання або зміна підсумкових налаштувань однієї збереженої моделі |
| `defaultSettings()` | Читання або зміна значень за замовчуванням для класу моделі |
| `modelSettings()` | Eloquent-зв’язок для попереднього завантаження |

## Збереження першого налаштування

Створіть значення за замовчуванням для всіх збережених моделей `User`:

```php
(new User)->defaultSettings()->set('timezone', 'UTC');
```

Перевизначте це значення для одного збереженого користувача:

```php
$user = User::query()->firstOrFail();

$user->settings()->set('timezone', 'Europe/Paris');

assert($user->settings()->get('timezone') === 'Europe/Paris');
```

Прочитайте всі підсумкові налаштування як колекцію з ключами за назвами налаштувань:

```php
$settings = $user->settings()->all();

assert($settings->get('timezone') === 'Europe/Paris');
```

Видаліть перевизначення, щоб знову використовувати `UTC`:

```php
$user->settings()->forget('timezone');

assert($user->settings()->get('timezone') === 'UTC');
```

## Спочатку зберігайте моделі

Викликайте `settings()->set()` і `settings()->forget()` лише після збереження батьківської моделі. Для
незбереженої моделі `settings()->get()` повертає `null`, а `settings()->all()` — порожню колекцію,
навіть якщо для класу є значення за замовчуванням. Обидва методи зміни викидають
`DragonCode\LaravelModelSettings\Exceptions\InvalidSettingsOwnerException` до виконання запиту до
сховища.

## Див. також

- [Робота з налаштуваннями](settings.md) — пріоритети, видалення, ключі й значення.
- [Конфігурація](configuration.md) — вибір підключення, таблиці та моделі зберігання.
- [Попереднє завантаження](eager-loading.md) — ефективне завантаження налаштувань для колекцій моделей.
