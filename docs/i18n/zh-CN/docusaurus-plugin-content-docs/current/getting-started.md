---
sidebar_position: 2
title: 快速开始
description: 安装 Laravel Model Settings，并保存第一个默认值和覆盖值。
---

[← 概述](index.md) · [返回 README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [使用设置 →](settings.md)

# 快速开始

## 要求

- PHP 8.3 或更高版本。
- Laravel 12 或 13。

## 安装软件包

```bash
composer require dragon-code/laravel-model-settings
```

Laravel 会自动发现此软件包的服务提供者。

发布配置和迁移，然后创建设置表：

```bash
php artisan vendor:publish --tag="model_settings"
php artisan migrate
```

`model_settings` 标签会发布 `config/model_settings.php` 和软件包迁移。默认迁移使用应用程序的默认数据库
连接创建 `settings` 表。

## 添加 trait

将 `HasSettings` 添加到每个需要设置的 Eloquent 模型：

```php
namespace App\Models;

use DragonCode\LaravelModelSettings\Concerns\HasSettings;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasSettings;
}
```

此 trait 添加以下公共方法：

| 方法 | 用途 |
|------|------|
| `settings()` | 读取或修改一个已保存模型的最终设置 |
| `defaultSettings()` | 读取或修改模型类的默认值 |
| `modelSettings()` | 用于预加载的 Eloquent 关联 |

## 保存第一个设置

为所有已保存的 `User` 模型创建默认值：

```php
(new User)->defaultSettings()->set('timezone', 'UTC');
```

为一个已保存用户覆盖该值：

```php
$user = User::query()->firstOrFail();

$user->settings()->set('timezone', 'Europe/Paris');

assert($user->settings()->get('timezone') === 'Europe/Paris');
```

将所有最终设置读取为按设置名称索引的集合：

```php
$settings = $user->settings()->all();

assert($settings->get('timezone') === 'Europe/Paris');
```

删除覆盖值以重新使用 `UTC`：

```php
$user->settings()->forget('timezone');

assert($user->settings()->get('timezone') === 'UTC');
```

## 先保存模型

只有在父模型保存后才能调用 `settings()->set()`。未保存的模型没有主键。即使类默认值存在，它的
`settings()->get()` 也会返回 `null`，`settings()->all()` 会返回空集合。

## 另请参阅

- [使用设置](settings.md) — 了解优先级、删除、键和值。
- [配置](configuration.md) — 选择连接、数据表或存储模型。
- [预加载](eager-loading.md) — 高效加载模型集合的设置。
