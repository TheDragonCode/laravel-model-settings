---
sidebar_position: 3
title: 使用设置
description: 管理共享默认值、单个模型的覆盖值、设置键和值。
---

[← 快速开始](getting-started.md) · [返回 README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [预加载 →](eager-loading.md)

# 使用设置

同一个服务同时处理默认值和模型值。入口方法决定要读取或修改的作用域：

| 入口 | 作用域 |
|------|--------|
| `(new User)->defaultSettings()` | 所有已保存 `User` 模型共享的默认值 |
| `$user->settings()` | 一个已保存用户的最终设置 |

## 共享默认值

默认值适用于 morph 类相同的所有已保存 Eloquent 模型：

```php
$defaults = (new User)->defaultSettings();

$defaults->set('timezone', 'UTC');
$defaults->set('notifications', ['email' => true]);
```

通过同一个服务读取或删除默认值：

```php
$timezone = $defaults->get('timezone');
$all = $defaults->all();

$defaults->forget('timezone');
```

每个模型类的默认值相互独立。

## 单个模型的覆盖值

`set()` 会创建设置或替换其现有值：

```php
$user->settings()->set('timezone', 'Europe/Paris');
$user->settings()->set('timezone', 'America/Toronto');
```

只有该模型的设置会被修改。其他模型继续使用自己的覆盖值或共享默认值。

`get()` 和 `all()` 按相同的优先级解析值：

```php
$timezone = $user->settings()->get('timezone');
$settings = $user->settings()->all();
```

`all()` 返回一个按设置键索引的 `Illuminate\Support\Collection`。

例如，一个覆盖值只替换对应的默认值：

```php
(new User)->defaultSettings()->set('timezone', 'UTC');
(new User)->defaultSettings()->set('locale', 'en');

$user->settings()->set('timezone', 'Europe/Paris');

assert($user->settings()->all()->sortKeys()->all() === [
    'locale' => 'en',
    'timezone' => 'Europe/Paris',
]);
```

## 删除值

删除模型覆盖值后会再次使用默认值：

```php
(new User)->defaultSettings()->set('timezone', 'UTC');

$user->settings()->set('timezone', 'Europe/Paris');
$user->settings()->forget('timezone');

assert($user->settings()->get('timezone') === 'UTC');
```

若要删除默认值本身，请通过 `defaultSettings()` 调用 `forget()`：

```php
(new User)->defaultSettings()->forget('timezone');
```

对不存在的键调用 `forget()` 不会产生影响。

## 空值

`set()` 使用 Laravel 的 `blank()` 辅助函数。空值会删除设置，而不是保存它。

| 值 | 结果 |
|----|------|
| `null` | 删除 |
| `''` 或仅包含空白字符的字符串 | 删除 |
| `[]` | 删除 |
| `0` | 保存 |
| `false` | 保存 |
| `'0'` | 保存 |

此软件包无法通过 `set()` 保存有意设置的空值。

## 设置键

键可以是字符串、整数或实现 `UnitEnum` 的 PHP 枚举：

```php
enum SettingKey: string
{
    case Timezone = 'timezone';
}

$user->settings()->set(SettingKey::Timezone, 'Europe/Paris');

$timezone = $user->settings()->get(SettingKey::Timezone);
```

Laravel 使用 backed enum 的底层值进行存储，使用 pure unit enum 的 case 名称进行存储。读取、替换或删除
设置时，请使用相同的键或枚举 case。

此软件包不会验证键的内容。公共 API 和默认结构允许空键以及仅包含空白字符的键。

## 模型标识符

支持整数、字符串、UUID 和 ULID 主键。

修改单个模型的设置时，需要所有者已持久化，且主键不为 `null`。对于未保存的模型，`get()` 返回 `null`，
`all()` 不查询模型覆盖值并返回空集合。它的 `set()` 和 `forget()` 会在执行存储查询前抛出
`InvalidSettingsOwnerException`。

在 1.x 中，整数 `0` 和字符串 `'0'` 保留给共享默认值。使用任一主键的已持久化模型可以读取类默认值，但
`set()` 和 `forget()` 会抛出 `InvalidSettingsOwnerException`。其他字符串主键（包括 `'00'`）仍然有效。

设置按模型当前的 morph 类存储。在设置写入后新增或修改 morph map 别名时，需要更新现有的 `item_type` 值。

## 另请参阅

- [预加载](eager-loading.md) — 避免为每个模型执行一次设置查询。
- [数据转换](payload-casts.md) — 返回领域对象，而不是解码后的 JSON。
- [API 参考](api-reference.md) — 查看方法签名和返回值。
