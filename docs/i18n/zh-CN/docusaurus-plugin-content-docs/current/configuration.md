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
| `casts` | `[]` | 按父模型类选择的数据转换 |

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

## 存储结构

发布的迁移会创建以下字段：

| 字段 | 用途 |
|------|------|
| `id` | 设置记录的主键 |
| `item_type` | 父模型的 morph 类或别名 |
| `item_id` | 父模型标识符，以最多 36 个字符的字符串存储 |
| `key` | 设置键 |
| `payload` | 迁移中声明为 `jsonb` 的数据 |
| `created_at` 和 `updated_at` | Laravel 时间戳 |

`item_type`、`item_id` 和 `key` 的组合是唯一的。

默认 `item_id` 字段最多存储 36 个字符。字符串表示不超过 36 个字符的整数、字符串、UUID 和 ULID 标识符
都适用于此结构。更长的自定义主键需要对迁移进行相应修改。

`item_id` 中的值 `0` 保留给类默认值。在 1.x 中，`set()` 和 `forget()` 会拒绝主键为整数 `0` 或字符串
`'0'` 的已持久化所有者，并在查询此表前抛出 `InvalidSettingsOwnerException`。如果数据已经存在，修改
数据库连接、表名或 morph map 别名时，需要自行移动或更新现有记录。

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
            'item_id' => 'string',
            'payload' => PayloadCast::class,
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
| 填充 `item_type`、`item_id`、`key` 和 `payload` | `updateOrCreate()` 会写入这些属性 |
| 使用配置的连接和数据表 | 迁移和仓库必须访问相同记录 |
| 将 `item_id` 转换为 `string` | 整数、字符串、UUID 和 ULID 共用一个字段 |
| 使用 `PayloadCast` 或等效方式转换 `payload` | 读写必须保持 JSON 行为 |

## 另请参阅

- [快速开始](getting-started.md) — 发布配置和迁移。
- [数据转换](payload-casts.md) — 配置应用程序专用的数据类型。
- [API 参考](api-reference.md) — 查看软件包的公共接口。
