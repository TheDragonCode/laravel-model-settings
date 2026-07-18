---
sidebar_position: 8
title: Development
description: Run the test suite, validate documentation, contribute, or report a security issue.
---

[← API Reference](api-reference.md) · [Back to README](https://github.com/TheDragonCode/laravel-model-settings#readme)

# Development

## Package checks

Install the PHP dependencies:

```bash
composer install
```

Run the test suite or generate coverage:

```bash
composer test
composer test:coverage
```

Apply the configured code style:

```bash
composer style
```

The Pest suites cover different contracts:

| Suite | Coverage |
|-------|----------|
| `tests/Feature` | Defaults, overrides, deletion, missing data, and ownership |
| `tests/Unit/Casts` | Default JSON, custom casts, morph maps, and Laravel Data |
| `tests/Unit/KeyTypes` | String, integer, backed enum, and pure unit enum keys |
| `tests/Unit/PrimaryKeyTypes` | Integer, string, UUID, and ULID parent identifiers |
| `tests/Unit/QueryCount` | Read and write query counts, including eager loading |
| `tests/Architecture` | Namespace, type, strictness, and Laravel architecture rules |

## Documentation checks

The Docusaurus site requires Node.js 20 or newer. Install its dependencies from the `docs`
directory:

```bash
npm ci
```

| Task | Command |
|------|---------|
| Start the local site | `npm run start` |
| Check TypeScript | `npm run typecheck` |
| Check translations | `npm run check:i18n` |
| Create a production build | `npm run build` |

The production build validates internal links for every configured locale.

Keep documentation pages in `docs/docs`. Each page uses front matter for sidebar order, a navigation
line at the top, relative links between guides, and a `See Also` section at the end.

Keep each non-default locale in
`docs/i18n/<locale>/docusaurus-plugin-content-docs/current`. Every locale must contain the same page
paths as `docs/docs`. The `npm run check:i18n` command enforces this before a production build.

## Contributing

Follow the [contribution guide](https://github.com/TheDragonCode/.github/blob/main/CONTRIBUTING.md)
before opening a pull request.

## Security

Report security issues privately to [helldar@dragon-code.pro](mailto:helldar@dragon-code.pro).

## Credits

Created by [Andrey Helldar](https://github.com/andrey-helldar) and
[the project contributors](https://github.com/TheDragonCode/laravel-model-settings/graphs/contributors).

## See Also

- [Getting Started](getting-started.md) — install the package in a Laravel application.
- [Configuration](configuration.md) — understand the published package files.
- [API Reference](api-reference.md) — review the public API before changing behavior.
