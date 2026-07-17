---
sidebar_position: 4
title: Eager Loading
description: N+1-Abfragen beim Lesen von Einstellungen für Eloquent-Modell-Collections vermeiden.
---

[← Mit Einstellungen arbeiten](settings.md) · [Zurück zur README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Konfiguration →](configuration.md)

# Eager Loading

## Einstellungen mit den Modellen laden

Beim verzögerten Lesen von Einstellungen wird die Relation `modelSettings` geladen. Für eine
Collection entsteht dadurch eine zusätzliche Einstellungsabfrage pro Modell.

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

Dieses Verhalten ist für ganzzahlige Primärschlüssel, UUIDs und ULIDs abgedeckt.

## Änderungen nach dem Eager Loading

`set()` und `forget()` löschen die geladene Relation `modelSettings` des betroffenen Modells. Beim
nächsten Lesen wird die Relation neu geladen, sodass kein veralteter Wert zurückgegeben wird.

Eine Änderung führt weiterhin ihre eigenen Schreibabfragen aus. Eager Loading beeinflusst nur die
nachfolgenden Lesevorgänge.

## Siehe auch

- [Mit Einstellungen arbeiten](settings.md) — verstehen, wie Standardwerte und Überschreibungen zusammengeführt werden.
- [API-Referenz](api-reference.md) — Service-Methoden und Relation unterscheiden.
- [Konfiguration](configuration.md) — Verbindung und Speichermodell konfigurieren.
