---
sidebar_position: 4
title: Eager Loading
description: N+1-Abfragen beim Lesen von Einstellungen für Eloquent-Modell-Collections vermeiden.
---

[← Mit Einstellungen arbeiten](settings.md) · [Zurück zur README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Konfiguration →](configuration.md)

# Eager Loading

## Einstellungen mit den Modellen laden

Ohne Eager Loading führt jeder Aufruf von `settings()->get()` oder `settings()->all()` eine
Einstellungsabfrage aus. Diese Service-Lesevorgänge laden `modelSettings` nicht als Nebeneffekt.

Lade die Relation vorab, wenn das Ergebnis mehrere Modelle enthält:

```php
$users = User::query()
    ->with('modelSettings')
    ->get();

$timezones = $users->map(
    fn (User $user) => $user->settings()->get('timezone')
);
```

Die vorab geladene Relation enthält die Überschreibungen jedes Modells sowie alle geerbten
Standardwerte. Nachfolgende Aufrufe von `get()` und `all()` verwenden die geladene Relation.

## Einstellungen nach der Abfrage laden

Verwende `loadMissing()`, wenn die Modelle bereits verfügbar sind:

```php
$users->loadMissing('modelSettings');

$settings = $users->map(
    fn (User $user) => $user->settings()->all()
);
```

## Relationsgrenze

Verwende `modelSettings` nur mit `with()`, `load()` oder `loadMissing()` sowie als geladene
Relationseigenschaft. Sie ist eine Leseoptimierung und keine alternative Abfrage- oder CRUD-API.
Lies und ändere Werte über `settings()` oder `defaultSettings()`.

## Abfrageverhalten

Wenn übergeordnete Modelle abgerufen und ihre Einstellungen anschließend gelesen werden, kosten Lazy
Loading und Eager Loading für ein Modell gleich viel. Bei einer Collection ist der Unterschied sichtbar:

| Geladene übergeordnete Modelle | Lazy Loading | Eager Loading |
|--------------------------------|--------------|---------------|
| 1 | 2 Abfragen | 2 Abfragen |
| N | 1 + N Abfragen | 2 Abfragen |

Der Eager-Loading-Pfad verwendet:

1. Eine Abfrage für die übergeordneten Modelle.
2. Eine Abfrage für deren Standardwerte und Überschreibungen.

Die Einstellungsabfrage enthält die Klassenstandards und alle angeforderten Modell-IDs. Die Relation
kopiert anschließend geerbte Standardwerte in das geladene Ergebnis jedes Modells und ersetzt
passende Schlüssel durch die Überschreibungen dieses Modells.

Dieses Verhalten ist für ganzzahlige, Zeichenfolgen-, UUID- und ULID-Primärschlüssel abgedeckt.

## Änderungen nach dem Eager Loading

`set()` und `forget()` löschen die geladene Relation `modelSettings` des betroffenen Modells. Der
nächste Service-Lesevorgang fragt den aktuellen effektiven Wert ab und gibt keine veralteten Daten
zurück. Lade die Relation vor einem weiteren gebündelten Lesevorgang erneut explizit vorab.

Eine Änderung führt weiterhin ihre eigenen Schreibabfragen aus. Eager Loading beeinflusst nur die
nachfolgenden Lesevorgänge.

## Siehe auch

- [Mit Einstellungen arbeiten](settings.md) — verstehen, wie Standardwerte und Überschreibungen zusammengeführt werden.
- [API-Referenz](api-reference.md) — Service-Methoden und Relation unterscheiden.
- [Konfiguration](configuration.md) — Verbindung und Speichermodell konfigurieren.
