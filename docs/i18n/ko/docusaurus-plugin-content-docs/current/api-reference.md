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

`modelSettings` 관계는 `with()`, `load()`, `loadMissing()`과 함께 사용합니다. 두 서비스 메서드는 값을
읽거나 변경할 때 사용합니다. 런타임에서 관계는 Laravel의 `MorphMany` 관계를 기반으로 하는 패키지의
`SettingsRelation`입니다.

## SettingsService

| 메서드 | 반환값 | 동작 |
|--------|--------|------|
| `all()` | `Collection` | 기본값과 모델 재정의 값을 병합하여 반환 |
| `get(int\|string\|UnitEnum $key)` | `mixed` | 재정의 값, 해당 기본값 또는 `null` 반환 |
| `set(int\|string\|UnitEnum $key, mixed $value)` | `void` | 설정을 생성하거나 교체하고 빈 설정은 제거 |
| `forget(int\|string\|UnitEnum $key)` | `void` | 설정이 있으면 제거 |

키를 받는 메서드는 backed enum과 pure unit enum을 지원합니다. Laravel은 backed enum을 기반 값으로,
pure unit enum을 case 이름으로 변환합니다.

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
```

결과는 설정 키로 인덱싱된 `Illuminate\Support\Collection`입니다. 모델 설정에서는 재정의 값이 같은 키의
기본값을 교체합니다.

## get

```php
$timezone = $user->settings()->get('timezone');
```

결과는 최종적으로 디코딩되거나 캐스트된 값입니다. 재정의 값이 없으면 기본값을 사용합니다. 재정의 값과 기본값이
모두 없으면 `null`을 반환합니다.

## set

```php
$user->settings()->set('timezone', 'Europe/Paris');
```

이 메서드는 모델 형식, 모델 식별자와 키를 기준으로 update-or-create를 수행합니다. Laravel이 빈 값으로 판단하는
값을 전달하면 행을 제거합니다. 두 경로 모두 로딩된 `modelSettings` 관계를 지워 다음 읽기에서 오래된 데이터를
재사용하지 않도록 합니다.

## forget

```php
$user->settings()->forget('timezone');
```

키가 없어도 안전합니다. 재정의 값을 제거해도 공유 기본값은 제거되지 않습니다. 삭제 후 로딩된 관계를 지웁니다.

## defaultSettings

`defaultSettings()`가 반환하는 서비스는 동일한 네 메서드를 제공합니다.

```php
$defaults = (new User)->defaultSettings();

$defaults->set('timezone', 'UTC');
$timezone = $defaults->get('timezone');
$all = $defaults->all();
$defaults->forget('timezone');
```

## 함께 보기

- [설정 사용하기](settings.md) — 각 작업의 동작을 알아봅니다.
- [즉시 로딩](eager-loading.md) — N+1 쿼리 없이 `modelSettings`를 사용합니다.
- [페이로드 캐스트](payload-casts.md) — `get()`과 `all()`이 반환하는 값을 제어합니다.
