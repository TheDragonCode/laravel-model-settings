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

Verwende die Relation `modelSettings` nur mit `with()`, `load()` oder `loadMissing()` sowie als die
daraus resultierende geladene Eigenschaft. Verwende ihre Relationsabfrage nicht als alternative
Lese- oder CRUD-API. Verwende die beiden Service-Methoden zum Lesen oder Ändern von Werten. Zur
Laufzeit ist die Relation eine paketinterne `SettingsRelation`, die auf Laravels `MorphMany`-Relation
basiert.

## SettingsService

| Methode | Rückgabe | Verhalten |
|---------|----------|-----------|
| `all()` | `Collection` | Gibt Standardwerte zusammengeführt mit Modellüberschreibungen zurück |
| `get(int\|string\|UnitEnum $key)` | `mixed` | Gibt eine Überschreibung, ihren Standardwert oder `null` zurück |
| `has(int\|string\|UnitEnum $key)` | `bool` | Meldet, ob ein effektiver Schlüssel existiert, auch bei gespeichertem `null` |
| `set(int\|string\|UnitEnum $key, mixed $value)` | `void` | Erstellt oder ersetzt eine Einstellung mit dem exakten JSON-Wert |
| `setMany(iterable $values)` | `void` | Führt ein Upsert für alle Werte in einem begrenzten transaktionalen Batch aus |
| `forget(int\|string\|UnitEnum $key)` | `void` | Entfernt eine Einstellung, falls sie vorhanden ist |
| `forgetMany(iterable $keys)` | `void` | Entfernt die angegebenen Schlüssel aus dem aktuellen Bereich |
| `purge()` | `void` | Entfernt alle im aktuellen Bereich gespeicherten Einstellungen |

Die Methoden mit Schlüssel akzeptieren Backed Enums und Pure Unit Enums. Laravel wandelt Backed
Enums in ihren zugrunde liegenden Wert und Pure Unit Enums in ihren Case-Namen um.

`SettingsService` besitzt keinen vom Aufrufer angegebenen Ersatzwert für `get()`. Verwende
`has($key)`, um einen fehlenden effektiven Schlüssel von einem gespeicherten JSON-`null` zu
unterscheiden.

## Auflösungsmatrix

| Modellüberschreibung | Klassenstandard | Ergebnis von `get()` | Ergebnis von `has()` | In `all()` enthalten |
|----------------------|-----------------|----------------------|----------------------|----------------------|
| Vorhanden | Vorhanden | Überschreibung, einschließlich `null` | `true` | Überschreibung |
| Vorhanden | Fehlt | Überschreibung, einschließlich `null` | `true` | Überschreibung |
| Fehlt | Vorhanden | Standardwert, einschließlich `null` | `true` | Standardwert |
| Fehlt | Fehlt | `null` | `false` | Kein Eintrag |

Für ein ungespeichertes Modell gibt `get()` `null`, `has()` gibt `false` und `all()` eine leere
Collection zurück. Klassenstandards werden nur von gespeicherten Modellen geerbt.

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
Standardwert verwendet. Fehlen Überschreibung und Standardwert, wird `null` zurückgegeben. Die
Signatur akzeptiert absichtlich kein zweites Argument als Ersatzwert.

## has

```php
$hasTimezone = $user->settings()->has('timezone');
```

Die Methode gibt `true` zurück, wenn eine Modellüberschreibung oder eine Zeile für den Klassenstandard
vorhanden ist. Ein gespeichertes JSON-`null` ergibt `true`, ein fehlender Schlüssel `false`. Lazy und
Eager Loading verwenden dieselbe Priorität; der Eager-Pfad führt keine zusätzliche Einstellungsabfrage
aus.

## set

```php
$user->settings()->set('timezone', 'Europe/Paris');
```

Die Methode validiert den Besitzer und den normalisierten Schlüssel und führt danach eine
Update-or-create-Operation für Modelltyp, Modell-ID, Bereichsdiskriminator und Schlüssel aus. Jeder
JSON-Wert wird gespeichert, einschließlich `null`, leerer Zeichenfolgen, Leerzeichenfolgen, leerer
Arrays, `0` und `false`. Nach erfolgreichem Schreiben wird die geladene `modelSettings`-Relation
gelöscht, damit beim nächsten Lesen keine veralteten Daten verwendet werden.

## setMany

```php
$user->settings()->setMany([
    'timezone' => 'Europe/Paris',
    'locale' => 'fr',
    'obsolete' => null,
]);
```

