---
sidebar_position: 6
title: 페이로드 캐스트
description: 설정 페이로드를 배열, 사용자 지정 캐스트 값 또는 Spatie Laravel Data 객체로 디코딩합니다.
---

[← 구성](configuration.md) · [README로 돌아가기](https://github.com/TheDragonCode/laravel-model-settings#readme) · [API 참조 →](api-reference.md)

# 페이로드 캐스트

## 기본 JSON 값

사용자 지정 캐스트가 없으면 패키지는 쓰기 시 모든 값을 JSON으로 인코딩하고 읽기 시 정확히 디코딩된 JSON 값을
반환합니다. 여기에는 `null`, 빈 문자열, 공백 문자열, 빈 배열, 0, `false`가 포함됩니다.

```php
$user->settings()->set('notifications', [
    'email' => true,
    'push' => false,
]);

$notifications = $user->settings()->get('notifications');
```

값은 JSON으로 직렬화할 수 있어야 합니다. JSON 인코딩 오류는 숨기지 않습니다.

## 캐스트 선택

기존의 모델 전체 형식은 상위 모델 클래스에 속한 모든 설정에 하나의 캐스트를 적용합니다.

```php
'casts' => [
    App\Models\User::class => App\Casts\UserSettingsPayloadCast::class,
],
```

정확히 일치하는 설정 키에만 사용자 지정 처리가 필요하면 키별 맵을 사용합니다.

```php
'casts' => [
    App\Models\User::class => [
        'profile' => App\Data\ProfileData::class,
        'billing.credentials' => App\Casts\EncryptedSettingPayload::class,
    ],
],
```

선택 전에 Laravel morph map 별칭을 상위 모델 클래스로 다시 해석합니다. 키 일치는 Eloquent 속성 이름
`payload`가 아니라 저장된 설정 키를 사용합니다. 점은 리터럴이므로 `billing.credentials`는 하나의 키입니다.
키별 맵에 없는 키는 일반 JSON을 사용합니다.

구성된 클래스는 `CastsAttributes`를 구현하거나 `Spatie\LaravelData\Data`를 확장해야 합니다. 구성된 클래스가
잘못되었거나 없거나 지원되지 않거나 컨테이너로 해석할 수 없으면 `InvalidPayloadCast`가 발생합니다. 패키지는
구성된 항목을 일반 JSON으로 조용히 대체하지 않습니다.

## 캐스트 수명 주기

`CastsAttributes` 구현에 대해 패키지는 다음 순서를 실행합니다.

| 방향 | 순서 |
|------|------|
| 쓰기 | 사용자 지정 `set()`을 호출한 다음 결과를 JSON으로 인코딩 |
| 읽기 | 저장된 JSON 문자열을 사용자 지정 `get()`에 전달 |

`$model` 인수는 상위 `User` 또는 `Post`가 아니라 구성된 설정 저장 모델입니다. 패키지는 Laravel 컨테이너를
통해 `CastsAttributes` 구현을 해석하므로 생성자 의존성은 일반 컨테이너 바인딩을 사용할 수 있습니다. 사용자
지정 `set()` 캐스트는 Laravel이 빈 값으로 판단하는 값을 포함해 모든 입력값을 받습니다.

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

## 키별 암호화

패키지 스키마에는 암호화 메타데이터나 키 순환 계약이 없으므로 암호화는 애플리케이션 캐스트에서 처리합니다.
다음 캐스트는 설정 키 하나를 암호화하고 다른 모든 키는 일반 JSON 경로에 둡니다.

```php
namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

final class EncryptedSettingPayload implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $ciphertext = Json::decode($value);

        return Json::decode(Crypt::decryptString((string) $ciphertext));
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return Crypt::encryptString(Json::encode($value));
    }
}
```

정확한 리터럴 키에 등록합니다.

```php
'casts' => [
    App\Models\User::class => [
        'billing.credentials' => App\Casts\EncryptedSettingPayload::class,
    ],
],
```

```php
$user->settings()->set('billing.credentials', $credentials);

$credentials = $user->settings()->get('billing.credentials');
```

캐스트 전후의 값을 로그에 기록하지 않습니다. 암호화 키가 바뀔 수 있다면 운영 데이터를 저장하기 전에
애플리케이션 수준의 순환 정책을 정의하고 테스트합니다. 버전 관리와 순환을 정의한 별도 저장 계약 없이 패키지
테이블에 메타데이터 열을 추가하지 않습니다.

## Spatie Laravel Data

`spatie/laravel-data`가 설치되어 있으면 `Data` 클래스를 직접 사용할 수 있습니다.

```bash
composer require spatie/laravel-data:^4.23
```

```php
'casts' => [
    App\Models\User::class => [
        'preferences' => App\Data\UserSettingsData::class,
    ],
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

같은 모델의 다른 키는 일반 JSON을 계속 사용합니다. 해당 상위 모델의 모든 페이로드가 구성된 데이터 클래스에
유효한 입력일 때만 기존 모델 전체 형식을 사용합니다.

## 캐스트 오류

`DragonCode\LaravelModelSettings\Exceptions\InvalidPayloadCast`는 해석에 실패했을 때 상위 모델 클래스,
설정 키, 구성된 캐스트를 식별합니다. 페이로드는 절대 포함하지 않습니다. 이 예외는 단일 쓰기와 일괄 쓰기,
그리고 해당 구성 항목을 사용하는 저장된 값 읽기에서 발생합니다.

## 함께 보기

- [구성](configuration.md) — 캐스트를 등록하고 저장 모델을 교체합니다.
- [설정 사용하기](settings.md) — 정확한 JSON 값이 저장되고 제거되는 방식을 확인합니다.
- [API 참조](api-reference.md) — `get()`과 `all()`의 반환 형식을 확인합니다.
