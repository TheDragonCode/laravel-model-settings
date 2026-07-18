# Implementation Plan: Store Blank JSON Values Explicitly

Branch: codex/issue-94-store-blank-json-values
Created: 2026-07-18

## Original Request

implement [https://github.com/TheDragonCode/laravel-model-settings/issues/94](https://github.com/TheDragonCode/laravel-model-settings/issues/94)

## Settings

- Testing: yes
- Logging: standard; never log rejected setting keys or payloads
- Docs: yes

## Context

- Implement on the `1.x` line as clarified by the issue author.
- Store every JSON-serializable value, including `null`, empty strings, whitespace strings, empty arrays, `0`, and `false`.
- Keep `get()` as a one-argument API. Add `has()` so stored JSON `null` remains distinguishable from a missing key.
- Reject empty and whitespace-only normalized keys through `InvalidSettingKey` without exposing the rejected key or payload.
- Keep deletion exclusive to `forget()` and `forgetMany()`; do not add a `put()` alias.
- Preserve lazy/eager equivalence, bounded bulk query counts, relation invalidation, and rollback guarantees.

## Commit Plan

- **Commit 1** (after tasks 1-3): `feat(settings): store blank json values explicitly`
- **Commit 2** (after tasks 4-6): `docs(settings): document explicit blank value storage`

## Tasks

### Phase 1: Runtime Contract

- [x] Task 1: Add `src/Exceptions/InvalidSettingKey.php`, validate normalized keys in `src/Internal/SettingKey.php`, add `has()` to `src/Services/SettingsService.php` and `src/Repositories/SettingsRepository.php`, and make single-key `set()` persist all JSON values. Keep `get()` without a fallback argument. Logging: exceptions and package logs must not contain rejected keys or payloads.
- [x] Task 2: Refactor `setMany()` and repository bulk storage so every normalized value is upserted, no blank value is routed to deletion, duplicate normalized keys remain last-write-wins, non-empty batches execute transactionally, empty batches issue no SQL, and the loaded relation is invalidated once after success. Logging: retain sanitized operation/count/error context only. (depends on Task 1)

### Phase 2: Regression Coverage

- [x] Task 3: Update and extend `tests/Datasets`, `tests/Feature`, and `tests/Unit` for exact round trips, stored-null `has()`, default shadowing, lazy/eager equivalence, valid enum/integer keys, typed invalid-key failures across every keyed API, idempotent forget operations, bulk rollback, sanitized logs, and bounded query counts. Logging: assertions may inspect counts and exception types but must not print rejected keys or payloads. (depends on Tasks 1-2)

### Phase 3: Documentation and Localization

- [x] Task 4: Update `README.md` and the English pages in `docs/docs/` to describe the eight-method service API, exact blank-value storage, explicit deletion, key validation, bulk transactions, custom-cast behavior, and every changed 1.x behavior in the existing migration guide. Logging: examples must not contain sensitive setting values. (depends on Tasks 1-3)
- [x] Task 5: Synchronize the changed pages for `ru`, `uk`, `be`, `fr`, `pt-BR`, `ko`, `zh-CN`, and `de` under `docs/i18n/`, preserving Docusaurus structure and technical tokens and validating each locale with the translation guard. Logging: localized examples must not expose sensitive setting values. (depends on Task 4)

### Phase 4: Verification

- [x] Task 6: Run targeted and full Pest suites, architecture and type-coverage checks, Pint and Composer normalization checks, localization audit, Docusaurus typecheck and production build, and available database-matrix checks. Self-review the final diff for API compatibility, query counts, rollback behavior, lazy/eager parity, and data leakage. Logging: report aggregate sanitized results only. (depends on Tasks 1-5)
