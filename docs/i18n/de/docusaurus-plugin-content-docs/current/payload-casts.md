---
sidebar_position: 6
title: Payload-Casts
description: Einstellungs-Payloads als Arrays, benutzerdefinierte Werte oder Spatie-Laravel-Data-Objekte dekodieren.
---

[← Konfiguration](configuration.md) · [Zurück zur README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [API-Referenz →](api-reference.md)

# Payload-Casts

## Standardmäßige JSON-Werte

Ohne benutzerdefinierten Cast codiert das Paket nicht leere Werte beim Schreiben als JSON und gibt
beim Lesen dekodierte Arrays oder skalare Werte zurück.

```php
$user->settings()->set('notifications', [
    'email' => true,
    'push' => false,
]);

$notifications = $user->settings()->get('notifications');
```

Werte müssen als JSON serialisierbar sein. Fehler bei der JSON-Codierung werden nicht unterdrückt.

## Cast-Auswahl

Benutzerdefinierte Casts werden nach der Klasse des übergeordneten Modells konfiguriert:

```php
'casts' => [
    App\Models\User::class => App\Casts\UserSettingsPayloadCast::class,
],
```

Ein konfigurierter Cast verarbeitet jeden Einstellungs-Payload dieser übergeordneten Modellklasse.
Laravel-Morph-Map-Aliase werden vor der Cast-Auswahl zur Modellklasse zurückaufgelöst.

Eine konfigurierte Klasse muss `CastsAttributes` implementieren oder `Spatie\LaravelData\Data`
erweitern. Andere Klassen erhalten keine Sonderbehandlung und verwenden den Standard-JSON-Pfad.

## Cast-Lebenszyklus

Für eine `CastsAttributes`-Implementierung führt das Paket folgende Reihenfolge aus:

| Richtung | Reihenfolge |
|----------|-------------|
| Schreiben | Benutzerdefiniertes `set()` aufrufen und das Ergebnis als JSON codieren |
| Lesen | Gespeicherte JSON-Zeichenfolge an das benutzerdefinierte `get()` übergeben |

Das Argument `$model` ist das konfigurierte Speichermodell und nicht das übergeordnete Modell `User`
oder `Post`. Das Paket instanziiert den Cast ohne Konstruktorargumente.

## Eloquent-Attribut-Cast

Der Cast kann Laravels `CastsAttributes`-Vertrag implementieren:

```php
namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

final class UserSettingsPayloadCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        return (array) $value;
    }
}
```

Das Ergebnis des benutzerdefinierten `set()` muss als JSON serialisierbar bleiben. Fehler bei der
JSON-Codierung werden nicht unterdrückt.

## Spatie Laravel Data

Wenn `spatie/laravel-data` installiert ist, kann eine `Data`-Klasse direkt verwendet werden:

```bash
composer require spatie/laravel-data:^4.23
```

```php
'casts' => [
    App\Models\User::class => App\Data\UserSettingsData::class,
],
```

Übergib an `set()` entweder von der Klasse akzeptierte Daten oder eine `Data`-Instanz. `get()` gibt
eine Dateninstanz zurück und `all()` eine Collection mit Dateninstanzen.

```php
$preferences = UserSettingsData::from([
    'timezone' => 'Europe/Paris',
    'notifications' => true,
]);

$user->settings()->set('preferences', $preferences);

$preferences = $user->settings()->get('preferences');
```

Ein Cast wird pro übergeordneter Modellklasse und nicht pro Schlüssel ausgewählt. Jeder Payload
dieses Modells muss daher eine gültige Eingabe für den konfigurierten Cast sein.

## Siehe auch

- [Konfiguration](configuration.md) — Casts registrieren und das Speichermodell ersetzen.
- [Mit Einstellungen arbeiten](settings.md) — sehen, welche leeren Werte entfernt werden.
- [API-Referenz](api-reference.md) — Rückgabetypen von `get()` und `all()` prüfen.
