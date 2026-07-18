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
| `casts` | `[]` | 상위 모델 클래스와 선택적으로 설정 키에 따라 선택되는 페이로드 캐스트 |

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

## 페이로드 캐스트 구성

기존의 모델 전체 형식도 계속 지원합니다. 하나의 캐스트가 해당 모델 클래스에 속한 모든 페이로드를 처리합니다.

```php
'casts' => [
    App\Models\User::class => App\Casts\UserSettingsPayloadCast::class,
],
```

키마다 다른 타입이나 처리가 필요하면 키별 맵을 사용합니다.

```php
'casts' => [
    App\Models\User::class => [
        'profile' => App\Data\ProfileData::class,
        'billing.credentials' => App\Casts\EncryptedSettingPayload::class,
    ],
],
```

키는 정확히 일치해야 합니다. 점은 중첩 경로 의미가 없으며 맵에 없는 키는 기본 JSON 캐스트를 사용합니다.
각 모델 항목은 모델 전체에 적용되는 클래스 문자열 또는 키별 맵 중 하나입니다. 키별 맵 안에는 와일드카드 항목이
없습니다. 지원되는 캐스트 계약과 암호화 예시는 [페이로드 캐스트](payload-casts.md)를 참조합니다.

## 저장 스키마

게시된 마이그레이션은 다음 열을 생성합니다.

| 열 | 용도 |
|----|------|
| `id` | 설정 행의 기본 키 |
| `item_type` | 상위 모델의 morph 클래스 또는 별칭 |
| `item_id` | 최대 36자의 문자열로 저장되는 상위 모델 식별자 |
| `is_default` | 클래스 기본값과 모델 재정의를 구분 |
| `key` | 설정 키 |
| `payload` | 마이그레이션에서 `jsonb`로 선언한 페이로드 |
| `created_at`과 `updated_at` | Laravel 타임스탬프 |

`item_type`, `item_id`, `is_default`, `key`의 조합은 고유합니다. `item_type`, `is_default`,
`item_id` 조회 인덱스는 기본값 및 소유자 범위 읽기를 지원합니다.

클래스 기본값과 모델 재정의 값은 이 테이블을 공유합니다. 패키지는 두 번째 기본값 테이블이나 암호화 메타데이터
열을 만들지 않습니다.

기본 `item_id` 열은 최대 36자를 저장합니다. 문자열 표현이 36자 이하인 정수, 문자열, UUID, ULID 식별자는
이 스키마에 맞습니다. 더 긴 사용자 정의 기본 키에는 해당 마이그레이션 변경이 필요합니다.

클래스 기본값은 `item_id = '0'`과 `is_default = true`를 사용합니다. 정수 키 `0` 또는 문자열 `'0'`을 가진
저장된 소유자는 동일한 물리적 `item_id`와 `is_default = false`를 사용합니다. 따라서 같은 모델 형식과 설정
키에 두 행이 함께 존재할 수 있습니다. 데이터가 존재한 뒤 데이터베이스 연결, 테이블 이름 또는 morph map
별칭을 변경하면 기존 행을 직접 이동하거나 업데이트해야 합니다.

## 이전 1.x 릴리스에서 업그레이드

이 릴리스는 저장소 판별자 마이그레이션 외에도 런타임 계약을 변경합니다.

| 이전 1.x 동작 | 현재 동작 | 필요한 애플리케이션 변경 |
|---------------|-----------|--------------------------|
| `set($key, null)`, 빈 문자열, 공백 문자열, 빈 배열이 행을 삭제 | `set()`이 모든 JSON 값을 정확히 저장 | 삭제 호출을 `forget($key)`로 교체 |
| `setMany()`의 빈 항목을 같은 일괄 작업에서 삭제 | 모든 `setMany()` 항목을 하나의 트랜잭션 upsert로 저장 | 삭제할 키를 별도의 `forgetMany()` 호출로 이동 |
| 빈 키와 공백만 있는 키를 허용 | 정규화 후 빈 키는 `InvalidSettingKey`를 발생 | 업그레이드 전에 유효하지 않은 키의 이름을 바꾸거나 제거 |
| 존재 확인에 `all()->has($key)`가 필요 | `has($key)`가 저장된 JSON `null`과 없는 키를 구분 | 전용 `has()` 메서드 사용 |

