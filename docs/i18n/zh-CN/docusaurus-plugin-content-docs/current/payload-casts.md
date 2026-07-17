---
sidebar_position: 6
title: 数据转换
description: 将设置数据解码为数组、自定义转换值或 Spatie Laravel Data 对象。
---

[← 配置](configuration.md) · [返回 README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [API 参考 →](api-reference.md)

# 数据转换

## 默认 JSON 值

未配置自定义转换时，软件包会在写入时将非空值编码为 JSON，并在读取时返回解码后的数组或标量值。

```php
$user->settings()->set('notifications', [
    'email' => true,
    'push' => false,
]);

$notifications = $user->settings()->get('notifications');
```

值必须可以序列化为 JSON。JSON 编码错误不会被忽略。

## 选择转换

自定义转换按父模型类配置：

```php
'casts' => [
    App\Models\User::class => App\Casts\UserSettingsPayloadCast::class,
],
```

一个已配置的转换会处理属于该父模型类的所有设置数据。在选择转换前，Laravel morph map 别名会被解析回模型类。

配置的类必须实现 `CastsAttributes` 或继承 `Spatie\LaravelData\Data`。其他类不会接受自定义处理，而会使用
默认 JSON 路径。

## 转换生命周期

对于 `CastsAttributes` 实现，软件包按以下顺序执行：

| 方向 | 顺序 |
|------|------|
| 写入 | 调用自定义 `set()`，然后将结果编码为 JSON |
| 读取 | 将已存储的 JSON 字符串传递给自定义 `get()` |

`$model` 参数是已配置的设置存储模型，不是父模型 `User` 或 `Post`。软件包创建转换实例时不会传入构造函数参数。

## Eloquent 属性转换

转换可以实现 Laravel 的 `CastsAttributes` 接口：

```php
namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

final class UserSettingsPayloadCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        return (array) $value;
    }
}
```

自定义 `set()` 的结果必须仍可序列化为 JSON。JSON 编码错误不会被忽略。

## Spatie Laravel Data

安装 `spatie/laravel-data` 后，可以直接使用 `Data` 类：

```bash
composer require spatie/laravel-data:^4.23
```

```php
'casts' => [
    App\Models\User::class => App\Data\UserSettingsData::class,
],
```

向 `set()` 传入该类接受的数据或 `Data` 实例。`get()` 返回数据实例，`all()` 返回包含数据实例的集合。

```php
$preferences = UserSettingsData::from([
    'timezone' => 'Europe/Paris',
    'notifications' => true,
]);

$user->settings()->set('preferences', $preferences);

$preferences = $user->settings()->get('preferences');
```

转换按父模型类选择，而不是按键选择。因此，该模型的每个设置数据都必须是已配置转换的有效输入。

## 另请参阅

- [配置](configuration.md) — 注册转换并替换存储模型。
- [使用设置](settings.md) — 查看哪些空值会被删除。
- [API 参考](api-reference.md) — 检查 `get()` 和 `all()` 的返回类型。
