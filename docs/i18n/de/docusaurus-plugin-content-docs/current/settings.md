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

## Leere Werte

`set()` verwendet Laravels `blank()`-Helper. Ein leerer Wert löscht die Einstellung, statt sie zu speichern.

| Wert | Ergebnis |
|------|----------|
| `null` | Entfernt |
| `''` oder eine Zeichenfolge nur aus Leerzeichen | Entfernt |
| `[]` | Entfernt |
| `0` | Gespeichert |
| `false` | Gespeichert |
| `'0'` | Gespeichert |

Das Paket kann mit `set()` keinen absichtlich leeren Wert speichern.

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

## Modell-IDs

Ganzzahlige, Zeichenfolgen-, UUID- und ULID-Primärschlüssel werden unterstützt.

Modellspezifische Änderungen benötigen einen gespeicherten Besitzer mit einem Schlüssel ungleich
`null`. Für ein ungespeichertes Modell gibt `get()` `null` und `all()` eine leere Collection zurück,
ohne Modellüberschreibungen abzufragen. Seine Methoden `set()` und `forget()` lösen vor einer
Speicherabfrage eine `InvalidSettingsOwnerException` aus.

Die Ganzzahl `0` und die Zeichenfolge `'0'` sind in 1.x für gemeinsame Standardwerte reserviert. Ein
gespeichertes Modell mit einem dieser Schlüssel kann Klassenstandards lesen, aber `set()` und
`forget()` lösen eine `InvalidSettingsOwnerException` aus. Andere Zeichenfolgenschlüssel,
einschließlich `'00'`, bleiben gültig.

Einstellungen werden unter der aktuellen Morph-Klasse des Modells gespeichert. Wird ein Morph-Map-
Alias nach dem Schreiben von Einstellungen eingeführt oder geändert, müssen vorhandene `item_type`-
Werte aktualisiert werden.

## Siehe auch

- [Eager Loading](eager-loading.md) — eine Einstellungsabfrage pro Modell vermeiden.
- [Payload-Casts](payload-casts.md) — Domänenobjekte statt dekodiertem JSON zurückgeben.
- [API-Referenz](api-reference.md) — Methodensignaturen und Rückgabewerte prüfen.
