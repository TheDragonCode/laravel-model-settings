from __future__ import annotations

import argparse
import hashlib
import json
import os
import re
import sys
import tempfile
from pathlib import Path
from typing import Any


TRANSLATABLE_FIELDS = {"message", "description"}
TOKEN_PATTERNS = (
    re.compile(r"\{[^{}\r\n]+\}"),
    re.compile(r"https?://[^\s<>()\"']+"),
    re.compile(r"`[^`\r\n]+`"),
    re.compile(r"</?[A-Za-z][^>\r\n]*>"),
    re.compile(r"(?<!%)%(?:\d+\$)?[-+#0 ]*\d*(?:\.\d+)?[A-Za-z]"),
    re.compile(r":::[A-Za-z][A-Za-z0-9_-]*"),
)


def normalized_path(path: Path) -> str:
    return os.path.normcase(os.path.normpath(str(path.resolve())))


def relative_path(path: Path, root: Path) -> str:
    return path.relative_to(root).as_posix()


def file_hash(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as stream:
        for chunk in iter(lambda: stream.read(65536), b""):
            digest.update(chunk)
    return digest.hexdigest()


def load_translation_document(path: Path) -> dict[str, Any]:
    with path.open("r", encoding="utf-8-sig") as stream:
        document = json.load(stream)

    if not isinstance(document, dict):
        raise ValueError(f"{path}: JSON root must be an object")

    for key, entry in document.items():
        if not isinstance(entry, dict):
            raise ValueError(f"{path}: entry {key!r} must be an object")
        if not isinstance(entry.get("message"), str):
            raise ValueError(f"{path}: entry {key!r} must contain a string message")
        if "description" in entry and not isinstance(entry["description"], str):
            raise ValueError(f"{path}: entry {key!r} description must be a string")

    return document


def resolve_locales(root: Path, requested: list[str] | None) -> list[str]:
    if not root.is_dir():
        raise ValueError(f"Localization root does not exist: {root}")

    available = sorted(path.name for path in root.iterdir() if path.is_dir())
    if not available:
        raise ValueError(f"No locale directories found below {root}")

    if not requested:
        return available

    locales = list(dict.fromkeys(requested))
    invalid = [locale for locale in locales if locale not in available]
    if invalid:
        raise ValueError(f"Unknown locale directories: {', '.join(invalid)}")

    return locales


def collect_state(root: Path, locales: list[str]) -> dict[str, Any]:
    directories: list[str] = []
    files: list[str] = []
    documents: dict[str, dict[str, Any]] = {}
    other_hashes: dict[str, str] = {}

    for locale in locales:
        locale_root = root / locale
        if not locale_root.is_dir():
            raise ValueError(f"Locale directory does not exist: {locale_root}")

        directories.append(relative_path(locale_root, root))

        for path in sorted(locale_root.rglob("*"), key=lambda item: item.as_posix()):
            relative = relative_path(path, root)
            if path.is_dir():
                directories.append(relative)
                continue
            if not path.is_file():
                raise ValueError(f"Unsupported filesystem entry: {path}")

            files.append(relative)
            if path.suffix.lower() == ".json":
                documents[relative] = load_translation_document(path)
            else:
                other_hashes[relative] = file_hash(path)

    return {
        "directories": directories,
        "files": files,
        "documents": documents,
        "other_hashes": other_hashes,
    }


def write_snapshot(payload: dict[str, Any], output: Path | None) -> Path:
    if output is None:
        descriptor, name = tempfile.mkstemp(prefix="translate-docs-", suffix=".json")
        with os.fdopen(descriptor, "w", encoding="utf-8", newline="\n") as stream:
            json.dump(payload, stream, ensure_ascii=False, indent=2)
            stream.write("\n")
        return Path(name).resolve()

    output = output.resolve()
    if output.exists():
        raise ValueError(f"Snapshot already exists: {output}")
    if not output.parent.is_dir():
        raise ValueError(f"Snapshot parent directory does not exist: {output.parent}")

    with output.open("x", encoding="utf-8", newline="\n") as stream:
        json.dump(payload, stream, ensure_ascii=False, indent=2)
        stream.write("\n")

    return output


def create_snapshot(
    root: Path,
    requested_locales: list[str] | None = None,
    output: Path | None = None,
) -> tuple[Path, dict[str, Any]]:
    root = root.resolve()
    locales = resolve_locales(root, requested_locales)
    state = collect_state(root, locales)
    payload = {
        "version": 1,
        "root": str(root),
        "locales": locales,
        **state,
    }
    return write_snapshot(payload, output), payload


def load_snapshot(path: Path) -> dict[str, Any]:
    with path.open("r", encoding="utf-8") as stream:
        payload = json.load(stream)

    if not isinstance(payload, dict):
        raise ValueError(f"Snapshot root must be an object: {path}")
    if payload.get("version") != 1:
        raise ValueError(f"Unsupported snapshot version: {payload.get('version')!r}")
    if not isinstance(payload.get("root"), str):
        raise ValueError("Snapshot root path is missing")
    if not isinstance(payload.get("locales"), list) or not all(
        isinstance(locale, str) for locale in payload["locales"]
    ):
        raise ValueError("Snapshot locales are invalid")

    for field in ("directories", "files"):
        if not isinstance(payload.get(field), list) or not all(
            isinstance(value, str) for value in payload[field]
        ):
            raise ValueError(f"Snapshot field {field!r} is invalid")

    for field in ("documents", "other_hashes"):
        if not isinstance(payload.get(field), dict):
            raise ValueError(f"Snapshot field {field!r} is invalid")

    return payload


def protected_tokens(value: str) -> tuple[tuple[str, ...], ...]:
    return tuple(tuple(sorted(set(pattern.findall(value)))) for pattern in TOKEN_PATTERNS)


def describe_path_changes(
    label: str,
    before: list[str],
    after: list[str],
    errors: list[str],
) -> None:
    if before == after:
        return

    before_set = set(before)
    after_set = set(after)
    removed = sorted(before_set - after_set)
    added = sorted(after_set - before_set)
    reordered = not removed and not added

    details: list[str] = []
    if removed:
        details.append(f"removed={removed}")
    if added:
        details.append(f"added={added}")
    if reordered:
        details.append("order changed")
    errors.append(f"{label} changed: {'; '.join(details)}")


def compare_document(
    relative: str,
    before: dict[str, Any],
    after: dict[str, Any],
    errors: list[str],
) -> None:
    before_keys = list(before.keys())
    after_keys = list(after.keys())
    if before_keys != after_keys:
        errors.append(f"{relative}: translation key set or order changed")

    for key in before.keys() & after.keys():
        before_entry = before[key]
        after_entry = after[key]

        if not isinstance(before_entry, dict) or not isinstance(after_entry, dict):
            errors.append(f"{relative}:{key}: entry type changed")
            continue

        before_fields = list(before_entry.keys())
        after_fields = list(after_entry.keys())
        if before_fields != after_fields:
            errors.append(f"{relative}:{key}: entry fields or field order changed")

        for field in before_entry.keys() & after_entry.keys():
            before_value = before_entry[field]
            after_value = after_entry[field]

            if field in TRANSLATABLE_FIELDS:
                if not isinstance(before_value, str) or not isinstance(after_value, str):
                    errors.append(f"{relative}:{key}.{field}: value type changed")
                    continue
                if protected_tokens(before_value) != protected_tokens(after_value):
                    errors.append(f"{relative}:{key}.{field}: protected tokens changed")
                continue

            if before_value != after_value:
                errors.append(f"{relative}:{key}.{field}: non-translatable value changed")


def validate_snapshot(root: Path, snapshot_path: Path) -> tuple[list[str], dict[str, Any]]:
    root = root.resolve()
    snapshot = load_snapshot(snapshot_path)
    errors: list[str] = []

    if normalized_path(root) != os.path.normcase(os.path.normpath(snapshot["root"])):
        errors.append(f"Localization root differs from snapshot: {root}")

    current = collect_state(root, snapshot["locales"])
    describe_path_changes("Directory paths", snapshot["directories"], current["directories"], errors)
    describe_path_changes("File paths", snapshot["files"], current["files"], errors)

    for relative, expected_hash in snapshot["other_hashes"].items():
        if relative in current["other_hashes"] and current["other_hashes"][relative] != expected_hash:
            errors.append(f"{relative}: non-JSON file content changed")

    before_documents = snapshot["documents"]
    after_documents = current["documents"]
    for relative in before_documents.keys() & after_documents.keys():
        compare_document(
            relative,
            before_documents[relative],
            after_documents[relative],
            errors,
        )

    return errors, current


def entry_count(documents: dict[str, dict[str, Any]]) -> int:
    return sum(len(document) for document in documents.values())


def build_parser() -> argparse.ArgumentParser:
    parser = argparse.ArgumentParser(prog="i18n_guard.py")
    commands = parser.add_subparsers(dest="command", required=True)

    snapshot = commands.add_parser("snapshot")
    snapshot.add_argument("--root", default="docs/i18n")
    snapshot.add_argument("--locale", action="append", dest="locales")
    snapshot.add_argument("--snapshot", dest="snapshot_path")

    validate = commands.add_parser("validate")
    validate.add_argument("--root", default="docs/i18n")
    validate.add_argument("--snapshot", required=True, dest="snapshot_path")
    validate.add_argument("--delete-snapshot", action="store_true")

    return parser


def main() -> int:
    args = build_parser().parse_args()

    try:
        if args.command == "snapshot":
            snapshot_path = Path(args.snapshot_path) if args.snapshot_path else None
            path, payload = create_snapshot(Path(args.root), args.locales, snapshot_path)
            print(f"Snapshot: {path}")
            print(f"Locales: {', '.join(payload['locales'])}")
            print(f"JSON files: {len(payload['documents'])}")
            print(f"Translation entries: {entry_count(payload['documents'])}")
            return 0

        snapshot_path = Path(args.snapshot_path).resolve()
        errors, current = validate_snapshot(Path(args.root), snapshot_path)
        if errors:
            print("Validation failed:", file=sys.stderr)
            for error in errors:
                print(f"- {error}", file=sys.stderr)
            print(f"Snapshot retained: {snapshot_path}", file=sys.stderr)
            return 1

        print(f"Validation passed: {len(current['documents'])} JSON files")
        print(f"Translation entries: {entry_count(current['documents'])}")
        if args.delete_snapshot:
            snapshot_path.unlink()
            print(f"Snapshot deleted: {snapshot_path}")
        return 0
    except (OSError, ValueError, json.JSONDecodeError) as error:
        print(f"ERROR: {error}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
