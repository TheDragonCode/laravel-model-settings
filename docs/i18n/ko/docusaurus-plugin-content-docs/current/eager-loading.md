---
sidebar_position: 4
title: 즉시 로딩
description: Eloquent 모델 컬렉션의 설정을 읽을 때 N+1 쿼리를 방지합니다.
---

[← 설정 사용하기](settings.md) · [README로 돌아가기](https://github.com/TheDragonCode/laravel-model-settings#readme) · [구성 →](configuration.md)

# 즉시 로딩

## 모델과 함께 설정 로딩

즉시 로딩하지 않으면 `settings()->get()` 또는 `settings()->all()`을 호출할 때마다 설정 쿼리가 실행됩니다.
이 서비스 읽기 작업은 부수 효과로 `modelSettings`를 로딩하지 않습니다.

결과에 여러 모델이 포함되면 관계를 즉시 로딩합니다.

```php
$users = User::query()
    ->with('modelSettings')
    ->get();

$timezones = $users->map(
    fn (User $user) => $user->settings()->get('timezone')
);
```

즉시 로딩된 관계에는 각 모델의 재정의 값과 상속된 모든 기본값이 포함됩니다. 이후 `get()`과 `all()` 호출은
로딩된 관계를 사용합니다.

## 쿼리 후 설정 로딩

모델을 이미 사용할 수 있으면 `loadMissing()`을 사용합니다.

```php
$users->loadMissing('modelSettings');

$settings = $users->map(
    fn (User $user) => $user->settings()->all()
);
```

## 관계의 사용 범위

`modelSettings`는 `with()`, `load()`, `loadMissing()` 및 로딩된 관계 속성으로만 사용합니다. 이 관계는 읽기
최적화이며 대체 쿼리 또는 CRUD API가 아닙니다. 값은 `settings()` 또는 `defaultSettings()`를 통해 읽고
변경합니다.

## 쿼리 동작

상위 모델을 가져온 뒤 설정을 읽을 때 모델 하나에 대한 지연 로딩과 즉시 로딩의 비용은 같습니다. 컬렉션에서는
차이가 분명합니다.

| 로딩된 상위 모델 | 지연 로딩 | 즉시 로딩 |
|------------------|-----------|-----------|
| 1 | 쿼리 2개 | 쿼리 2개 |
| N | 쿼리 1 + N개 | 쿼리 2개 |

즉시 로딩 경로는 다음 쿼리를 사용합니다.

1. 상위 모델을 위한 쿼리 하나.
2. 기본값과 재정의 값을 위한 쿼리 하나.

설정 쿼리에는 클래스 기본값과 요청된 모든 모델 식별자가 포함됩니다. 관계는 상속된 기본값을 각 모델의 로딩된
결과에 복사한 다음 일치하는 키를 해당 모델의 재정의 값으로 교체합니다.

이 동작은 정수, 문자열, UUID, ULID 기본 키에 대해 검증됩니다.

## 즉시 로딩 후 변경

`set()`, `setMany()`, `forget()`, `forgetMany()`, `purge()`가 성공하면 패키지는 해당 모델에 로딩된
`modelSettings` 관계를 정확히 한 번 지웁니다. 다음 서비스 읽기는 현재 최종 값을 쿼리하므로 오래된 데이터를
반환하지 않습니다. 일괄 변경이 실패하면 기존 로딩 관계를 유지하고 쓰기와 삭제가 섞인 트랜잭션을 롤백합니다.

다음 일괄 읽기 전에 관계를 명시적으로 다시 로딩합니다.

```php
$user->settings()->setMany([
    'timezone' => 'Europe/Paris',
    'locale' => 'fr',
]);

$user->load('modelSettings');
```

변경 작업은 자체 쓰기 쿼리를 계속 수행합니다. 즉시 로딩은 이후 읽기에만 영향을 줍니다.

## 함께 보기

- [설정 사용하기](settings.md) — 기본값과 재정의 값이 병합되는 방식을 알아봅니다.
- [API 참조](api-reference.md) — 서비스 메서드와 관계를 구분합니다.
- [구성](configuration.md) — 설정 연결과 저장 모델을 구성합니다.
