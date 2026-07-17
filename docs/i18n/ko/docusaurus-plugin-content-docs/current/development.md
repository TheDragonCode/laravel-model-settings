---
sidebar_position: 8
title: 개발
description: 테스트를 실행하고 문서를 검증하며 기여하거나 보안 문제를 보고합니다.
---

[← API 참조](api-reference.md) · [README로 돌아가기](https://github.com/TheDragonCode/laravel-model-settings#readme)

# 개발

## 패키지 검사

PHP 의존성을 설치합니다.

```bash
composer install
```

테스트 스위트를 실행하거나 커버리지를 생성합니다.

```bash
composer test
composer test:coverage
```

구성된 코드 스타일을 적용합니다.

```bash
composer style
```

Pest 스위트는 서로 다른 계약을 검사합니다.

| 스위트 | 검사 범위 |
|--------|-----------|
| `tests/Feature` | 기본값, 재정의 값, 삭제, 누락 데이터와 소유권 |
| `tests/Unit/Casts` | 기본 JSON, 사용자 지정 캐스트, morph map과 Laravel Data |
| `tests/Unit/KeyTypes` | 문자열, 정수, backed enum과 pure unit enum 키 |
| `tests/Unit/PrimaryKeyTypes` | 정수, UUID와 ULID 상위 모델 식별자 |
| `tests/Unit/QueryCount` | 즉시 로딩을 포함한 읽기 및 쓰기 쿼리 수 |
| `tests/Architecture` | 네임스페이스, 형식, 엄격성 및 Laravel 아키텍처 규칙 |

## 문서 검사

Docusaurus 사이트에는 Node.js 20 이상이 필요합니다. `docs` 디렉터리에서 의존성을 설치합니다.

```bash
npm ci
```

| 작업 | 명령 |
|------|------|
| 로컬 사이트 시작 | `npm run start` |
| TypeScript 검사 | `npm run typecheck` |
| 번역 검사 | `npm run check:i18n` |
| 프로덕션 빌드 생성 | `npm run build` |

프로덕션 빌드는 구성된 각 locale의 내부 링크를 검증합니다.

문서 페이지는 `docs/docs`에 보관합니다. 각 페이지는 사이드바 순서를 위한 front matter, 상단 탐색 줄, 가이드 간
상대 링크와 마지막의 `함께 보기` 섹션을 사용합니다.

기본 locale 이외의 각 locale은 `docs/i18n/<locale>/docusaurus-plugin-content-docs/current`에 보관합니다.
각 locale은 `docs/docs`와 같은 페이지 경로를 포함해야 합니다. `npm run check:i18n` 명령은 프로덕션 빌드 전에
이를 검사합니다.

## 기여

Pull request를 열기 전에 [기여 가이드](https://github.com/TheDragonCode/.github/blob/main/CONTRIBUTING.md)를
따릅니다.

## 보안

보안 문제는 [helldar@dragon-code.pro](mailto:helldar@dragon-code.pro)로 비공개 보고합니다.

## 제작자

[Andrey Helldar](https://github.com/andrey-helldar)와
[프로젝트 기여자](https://github.com/TheDragonCode/laravel-model-settings/graphs/contributors)가 만들었습니다.

## 함께 보기

- [시작하기](getting-started.md) — Laravel 애플리케이션에 패키지를 설치합니다.
- [구성](configuration.md) — 게시된 패키지 파일을 이해합니다.
- [API 참조](api-reference.md) — 동작을 변경하기 전에 공개 API를 검토합니다.
