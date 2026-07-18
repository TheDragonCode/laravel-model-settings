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
| `set(int\|string\|UnitEnum $key, mixed $value)` | `void` | Erstellt, ersetzt oder entfernt eine leere Einstellung |
| `setMany(iterable $values)` | `void` | Führt ein Upsert für gefüllte Werte aus und entfernt leere Werte in einem begrenzten Batch |
| `forget(int\|string\|UnitEnum $key)` | `void` | Entfernt eine Einstellung, falls sie vorhanden ist |
| `forgetMany(iterable $keys)` | `void` | Entfernt die angegebenen Schlüssel aus dem aktuellen Bereich |
| `purge()` | `void` | Entfernt alle im aktuellen Bereich gespeicherten Einstellungen |

Die Methoden mit Schlüssel akzeptieren Backed Enums und Pure Unit Enums. Laravel wandelt Backed
Enums in ihren zugrunde liegenden Wert und Pure Unit Enums in ihren Case-Namen um.

`SettingsService` besitzt weder einen vom Aufrufer angegebenen Ersatzwert für `get()` noch eine
eigene Methode `has()`. Verwende `all()->has($key)`, um zu prüfen, ob ein effektiver Schlüssel
vorhanden ist.

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
$hasTimezone = $settings->has('timezone');
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

## set

```php
$user->settings()->set('timezone', 'Europe/Paris');
```

Die Methode validiert den Besitzer und führt danach eine Update-or-create-Operation für Modelltyp,
Modell-ID und Schlüssel aus. Ein von Laravel als leer betrachteter Wert löscht die Zeile. Die
Validierung erfolgt vor der Auswahl des Pfads für leere Werte. In beiden Fällen wird die geladene
`modelSettings`-Relation gelöscht, damit beim nächsten Lesen keine veralteten Daten verwendet werden.

## setMany

```php
$user->settings()->setMany([
    'timezone' => 'Europe/Paris',
    'locale' => 'fr',
    'obsolete' => null,
]);
```

Die Schlüssel des Iterables verwenden dieselbe Normalisierung wie `set()`. Werden mehrere
Eingabeschlüssel auf dieselbe Zeichenfolge normalisiert, gewinnt der letzte Wert. Gefüllte Werte
verwenden ein datenbankeigenes Upsert, leere Werte einen Löschvorgang. Sind beide Gruppen vorhanden,
laufen beide Operationen in einer Transaktion. Die Methode validiert den Besitzer vor dem Durchlaufen
des Iterables und löscht `modelSettings` nach Erfolg einmal.

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

Der von `defaultSettings()` zurückgegebene Service besitzt dieselben sieben Methoden:

```php
$defaults = (new User)->defaultSettings();

$defaults->set('timezone', 'UTC');
$defaults->setMany(['timezone' => 'UTC', 'locale' => 'en']);
$timezone = $defaults->get('timezone');
$all = $defaults->all();
$defaults->forget('timezone');
$defaults->forgetMany(['timezone', 'locale']);
$defaults->purge();
```

## Exceptions

`DragonCode\LaravelModelSettings\Exceptions\InvalidSettingsOwnerException` erweitert PHPs
`DomainException`. Jede Änderung über `settings()` löst sie vor einer Speicherabfrage aus, wenn eine
der folgenden Bedingungen erfüllt ist:

- Das Besitzermodell ist ungespeichert, einschließlich eines ungespeicherten Modells mit vorab
  zugewiesenem Schlüssel.
- Der Schlüssel des gespeicherten Besitzers ist die Ganzzahl `0` oder die Zeichenfolge `'0'` und
  kollidiert dadurch mit dem Sentinel für Klassenstandards in 1.x.

Diese Validierung erfolgt auch vor dem Durchlaufen eines gebündelten Iterables. Änderungen über
`defaultSettings()` bleiben gültig, weil dieser Service den Bereich für Klassenstandards explizit
auswählt. Der Lesezugriff bleibt eindeutig: Ein ungespeicherter Besitzer gibt `null` oder eine leere
Collection zurück, ohne Überschreibungen abzufragen. Ein gespeicherter Besitzer mit Schlüssel `0`
kann Klassenstandards lesen, aber nicht als Modellüberschreibungen ändern.

`DragonCode\LaravelModelSettings\Exceptions\InvalidPayloadCast` wird ausgelöst, wenn ein
konfigurierter modellweiter oder schlüsselbezogener Cast fehlt, einen ungültigen Typ besitzt, keinen
unterstützten Vertrag implementiert oder nicht durch Laravels Container aufgelöst werden kann. Seine
Meldung darf das übergeordnete Modell, den Einstellungsschlüssel und die Cast-Klasse nennen, aber nie
den Payload.

Schlägt eine gemischte `setMany()`-Operation fehl, setzt die Transaktion ihre Schreib- und
Löschvorgänge zurück. Die Exception wird erneut ausgelöst und die bestehende geladene Relation
`modelSettings` wird nicht gelöscht.

## Siehe auch

- [Mit Einstellungen arbeiten](settings.md) — das Verhalten jeder Operation verstehen.
- [Eager Loading](eager-loading.md) — `modelSettings` ohne N+1-Abfragen verwenden.
- [Payload-Casts](payload-casts.md) — die von `get()` und `all()` zurückgegebenen Werte steuern.
