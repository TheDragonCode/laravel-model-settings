---
sidebar_position: 5
title: Конфігурація
description: Налаштування моделі зберігання, підключення до бази даних, таблиці та перетворень даних.
---

[← Попереднє завантаження](eager-loading.md) · [Повернутися до README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Перетворення даних →](payload-casts.md)

# Конфігурація

## Публікація конфігурації

```bash
php artisan vendor:publish --tag="model_settings"
```

Команда публікує `config/model_settings.php` і міграцію пакета.

## Доступні параметри

| Параметр | Значення за замовчуванням | Призначення |
|----------|---------------------------|-------------|
| `model` | `DragonCode\LaravelModelSettings\Models\Settings` | Eloquent-модель для збережених налаштувань |
| `connection` | Стандартне для застосунку | Підключення до бази даних для моделі та міграції |
| `table` | `settings` | Таблиця бази даних для моделі та міграції |
| `casts` | `[]` | Перетворення даних, вибране за класом батьківської моделі |

Пакет читає такі змінні середовища:

| Змінна | Значення за замовчуванням |
|--------|---------------------------|
| `MODEL_SETTINGS_DATABASE_CONNECTION` | `DATABASE_CONNECTION`, потім стандартне підключення Laravel |
| `MODEL_SETTINGS_DATABASE_TABLE` | `settings` |

Задайте підключення й таблицю до запуску міграції:

```dotenv
MODEL_SETTINGS_DATABASE_CONNECTION=mysql
MODEL_SETTINGS_DATABASE_TABLE=model_settings
```

Пізніша зміна цих параметрів не переносить наявні записи.

## Схема зберігання

Опублікована міграція створює такі стовпці:

| Стовпець | Призначення |
|----------|-------------|
| `id` | Первинний ключ рядка налаштування |
| `item_type` | Morph-клас батьківської моделі або псевдонім |
| `item_id` | Ідентифікатор батьківської моделі як рядок довжиною до 36 символів |
| `key` | Ключ налаштування |
| `payload` | Дані, оголошені в міграції як `jsonb` |
| `created_at` і `updated_at` | Часові мітки Laravel |

Комбінація `item_type`, `item_id` і `key` унікальна.

Стандартний стовпець `item_id` зберігає не більше 36 символів. У цю схему вміщуються цілочислові,
рядкові, UUID- та ULID-ідентифікатори, якщо їхнє рядкове представлення не довше за 36 символів. Для
довшого користувацького первинного ключа потрібна відповідна зміна міграції.

Значення `0` в `item_id` зарезервоване для налаштувань класу за замовчуванням. У версії 1.x `set()` і
`forget()` відхиляють збереженого власника з цілим ключем `0` або рядковим ключем `'0'`, викидаючи
`InvalidSettingsOwnerException` до запиту до цієї таблиці. Якщо дані вже існують, зміна підключення,
назви таблиці або псевдонімів morph map потребує самостійного перенесення чи оновлення рядків.

## Заміна моделі зберігання

Вбудована модель налаштувань оголошена як final. Замість успадкування налаштуйте заміну:

```php
namespace App\Models;

use DragonCode\LaravelModelSettings\Casts\PayloadCast;
use Illuminate\Database\Eloquent\Model;

final class ApplicationSetting extends Model
{
    protected $fillable = [
        'item_type',
        'item_id',
        'key',
        'payload',
    ];

    public function __construct(array $attributes = [])
    {
        $this->setConnection(config('model_settings.connection'));
        $this->setTable(config('model_settings.table'));

        parent::__construct($attributes);
    }

    protected function casts(): array
    {
        return [
            'item_id' => 'string',
            'payload' => PayloadCast::class,
        ];
    }
}
```

Потім оновіть конфігурацію:

```php
'model' => App\Models\ApplicationSetting::class,
```

Заміна має залишатися сумісною з опублікованою схемою. Збережіть заповнювані атрибути та
`PayloadCast`, якщо нова модель не реалізує еквівалентну серіалізацію.

Щонайменше нова модель має зберігати таку поведінку:

| Вимога | Причина |
|--------|---------|
| Заповнювати `item_type`, `item_id`, `key` і `payload` | `updateOrCreate()` записує ці атрибути |
| Використовувати налаштовані підключення й таблицю | Міграція та репозиторій мають звертатися до тих самих рядків |
| Перетворювати `item_id` на `string` | Цілі числа, рядки, UUID і ULID зберігаються в одному стовпці |
| Перетворювати `payload` через `PayloadCast` або еквівалент | Читання й запис мають зберігати поведінку JSON |

## Див. також

- [Початок роботи](getting-started.md) — публікація конфігурації та міграції.
- [Перетворення даних](payload-casts.md) — налаштування прикладних типів даних.
- [Довідник API](api-reference.md) — публічна поверхня пакета.
