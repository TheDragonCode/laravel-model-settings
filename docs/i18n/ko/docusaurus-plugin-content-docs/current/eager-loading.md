---
sidebar_position: 4
title: 즉시 로딩
description: Eloquent 모델 컬렉션의 설정을 읽을 때 N+1 쿼리를 방지합니다.
---

[← 설정 사용하기](settings.md) · [README로 돌아가기](https://github.com/TheDragonCode/laravel-model-settings#readme) · [구성 →](configuration.md)

# 즉시 로딩

## 모델과 함께 설정 로딩

설정을 지연 로딩하면 `modelSettings` 관계가 로딩됩니다. 컬렉션에서는 모델마다 설정 쿼리가 하나씩 추가됩니다.

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

이 동작은 정수, UUID, ULID 기본 키에 대해 검증됩니다.

## 즉시 로딩 후 변경

`set()`과 `forget()`은 해당 모델에 로딩된 `modelSettings` 관계를 지웁니다. 다음 읽기에서 관계를 다시
로딩하므로 오래된 값을 반환하지 않습니다.

변경 작업은 자체 쓰기 쿼리를 계속 수행합니다. 즉시 로딩은 이후 읽기에만 영향을 줍니다.

## 함께 보기

- [설정 사용하기](settings.md) — 기본값과 재정의 값이 병합되는 방식을 알아봅니다.
- [API 참조](api-reference.md) — 서비스 메서드와 관계를 구분합니다.
- [구성](configuration.md) — 설정 연결과 저장 모델을 구성합니다.
