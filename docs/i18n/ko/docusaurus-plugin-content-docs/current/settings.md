---
sidebar_position: 3
title: 설정 사용하기
description: 공유 기본값, 모델별 재정의 값, 설정 키와 값을 관리합니다.
---

[← 시작하기](getting-started.md) · [README로 돌아가기](https://github.com/TheDragonCode/laravel-model-settings#readme) · [즉시 로딩 →](eager-loading.md)

# 설정 사용하기

같은 서비스가 기본값과 모델 값을 처리합니다. 진입점에 따라 읽거나 변경할 범위가 결정됩니다.

| 진입점 | 범위 |
|--------|------|
| `(new User)->defaultSettings()` | 저장된 `User` 모델이 공유하는 기본값 |
| `$user->settings()` | 저장된 사용자 한 명의 최종 설정 |

## 공유 기본값

기본값은 같은 Eloquent morph 클래스를 사용하는 모든 저장된 모델에 적용됩니다.

```php
$defaults = (new User)->defaultSettings();

$defaults->set('timezone', 'UTC');
$defaults->set('notifications', ['email' => true]);
```

같은 서비스를 통해 기본값을 읽거나 제거합니다.

```php
$timezone = $defaults->get('timezone');
$all = $defaults->all();

$defaults->forget('timezone');
```

기본값은 모델 클래스마다 독립적입니다.

## 모델별 재정의 값

`set()`은 설정을 생성하거나 기존 값을 교체합니다.

```php
$user->settings()->set('timezone', 'Europe/Paris');
$user->settings()->set('timezone', 'America/Toronto');
```

해당 모델의 설정만 변경됩니다. 다른 모델은 자체 재정의 값이나 공유 기본값을 계속 사용합니다.

`get()`과 `all()`은 같은 우선순위로 값을 결정합니다.

```php
$timezone = $user->settings()->get('timezone');
$settings = $user->settings()->all();
```

`all()`은 설정 키로 인덱싱된 `Illuminate\Support\Collection`을 반환합니다.

`get()`은 키만 받습니다. 모델 재정의 값, 저장된 클래스 기본값, `null` 순서로 반환합니다. 호출자가 지정하는
대체값은 받지 않습니다. 최종 키가 없는 경우와 저장된 값을 구분해야 하면 컬렉션을 사용합니다.

```php
$settings = $user->settings()->all();

if ($settings->has('timezone')) {
    $timezone = $settings->get('timezone');
}
```

예를 들어 하나의 재정의 값은 일치하는 기본값만 교체합니다.

```php
(new User)->defaultSettings()->set('timezone', 'UTC');
(new User)->defaultSettings()->set('locale', 'en');

$user->settings()->set('timezone', 'Europe/Paris');

assert($user->settings()->all()->sortKeys()->all() === [
    'locale' => 'en',
    'timezone' => 'Europe/Paris',
]);
```

## 값 제거

모델 재정의 값을 제거하면 기본값이 다시 사용됩니다.

```php
(new User)->defaultSettings()->set('timezone', 'UTC');

$user->settings()->set('timezone', 'Europe/Paris');
$user->settings()->forget('timezone');

assert($user->settings()->get('timezone') === 'UTC');
```

기본값 자체를 제거하려면 `defaultSettings()`를 통해 `forget()`을 호출합니다.

```php
(new User)->defaultSettings()->forget('timezone');
```

없는 키에 `forget()`을 호출해도 아무 변화가 없습니다.

## 일괄 변경

한 범위에서 여러 값을 변경할 때 `setMany()`와 `forgetMany()`를 사용합니다.

```php
$user->settings()->setMany([
    'timezone' => 'Europe/Paris',
    'locale' => 'fr',
]);

$user->settings()->forgetMany(['timezone', 'locale']);
```

두 메서드는 모든 iterable을 받습니다. `setMany()`는 쓰기 전에 각 키를 정규화합니다. 여러 입력 키가 같은
저장 키로 정규화되면 마지막 값이 적용됩니다. 빈 값은 `set()`과 같은 규칙으로 현재 범위의 키를 삭제합니다.

쓰기와 삭제가 섞인 `setMany()` 일괄 작업은 트랜잭션 안에서 upsert 한 번과 삭제 한 번을 사용합니다.
`forgetMany()`는 나열된 모든 키를 한 번의 삭제로 처리합니다. 쿼리 수는 키 수가 아니라 일괄 작업에 포함된
연산 유형에 따라 결정됩니다.

현재 범위 전체를 제거하려면 `purge()`를 사용합니다.

```php
$user->settings()->purge();
```

`settings()`의 `purge()`는 해당 소유자의 재정의 값만 삭제하고 저장된 기본값을 다시 노출합니다.
`defaultSettings()`에서는 모델 재정의 값을 삭제하지 않고 해당 모델 클래스의 기본값을 삭제합니다. 세 일괄
메서드는 모두 `void`를 반환합니다.

## 빈 값

`set()`과 `setMany()`는 Laravel의 `blank()` 헬퍼를 사용합니다. 빈 값은 저장되지 않고 설정을 제거합니다.

| 값 | 결과 |
|----|------|
| `null` | 제거 |
| `''` 또는 공백만 있는 문자열 | 제거 |
| `[]` | 제거 |
| `0` | 저장 |
| `false` | 저장 |
| `'0'` | 저장 |

이 패키지는 두 메서드 모두에서 의도적으로 빈 값을 저장할 수 없습니다.

## 설정 키

키는 문자열, 정수 또는 `UnitEnum`을 구현하는 PHP enum일 수 있습니다.

```php
enum SettingKey: string
{
    case Timezone = 'timezone';
}

$user->settings()->set(SettingKey::Timezone, 'Europe/Paris');

$timezone = $user->settings()->get(SettingKey::Timezone);
```

Laravel은 backed enum을 기반 값으로 저장하고 pure unit enum을 case 이름으로 저장합니다. 설정을 읽거나 교체하거나
제거할 때 같은 키 또는 enum case를 사용합니다.

패키지는 키 내용을 검증하지 않습니다. 공개 API와 기본 스키마는 빈 키와 공백만 있는 키를 허용합니다.

점은 리터럴 문자입니다. `mail.from.address`는 하나의 불투명한 설정 키이며 중첩 경로를 뜻하지 않습니다.

```php
$user->settings()->set('mail.from.address', 'noreply@example.com');

$address = $user->settings()->get('mail.from.address');
```

## 모델 식별자

정수, 문자열, UUID, ULID 기본 키를 지원합니다.

모델별 설정을 변경하려면 키가 `null`이 아닌 저장된 소유자가 필요합니다. 저장되지 않은 모델에서 `get()`은
`null`을 반환하고 `all()`은 모델 재정의를 쿼리하지 않고 빈 컬렉션을 반환합니다. `set()`, `setMany()`,
`forget()`, `forgetMany()`, `purge()`는 저장소 쿼리나 iterable 소비 전에
`InvalidSettingsOwnerException`을 발생시킵니다.

정수 `0`과 문자열 `'0'`은 1.x에서 공유 기본값을 위해 예약되어 있습니다. 이 키를 가진 저장된 모델은 클래스
기본값을 읽을 수 있지만 모든 변경 메서드는 `InvalidSettingsOwnerException`을 발생시킵니다. `'00'`을 포함한
다른 문자열 키는 계속 유효합니다.

설정은 모델의 현재 morph 클래스에 대해 저장됩니다. 설정을 기록한 후 morph map 별칭을 추가하거나 변경하면 기존
`item_type` 값을 업데이트해야 합니다.

## 함께 보기

- [즉시 로딩](eager-loading.md) — 모델마다 설정 쿼리가 하나씩 실행되는 것을 방지합니다.
- [페이로드 캐스트](payload-casts.md) — 디코딩된 JSON 대신 도메인 객체를 반환합니다.
- [API 참조](api-reference.md) — 메서드 시그니처와 반환값을 확인합니다.
