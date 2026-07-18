---
sidebar_position: 3
title: Mit Einstellungen arbeiten
description: Gemeinsame Standardwerte, modellspezifische Überschreibungen, Schlüssel und Werte verwalten.
---

[← Erste Schritte](getting-started.md) · [Zurück zur README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Eager Loading →](eager-loading.md)

# Mit Einstellungen arbeiten

Der gleiche Service verwaltet Standardwerte und Modellwerte. Der Einstiegspunkt bestimmt, welcher
Bereich gelesen oder geändert wird:

| Einstiegspunkt | Bereich |
|----------------|---------|
| `(new User)->defaultSettings()` | Gemeinsame Standardwerte für gespeicherte `User`-Modelle |
| `$user->settings()` | Effektive Einstellungen eines gespeicherten Benutzers |

## Gemeinsame Standardwerte

Standardwerte gelten für alle gespeicherten Modelle mit derselben Eloquent-Morph-Klasse:

```php
$defaults = (new User)->defaultSettings();

$defaults->set('timezone', 'UTC');
$defaults->set('notifications', ['email' => true]);
```

Standardwerte werden über denselben Service gelesen oder entfernt:

```php
$timezone = $defaults->get('timezone');
$all = $defaults->all();

$defaults->forget('timezone');
```

Standardwerte sind für jede Modellklasse unabhängig.

## Modellspezifische Überschreibungen

`set()` erstellt eine Einstellung oder ersetzt ihren bestehenden Wert:

```php
$user->settings()->set('timezone', 'Europe/Paris');
$user->settings()->set('timezone', 'America/Toronto');
```

Nur die Einstellung dieses Modells wird geändert. Andere Modelle verwenden weiterhin ihre eigene
Überschreibung oder den gemeinsamen Standardwert.

`get()` und `all()` lösen Werte mit derselben Priorität auf:

```php
$timezone = $user->settings()->get('timezone');
$settings = $user->settings()->all();
```

`all()` gibt eine `Illuminate\Support\Collection` zurück, die nach Einstellungsschlüsseln indiziert ist.

`get()` akzeptiert nur den Schlüssel. Die Methode gibt zuerst die Modellüberschreibung, dann den
persistierten Klassenstandard und anschließend `null` zurück. Sie akzeptiert keinen vom Aufrufer
angegebenen Ersatzwert. Verwende die Collection, um einen fehlenden effektiven Schlüssel von einem
gespeicherten Wert zu unterscheiden:

```php
$settings = $user->settings()->all();

if ($settings->has('timezone')) {
    $timezone = $settings->get('timezone');
}
```

Eine Überschreibung ersetzt beispielsweise nur den passenden Standardwert:

```php
(new User)->defaultSettings()->set('timezone', 'UTC');
(new User)->defaultSettings()->set('locale', 'en');

$user->settings()->set('timezone', 'Europe/Paris');

assert($user->settings()->all()->sortKeys()->all() === [
    'locale' => 'en',
    'timezone' => 'Europe/Paris',
]);
```

## Wert entfernen

Das Entfernen einer Modellüberschreibung macht den Standardwert wieder sichtbar:

```php
(new User)->defaultSettings()->set('timezone', 'UTC');

$user->settings()->set('timezone', 'Europe/Paris');
$user->settings()->forget('timezone');

assert($user->settings()->get('timezone') === 'UTC');
```

Rufe `forget()` über `defaultSettings()` auf, um den Standardwert selbst zu entfernen:

```php
(new User)->defaultSettings()->forget('timezone');
```

Der Aufruf von `forget()` für einen fehlenden Schlüssel hat keine Auswirkung.

## Gebündelte Änderungen

Verwende `setMany()` und `forgetMany()`, wenn in einem Bereich mehrere Änderungen nötig sind:

```php
$user->settings()->setMany([
    'timezone' => 'Europe/Paris',
    'locale' => 'fr',
]);

$user->settings()->forgetMany(['timezone', 'locale']);
```

Beide Methoden akzeptieren jedes Iterable. `setMany()` normalisiert jeden Schlüssel vor dem
Schreiben. Werden mehrere Eingabeschlüssel auf denselben gespeicherten Schlüssel normalisiert,
gewinnt der letzte Wert. Leere Werte löschen diesen Schlüssel nach derselben Regel wie `set()` aus
dem aktuellen Bereich.

