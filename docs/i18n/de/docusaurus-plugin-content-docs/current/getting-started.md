---
sidebar_position: 2
title: Erste Schritte
description: Laravel Model Settings installieren und den ersten Standardwert und die erste Überschreibung speichern.
---

[← Übersicht](index.md) · [Zurück zur README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Mit Einstellungen arbeiten →](settings.md)

# Erste Schritte

## Voraussetzungen

- PHP 8.3 oder neuer.
- Laravel 12 oder 13.

## Paket installieren

```bash
composer require dragon-code/laravel-model-settings
```

Laravel erkennt den Service Provider des Pakets automatisch.

Veröffentliche die Konfiguration und Migration und erstelle danach die Einstellungstabelle:

```bash
php artisan vendor:publish --tag="model_settings"
php artisan migrate
```

Das Tag `model_settings` veröffentlicht `config/model_settings.php` und die Paketmigration. Die
Standardmigration erstellt eine Tabelle `settings` über die Standard-Datenbankverbindung der
Anwendung.

## Trait hinzufügen

Füge `HasSettings` jedem Eloquent-Modell hinzu, das Einstellungen benötigt:

```php
namespace App\Models;

use DragonCode\LaravelModelSettings\Concerns\HasSettings;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasSettings;
}
```

Der Trait fügt folgende öffentliche Methoden hinzu:

| Element | Verwendung |
|---------|------------|
| `settings()` | Effektive Einstellungen eines gespeicherten Modells lesen oder ändern |
| `defaultSettings()` | Standardwerte der Modellklasse lesen oder ändern |
| `modelSettings()` | Eloquent-Relation für Eager Loading |

## Erste Einstellung speichern

Erstelle einen Standardwert für alle gespeicherten `User`-Modelle:

```php
(new User)->defaultSettings()->set('timezone', 'UTC');
```

Überschreibe diesen Wert für einen gespeicherten Benutzer:

```php
$user = User::query()->firstOrFail();

$user->settings()->set('timezone', 'Europe/Paris');

assert($user->settings()->get('timezone') === 'Europe/Paris');
```

Lies alle effektiven Einstellungen als Collection, die nach Einstellungsnamen indiziert ist:

```php
$settings = $user->settings()->all();

assert($settings->get('timezone') === 'Europe/Paris');
```

Entferne die Überschreibung, um wieder `UTC` zu verwenden:

```php
$user->settings()->forget('timezone');

assert($user->settings()->get('timezone') === 'UTC');
```

## Modelle zuerst speichern

Verwende `settings()->set()`, `setMany()`, `forget()`, `forgetMany()` und `purge()` erst, nachdem das
übergeordnete Modell gespeichert wurde. Für ein ungespeichertes Modell gibt `settings()->get()`
`null` und `settings()->all()` eine leere Collection zurück, selbst wenn Klassenstandards vorhanden
sind. Jede Änderungsmethode löst vor einer Speicherabfrage eine
`DragonCode\LaravelModelSettings\Exceptions\InvalidSettingsOwnerException` aus.

## Siehe auch

- [Mit Einstellungen arbeiten](settings.md) — Priorität, Löschen, Schlüssel und Werte verstehen.
- [Konfiguration](configuration.md) — Verbindung, Tabelle oder Speichermodell auswählen.
- [Eager Loading](eager-loading.md) — Einstellungen für Modell-Collections effizient laden.
