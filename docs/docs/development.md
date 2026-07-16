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
| Create a production build | `npm run build` |

The production build validates internal links for every configured locale.

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