Ein gemischter `setMany()`-Batch verwendet ein Upsert und einen Löschvorgang innerhalb einer
Transaktion. `forgetMany()` verwendet einen Löschvorgang für alle angegebenen Schlüssel. Die Anzahl
der Abfragen hängt von den Operationstypen im Batch und nicht von der Anzahl der Schlüssel ab.

Verwende `purge()`, um den gesamten aktuellen Bereich zu entfernen:

```php
$user->settings()->purge();
```

Bei `settings()` löscht `purge()` nur die Überschreibungen dieses Besitzers und macht persistierte
Standardwerte wieder sichtbar. Bei `defaultSettings()` löscht die Methode die Standardwerte dieser
Modellklasse, ohne Modellüberschreibungen zu löschen. Alle drei gebündelten Methoden geben `void`
zurück.

## Leere Werte

`set()` und `setMany()` verwenden Laravels `blank()`-Helper. Ein leerer Wert löscht die Einstellung,
statt sie zu speichern.

| Wert | Ergebnis |
|------|----------|
| `null` | Entfernt |
| `''` oder eine Zeichenfolge nur aus Leerzeichen | Entfernt |
| `[]` | Entfernt |
| `0` | Gespeichert |
| `false` | Gespeichert |
| `'0'` | Gespeichert |

Das Paket kann mit keiner der beiden Methoden einen absichtlich leeren Wert speichern.

## Einstellungsschlüssel

Schlüssel können Zeichenfolgen, Ganzzahlen oder PHP-Enums sein, die `UnitEnum` implementieren:

```php
enum SettingKey: string
{
    case Timezone = 'timezone';
}

$user->settings()->set(SettingKey::Timezone, 'Europe/Paris');

$timezone = $user->settings()->get(SettingKey::Timezone);
```

Laravel speichert ein Backed Enum mit seinem zugrunde liegenden Wert und ein Pure Unit Enum mit
seinem Case-Namen. Verwende beim Lesen, Ersetzen oder Entfernen denselben Schlüssel oder Enum-Case.

Das Paket validiert den Inhalt eines Schlüssels nicht. Die öffentliche API und das Standardschema
akzeptieren leere Schlüssel und Schlüssel, die nur aus Leerzeichen bestehen.

Punkte sind literale Zeichen. Der Schlüssel `mail.from.address` ist ein einzelner undurchsichtiger
Einstellungsschlüssel und bezeichnet nie einen verschachtelten Pfad:

```php
$user->settings()->set('mail.from.address', 'noreply@example.com');

$address = $user->settings()->get('mail.from.address');
```

## Modell-IDs

Ganzzahlige, Zeichenfolgen-, UUID- und ULID-Primärschlüssel werden unterstützt.

Modellspezifische Änderungen benötigen einen gespeicherten Besitzer mit einem Schlüssel ungleich
`null`. Für ein ungespeichertes Modell gibt `get()` `null` und `all()` eine leere Collection zurück,
ohne Modellüberschreibungen abzufragen. Seine Methoden `set()`, `setMany()`, `forget()`,
`forgetMany()` und `purge()` lösen vor einer Speicherabfrage oder dem Durchlaufen des Iterables eine
`InvalidSettingsOwnerException` aus.

Gespeicherte Modelle mit der Ganzzahl-ID `0` oder der Zeichenfolge `'0'` unterstützen dieselben Lese-
und Änderungsvorgänge wie jeder andere gespeicherte Besitzer. Der Bereichsdiskriminator trennt ihre
Überschreibungen von Klassenstandards, obwohl beide Zeilen `item_id = '0'` behalten. Andere
Zeichenfolgenschlüssel, einschließlich `'00'`, bleiben gültig.

Einstellungen werden unter der aktuellen Morph-Klasse des Modells gespeichert. Wird ein Morph-Map-
Alias nach dem Schreiben von Einstellungen eingeführt oder geändert, müssen vorhandene `item_type`-
Werte aktualisiert werden.

## Siehe auch

- [Eager Loading](eager-loading.md) — eine Einstellungsabfrage pro Modell vermeiden.
- [Payload-Casts](payload-casts.md) — Domänenobjekte statt dekodiertem JSON zurückgeben.
- [API-Referenz](api-reference.md) — Methodensignaturen und Rückgabewerte prüfen.
