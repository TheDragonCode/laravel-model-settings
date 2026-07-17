---
sidebar_position: 5
title: Конфигурация
description: Настройка модели хранения, подключения к базе данных, таблицы и преобразований данных.
---

[← Предварительная загрузка](eager-loading.md) · [Вернуться к README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Преобразования данных →](payload-casts.md)

# Конфигурация

## Публикация конфигурации

```bash
php artisan vendor:publish --tag="model_settings"
```

Команда публикует `config/model_settings.php` и миграцию пакета.

## Доступные параметры

| Параметр | Значение по умолчанию | Назначение |
|----------|-----------------------|------------|
| `model` | `DragonCode\LaravelModelSettings\Models\Settings` | Eloquent-модель для сохранённых настроек |
| `connection` | Стандартное для приложения | Подключение к базе данных для модели и миграции |
| `table` | `settings` | Таблица базы данных для модели и миграции |
| `casts` | `[]` | Преобразование данных, выбранное по классу родительской модели |

Пакет читает следующие переменные окружения:

| Переменная | Значение по умолчанию |
|------------|-----------------------|
| `MODEL_SETTINGS_DATABASE_CONNECTION` | `DATABASE_CONNECTION`, затем стандартное подключение Laravel |
| `MODEL_SETTINGS_DATABASE_TABLE` | `settings` |

Задайте подключение и таблицу до запуска миграции:

```dotenv
MODEL_SETTINGS_DATABASE_CONNECTION=mysql
MODEL_SETTINGS_DATABASE_TABLE=model_settings
```

Изменение этих параметров позже не переносит существующие записи.

## Схема хранения

Опубликованная миграция создаёт следующие столбцы:

| Столбец | Назначение |
|---------|------------|
| `id` | Первичный ключ строки настройки |
| `item_type` | Morph-класс родительской модели или псевдоним |
| `item_id` | Идентификатор родителя в виде строки длиной до 36 символов |
| `key` | Ключ настройки |
| `payload` | Данные, объявленные в миграции как `jsonb` |
| `created_at` и `updated_at` | Временные метки Laravel |

Комбинация `item_type`, `item_id` и `key` уникальна.

Стандартный столбец `item_id` хранит не более 36 символов. В эту схему помещаются целочисленные
идентификаторы, UUID и ULID. Для более длинного пользовательского первичного ключа потребуется
соответствующее изменение миграции.

Значение `0` в `item_id` зарезервировано для настроек класса по умолчанию. Если данные уже существуют,
изменение подключения, имени таблицы или псевдонимов morph map требует самостоятельного переноса или
обновления строк.

## Замена модели хранения

Встроенная модель настроек объявлена как final. Вместо наследования настройте замену:

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

Затем обновите конфигурацию:

```php
'model' => App\Models\ApplicationSetting::class,
```

Замена должна оставаться совместимой с опубликованной схемой. Сохраните заполняемые атрибуты и
`PayloadCast`, если новая модель не реализует эквивалентную сериализацию.

Как минимум, новая модель должна сохранять следующее поведение:

| Требование | Причина |
|------------|---------|
| Заполнять `item_type`, `item_id`, `key` и `payload` | `updateOrCreate()` записывает эти атрибуты |
| Использовать настроенные подключение и таблицу | Миграция и репозиторий должны обращаться к одним строкам |
| Преобразовывать `item_id` в `string` | Целые числа, UUID и ULID хранятся в одном столбце |
| Преобразовывать `payload` через `PayloadCast` или эквивалент | Чтение и запись должны сохранять поведение JSON |

## См. также

- [Начало работы](getting-started.md) — публикация конфигурации и миграции.
- [Преобразования данных](payload-casts.md) — настройка прикладных типов данных.
- [Справочник API](api-reference.md) — публичная поверхность пакета.
