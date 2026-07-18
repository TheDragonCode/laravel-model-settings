---
sidebar_position: 8
title: Разработка
description: Запуск тестов, проверка документации, участие в разработке и сообщение об уязвимости.
---

[← Справочник API](api-reference.md) · [Вернуться к README](https://github.com/TheDragonCode/laravel-model-settings#readme)

# Разработка

## Проверки пакета

Установите PHP-зависимости:

```bash
composer install
```

Запустите тесты или сформируйте отчёт о покрытии:

```bash
composer test
composer test:coverage
```

Примените настроенный стиль кода:

```bash
composer style
```

Наборы Pest проверяют разные контракты:

| Набор | Покрытие |
|-------|----------|
| `tests/Feature` | Значения по умолчанию, переопределения, удаление, отсутствующие данные и владельцы |
| `tests/Unit/Casts` | Стандартный JSON, пользовательские преобразования, morph map и Laravel Data |
| `tests/Unit/KeyTypes` | Строковые, целочисленные ключи, backed enum и pure unit enum |
| `tests/Unit/PrimaryKeyTypes` | Целочисленные, строковые, UUID- и ULID-идентификаторы родительских моделей |
| `tests/Unit/QueryCount` | Количество запросов при чтении и записи, включая предварительную загрузку |
| `tests/Architecture` | Пространства имён, типы, строгий режим и архитектурные правила Laravel |

## Проверки документации

Для сайта Docusaurus требуется Node.js 20 или новее. Установите зависимости из каталога `docs`:

```bash
npm ci
```

| Задача | Команда |
|--------|---------|
| Запустить локальный сайт | `npm run start` |
| Проверить TypeScript | `npm run typecheck` |
| Проверить переводы | `npm run check:i18n` |
| Создать production-сборку | `npm run build` |

Production-сборка проверяет внутренние ссылки для каждой настроенной локали.

Храните страницы документации в `docs/docs`. Каждая страница использует front matter для позиции в
боковой панели, строку навигации в начале, относительные ссылки между руководствами и раздел
`См. также` в конце.

Храните каждую локаль, кроме основной, в каталоге
`docs/i18n/<locale>/docusaurus-plugin-content-docs/current`. Каждая локаль должна содержать те же пути
страниц, что и `docs/docs`. Команда `npm run check:i18n` проверяет это перед production-сборкой.

## Участие в разработке

Перед созданием pull request прочитайте
[руководство по участию](https://github.com/TheDragonCode/.github/blob/main/CONTRIBUTING.md).

## Безопасность

Отправляйте сообщения об уязвимостях приватно на
[helldar@dragon-code.pro](mailto:helldar@dragon-code.pro).

## Авторы

Пакет создан [Andrey Helldar](https://github.com/andrey-helldar) и
[участниками проекта](https://github.com/TheDragonCode/laravel-model-settings/graphs/contributors).

## См. также

- [Начало работы](getting-started.md) — установка пакета в Laravel-приложение.
- [Конфигурация](configuration.md) — опубликованные файлы пакета.
- [Справочник API](api-reference.md) — публичный API перед изменением поведения.
