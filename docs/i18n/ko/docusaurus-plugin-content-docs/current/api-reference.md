---
sidebar_position: 7
title: API 참조
description: Laravel Model Settings가 제공하는 공개 트레이트, 서비스 및 관계 메서드입니다.
---

[← 페이로드 캐스트](payload-casts.md) · [README로 돌아가기](https://github.com/TheDragonCode/laravel-model-settings#readme) · [개발 →](development.md)

# API 참조

## HasSettings 트레이트

| 메서드 | 반환값 | 용도 |
|--------|--------|------|
| `settings()` | `SettingsService` | 이 모델의 최종 설정에 접근 |
| `defaultSettings()` | `SettingsService` | 이 모델 클래스의 공유 기본값에 접근 |
| `modelSettings()` | Eloquent `Relation` | 기본값과 재정의 값을 관계로 로딩 |

`modelSettings` 관계는 `with()`, `load()`, `loadMissing()` 및 그 결과로 로딩된 속성으로만 사용합니다.
관계 쿼리를 대체 읽기 또는 CRUD API로 사용하지 않습니다. 두 서비스 메서드는 값을 읽거나 변경할 때 사용합니다.
런타임에서 관계는 Laravel의 `MorphMany` 관계를 기반으로 하는 패키지의 `SettingsRelation`입니다.

## SettingsService

| 메서드 | 반환값 | 동작 |
|--------|--------|------|
| `all()` | `Collection` | 기본값과 모델 재정의 값을 병합하여 반환 |
| `get(int\|string\|UnitEnum $key)` | `mixed` | 재정의 값, 해당 기본값 또는 `null` 반환 |
| `set(int\|string\|UnitEnum $key, mixed $value)` | `void` | 설정을 생성하거나 교체하고 빈 설정은 제거 |
| `setMany(iterable $values)` | `void` | 채워진 값을 upsert하고 빈 값을 제한된 한 일괄 작업으로 제거 |
| `forget(int\|string\|UnitEnum $key)` | `void` | 설정이 있으면 제거 |
| `forgetMany(iterable $keys)` | `void` | 현재 범위에서 나열된 키 제거 |
| `purge()` | `void` | 현재 범위에 저장된 모든 설정 제거 |

키를 받는 메서드는 backed enum과 pure unit enum을 지원합니다. Laravel은 backed enum을 기반 값으로,
pure unit enum을 case 이름으로 변환합니다.

`SettingsService`의 `get()`에는 호출자가 지정하는 대체값 인수가 없으며 별도의 `has()` 메서드도 없습니다.
최종 키가 존재하는지 확인하려면 `all()->has($key)`를 사용합니다.

## 값 결정표

| 모델 재정의 값 | 클래스 기본값 | `get()` 결과 | `all()` 포함 여부 |
|----------------|---------------|--------------|-------------------|
| 있음 | 있음 | 재정의 값 | 재정의 값 |
| 있음 | 없음 | 재정의 값 | 재정의 값 |
| 없음 | 있음 | 기본값 | 기본값 |
| 없음 | 없음 | `null` | 항목 없음 |

저장되지 않은 모델에서 `get()`은 `null`을 반환하고 `all()`은 빈 컬렉션을 반환합니다. 클래스 기본값은 저장된
모델에만 상속됩니다.

## all

```php
$settings = $user->settings()->all();

$timezone = $settings->get('timezone');
$hasTimezone = $settings->has('timezone');
```

결과는 설정 키로 인덱싱된 `Illuminate\Support\Collection`입니다. 모델 설정에서는 재정의 값이 같은 키의
기본값을 교체합니다.

## get

```php
$timezone = $user->settings()->get('timezone');
```

결과는 최종적으로 디코딩되거나 캐스트된 값입니다. 재정의 값이 없으면 기본값을 사용합니다. 재정의 값과 기본값이
모두 없으면 `null`을 반환합니다. 시그니처는 의도적으로 두 번째 대체값 인수를 받지 않습니다.

## set

```php
$user->settings()->set('timezone', 'Europe/Paris');
```

이 메서드는 소유자를 검증한 다음 모델 형식, 모델 식별자와 키를 기준으로 update-or-create를 수행합니다.
Laravel이 빈 값으로 판단하는 값을 전달하면 행을 제거합니다. 빈 값 경로를 선택하기 전에 검증이 실행됩니다.
두 경로 모두 로딩된 `modelSettings` 관계를 지워 다음 읽기에서 오래된 데이터를 재사용하지 않도록 합니다.

## setMany

```php
$user->settings()->setMany([
    'timezone' => 'Europe/Paris',
    'locale' => 'fr',
    'obsolete' => null,
]);
```

iterable의 키는 `set()`과 같은 방식으로 정규화됩니다. 여러 입력 키가 같은 문자열로 정규화되면 마지막 값이
적용됩니다. 채워진 값은 데이터베이스 네이티브 upsert 한 번을 사용하고 빈 값은 삭제 한 번을 사용합니다. 두
그룹이 모두 있으면 두 작업을 하나의 트랜잭션에서 실행합니다. 메서드는 iterable을 소비하기 전에 소유자를
검증하고 성공 후 `modelSettings`를 한 번 지웁니다.

## forget

```php
$user->settings()->forget('timezone');
```

유효한 소유자라면 키가 없어도 안전합니다. 재정의 값을 제거해도 공유 기본값은 제거되지 않습니다. 삭제 후 로딩된
관계를 지웁니다.

## forgetMany

```php
$user->settings()->forgetMany(['timezone', 'locale']);
```

이 메서드는 iterable을 정규화하고 중복을 제거한 다음 현재 범위에서 해당 키만 한 번의 삭제로 제거합니다.
없는 키는 영향을 주지 않습니다. `void`를 반환하며 빈 iterable을 포함해 호출이 성공하면 로딩된 관계를 지웁니다.

## purge

```php
$user->settings()->purge();
```

`settings()`에서는 저장된 해당 소유자의 모든 재정의 값을 삭제합니다. 클래스 기본값이나 다른 소유자의 재정의
값은 삭제하지 않습니다. `defaultSettings()`에서는 해당 모델 클래스의 모든 기본값을 삭제하고 모델 재정의
값은 유지합니다. `void`를 반환하며 성공 후 로딩된 관계를 지웁니다.

## defaultSettings

`defaultSettings()`가 반환하는 서비스는 동일한 일곱 메서드를 제공합니다.

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

## 예외

`DragonCode\LaravelModelSettings\Exceptions\InvalidSettingsOwnerException`은 PHP의
`DomainException`을 확장합니다. 다음 조건 중 하나가 참이면 `settings()`를 통한 모든 변경은 저장소 쿼리가
실행되기 전에 이 예외를 발생시킵니다.

- 소유자 모델이 저장되지 않았습니다. 키가 미리 할당된 저장되지 않은 모델도 포함됩니다.
- 저장된 소유자 키가 정수 `0` 또는 문자열 `'0'`이며, 1.x 클래스 기본값 센티널과 충돌합니다.

이 검증은 일괄 iterable을 소비하기 전에도 실행됩니다. `defaultSettings()`를 통한 변경은 해당 서비스가 클래스
기본값 범위를 명시적으로 선택하므로 계속 유효합니다. 읽기 동작은 결정적입니다. 저장되지 않은 소유자는 재정의를
쿼리하지 않고 `null` 또는 빈 컬렉션을 반환합니다. 키가 `0`인 저장된 소유자는 클래스 기본값을 읽을 수 있지만
모델 재정의로 변경할 수 없습니다.

`DragonCode\LaravelModelSettings\Exceptions\InvalidPayloadCast`는 구성된 모델 전체 또는 키별 캐스트가
없거나 타입이 잘못되었거나 지원 계약을 구현하지 않았거나 Laravel 컨테이너로 해석할 수 없을 때 발생합니다.
메시지는 상위 모델, 설정 키, 캐스트 클래스를 식별할 수 있지만 페이로드는 절대 포함하지 않습니다.

쓰기와 삭제가 섞인 `setMany()` 작업이 실패하면 트랜잭션은 두 작업을 롤백합니다. 예외는 다시 발생하며 기존에
로딩된 `modelSettings` 관계는 지워지지 않습니다.

## 함께 보기

- [설정 사용하기](settings.md) — 각 작업의 동작을 알아봅니다.
- [즉시 로딩](eager-loading.md) — N+1 쿼리 없이 `modelSettings`를 사용합니다.
- [페이로드 캐스트](payload-casts.md) — `get()`과 `all()`이 반환하는 값을 제어합니다.
