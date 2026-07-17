---
sidebar_position: 6
title: 페이로드 캐스트
description: 설정 페이로드를 배열, 사용자 지정 캐스트 값 또는 Spatie Laravel Data 객체로 디코딩합니다.
---

[← 구성](configuration.md) · [README로 돌아가기](https://github.com/TheDragonCode/laravel-model-settings#readme) · [API 참조 →](api-reference.md)

# 페이로드 캐스트

## 기본 JSON 값

사용자 지정 캐스트가 없으면 패키지는 쓰기 시 비어 있지 않은 값을 JSON으로 인코딩하고 읽기 시 디코딩된 배열
또는 스칼라 값을 반환합니다.

```php
$user->settings()->set('notifications', [
    'email' => true,
    'push' => false,
]);

$notifications = $user->settings()->get('notifications');
```

값은 JSON으로 직렬화할 수 있어야 합니다. JSON 인코딩 오류는 숨기지 않습니다.

## 캐스트 선택

사용자 지정 캐스트는 상위 모델 클래스별로 구성합니다.

```php
'casts' => [
    App\Models\User::class => App\Casts\UserSettingsPayloadCast::class,
],
```

구성된 캐스트 하나가 해당 상위 모델 클래스에 속한 모든 설정 페이로드를 처리합니다. 캐스트를 선택하기 전에
Laravel morph map 별칭을 모델 클래스로 다시 해석합니다.

구성된 클래스는 `CastsAttributes`를 구현하거나 `Spatie\LaravelData\Data`를 확장해야 합니다. 다른 클래스는
사용자 지정 처리를 받지 않고 기본 JSON 경로를 사용합니다.

## 캐스트 수명 주기

`CastsAttributes` 구현에 대해 패키지는 다음 순서를 실행합니다.

| 방향 | 순서 |
|------|------|
| 쓰기 | 사용자 지정 `set()`을 호출한 다음 결과를 JSON으로 인코딩 |
| 읽기 | 저장된 JSON 문자열을 사용자 지정 `get()`에 전달 |

`$model` 인수는 상위 `User` 또는 `Post`가 아니라 구성된 설정 저장 모델입니다. 패키지는 생성자 인수 없이
캐스트를 생성합니다.

## Eloquent 속성 캐스트

캐스트는 Laravel의 `CastsAttributes` 계약을 구현할 수 있습니다.

```php
namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

final class UserSettingsPayloadCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        return (array) $value;
    }
}
```

사용자 지정 `set()`의 결과는 계속 JSON으로 직렬화할 수 있어야 합니다. JSON 인코딩 오류는 숨기지 않습니다.

## Spatie Laravel Data

`spatie/laravel-data`가 설치되어 있으면 `Data` 클래스를 직접 사용할 수 있습니다.

```bash
composer require spatie/laravel-data:^4.23
```

```php
'casts' => [
    App\Models\User::class => App\Data\UserSettingsData::class,
],
```

클래스가 허용하는 데이터 또는 `Data` 인스턴스를 `set()`에 전달합니다. `get()`은 데이터 인스턴스를 반환하고
`all()`은 데이터 인스턴스를 포함하는 컬렉션을 반환합니다.

```php
$preferences = UserSettingsData::from([
    'timezone' => 'Europe/Paris',
    'notifications' => true,
]);

$user->settings()->set('preferences', $preferences);

$preferences = $user->settings()->get('preferences');
```

캐스트는 키별이 아니라 상위 모델 클래스별로 선택됩니다. 따라서 이 모델의 모든 페이로드는 구성된 캐스트의 유효한
입력이어야 합니다.

## 함께 보기

- [구성](configuration.md) — 캐스트를 등록하고 저장 모델을 교체합니다.
- [설정 사용하기](settings.md) — 어떤 빈 값이 제거되는지 확인합니다.
- [API 참조](api-reference.md) — `get()`과 `all()`의 반환 형식을 확인합니다.
