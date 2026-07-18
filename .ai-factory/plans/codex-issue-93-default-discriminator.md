# Implementation Plan: Distinguish Persistent Defaults from Owner ID 0

Branch: codex/issue-93-default-discriminator
Created: 2026-07-18

## Original Request

implement [https://github.com/TheDragonCode/laravel-model-settings/issues/93](https://github.com/TheDragonCode/laravel-model-settings/issues/93)

## Settings

- Testing: yes
- Logging: standard; never log setting keys or payloads
- Docs: yes

## Context

- Implement on the `1.x` line as clarified by the issue author.
- Preserve one authoritative settings table and the explicit scope representation introduced by issue #88.
- Keep the original create migration immutable; run the new upgrade migration on both fresh and existing installations.
- Preserve lazy/eager equivalence and the two-query eager-loading contract.

## Commit Plan

- **Commit 1** (after tasks 1-3): `feat(settings): add explicit default discriminator storage`
- **Commit 2** (after tasks 4-7): `feat(settings): resolve defaults with explicit scope`

## Tasks

### Phase 1: Schema and Baseline

- [x] Task 1: Add `database/migrations/2026_07_18_000000_add_is_default_to_model_settings_table.php` and an isolated `tests/Migration/IsDefaultMigrationTest.php` suite without `RefreshDatabase`. Add a non-null `is_default` boolean, classify legacy `item_id = '0'` rows without row-level output, create discriminator-aware unique and lookup indexes before removing the legacy unique index, and provide a rollback that stops before DDL when owner-ID-0 overrides make the downgrade ambiguous. Logging: migration output remains silent and exceptions expose neither keys nor payloads.
- [x] Task 2: Update `src/Models/Settings.php`, `src/Internal/SettingsScope.php`, `src/Repositories/SettingsRepository.php`, and `src/Exceptions/InvalidSettingsOwnerException.php` so every read, write, bulk upsert, delete, and purge carries `is_default`, while unsaved-owner validation remains and real integer/string ID `0` mutations become valid. Logging: preserve existing structured service logs and never add owner setting keys or payloads.
- [x] Task 3: Add `tests/Benchmark/EagerLoadingTest.php` and the Composer benchmark script for 100/1,000 owners with 10/100 defaults; execute the benchmark before changing the eager-relation replication algorithm. Logging: benchmark output contains scenario dimensions and aggregate timing/memory only.

### Phase 2: Resolution and Eager Loading

- [x] Task 4: Update `src/Scopes/PriorityScope.php` and `src/Relations/SettingsRelation.php` to select defaults and overrides by `is_default`, resolve actual owner key `0`, preserve morph-map constraints, replicate inherited defaults for Laravel matching, and retain two-query eager loading. Logging: do not add per-read runtime logs or expose keys/payloads.
- [x] Task 5: Rewrite zero-owner tests and add migration, coexistence, bulk isolation, morph-map, primary-key, lazy/eager equivalence, uniqueness, rollback-boundary, and query-count coverage under `tests/Feature` and `tests/Unit`. Logging: assertions may inspect query counts and discriminator values but never print stored keys or payloads.

### Phase 3: Documentation and Verification

- [x] Task 6: Update `README.md`, `docs/docs/index.md`, `docs/docs/settings.md`, `docs/docs/configuration.md`, and `docs/docs/api-reference.md`, then synchronize the changed pages in all eight configured non-default locales. Document upgrade classification ambiguity, deployment sequencing, index shape, custom-model requirements, and the rollback boundary. Logging: documentation examples contain no sensitive setting values.
- [x] Task 7: Run Pint, Composer normalization, targeted and full Pest suites, the benchmark, Docusaurus i18n/type/build checks, and available SQLite/MySQL/MariaDB/PostgreSQL migration verification. Self-review the final diff for compatibility, query count, index order, and data-loss risks. Logging: report only aggregate pass/fail results and sanitized migration errors.
