---
sidebar_position: 1
slug: /
title: Laravel Model Settings
description: Gemeinsame Standardwerte und modellspezifische Überschreibungen für Laravel-Eloquent-Modelle.
---

[Zurück zur README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Erste Schritte →](getting-started.md)

# Laravel Model Settings

Laravel Model Settings speichert gemeinsame Standardwerte und modellspezifische Überschreibungen in
einer separaten Datenbanktabelle. Verwende das Paket, wenn jedes Modell mit demselben Wert beginnen
soll, einzelne Datensätze ihn aber überschreiben dürfen.

Das Paket fügt den übergeordneten Tabellen keine Einstellungsspalten hinzu. Die Einstellungen bleiben
vom Modellschema unabhängig und werden nach der Eloquent-Morph-Klasse des Modells gruppiert.

## Wann das Paket passt

| Anforderung | Verhalten des Pakets |
|-------------|----------------------|
| Allen gespeicherten Modellen denselben Anfangswert geben | Einen Standardwert auf Klassenebene speichern |
| Den Wert eines Modells ändern | Eine modellspezifische Überschreibung speichern |
| Eine Überschreibung entfernen | Den Klassenstandard wieder verwenden |
| Viele Modelle lesen | Eine Relation mit Standardwerten und Überschreibungen vorab laden |

## Auflösungsreihenfolge

Beim Lesen einer Einstellung gibt das Paket den ersten verfügbaren Wert zurück:

1. Die Überschreibung des gespeicherten Modells.
2. Den Standardwert für die Klasse dieses Modells.
3. `null`.

| Quelle | `timezone` |
|--------|------------|
| Standardwert für `User` | `UTC` |
| Überschreibung für Benutzer 123 | `Europe/Paris` |
| Effektiver Wert für Benutzer 123 | `Europe/Paris` |
| Effektiver Wert für einen anderen gespeicherten Benutzer | `UTC` |

Das Entfernen einer Überschreibung macht den Standardwert wieder sichtbar. Der Standardwert selbst
wird nicht gelöscht.

## Kernoperationen

```php
(new User)->defaultSettings()->set('timezone', 'UTC');

$user->settings()->set('timezone', 'Europe/Paris');

$timezone = $user->settings()->get('timezone');
$settings = $user->settings()->all();

$user->settings()->forget('timezone');
```

`get()` gibt einen effektiven Wert zurück. `all()` gibt eine `Illuminate\Support\Collection` zurück,
in der Standardwerte und Überschreibungen zusammengeführt sind.

Für Standardwerte und Überschreibungen stehen dieselben vier Operationen bereit: `all()`, `get()`,
`set()` und `forget()`.

## Speichergrenzen

Jede Zeile wird durch drei Werte identifiziert:

| Wert | Bedeutung |
|------|-----------|
| `item_type` | Morph-Klasse oder Morph-Map-Alias des übergeordneten Modells |
| `item_id` | Primärschlüssel des übergeordneten Modells oder der reservierte Wert `0` für Klassenstandards |
| `key` | Name der Einstellung |

Dadurch bleiben Standardwerte für jede Modellklasse unabhängig. Ein `User`-Standard wird nie zum
`Post`-Standard, selbst wenn beide Klassen denselben Einstellungsschlüssel verwenden.

## Unterstützte Modelle

Das Paket unterstützt Eloquent-Modelle mit ganzzahligen, Zeichenfolgen-, UUID- oder ULID-
Primärschlüsseln. Modelle können außerdem eine Laravel Morph Map verwenden.

Modellspezifische Einstellungen gehören zu gespeicherten Modellen. Ein ungespeichertes Modell erbt
keine Standardwerte: `get()` gibt `null` und `all()` eine leere Collection zurück. `set()` oder
`forget()` für einen ungespeicherten Besitzer lösen vor einer Speicherabfrage eine
`InvalidSettingsOwnerException` aus.

Payloads werden als JSON gespeichert. Ohne konfigurierten Cast geben Lesevorgänge dekodierte Arrays
oder skalare Werte zurück. [Payload-Casts](payload-casts.md) können stattdessen anwendungsspezifische
Objekte zurückgeben.

## Siehe auch

- [Erste Schritte](getting-started.md) — das Paket installieren und ein Modell konfigurieren.
- [Mit Einstellungen arbeiten](settings.md) — Standardwerte, Überschreibungen, Schlüssel und Werte verwalten.
- [API-Referenz](api-reference.md) — alle öffentlichen Methoden und Rückgabetypen prüfen.
