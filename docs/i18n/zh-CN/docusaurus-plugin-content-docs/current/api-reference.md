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
| `setMany(iterable $values)` | `void` | 在一个有限批次中 upsert 非空值并删除空值 |
| `forget(int\|string\|UnitEnum $key)` | `void` | 删除存在的设置 |
| `forgetMany(iterable $keys)` | `void` | 从当前作用域删除列出的键 |
| `purge()` | `void` | 删除当前作用域中存储的所有设置 |

带键的方法接受 backed enum 和 pure unit enum。Laravel 将 backed enum 转换为底层值，将 pure unit enum
转换为 case 名称。

`SettingsService` 的 `get()` 没有调用方提供的回退值参数，也没有单独的 `has()` 方法。请使用
`all()->has($key)` 测试最终键是否存在。

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
$hasTimezone = $settings->has('timezone');
```

结果是按设置键索引的 `Illuminate\Support\Collection`。对于模型设置，覆盖值会替换相同键的默认值。

## get

```php
$timezone = $user->settings()->get('timezone');
```

结果是最终解码或转换后的值。如果覆盖值不存在，则回退到默认值。如果覆盖值和默认值都不存在，则返回
`null`。此签名有意不接受第二个回退值参数。

## set

```php
$user->settings()->set('timezone', 'Europe/Paris');
```

此方法先验证所有者，然后按模型类型、模型标识符、作用域判别字段和键执行 update-or-create。传入 Laravel
认为是空的值会删除记录。验证在选择空值处理路径前执行。无论执行哪条路径，已加载的 `modelSettings` 关联
都会被清除，确保下一次读取不会复用过期数据。

## setMany

```php
$user->settings()->setMany([
    'timezone' => 'Europe/Paris',
    'locale' => 'fr',
    'obsolete' => null,
]);
```

iterable 的键按与 `set()` 相同的方式规范化。多个输入键规范化为同一个字符串时，最后一个值生效。非空值
使用一次数据库原生 upsert，空值使用一次删除。两组都存在时，两个操作在同一个事务中执行。此方法在读取
iterable 前验证所有者，并在成功后清除一次 `modelSettings`。

## forget

```php
$user->settings()->forget('timezone');
```

对于有效的所有者，键不存在时调用此方法也是安全的。删除覆盖值不会删除其共享默认值。删除后会清除已加载的
关联。

## forgetMany

```php
$user->settings()->forgetMany(['timezone', 'locale']);
```

此方法规范化 iterable 并去重，然后用一次删除只移除当前作用域中的这些键。缺失的键没有影响。它返回
`void`，成功调用后会清除已加载的关联，空 iterable 也不例外。

## purge

```php
$user->settings()->purge();
```

对于 `settings()`，此方法删除属于该已保存所有者的所有覆盖值，绝不删除类默认值或其他所有者的覆盖值。
对于 `defaultSettings()`，它删除该模型类的所有默认值，并保留模型覆盖值。它返回 `void`，成功后会清除
已加载的关联。

## defaultSettings

`defaultSettings()` 返回的服务提供相同的七个方法：

```php
$defaults = (new User)->defaultSettings();

$defaults->set('timezone', 'UTC');
$defaults->setMany(['timezone' => 'UTC', 'locale' => 'en']);
$timezone = $defaults->get('timezone');
$all = $defaults->all();
$defaults->forget('timezone');
$defaults->forgetMany(['timezone', 'locale']);
$defaults->purge();
```

## 异常

`DragonCode\LaravelModelSettings\Exceptions\InvalidSettingsOwnerException` 继承 PHP 的
`DomainException`。所有者模型尚未保存时，通过 `settings()` 执行的每个修改都会在存储查询前抛出此异常，
包括已预先分配主键的未保存模型。

此验证也会在读取批量 iterable 前执行。通过 `defaultSettings()` 修改仍然有效，因为该服务会显式选择类
默认值作用域。只读访问保持确定：未保存的所有者不查询覆盖值并返回 `null` 或空集合。主键为整数 `0` 或
字符串 `'0'` 的已持久化所有者可以读取和修改自己的覆盖值；`is_default` 会将这些记录与类默认值分开。

配置的模型级或按键转换缺失、类型无效、未实现受支持接口或无法通过 Laravel 容器解析时，会抛出
`DragonCode\LaravelModelSettings\Exceptions\InvalidPayloadCast`。其消息可以标明父模型、设置键和转换类，
但绝不包含数据。

同时包含写入和删除的 `setMany()` 操作失败时，事务会回滚两部分工作。异常会重新抛出，现有的已加载
`modelSettings` 关联不会被清除。

## 另请参阅

- [使用设置](settings.md) — 了解每个操作的行为。
- [预加载](eager-loading.md) — 使用 `modelSettings` 避免 N+1 查询。
- [数据转换](payload-casts.md) — 控制 `get()` 和 `all()` 返回的值。
