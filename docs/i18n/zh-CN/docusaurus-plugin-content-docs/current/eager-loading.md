---
sidebar_position: 4
title: 预加载
description: 读取 Eloquent 模型集合的设置时避免 N+1 查询。
---

[← 使用设置](settings.md) · [返回 README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [配置 →](configuration.md)

# 预加载

## 与模型一起加载设置

未预加载时，每次调用 `settings()->get()`、`settings()->has()` 或 `settings()->all()` 都会执行一次设置
查询。这些服务读取操作不会产生加载 `modelSettings` 的副作用。

当结果包含多个模型时，请预加载该关联：

```php
$users = User::query()
    ->with('modelSettings')
    ->get();

$timezones = $users->map(
    fn (User $user) => $user->settings()->get('timezone')
);
```

预加载的关联包含每个模型的覆盖值及其继承的所有默认值。后续的 `get()`、`has()` 和 `all()` 调用会使用
已加载关联。

## 查询后加载设置

如果模型已经存在，请使用 `loadMissing()`：

```php
$users->loadMissing('modelSettings');

$settings = $users->map(
    fn (User $user) => $user->settings()->all()
);
```

## 关联边界

`modelSettings` 只能与 `with()`、`load()` 或 `loadMissing()` 一起使用，也可以作为已加载的关联属性使用。
它是读取优化，不是替代查询或 CRUD API。请通过 `settings()` 或 `defaultSettings()` 读取和修改值。

## 查询行为

先获取父模型再读取设置时，对于一个模型，延迟加载和预加载的成本相同。对于集合，差异很明显：

| 已加载的父模型 | 延迟加载 | 预加载 |
|----------------|----------|--------|
| 1 | 2 次查询 | 2 次查询 |
| N | 1 + N 次查询 | 2 次查询 |

预加载路径会执行：

1. 一次父模型查询。
2. 一次默认值和覆盖值查询。

设置查询包含类默认值和所有请求的模型标识符。随后，该关联把继承的默认值复制到每个模型的加载结果中，并用该
模型的覆盖值替换相同的键。

整数、字符串、UUID 和 ULID 主键都已覆盖此行为。

## 预加载后的修改

成功执行 `set()`、`setMany()`、`forget()`、`forgetMany()` 或 `purge()` 后，软件包会恰好清除一次该模型上
已加载的 `modelSettings` 关联。下一次服务读取会查询当前最终值，因此不会返回过期数据。批量修改失败时，
现有的已加载关联会保留，同时事务批次会回滚。

在下一次批量读取前，请显式重新加载该关联：

```php
$user->settings()->setMany([
    'timezone' => 'Europe/Paris',
    'locale' => 'fr',
]);

$user->load('modelSettings');
```

修改操作仍会执行自己的写入查询。预加载只影响后续读取。

## 另请参阅

- [使用设置](settings.md) — 了解默认值和覆盖值如何合并。
- [API 参考](api-reference.md) — 区分服务方法和关联。
- [配置](configuration.md) — 配置设置连接和存储模型。
