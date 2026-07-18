---
sidebar_position: 5
title: 配置
description: 配置设置存储模型、数据库连接、数据表和数据转换。
---

[← 预加载](eager-loading.md) · [返回 README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [数据转换 →](payload-casts.md)

# 配置

## 发布配置

```bash
php artisan vendor:publish --tag="model_settings"
```

此命令会发布 `config/model_settings.php` 和软件包迁移。

## 可用选项

| 选项 | 默认值 | 用途 |
|------|--------|------|
| `model` | `DragonCode\LaravelModelSettings\Models\Settings` | 用于已存储设置的 Eloquent 模型 |
| `connection` | 应用程序默认值 | 模型和迁移使用的数据库连接 |
| `table` | `settings` | 模型和迁移使用的数据库表 |
| `casts` | `[]` | 按父模型类以及可选的设置键选择的数据转换 |

此软件包读取以下环境变量：

| 变量 | 默认值 |
|------|--------|
| `MODEL_SETTINGS_DATABASE_CONNECTION` | `DATABASE_CONNECTION`，然后使用 Laravel 默认连接 |
| `MODEL_SETTINGS_DATABASE_TABLE` | `settings` |

请在运行迁移前设置连接和数据表：

```dotenv
MODEL_SETTINGS_DATABASE_CONNECTION=mysql
MODEL_SETTINGS_DATABASE_TABLE=model_settings
```

以后修改任一值都不会移动现有记录。

## 数据转换配置

旧有的模型级格式仍受支持。一个转换处理属于该模型类的所有数据：

```php
'casts' => [
    App\Models\User::class => App\Casts\UserSettingsPayloadCast::class,
],
```

不同的键需要不同类型或处理方式时，请使用按键映射：

```php
'casts' => [
    App\Models\User::class => [
        'profile' => App\Data\ProfileData::class,
        'billing.credentials' => App\Casts\EncryptedSettingPayload::class,
    ],
],
```

键必须精确匹配。点号没有嵌套路径含义，映射中缺少的键使用默认 JSON 转换。每个模型条目只能是应用于
整个模型的类字符串或按键映射；按键映射中没有通配符条目。支持的转换接口和加密示例见
[数据转换](payload-casts.md)。

## 存储结构

发布的迁移会创建以下字段：

| 字段 | 用途 |
|------|------|
| `id` | 设置记录的主键 |
| `item_type` | 父模型的 morph 类或别名 |
| `item_id` | 父模型标识符，以最多 36 个字符的字符串存储 |
| `is_default` | 区分类默认值和模型覆盖值 |
| `key` | 设置键 |
| `payload` | 迁移中声明为 `jsonb` 的数据 |
| `created_at` 和 `updated_at` | Laravel 时间戳 |

`item_type`、`item_id`、`is_default` 和 `key` 的组合是唯一的。`item_type`、`is_default` 和
`item_id` 上的查询索引支持读取默认值和所有者作用域。

类默认值和模型覆盖值共用此表。软件包不会创建第二个默认值表，也不会添加加密元数据字段。

默认 `item_id` 字段最多存储 36 个字符。字符串表示不超过 36 个字符的整数、字符串、UUID 和 ULID 标识符
都适用于此结构。更长的自定义主键需要对迁移进行相应修改。

类默认值使用 `item_id = '0'` 和 `is_default = true`。主键为整数 `0` 或字符串 `'0'` 的已持久化所有者
使用相同的物理 `item_id`，但 `is_default = false`。因此，同一模型类型和设置键可以同时存在这两条记录。
如果数据已经存在，修改数据库连接、表名或 morph map 别名时，需要自行移动或更新现有记录。

## 从早期 1.x 版本升级

除存储判别字段迁移外，此版本还更改了运行时契约：

| 早期 1.x 行为 | 当前行为 | 所需的应用程序修改 |
|----------------|----------|----------------------|
| `set($key, null)`、空字符串、仅含空白字符的字符串和空数组会删除记录 | `set()` 准确存储每个 JSON 值 | 将删除调用替换为 `forget($key)` |
| `setMany()` 中的空值会在同一批次中删除 | 每个 `setMany()` 条目通过一次事务 upsert 存储 | 将要删除的键移到单独的 `forgetMany()` 调用 |
| 空键和仅含空白字符的键会被接受 | 规范化后的空键会抛出 `InvalidSettingKey` | 升级前重命名或删除无效键 |
| 存在性检查需要 `all()->has($key)` | `has($key)` 可区分已存储的 JSON `null` 和缺失的键 | 优先使用专用的 `has()` 方法 |

存储值为 `null` 的模型覆盖现在会隐藏非空的类默认值，直到通过 `forget()` 删除该覆盖值。自定义数据转换现在
会收到两个 setter 传入的空值，自定义存储模型的创建或更新事件也会收到 `set()` 传入的空值。`get()` 仍然
只接受一个参数；未添加调用方回退值或永久的 `put()` 别名。

更新软件包后，在应用程序维护模式下发布并运行新迁移：

```bash
php artisan vendor:publish --tag="model_settings"
php artisan migrate
```

升级迁移会添加 `is_default`，将所有旧的 `item_id = '0'` 记录归类为类默认值，创建包含判别字段的索引，
然后删除旧的唯一索引。迁移输出绝不会写入设置键或数据。

早期 1.x 结构以相同方式编码类默认值和真实所有者 ID `0` 的记录。因此，迁移无法区分手动插入的所有者
覆盖值和默认值，并会将两者都归类为默认值。迁移后，请检查已知的旧所有者 ID `0` 数据，并对实际属于
模型覆盖值的记录设置 `is_default = false`。

不要在升级后的结构上运行旧版软件包。旧运行时不会写入判别字段，并会把默认值存储为覆盖值。请在同一
维护窗口内部署迁移和兼容的运行时。

只有在尚未出现真实所有者 ID `0` 覆盖值时，回滚才安全。如果迁移发现 `item_id = '0'` 且
`is_default = false`，它会在修改结构前停止，因为旧结构无法在不改变含义的情况下表示该记录。回滚前请
删除或导出这些覆盖值。安全回滚会恢复旧的唯一索引并删除 `is_default`。

## 替换存储模型

内置设置模型是 final 类。请配置替代模型，而不是继承它：

```php
namespace App\Models;

use DragonCode\LaravelModelSettings\Casts\PayloadCast;
use Illuminate\Database\Eloquent\Model;

final class ApplicationSetting extends Model
{
    protected $fillable = [
        'item_type',
        'item_id',
        'is_default',
        'key',
        'payload',
    ];

    public function __construct(array $attributes = [])
    {
        $this->setConnection(config('model_settings.connection'));
        $this->setTable(config('model_settings.table'));

        parent::__construct($attributes);
    }

    protected function casts(): array
    {
        return [
            'item_id'    => 'string',
            'is_default' => 'boolean',
            'payload'    => PayloadCast::class,
        ];
    }
}
```

然后更新配置：

```php
'model' => App\Models\ApplicationSetting::class,
```

替代模型必须与已发布结构保持兼容。除非新模型实现等效的序列化，否则应保留可批量赋值属性和 `PayloadCast`。

替代模型至少必须保留以下行为：

| 要求 | 原因 |
|------|------|
| 填充 `item_type`、`item_id`、`is_default`、`key` 和 `payload` | 存储层会写入这些属性 |
| 使用配置的连接和数据表 | 迁移和仓库必须访问相同记录 |
| 将 `item_id` 转换为 `string` | 整数、字符串、UUID 和 ULID 共用一个字段 |
| 将 `is_default` 转换为 `boolean` | 延迟和预加载解析必须读取相同的作用域判别字段 |
| 使用 `PayloadCast` 或等效方式转换 `payload` | 读写必须保持 JSON 行为 |

## 另请参阅

- [快速开始](getting-started.md) — 发布配置和迁移。
- [数据转换](payload-casts.md) — 配置应用程序专用的数据类型。
- [API 参考](api-reference.md) — 查看软件包的公共接口。
