---
sidebar_position: 2
title: 시작하기
description: Laravel Model Settings를 설치하고 첫 기본값과 재정의 값을 저장합니다.
---

[← 개요](index.md) · [README로 돌아가기](https://github.com/TheDragonCode/laravel-model-settings#readme) · [설정 사용하기 →](settings.md)

# 시작하기

## 요구 사항

- PHP 8.3 이상.
- Laravel 12 또는 13.

## 패키지 설치

```bash
composer require dragon-code/laravel-model-settings
```

Laravel은 패키지 서비스 프로바이더를 자동으로 검색합니다.

구성과 마이그레이션을 게시한 다음 설정 테이블을 생성합니다.

```bash
php artisan vendor:publish --tag="model_settings"
php artisan migrate
```

`model_settings` 태그는 `config/model_settings.php`와 패키지 마이그레이션을 게시합니다. 기본 마이그레이션은
애플리케이션의 기본 데이터베이스 연결에 `settings` 테이블을 생성합니다.

## 트레이트 추가

설정이 필요한 각 Eloquent 모델에 `HasSettings`를 추가합니다.

```php
namespace App\Models;

use DragonCode\LaravelModelSettings\Concerns\HasSettings;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasSettings;
}
```

트레이트는 다음 공개 메서드를 추가합니다.

| 멤버 | 용도 |
|------|------|
| `settings()` | 저장된 모델 하나의 최종 설정을 읽거나 변경 |
| `defaultSettings()` | 모델 클래스의 기본값을 읽거나 변경 |
| `modelSettings()` | 즉시 로딩에 사용하는 Eloquent 관계 |

## 첫 설정 저장

저장된 모든 `User` 모델에 적용할 기본값을 생성합니다.

```php
(new User)->defaultSettings()->set('timezone', 'UTC');
```

저장된 사용자 한 명에 대해 그 값을 재정의합니다.

```php
$user = User::query()->firstOrFail();

$user->settings()->set('timezone', 'Europe/Paris');

assert($user->settings()->get('timezone') === 'Europe/Paris');
```

설정 이름을 키로 사용하는 컬렉션으로 모든 최종 설정을 읽습니다.

```php
$settings = $user->settings()->all();

assert($settings->get('timezone') === 'Europe/Paris');
```

재정의 값을 제거하여 `UTC`로 돌아갑니다.

```php
$user->settings()->forget('timezone');

assert($user->settings()->get('timezone') === 'UTC');
```

## 모델을 먼저 저장

상위 모델을 저장한 후에만 `settings()->set()`, `setMany()`, `forget()`, `forgetMany()`, `purge()`를
사용합니다. 저장되지 않은 모델에서는 클래스 기본값이 있어도 `settings()->get()`이 `null`을 반환하고
`settings()->all()`이 빈 컬렉션을 반환합니다. 모든 변경 메서드는 저장소 쿼리가 실행되기 전에
`DragonCode\LaravelModelSettings\Exceptions\InvalidSettingsOwnerException`을 발생시킵니다.

## 함께 보기

- [설정 사용하기](settings.md) — 우선순위, 삭제, 키와 값의 동작을 알아봅니다.
- [구성](configuration.md) — 연결, 테이블 또는 저장 모델을 선택합니다.
- [즉시 로딩](eager-loading.md) — 모델 컬렉션의 설정을 효율적으로 로딩합니다.