저장된 `null` 모델 재정의는 이제 `forget()`으로 재정의를 제거할 때까지 채워진 클래스 기본값을 숨깁니다. 사용자
지정 페이로드 캐스트는 두 setter의 빈 값을 모두 받으며, 사용자 지정 저장 모델의 생성 또는 업데이트 이벤트는
`set()`에서 해당 값을 받습니다. `get()`은 계속 인수 하나만 받습니다. 호출자 대체값이나 영구 `put()` 별칭은
추가되지 않았습니다.

패키지를 업데이트한 후 애플리케이션을 유지 관리 모드로 전환하고 새 마이그레이션을 게시하여 실행합니다.

```bash
php artisan vendor:publish --tag="model_settings"
php artisan migrate
```

업그레이드 마이그레이션은 `is_default`를 추가하고 기존 `item_id = '0'` 행을 모두 클래스 기본값으로
분류합니다. 그런 다음 판별자를 포함한 인덱스를 만들고 이전 고유 인덱스를 제거합니다. 마이그레이션 출력에는
설정 키나 페이로드를 기록하지 않습니다.

이전 1.x 스키마는 클래스 기본값과 실제 소유자 ID `0` 행을 동일하게 인코딩했습니다. 따라서 마이그레이션은
수동으로 삽입한 소유자 재정의와 기본값을 구분할 수 없으며 둘 다 기본값으로 분류합니다. 마이그레이션 후 알려진
기존 소유자 ID `0` 데이터를 확인하고 실제 모델 재정의 행에는 `is_default = false`를 설정합니다.

업그레이드된 스키마에서 이전 패키지 런타임을 실행하지 마십시오. 이전 런타임은 판별자를 기록하지 않으므로
기본값을 재정의로 저장합니다. 마이그레이션과 호환 런타임을 하나의 유지 관리 경계에서 배포합니다.

실제 소유자 ID `0` 재정의가 생기기 전에만 롤백이 안전합니다. 마이그레이션은
`item_id = '0'`과 `is_default = false`인 행을 발견하면 스키마를 변경하기 전에 중단합니다. 이전 스키마는
의미를 바꾸지 않고 이 행을 표현할 수 없기 때문입니다. 롤백 전에 해당 재정의를 제거하거나 내보냅니다. 안전한
롤백은 이전 고유 인덱스를 복원하고 `is_default`를 제거합니다.

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

그런 다음 구성을 업데이트합니다.

```php
'model' => App\Models\ApplicationSetting::class,
```

대체 모델은 게시된 스키마와 호환되어야 합니다. 대체 모델이 동일한 직렬화를 구현하지 않는 한 fillable 속성과
`PayloadCast`를 유지합니다.

대체 모델은 최소한 다음 동작을 유지해야 합니다.

| 요구 사항 | 이유 |
|-----------|------|
| `item_type`, `item_id`, `is_default`, `key`, `payload` 채우기 | 저장소가 이 속성을 기록 |
| 구성된 연결과 테이블 사용 | 마이그레이션과 리포지토리가 같은 행을 사용해야 함 |
| `item_id`를 `string`으로 캐스트 | 정수, 문자열, UUID, ULID 식별자가 한 열을 공유 |
| `is_default`를 `boolean`으로 캐스트 | 지연 및 즉시 해석이 같은 범위 판별자를 읽어야 함 |
| `payload`를 `PayloadCast` 또는 동등한 방식으로 캐스트 | 읽기와 쓰기가 JSON 동작을 유지해야 함 |

## 함께 보기

- [시작하기](getting-started.md) — 구성과 마이그레이션을 게시합니다.
- [페이로드 캐스트](payload-casts.md) — 애플리케이션 전용 페이로드 형식을 구성합니다.
- [API 참조](api-reference.md) — 패키지의 공개 인터페이스를 확인합니다.
