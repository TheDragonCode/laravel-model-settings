---
sidebar_position: 1
slug: /
title: Laravel Model Settings
description: 为 Laravel Eloquent 模型提供共享默认设置和单个模型的覆盖设置。
---

[返回 README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [快速开始 →](getting-started.md)

# Laravel Model Settings

Laravel Model Settings 将共享默认设置和单个模型的覆盖设置存储在独立的数据库表中。当每个模型都需要
使用相同的初始值，但个别记录可以覆盖该值时，可以使用此软件包。

此软件包不会向父表添加设置字段。设置独立于模型结构，并按模型的 Eloquent morph 类进行分组。

## 适用场景

| 需求 | 软件包行为 |
|------|------------|
| 让所有已保存模型使用相同的初始值 | 存储一个类级默认值 |
| 修改一个模型的值 | 存储该模型的覆盖值 |
| 删除覆盖值 | 重新使用类默认值 |
| 读取多个模型 | 预加载包含默认值和覆盖值的一个关联 |

## 值解析顺序

读取设置时，软件包会返回第一个可用值：

1. 已保存模型的覆盖值。
2. 该模型类的默认值。
3. `null`。

| 来源 | `timezone` |
|------|------------|
| `User` 默认值 | `UTC` |
| 用户 123 的覆盖值 | `Europe/Paris` |
| 用户 123 的最终值 | `Europe/Paris` |
| 其他已保存用户的最终值 | `UTC` |

删除覆盖值后会再次使用默认值，但不会删除默认值本身。

## 核心操作

```php
(new User)->defaultSettings()->set('timezone', 'UTC');

$user->settings()->set('timezone', 'Europe/Paris');

$timezone = $user->settings()->get('timezone');
$settings = $user->settings()->all();
$hasTimezone = $settings->has('timezone');

$user->settings()->setMany([
    'locale' => 'fr',
    'notifications.email' => true,
]);
$user->settings()->forgetMany(['timezone', 'locale']);
```

`get()` 返回一个最终值。`all()` 返回一个 `Illuminate\Support\Collection`，其中默认值与覆盖值已合并。
需要判断最终键是否存在时，请使用集合的 `has()` 方法。`get()` 有意不接受调用方提供的回退值参数：唯一的
回退值是持久化的类默认值；两个作用域都没有该键时返回 `null`。

默认值和覆盖值使用相同的操作：`all()`、`get()`、`set()`、`setMany()`、`forget()`、`forgetMany()` 和
`purge()`。

## 明确的软件包边界

Laravel Model Settings 是一个专注于 Eloquent 的软件包，不是通用的应用程序设置框架。

| 边界 | 预期行为 |
|------|----------|
| 存储 | 使用一个数据库表；不提供 Redis 后端或父模型字段存储 |
| 默认值 | 使用同一表中的保留记录；不提供第二个默认值表 |
| 注册 | 不提供仓库注册表、类型化全局设置类或类自动发现 |
| 迁移 | 不提供按设置键运行的迁移器 |
| 缓存 | 不强制使用跨请求缓存；预加载只复用已加载的关联 |

需要这些功能的应用程序应在软件包外部组合实现，不应将 `modelSettings` 或内部仓库视为扩展 API。

## 存储边界

每一行由三个值标识：

| 值 | 含义 |
|----|------|
| `item_type` | 父模型的 morph 类或 morph map 别名 |
| `item_id` | 父模型主键，或用于类默认值的保留值 `0` |
| `key` | 设置名称 |

因此，每个模型类的默认值相互独立。即使 `User` 和 `Post` 使用相同的设置键，`User` 的默认值也不会成为
`Post` 的默认值。

## 支持的模型

此软件包支持使用整数、字符串、UUID 或 ULID 主键的 Eloquent 模型。模型也可以使用 Laravel morph map。

单个模型的设置只属于已持久化的模型。未保存的模型不会继承默认值：`get()` 返回 `null`，`all()` 返回空集合。
对未保存的所有者调用 `set()`、`setMany()`、`forget()`、`forgetMany()` 或 `purge()` 时，会在执行存储
查询前抛出 `InvalidSettingsOwnerException`。

设置数据以 JSON 格式存储。未配置转换时，读取操作会返回解码后的数组或标量值。
[数据转换](payload-casts.md)可以改为返回应用程序专用对象。

## 另请参阅

- [快速开始](getting-started.md) — 安装软件包并配置模型。
- [使用设置](settings.md) — 管理默认值、覆盖值、键和值。
- [API 参考](api-reference.md) — 查看所有公共方法和返回类型。
