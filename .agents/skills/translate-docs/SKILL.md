---
name: translate-docs
description: >-
  Translates Docusaurus JSON localization entries under docs/i18n into the
  language declared by docs/docusaurus.config.ts while preserving paths,
  translation keys, entry schemas, and protected tokens. Use when translating,
  completing, correcting, or reviewing documentation UI localization for one
  or more locales.
argument-hint: "[locale-code ... | all]"
allowed-tools: Read Grep Glob Write Edit Bash(git status *) Bash(git diff *) Bash(python *) Bash(python3 *) Bash(py *)
disable-model-invocation: false
user-invocable: true
compatibility: Requires Python 3.9+ and the project Docusaurus layout.
metadata:
  author: TheDragonCode
  version: "1.0"
  category: documentation
---

# Docusaurus Documentation Translation

Translate the locales requested in `$ARGUMENTS`.

If no locale is supplied, process every direct locale directory under
`docs/i18n`. Treat `all` the same way. Never create a missing locale directory.

## Ownership

This skill may edit only existing JSON files below the selected locale
directories in `docs/i18n`.

Treat these files as read-only context:

- `docs/docusaurus.config.ts`
- every Markdown file below `docs/docs`
- localization directories outside the selected locale

Preserve pre-existing user changes. Never use `git checkout`, `git restore`,
`git reset`, or file-wide regeneration to undo them.

## Hard Invariants

For every selected locale:

1. Keep every filename unchanged.
2. Keep every relative path and directory unchanged.
3. Keep every translation key unchanged and in its original order.
4. Keep every entry's fields, field order, nesting, and value types unchanged.
5. Edit only string values of `message` and existing `description` fields.
6. Do not add `description` when an entry does not already contain it.
7. Keep placeholders, URLs, inline code, markup tokens, commands, paths,
   identifiers, package names, class names, and method names intact.
8. Preserve the file's encoding, indentation, line endings, and final newline.
9. Write each locale only in the language and regional variant declared for
   that locale in `i18n.localeConfigs`.

The JSON key is an identifier, not text. Never translate it.

## Workflow

### 1. Resolve the Locale Scope

Read `docs/docusaurus.config.ts` and locate:

- `i18n.defaultLocale`
- `i18n.locales`
- `i18n.localeConfigs`
- each locale's `label` and `htmlLang`

Use `localeConfigs` as the authority for the target language. Respect regional
variants such as Brazilian Portuguese and Simplified Chinese.

Resolve the requested locale codes against both `docs/i18n/<locale>` and
`i18n.localeConfigs`. If either side is missing, report the mismatch and do not
edit that locale. Do not infer a language from a folder name alone.

Record `git status --short` before editing so existing changes remain visible.

### 2. Read All Translation Context

Before editing any JSON file:

1. Enumerate every `docs/docs/**/*.md` file recursively.
2. Read every discovered Markdown file in full.
3. Build a consistent glossary for project concepts, public API names, and UI
   terms from that content.
4. Read every JSON file in the selected locale recursively.
5. Read matching files and keys from the `defaultLocale` directory when it
   exists. Use them as the semantic source, not as permission to copy English
   text into another locale.

If a matching default-locale entry does not exist, derive meaning from the
translation key, current fields, and Markdown context. Do not invent product
behavior.

### 3. Capture the Structural Baseline

Find a working Python 3 interpreter. Try `python3 --version`,
`python --version`, `py -3 --version`, then `py --version`, and use the first
command that reports Python 3.

Create one baseline per locale immediately before editing it:

```text
python .agents/skills/translate-docs/scripts/i18n_guard.py snapshot --root docs/i18n --locale <locale>
```

Replace `python` with the resolved interpreter command when needed. Record the
exact path printed after `Snapshot:`. If snapshot creation fails, do not edit
the locale.

### 4. Translate One Locale

Process all JSON files in the locale recursively. Use targeted edits rather
than serializing whole files.

For each translation entry:

1. Keep the entry key untouched.
2. Translate `message` into the target locale's language.
3. Translate `description` into the same language when that field exists.
4. Keep proper names and stable technical identifiers unchanged.
5. Preserve every protected token such as `{count}`, `{versionLabel}`, URLs,
   inline code, HTML tags, and admonition markers.
6. Preserve Docusaurus plural syntax. Use the number and wording of plural
   forms required by the target locale; repeated placeholders are allowed.
7. Keep intentional leading or trailing whitespace when strings are composed
   at runtime, unless the target grammar requires a different boundary.
8. Keep text already correct for the target locale instead of rewriting it for
   style alone.

Use natural UI wording. Translate meaning rather than English word order.
`description` must explain the same UI purpose as its source after translation.

### 5. Validate Before Moving On

Run the guard with the recorded snapshot:

```text
python .agents/skills/translate-docs/scripts/i18n_guard.py validate --root docs/i18n --snapshot <snapshot-path> --delete-snapshot
```

The guard must pass. It checks directory and file paths, JSON validity,
translation keys and order, entry schemas, non-translatable values, and
protected tokens. On failure, it retains the snapshot. Fix only the changes
made by this skill and rerun validation.

Then inspect:

```text
git diff -- docs/i18n/<locale>
```

Confirm that every semantic change is confined to `message` or an existing
`description`. Reject whole-file formatting churn.

Perform a language review across every changed value:

- the language and regional variant match `localeConfigs`;
- no ordinary source-language prose remains accidentally;
- terminology is consistent with `docs/docs`;
- placeholders still fit the translated grammar;
- plural forms are valid for the target language;
- punctuation and accessibility wording are natural for the locale.

Only after structural and language review pass may processing continue to the
next locale.

### 6. Report the Result

Report:

- processed locale codes and their configured language labels;
- changed JSON files;
- whether both structural and language review passed for each locale;
- any locale skipped because its directory or config entry was missing.

Do not claim a locale is complete when any file was skipped or any validation
failed.

## Example

For locale `be`, this is valid because only the two text values change:

```json
"theme.blog.archive.title": {
    "message": "Архіў",
    "description": "Назва старонкі і галоўнага блока архіва блога"
}
```

The key, field names, field order, and object shape remain unchanged.