Die Schlüssel des Iterables verwenden dieselbe Normalisierung wie `set()`. Werden mehrere
Eingabeschlüssel auf dieselbe Zeichenfolge normalisiert, gewinnt der letzte Wert. Alle Werte verwenden
ein einziges datenbankeigenes Upsert innerhalb einer Transaktion. Die Methode validiert den Besitzer
vor dem Durchlaufen des Iterables und löscht `modelSettings` nach Erfolg einmal. Verwende
`forgetMany()` zum Löschen.

## forget

```php
$user->settings()->forget('timezone');
```

Für einen gültigen Besitzer ist die Methode sicher, wenn der Schlüssel nicht existiert. Das
Entfernen einer Überschreibung löscht nicht ihren gemeinsamen Standardwert. Nach dem Löschen wird die
geladene Relation entfernt.

## forgetMany

```php
$user->settings()->forgetMany(['timezone', 'locale']);
```

Die Methode normalisiert und dedupliziert das Iterable und entfernt anschließend nur diese Schlüssel
mit einem Löschvorgang aus dem aktuellen Bereich. Fehlende Schlüssel haben keine Auswirkung. Sie gibt
`void` zurück und löscht die geladene Relation nach einem erfolgreichen Aufruf, auch bei einem leeren
Iterable.

## purge

```php
$user->settings()->purge();
```

Bei `settings()` löscht die Methode alle Überschreibungen dieses gespeicherten Besitzers. Sie löscht
nie Klassenstandards oder Überschreibungen anderer Besitzer. Bei `defaultSettings()` löscht sie alle
Standardwerte dieser Modellklasse und lässt Modellüberschreibungen unverändert. Sie gibt `void`
zurück und löscht eine geladene Relation nach Erfolg.

## defaultSettings

Der von `defaultSettings()` zurückgegebene Service besitzt dieselben acht Methoden:

```php
$defaults = (new User)->defaultSettings();

$defaults->set('timezone', 'UTC');
$defaults->setMany(['timezone' => 'UTC', 'locale' => 'en']);
$timezone = $defaults->get('timezone');
$hasTimezone = $defaults->has('timezone');
$all = $defaults->all();
$defaults->forget('timezone');
$defaults->forgetMany(['timezone', 'locale']);
$defaults->purge();
```

## Exceptions

`DragonCode\LaravelModelSettings\Exceptions\InvalidSettingsOwnerException` erweitert PHPs
`DomainException`. Jede Änderung über `settings()` löst sie vor einer Speicherabfrage aus, wenn das
Besitzermodell ungespeichert ist, einschließlich eines ungespeicherten Modells mit vorab zugewiesenem
Schlüssel.

Diese Validierung erfolgt auch vor dem Durchlaufen eines gebündelten Iterables. Änderungen über
`defaultSettings()` bleiben gültig, weil dieser Service den Bereich für Klassenstandards explizit
auswählt. Der Lesezugriff bleibt eindeutig: Ein ungespeicherter Besitzer gibt `null` oder eine leere
Collection zurück, ohne Überschreibungen abzufragen, und `has()` gibt `false` zurück. Ein gespeicherter
Besitzer mit dem ganzzahligen Schlüssel `0` oder der Zeichenfolge `'0'` kann seine Überschreibungen
lesen und ändern; `is_default` trennt diese Zeilen von Klassenstandards.

`DragonCode\LaravelModelSettings\Exceptions\InvalidPayloadCast` wird ausgelöst, wenn ein
konfigurierter modellweiter oder schlüsselbezogener Cast fehlt, einen ungültigen Typ besitzt, keinen
unterstützten Vertrag implementiert oder nicht durch Laravels Container aufgelöst werden kann. Seine
Meldung darf das übergeordnete Modell, den Einstellungsschlüssel und die Cast-Klasse nennen, aber nie
den Payload.

`DragonCode\LaravelModelSettings\Exceptions\InvalidSettingKey` wird nach der Normalisierung
ausgelöst, wenn ein Schlüssel leer ist oder nur aus Leerzeichen besteht. Die Meldung und die
Paketprotokolle enthalten weder den abgelehnten Schlüssel noch den Payload.

Schlägt eine nicht leere `setMany()`-Operation fehl, setzt die Transaktion den Batch zurück. Die
Exception wird erneut ausgelöst und die bestehende geladene Relation `modelSettings` wird nicht
gelöscht.

## Siehe auch

- [Mit Einstellungen arbeiten](settings.md) — das Verhalten jeder Operation verstehen.
- [Eager Loading](eager-loading.md) — `modelSettings` ohne N+1-Abfragen verwenden.
- [Payload-Casts](payload-casts.md) — die von `get()` und `all()` zurückgegebenen Werte steuern.
