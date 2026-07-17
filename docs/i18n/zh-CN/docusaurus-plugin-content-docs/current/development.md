---
sidebar_position: 8
title: 开发
description: 运行测试、验证文档、参与贡献或报告安全问题。
---

[← API 参考](api-reference.md) · [返回 README](https://github.com/TheDragonCode/laravel-model-settings#readme)

# 开发

## 软件包检查

安装 PHP 依赖：

```bash
composer install
```

运行测试套件或生成覆盖率：

```bash
composer test
composer test:coverage
```

应用已配置的代码风格：

```bash
composer style
```

不同的 Pest 测试套件覆盖不同的契约：

| 测试套件 | 覆盖内容 |
|----------|----------|
| `tests/Feature` | 默认值、覆盖值、删除、缺失数据和所有权 |
| `tests/Unit/Casts` | 默认 JSON、自定义转换、morph map 和 Laravel Data |
| `tests/Unit/KeyTypes` | 字符串、整数、backed enum 和 pure unit enum 键 |
| `tests/Unit/PrimaryKeyTypes` | 整数、UUID 和 ULID 父模型标识符 |
| `tests/Unit/QueryCount` | 读取和写入的查询次数，包括预加载 |
| `tests/Architecture` | 命名空间、类型、严格性和 Laravel 架构规则 |

## 文档检查

Docusaurus 站点需要 Node.js 20 或更高版本。请在 `docs` 目录中安装依赖：

```bash
npm ci
```

| 任务 | 命令 |
|------|------|
| 启动本地站点 | `npm run start` |
| 检查 TypeScript | `npm run typecheck` |
| 检查翻译 | `npm run check:i18n` |
| 创建生产构建 | `npm run build` |

生产构建会验证每个已配置 locale 的内部链接。

请将文档页面保存在 `docs/docs` 中。每个页面使用 front matter 指定侧边栏顺序，在顶部提供导航行，使用指南间的
相对链接，并在末尾提供 `另请参阅` 部分。

请将默认 locale 之外的每个 locale 保存在
`docs/i18n/<locale>/docusaurus-plugin-content-docs/current` 中。每个 locale 必须包含与 `docs/docs` 相同的
页面路径。`npm run check:i18n` 命令会在生产构建前检查这一点。

## 参与贡献

提交 pull request 前，请阅读[贡献指南](https://github.com/TheDragonCode/.github/blob/main/CONTRIBUTING.md)。

## 安全

请将安全问题私下发送至 [helldar@dragon-code.pro](mailto:helldar@dragon-code.pro)。

## 致谢

由 [Andrey Helldar](https://github.com/andrey-helldar) 和
[项目贡献者](https://github.com/TheDragonCode/laravel-model-settings/graphs/contributors)创建。

## 另请参阅

- [快速开始](getting-started.md) — 在 Laravel 应用程序中安装软件包。
- [配置](configuration.md) — 了解软件包发布的文件。
- [API 参考](api-reference.md) — 修改行为前查看公共 API。
