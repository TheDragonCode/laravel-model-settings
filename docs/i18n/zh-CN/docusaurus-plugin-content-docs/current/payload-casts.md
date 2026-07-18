---
sidebar_position: 6
title: 数据转换
description: 将设置数据解码为数组、自定义转换值或 Spatie Laravel Data 对象。
---

[← 配置](configuration.md) · [返回 README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [API 参考 →](api-reference.md)

# 数据转换

## 默认 JSON 值

未配置自定义转换时，软件包会在写入时将每个值编码为 JSON，并在读取时返回准确的 JSON 解码值。其中包括
`null`、空字符串、仅含空白字符的字符串、空数组、零和 `false`。

```php
$user->settings()->set('notifications', [
    'email' => true,
    'push' => false,
]);

$notifications = $user->settings()->get('notifications');
```

值必须可以序列化为 JSON。JSON 编码错误不会被忽略。

## 选择转换

旧有的模型级格式会将一个转换应用于属于父模型类的所有设置：

```php
'casts' => [
    App\Models\User::class => App\Casts\UserSettingsPayloadCast::class,
],
```

只有精确设置键需要自定义处理时，请使用按键映射：

```php
'casts' => [
    App\Models\User::class => [
        'profile' => App\Data\ProfileData::class,
        'billing.credentials' => App\Casts\EncryptedSettingPayload::class,
    ],
],
```

选择前，Laravel morph map 别名会被解析回父模型类。键匹配使用已存储的设置键，而不是 Eloquent 属性名
`payload`。点号是普通字符，因此 `billing.credentials` 是一个键。按键映射中缺少的键使用普通 JSON。

配置的类必须实现 `CastsAttributes` 或继承 `Spatie\LaravelData\Data`。配置的类无效、缺失、不受支持或
无法通过容器解析时，会抛出 `InvalidPayloadCast`；软件包不会为已配置的条目静默回退到普通 JSON。

## 转换生命周期

对于 `CastsAttributes` 实现，软件包按以下顺序执行：

| 方向 | 顺序 |
|------|------|
| 写入 | 调用自定义 `set()`，然后将结果编码为 JSON |
| 读取 | 将已存储的 JSON 字符串传递给自定义 `get()` |

`$model` 参数是已配置的设置存储模型，不是父模型 `User` 或 `Post`。软件包通过 Laravel 容器解析
`CastsAttributes` 实现，因此构造函数依赖可以使用普通容器绑定。自定义 `set()` 转换会接收每个输入值，
包括 Laravel 认为是空的值。

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

## 按键加密

加密应由应用程序转换处理，因为软件包结构没有加密元数据或密钥轮换约定。以下转换只加密一个设置键，
其他所有键仍使用普通 JSON 路径：

```php
namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

final class EncryptedSettingPayload implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $ciphertext = Json::decode($value);

        return Json::decode(Crypt::decryptString((string) $ciphertext));
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return Crypt::encryptString(Json::encode($value));
    }
}
```

为一个精确的普通键注册它：

```php
'casts' => [
    App\Models\User::class => [
        'billing.credentials' => App\Casts\EncryptedSettingPayload::class,
    ],
],
```

```php
$user->settings()->set('billing.credentials', $credentials);

$credentials = $user->settings()->get('billing.credentials');
```

不要记录转换前后的值。如果加密密钥可能更改，请在存储生产数据前定义并测试应用程序级轮换策略。没有定义
版本控制和轮换的独立存储约定时，不要向软件包表添加元数据字段。

## Spatie Laravel Data

安装 `spatie/laravel-data` 后，可以直接使用 `Data` 类：

```bash
composer require spatie/laravel-data:^4.23
```

```php
'casts' => [
    App\Models\User::class => [
        'preferences' => App\Data\UserSettingsData::class,
    ],
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

同一模型的其他键继续使用普通 JSON。只有该父模型的每个数据都是已配置数据类的有效输入时，才使用旧有的
模型级格式。

## 转换错误

解析失败时，`DragonCode\LaravelModelSettings\Exceptions\InvalidPayloadCast` 会标明父模型类、设置键和
配置的转换，但绝不包含数据。单个写入、批量写入以及读取使用该配置条目的持久化值时都会抛出此异常。

## 另请参阅

- [配置](configuration.md) — 注册转换并替换存储模型。
- [使用设置](settings.md) — 查看准确 JSON 值的存储和删除方式。
- [API 参考](api-reference.md) — 检查 `get()` 和 `all()` 的返回类型。
