---
sidebar_position: 8
title: Распрацоўка
description: Запуск тэстаў, праверка дакументацыі, удзел у распрацоўцы і паведамленне пра ўразлівасць.
---

[← Даведнік API](api-reference.md) · [Вярнуцца да README](https://github.com/TheDragonCode/laravel-model-settings#readme)

# Распрацоўка

## Праверкі пакета

Усталюйце PHP-залежнасці:

```bash
composer install
```

Запусціце тэсты або стварыце справаздачу аб пакрыцці:

```bash
composer test
composer test:coverage
```

Прымяніце наладжаны стыль кода:

```bash
composer style
```

Наборы Pest правяраюць розныя кантракты:

| Набор | Пакрыццё |
|-------|----------|
| `tests/Feature` | Значэнні па змаўчанні, перавызначэнні, выдаленне, адсутныя даныя і ўладальнікі |
| `tests/Unit/Casts` | Стандартны JSON, карыстальніцкія пераўтварэнні, morph map і Laravel Data |
| `tests/Unit/KeyTypes` | Радковыя і цэлалікавыя ключы, backed enum і pure unit enum |
| `tests/Unit/PrimaryKeyTypes` | Цэлалікавыя, радковыя, UUID- і ULID-ідэнтыфікатары бацькоўскіх мадэляў |
| `tests/Unit/QueryCount` | Колькасць запытаў пры чытанні і запісе, уключаючы папярэднюю загрузку |
| `tests/Architecture` | Прасторы імёнаў, тыпы, строгі рэжым і архітэктурныя правілы Laravel |

## Праверкі дакументацыі

Для сайта Docusaurus патрэбны Node.js 20 або навейшы. Усталюйце залежнасці з каталога `docs`:

```bash
npm ci
```

| Задача | Каманда |
|--------|---------|
| Запусціць лакальны сайт | `npm run start` |
| Праверыць TypeScript | `npm run typecheck` |
| Праверыць пераклады | `npm run check:i18n` |
| Стварыць production-зборку | `npm run build` |

Production-зборка правярае ўнутраныя спасылкі для кожнай наладжанай лакалі.

Захоўвайце старонкі дакументацыі ў `docs/docs`. Кожная старонка выкарыстоўвае front matter для
пазіцыі ў бакавой панэлі, радок навігацыі ў пачатку, адносныя спасылкі паміж кіраўніцтвамі і раздзел
`Гл. таксама` у канцы.

Захоўвайце кожную лакаль, акрамя асноўнай, у каталогу
`docs/i18n/<locale>/docusaurus-plugin-content-docs/current`. Кожная лакаль павінна змяшчаць тыя ж шляхі
старонак, што і `docs/docs`. Каманда `npm run check:i18n` правярае гэта перад production-зборкай.

## Удзел у распрацоўцы

Перад стварэннем pull request прачытайце
[кіраўніцтва па ўдзеле](https://github.com/TheDragonCode/.github/blob/main/CONTRIBUTING.md).

## Бяспека

Адпраўляйце паведамленні пра ўразлівасці прыватна на
[helldar@dragon-code.pro](mailto:helldar@dragon-code.pro).

## Аўтары

Пакет стварылі [Andrey Helldar](https://github.com/andrey-helldar) і
[удзельнікі праекта](https://github.com/TheDragonCode/laravel-model-settings/graphs/contributors).

## Гл. таксама

- [Пачатак працы](getting-started.md) — усталяванне пакета ў Laravel-праграму.
- [Канфігурацыя](configuration.md) — апублікаваныя файлы пакета.
- [Даведнік API](api-reference.md) — публічны API перад змяненнем паводзін.
