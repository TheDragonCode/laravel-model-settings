---
sidebar_position: 8
title: Розробка
description: Запуск тестів, перевірка документації, участь у розробці та повідомлення про вразливість.
---

[← Довідник API](api-reference.md) · [Повернутися до README](https://github.com/TheDragonCode/laravel-model-settings#readme)

# Розробка

## Перевірки пакета

Встановіть PHP-залежності:

```bash
composer install
```

Запустіть тести або створіть звіт про покриття:

```bash
composer test
composer test:coverage
```

Застосуйте налаштований стиль коду:

```bash
composer style
```

Набори Pest перевіряють різні контракти:

| Набір | Покриття |
|-------|----------|
| `tests/Feature` | Значення за замовчуванням, перевизначення, видалення, відсутні дані та власники |
| `tests/Unit/Casts` | Стандартний JSON, користувацькі перетворення, morph map і Laravel Data |
| `tests/Unit/KeyTypes` | Рядкові й цілочислові ключі, backed enum і pure unit enum |
| `tests/Unit/PrimaryKeyTypes` | Цілочислові ідентифікатори, UUID і ULID батьківських моделей |
| `tests/Unit/QueryCount` | Кількість запитів під час читання й запису, включно з попереднім завантаженням |
| `tests/Architecture` | Простори імен, типи, строгий режим та архітектурні правила Laravel |

## Перевірки документації

Для сайту Docusaurus потрібен Node.js 20 або новіший. Встановіть залежності з каталогу `docs`:

```bash
npm ci
```

| Завдання | Команда |
|----------|---------|
| Запустити локальний сайт | `npm run start` |
| Перевірити TypeScript | `npm run typecheck` |
| Перевірити переклади | `npm run check:i18n` |
| Створити production-збірку | `npm run build` |

Production-збірка перевіряє внутрішні посилання для кожної налаштованої локалі.

Зберігайте сторінки документації в `docs/docs`. Кожна сторінка використовує front matter для позиції
в бічній панелі, рядок навігації на початку, відносні посилання між посібниками та розділ `Див. також`
наприкінці.

Зберігайте кожну локаль, крім основної, у каталозі
`docs/i18n/<locale>/docusaurus-plugin-content-docs/current`. Кожна локаль має містити ті самі шляхи
сторінок, що й `docs/docs`. Команда `npm run check:i18n` перевіряє це перед production-збіркою.

## Участь у розробці

Перед створенням pull request прочитайте
[посібник з участі](https://github.com/TheDragonCode/.github/blob/main/CONTRIBUTING.md).

## Безпека

Надсилайте повідомлення про вразливості приватно на
[helldar@dragon-code.pro](mailto:helldar@dragon-code.pro).

## Автори

Пакет створили [Andrey Helldar](https://github.com/andrey-helldar) і
[учасники проєкту](https://github.com/TheDragonCode/laravel-model-settings/graphs/contributors).

## Див. також

- [Початок роботи](getting-started.md) — встановлення пакета в Laravel-застосунок.
- [Конфігурація](configuration.md) — опубліковані файли пакета.
- [Довідник API](api-reference.md) — публічний API перед зміною поведінки.
