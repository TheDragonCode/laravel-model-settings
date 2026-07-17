---
sidebar_position: 7
title: API-Referenz
description: Öffentliche Trait-, Service- und Relationsmethoden von Laravel Model Settings.
---

[← Payload-Casts](payload-casts.md) · [Zurück zur README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Entwicklung →](development.md)

# API-Referenz

## HasSettings-Trait

| Methode | Rückgabe | Zweck |
|---------|----------|-------|
| `settings()` | `SettingsService` | Auf die effektiven Einstellungen dieses Modells zugreifen |
| `defaultSettings()` | `SettingsService` | Auf gemeinsame Standardwerte dieser Modellklasse zugreifen |
| `modelSettings()` | Eloquent `Relation` | Standardwerte und Überschreibungen als Relation laden |

Verwende die Relation `modelSettings` mit `with()`, `load()` oder `loadMissing()`. Verwende die beiden
Service-Methoden zum Lesen oder Ändern von Werten. Zur Laufzeit ist die Relation eine paketinterne
`SettingsRelation`, die auf Laravels `MorphMany`-Relation basiert.

## SettingsService

| Methode | Rückgabe | Verhalten |
|---------|----------|-----------|
| `all()` | `Collection` | Gibt Standardwerte zusammengeführt mit Modellüberschreibungen zurück |
| `get(int\|string\|UnitEnum $key)` | `mixed` | Gibt eine Überschreibung, ihren Standardwert oder `null` zurück |
| `set(int\|string\|UnitEnum $key, mixed $value)` | `void` | Erstellt, ersetzt oder entfernt eine leere Einstellung |
| `forget(int\|string\|UnitEnum $key)` | `void` | Entfernt eine Einstellung, falls sie vorhanden ist |

Die Methoden mit Schlüssel akzeptieren Backed Enums und Pure Unit Enums. Laravel wandelt Backed
Enums in ihren zugrunde liegenden Wert und Pure Unit Enums in ihren Case-Namen um.

## Auflösungsmatrix

| Modellüberschreibung | Klassenstandard | Ergebnis von `get()` | In `all()` enthalten |
|----------------------|-----------------|----------------------|----------------------|
| Vorhanden | Vorhanden | Überschreibung | Überschreibung |
| Vorhanden | Fehlt | Überschreibung | Überschreibung |
| Fehlt | Vorhanden | Standardwert | Standardwert |
| Fehlt | Fehlt | `null` | Kein Eintrag |

Für ein ungespeichertes Modell gibt `get()` `null` und `all()` eine leere Collection zurück.
Klassenstandards werden nur von gespeicherten Modellen geerbt.

## all

```php
$settings = $user->settings()->all();

$timezone = $settings->get('timezone');
```

Das Ergebnis ist eine `Illuminate\Support\Collection`, die nach Einstellungsschlüsseln indiziert
ist. Bei Modelleinstellungen ersetzen Überschreibungen Standardwerte mit demselben Schlüssel.

## get

```php
$timezone = $user->settings()->get('timezone');
```

Das Ergebnis ist der effektive dekodierte oder gecastete Wert. Fehlt eine Überschreibung, wird der
Standardwert verwendet. Fehlen Überschreibung und Standardwert, wird `null` zurückgegeben.

## set

```php
$user->settings()->set('timezone', 'Europe/Paris');
```

Die Methode führt eine Update-or-create-Operation für Modelltyp, Modell-ID und Schlüssel aus. Ein
von Laravel als leer betrachteter Wert löscht die Zeile. In beiden Fällen wird die geladene
`modelSettings`-Relation gelöscht, damit beim nächsten Lesen keine veralteten Daten verwendet werden.

## forget

```php
$user->settings()->forget('timezone');
```

Die Methode ist sicher, wenn der Schlüssel nicht existiert. Das Entfernen einer Überschreibung löscht
nicht ihren gemeinsamen Standardwert. Nach dem Löschen wird die geladene Relation entfernt.

## defaultSettings

Der von `defaultSettings()` zurückgegebene Service besitzt dieselben vier Methoden:

```php
$defaults = (new User)->defaultSettings();

$defaults->set('timezone', 'UTC');
$timezone = $defaults->get('timezone');
$all = $defaults->all();
$defaults->forget('timezone');
```

## Siehe auch

- [Mit Einstellungen arbeiten](settings.md) — das Verhalten jeder Operation verstehen.
- [Eager Loading](eager-loading.md) — `modelSettings` ohne N+1-Abfragen verwenden.
- [Payload-Casts](payload-casts.md) — die von `get()` und `all()` zurückgegebenen Werte steuern.
