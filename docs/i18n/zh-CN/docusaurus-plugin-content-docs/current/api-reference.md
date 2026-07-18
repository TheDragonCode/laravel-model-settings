---
sidebar_position: 7
title: API 参考
description: Laravel Model Settings 提供的公共 trait、服务和关联方法。
---

[← 数据转换](payload-casts.md) · [返回 README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [开发 →](development.md)

# API 参考

## HasSettings trait

| 方法 | 返回值 | 用途 |
|------|--------|------|
| `settings()` | `SettingsService` | 访问当前模型的最终设置 |
| `defaultSettings()` | `SettingsService` | 访问当前模型类的共享默认值 |
| `modelSettings()` | Eloquent `Relation` | 将默认值和覆盖值作为关联加载 |

`modelSettings` 关联只能用于 `with()`、`load()` 或 `loadMissing()`，以及作为由此加载的关联属性使用。不要将
关联查询用作替代读取或 CRUD API。请使用两个服务方法读取或修改值。运行时，该关联是软件包的
`SettingsRelation`，它基于 Laravel 的 `MorphMany` 关联。

## SettingsService

| 方法 | 返回值 | 行为 |
|------|--------|------|
| `all()` | `Collection` | 返回与模型覆盖值合并后的默认值 |
| `get(int\|string\|UnitEnum $key)` | `mixed` | 返回覆盖值、对应默认值或 `null` |
| `set(int\|string\|UnitEnum $key, mixed $value)` | `void` | 创建、替换设置，或删除空设置 |
| `forget(int\|string\|UnitEnum $key)` | `void` | 删除存在的设置 |

带键的方法接受 backed enum 和 pure unit enum。Laravel 将 backed enum 转换为底层值，将 pure unit enum
转换为 case 名称。

## 解析矩阵

| 模型覆盖值 | 类默认值 | `get()` 结果 | 是否包含在 `all()` 中 |
|------------|----------|--------------|-------------------------|
| 存在 | 存在 | 覆盖值 | 覆盖值 |
| 存在 | 不存在 | 覆盖值 | 覆盖值 |
| 不存在 | 存在 | 默认值 | 默认值 |
| 不存在 | 不存在 | `null` | 无记录 |

对于未保存的模型，`get()` 返回 `null`，`all()` 返回空集合。只有已持久化的模型才会继承类默认值。

## all

```php
$settings = $user->settings()->all();

$timezone = $settings->get('timezone');
```

结果是按设置键索引的 `Illuminate\Support\Collection`。对于模型设置，覆盖值会替换相同键的默认值。

## get

```php
$timezone = $user->settings()->get('timezone');
```

结果是最终解码或转换后的值。如果覆盖值不存在，则回退到默认值。如果覆盖值和默认值都不存在，则返回 `null`。

## set

```php
$user->settings()->set('timezone', 'Europe/Paris');
```

此方法先验证所有者，然后按模型类型、模型标识符和键执行 update-or-create。传入 Laravel 认为是空的值会
删除记录。验证在选择空值处理路径前执行。无论执行哪条路径，已加载的 `modelSettings` 关联都会被清除，确保
下一次读取不会复用过期数据。

## forget

```php
$user->settings()->forget('timezone');
```

对于有效的所有者，键不存在时调用此方法也是安全的。删除覆盖值不会删除其共享默认值。删除后会清除已加载的
关联。

## defaultSettings

`defaultSettings()` 返回的服务提供相同的四个方法：

```php
$defaults = (new User)->defaultSettings();

$defaults->set('timezone', 'UTC');
$timezone = $defaults->get('timezone');
$all = $defaults->all();
$defaults->forget('timezone');
```

## 异常

`DragonCode\LaravelModelSettings\Exceptions\InvalidSettingsOwnerException` 继承 PHP 的
`DomainException`。符合以下任一条件时，`settings()->set()` 和 `settings()->forget()` 会在执行存储查询前
抛出此异常：

- 所有者模型尚未保存，包括已预先分配主键的未保存模型。
- 已持久化所有者的主键是整数 `0` 或字符串 `'0'`，与 1.x 的类默认值哨兵冲突。

当 `set()` 收到空值时也会执行此验证。通过 `defaultSettings()` 修改仍然有效，因为该服务会显式选择类默认值
作用域。只读访问保持确定：未保存的所有者不查询覆盖值并返回 `null` 或空集合；主键为 `0` 的已持久化所有者
可以读取类默认值，但不能将其作为模型覆盖值进行修改。

## 另请参阅

- [使用设置](settings.md) — 了解每个操作的行为。
- [预加载](eager-loading.md) — 使用 `modelSettings` 避免 N+1 查询。
- [数据转换](payload-casts.md) — 控制 `get()` 和 `all()` 返回的值。
