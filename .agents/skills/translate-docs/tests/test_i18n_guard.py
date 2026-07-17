from __future__ import annotations

import importlib.util
import json
import tempfile
import unittest
from pathlib import Path


MODULE_PATH = Path(__file__).parents[1] / "scripts" / "i18n_guard.py"
SPEC = importlib.util.spec_from_file_location("i18n_guard", MODULE_PATH)
if SPEC is None or SPEC.loader is None:
    raise RuntimeError(f"Cannot load {MODULE_PATH}")
GUARD = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(GUARD)


class I18nGuardTest(unittest.TestCase):
    def setUp(self) -> None:
        self.temporary_directory = tempfile.TemporaryDirectory()
        self.workspace = Path(self.temporary_directory.name)
        self.root = self.workspace / "i18n"
        self.document_path = self.root / "be" / "nested" / "code.json"
        self.document_path.parent.mkdir(parents=True)
        self.document = {
            "theme.example": {
                "message": "Open {name}",
                "description": "The label for `open` at https://example.com",
                "fixed": "unchanged",
            },
            "theme.plural": {
                "message": "{count} page|{count} pages",
                "description": "Pluralized page count",
            },
        }
        self.write_document(self.document)
        self.snapshot_path = self.workspace / "snapshot.json"
        GUARD.create_snapshot(self.root, ["be"], self.snapshot_path)

    def tearDown(self) -> None:
        self.temporary_directory.cleanup()

    def write_document(self, document: dict[str, object]) -> None:
        self.document_path.write_text(
            json.dumps(document, ensure_ascii=False, indent=4) + "\n",
            encoding="utf-8",
        )

    def validate(self) -> list[str]:
        errors, _ = GUARD.validate_snapshot(self.root, self.snapshot_path)
        return errors

    def test_accepts_translated_fields_and_locale_plural_forms(self) -> None:
        self.document["theme.example"]["message"] = "Адкрыць {name}"
        self.document["theme.example"]["description"] = (
            "Подпіс для `open` на https://example.com"
        )
        self.document["theme.plural"]["message"] = (
            "{count} старонка|{count} старонкі|{count} старонак"
        )
        self.document["theme.plural"]["description"] = "Формы колькасці старонак"
        self.write_document(self.document)

        self.assertEqual([], self.validate())

    def test_rejects_translation_key_changes(self) -> None:
        entry = self.document.pop("theme.example")
        self.document["theme.renamed"] = entry
        self.write_document(self.document)

        self.assertTrue(any("translation key" in error for error in self.validate()))

    def test_rejects_entry_structure_changes(self) -> None:
        self.document["theme.example"]["extra"] = "value"
        self.write_document(self.document)

        self.assertTrue(any("entry fields" in error for error in self.validate()))

    def test_rejects_protected_token_changes(self) -> None:
        self.document["theme.example"]["message"] = "Адкрыць {title}"
        self.write_document(self.document)

        self.assertTrue(any("protected tokens" in error for error in self.validate()))

    def test_rejects_non_translatable_value_changes(self) -> None:
        self.document["theme.example"]["fixed"] = "changed"
        self.write_document(self.document)

        self.assertTrue(any("non-translatable value" in error for error in self.validate()))

    def test_rejects_file_renames(self) -> None:
        self.document_path.rename(self.document_path.with_name("renamed.json"))

        self.assertTrue(any("File paths changed" in error for error in self.validate()))


if __name__ == "__main__":
    unittest.main()
