---
sidebar_position: 5
title: 구성
description: 설정 모델, 데이터베이스 연결, 테이블과 페이로드 캐스트를 구성합니다.
---

[← 즉시 로딩](eager-loading.md) · [README로 돌아가기](https://github.com/TheDragonCode/laravel-model-settings#readme) · [페이로드 캐스트 →](payload-casts.md)

# 구성

## 구성 게시

```bash
php artisan vendor:publish --tag="model_settings"
```

이 명령은 `config/model_settings.php`와 패키지 마이그레이션을 게시합니다.

## 사용 가능한 옵션

| 옵션 | 기본값 | 용도 |
|------|--------|------|
| `model` | `DragonCode\LaravelModelSettings\Models\Settings` | 저장된 설정에 사용하는 Eloquent 모델 |
| `connection` | 애플리케이션 기본값 | 모델과 마이그레이션이 사용하는 데이터베이스 연결 |
| `table` | `settings` | 모델과 마이그레이션이 사용하는 데이터베이스 테이블 |
| `casts` | `[]` | 상위 모델 클래스에 따라 선택되는 페이로드 캐스트 |

패키지는 다음 환경 변수를 읽습니다.

| 변수 | 기본값 |
|------|--------|
| `MODEL_SETTINGS_DATABASE_CONNECTION` | `DATABASE_CONNECTION`, 그다음 Laravel 기본 연결 |
| `MODEL_SETTINGS_DATABASE_TABLE` | `settings` |

마이그레이션을 실행하기 전에 연결과 테이블을 설정합니다.

```dotenv
MODEL_SETTINGS_DATABASE_CONNECTION=mysql
MODEL_SETTINGS_DATABASE_TABLE=model_settings
```

나중에 값을 변경해도 기존 레코드는 이동하지 않습니다.

## 저장 스키마

게시된 마이그레이션은 다음 열을 생성합니다.

| 열 | 용도 |
|----|------|
| `id` | 설정 행의 기본 키 |
| `item_type` | 상위 모델의 morph 클래스 또는 별칭 |
| `item_id` | 최대 36자의 문자열로 저장되는 상위 모델 식별자 |
| `key` | 설정 키 |
| `payload` | 마이그레이션에서 `jsonb`로 선언한 페이로드 |
| `created_at`과 `updated_at` | Laravel 타임스탬프 |

`item_type`, `item_id`, `key`의 조합은 고유합니다.

기본 `item_id` 열은 최대 36자를 저장합니다. 정수, UUID, ULID 식별자는 이 스키마에 맞습니다. 더 긴 사용자
정의 기본 키에는 해당 마이그레이션 변경이 필요합니다.

`item_id`의 값 `0`은 클래스 기본값을 위해 예약됩니다. 데이터가 존재한 뒤 데이터베이스 연결, 테이블 이름 또는
morph map 별칭을 변경하면 기존 행을 직접 이동하거나 업데이트해야 합니다.

## 저장 모델 교체

기본 설정 모델은 final입니다. 상속하지 말고 대체 모델을 구성합니다.

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

그런 다음 구성을 업데이트합니다.

```php
'model' => App\Models\ApplicationSetting::class,
```

대체 모델은 게시된 스키마와 호환되어야 합니다. 대체 모델이 동일한 직렬화를 구현하지 않는 한 fillable 속성과
`PayloadCast`를 유지합니다.

대체 모델은 최소한 다음 동작을 유지해야 합니다.

| 요구 사항 | 이유 |
|-----------|------|
| `item_type`, `item_id`, `key`, `payload` 채우기 | `updateOrCreate()`가 이 속성을 기록 |
| 구성된 연결과 테이블 사용 | 마이그레이션과 리포지토리가 같은 행을 사용해야 함 |
| `item_id`를 `string`으로 캐스트 | 정수, UUID, ULID 식별자가 한 열을 공유 |
| `payload`를 `PayloadCast` 또는 동등한 방식으로 캐스트 | 읽기와 쓰기가 JSON 동작을 유지해야 함 |

## 함께 보기

- [시작하기](getting-started.md) — 구성과 마이그레이션을 게시합니다.
- [페이로드 캐스트](payload-casts.md) — 애플리케이션 전용 페이로드 형식을 구성합니다.
- [API 참조](api-reference.md) — 패키지의 공개 인터페이스를 확인합니다.
