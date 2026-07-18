---
sidebar_position: 1
slug: /
title: Laravel Model Settings
description: Laravel Eloquent 모델을 위한 공유 기본 설정과 모델별 재정의 설정입니다.
---

[README로 돌아가기](https://github.com/TheDragonCode/laravel-model-settings#readme) · [시작하기 →](getting-started.md)

# Laravel Model Settings

Laravel Model Settings는 공유 기본 설정과 모델별 재정의 설정을 별도의 데이터베이스 테이블에 저장합니다.
모든 모델이 같은 값으로 시작하지만 개별 레코드가 그 값을 재정의해야 할 때 사용할 수 있습니다.

이 패키지는 상위 테이블에 설정 열을 추가하지 않습니다. 설정은 모델 스키마와 독립적으로 유지되며 모델의
Eloquent morph 클래스를 기준으로 그룹화됩니다.

## 적합한 사용 사례

| 요구 사항 | 패키지 동작 |
|-----------|-------------|
| 저장된 모든 모델에 같은 초기값 제공 | 클래스 수준의 기본값 하나를 저장 |
| 한 모델의 값 변경 | 해당 모델의 재정의 값을 저장 |
| 재정의 값 제거 | 클래스 기본값을 다시 사용 |
| 여러 모델 읽기 | 기본값과 재정의 값을 위한 관계 하나를 즉시 로딩 |

## 값 결정 순서

설정을 읽으면 패키지는 다음 순서로 첫 번째 사용 가능한 값을 반환합니다.

1. 저장된 모델의 재정의 값.
2. 해당 모델 클래스의 기본값.
3. `null`.

| 출처 | `timezone` |
|------|------------|
| `User` 기본값 | `UTC` |
| 사용자 123의 재정의 값 | `Europe/Paris` |
| 사용자 123의 최종 값 | `Europe/Paris` |
| 다른 저장된 사용자의 최종 값 | `UTC` |

재정의 값을 제거하면 기본값이 다시 사용됩니다. 기본값 자체는 삭제되지 않습니다.

## 핵심 작업

```php
(new User)->defaultSettings()->set('timezone', 'UTC');

$user->settings()->set('timezone', 'Europe/Paris');

$timezone = $user->settings()->get('timezone');
$settings = $user->settings()->all();
$hasTimezone = $settings->has('timezone');

$user->settings()->setMany([
    'locale' => 'fr',
    'notifications.email' => true,
]);
$user->settings()->forgetMany(['timezone', 'locale']);
```

`get()`은 최종 값 하나를 반환합니다. `all()`은 기본값과 재정의 값이 병합된
`Illuminate\Support\Collection`을 반환합니다.
최종 키의 존재 여부가 중요하면 컬렉션의 `has()` 메서드를 사용합니다. `get()`은 호출자가 지정하는 대체값
인수를 의도적으로 받지 않습니다. 유일한 대체값은 저장된 클래스 기본값이며, 두 범위에 키가 없으면 `null`입니다.

기본값과 재정의 값에는 `all()`, `get()`, `set()`, `setMany()`, `forget()`, `forgetMany()`, `purge()`의
동일한 작업을 사용합니다.

## 패키지의 명확한 경계

Laravel Model Settings는 Eloquent에 집중한 패키지이며 범용 애플리케이션 설정 프레임워크가 아닙니다.

| 경계 | 의도된 동작 |
|------|-------------|
| 저장소 | 데이터베이스 테이블 하나만 사용하며 Redis 백엔드나 상위 모델 필드 저장소는 제공하지 않음 |
| 기본값 | 같은 테이블의 예약 행을 사용하며 두 번째 기본값 테이블은 제공하지 않음 |
| 등록 | 리포지토리 레지스트리, 타입이 지정된 전역 설정 클래스, 클래스 자동 검색을 제공하지 않음 |
| 마이그레이션 | 설정 키별 마이그레이션 실행기를 제공하지 않음 |
| 캐싱 | 요청 간 필수 캐시를 제공하지 않으며 즉시 로딩은 로딩된 관계만 재사용함 |

이 기능이 필요한 애플리케이션은 `modelSettings` 관계나 내부 리포지토리를 확장 API로 취급하지 말고 패키지
외부에서 조합해야 합니다.

## 저장 범위

각 행은 네 값으로 식별됩니다.

| 값 | 의미 |
|----|------|
| `item_type` | 상위 모델의 morph 클래스 또는 morph map 별칭 |
| `item_id` | 상위 모델의 기본 키. 클래스 기본값은 물리적 값 `0`을 유지 |
| `is_default` | 클래스 기본값이면 `true`, 모델 재정의이면 `false` |
| `key` | 설정 이름 |

따라서 기본값은 모델 클래스마다 독립적입니다. 두 클래스가 같은 설정 키를 사용해도 `User` 기본값이
`Post` 기본값이 되지 않습니다.

## 지원되는 모델

이 패키지는 정수, 문자열, UUID 또는 ULID 기본 키를 사용하는 Eloquent 모델을 지원합니다. 정수 식별자 `0`
또는 문자열 `'0'`을 가진 저장된 모델도 클래스 기본값과 충돌하지 않고 재정의를 저장할 수 있습니다. Laravel
morph map도 사용할 수 있습니다.

모델별 설정은 저장된 모델에만 속합니다. 저장되지 않은 모델은 기본값을 상속하지 않습니다. `get()`은
`null`을 반환하고 `all()`은 빈 컬렉션을 반환합니다. 저장되지 않은 소유자에 `set()`, `setMany()`,
`forget()`, `forgetMany()`, `purge()`를 호출하면 저장소 쿼리가 실행되기 전에
`InvalidSettingsOwnerException`이 발생합니다.

페이로드는 JSON으로 저장됩니다. 캐스트를 구성하지 않으면 읽을 때 디코딩된 배열 또는 스칼라 값을 반환합니다.
[페이로드 캐스트](payload-casts.md)를 사용하면 애플리케이션 전용 객체를 반환할 수 있습니다.

## 함께 보기

- [시작하기](getting-started.md) — 패키지를 설치하고 모델을 구성합니다.
- [설정 사용하기](settings.md) — 기본값, 재정의 값, 키와 값을 관리합니다.
- [API 참조](api-reference.md) — 모든 공개 메서드와 반환 형식을 확인합니다.
