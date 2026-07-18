---
sidebar_position: 8
title: Entwicklung
description: Tests ausführen, Dokumentation prüfen, beitragen oder ein Sicherheitsproblem melden.
---

[← API-Referenz](api-reference.md) · [Zurück zur README](https://github.com/TheDragonCode/laravel-model-settings#readme)

# Entwicklung

## Paketprüfungen

Installiere die PHP-Abhängigkeiten:

```bash
composer install
```

Führe die Tests aus oder erzeuge einen Coverage-Bericht:

```bash
composer test
composer test:coverage
```

Wende den konfigurierten Codestil an:

```bash
composer style
```

Die Pest-Suites decken verschiedene Verträge ab:

| Suite | Abdeckung |
|-------|-----------|
| `tests/Feature` | Standardwerte, Überschreibungen, Löschen, fehlende Daten und Eigentümerschaft |
| `tests/Unit/Casts` | Standard-JSON, benutzerdefinierte Casts, Morph Maps und Laravel Data |
| `tests/Unit/KeyTypes` | Zeichenfolgen-, Ganzzahl-, Backed-Enum- und Pure-Unit-Enum-Schlüssel |
| `tests/Unit/PrimaryKeyTypes` | Ganzzahlige, Zeichenfolgen-, UUID- und ULID-IDs übergeordneter Modelle |
| `tests/Unit/QueryCount` | Anzahl der Lese- und Schreibabfragen einschließlich Eager Loading |
| `tests/Architecture` | Namespaces, Typen, Striktheit und Laravel-Architekturregeln |

## Dokumentationsprüfungen

Die Docusaurus-Seite benötigt Node.js 20 oder neuer. Installiere ihre Abhängigkeiten im Verzeichnis
`docs`:

```bash
npm ci
```

| Aufgabe | Befehl |
|---------|--------|
| Lokale Seite starten | `npm run start` |
| TypeScript prüfen | `npm run typecheck` |
| Übersetzungen prüfen | `npm run check:i18n` |
| Produktions-Build erstellen | `npm run build` |

Der Produktions-Build validiert interne Links für jede konfigurierte Locale.

Dokumentationsseiten liegen in `docs/docs`. Jede Seite verwendet Front Matter für die Reihenfolge in
der Seitenleiste, eine Navigationszeile am Anfang, relative Links zwischen Anleitungen und einen
Abschnitt `Siehe auch` am Ende.

Jede Locale außer der Standard-Locale liegt in
`docs/i18n/<locale>/docusaurus-plugin-content-docs/current`. Jede Locale muss dieselben Seitenpfade wie
`docs/docs` enthalten. Der Befehl `npm run check:i18n` prüft dies vor einem Produktions-Build.

## Mitwirken

Beachte den [Leitfaden für Beiträge](https://github.com/TheDragonCode/.github/blob/main/CONTRIBUTING.md),
bevor du einen Pull Request öffnest.

## Sicherheit

Melde Sicherheitsprobleme vertraulich an
[helldar@dragon-code.pro](mailto:helldar@dragon-code.pro).

## Mitwirkende

Erstellt von [Andrey Helldar](https://github.com/andrey-helldar) und den
[Projektmitwirkenden](https://github.com/TheDragonCode/laravel-model-settings/graphs/contributors).

## Siehe auch

- [Erste Schritte](getting-started.md) — das Paket in einer Laravel-Anwendung installieren.
- [Konfiguration](configuration.md) — die veröffentlichten Paketdateien verstehen.
- [API-Referenz](api-reference.md) — die öffentliche API vor Verhaltensänderungen prüfen.
