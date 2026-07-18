---
sidebar_position: 6
title: Payload-Casts
description: Einstellungs-Payloads als Arrays, benutzerdefinierte Werte oder Spatie-Laravel-Data-Objekte dekodieren.
---

[← Konfiguration](configuration.md) · [Zurück zur README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [API-Referenz →](api-reference.md)

# Payload-Casts

## Standardmäßige JSON-Werte

Ohne benutzerdefinierten Cast codiert das Paket jeden Wert beim Schreiben als JSON und gibt beim
Lesen den exakt dekodierten JSON-Wert zurück. Dazu gehören `null`, leere Zeichenfolgen,
Leerzeichenfolgen, leere Arrays, die Zahl null und `false`.

```php
$user->settings()->set('notifications', [
    'email' => true,
    'push' => false,
]);

$notifications = $user->settings()->get('notifications');
```

Werte müssen als JSON serialisierbar sein. Fehler bei der JSON-Codierung werden nicht unterdrückt.

## Cast-Auswahl

Die bisherige modellweite Form wendet einen Cast auf jede Einstellung einer übergeordneten
Modellklasse an:

```php
'casts' => [
    App\Models\User::class => App\Casts\UserSettingsPayloadCast::class,
],
```

Verwende eine schlüsselbezogene Map, wenn nur exakte Einstellungsschlüssel eine benutzerdefinierte
Behandlung benötigen:

```php
'casts' => [
    App\Models\User::class => [
        'profile' => App\Data\ProfileData::class,
        'billing.credentials' => App\Casts\EncryptedSettingPayload::class,
    ],
],
```

Laravel-Morph-Map-Aliase werden vor der Auswahl zur übergeordneten Modellklasse zurückaufgelöst. Der
Schlüsselabgleich verwendet den gespeicherten Einstellungsschlüssel und nicht den Namen des
Eloquent-Attributs `payload`. Punkte sind literal, deshalb ist `billing.credentials` ein einzelner
Schlüssel. In einer schlüsselbezogenen Map fehlende Schlüssel verwenden normales JSON.

Eine konfigurierte Klasse muss `CastsAttributes` implementieren oder `Spatie\LaravelData\Data`
erweitern. Ungültige, fehlende, nicht unterstützte oder nicht durch den Container auflösbare Klassen
lösen `InvalidPayloadCast` aus. Das Paket greift für einen konfigurierten Eintrag nicht stillschweigend
auf einfaches JSON zurück.

## Cast-Lebenszyklus

Für eine `CastsAttributes`-Implementierung führt das Paket folgende Reihenfolge aus:

| Richtung | Reihenfolge |
|----------|-------------|
| Schreiben | Benutzerdefiniertes `set()` aufrufen und das Ergebnis als JSON codieren |
| Lesen | Gespeicherte JSON-Zeichenfolge an das benutzerdefinierte `get()` übergeben |

Das Argument `$model` ist das konfigurierte Speichermodell und nicht das übergeordnete Modell `User`
oder `Post`. Das Paket löst `CastsAttributes`-Implementierungen über Laravels Container auf.
Abhängigkeiten im Konstruktor können deshalb normale Container-Bindings verwenden. Benutzerdefinierte
`set()`-Casts erhalten jeden Eingabewert, auch Werte, die Laravel als leer betrachtet.

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

## Verschlüsselung pro Schlüssel

Verschlüsselung gehört in einen anwendungsspezifischen Cast, da das Paketschema keine
Verschlüsselungsmetadaten und keinen Vertrag zur Schlüsselrotation besitzt. Dieser Cast verschlüsselt
einen Einstellungsschlüssel, während alle anderen Schlüssel den normalen JSON-Pfad verwenden:

```php
namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

final class EncryptedSettingPayload implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $ciphertext = Json::decode($value);

        return Json::decode(Crypt::decryptString((string) $ciphertext));
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return Crypt::encryptString(Json::encode($value));
    }
}
```

Registriere ihn für einen exakten literalen Schlüssel:

```php
'casts' => [
    App\Models\User::class => [
        'billing.credentials' => App\Casts\EncryptedSettingPayload::class,
    ],
],
```

```php
$user->settings()->set('billing.credentials', $credentials);

$credentials = $user->settings()->get('billing.credentials');
```

Protokolliere den Wert weder vor noch nach dem Cast. Wenn sich Verschlüsselungsschlüssel ändern
können, definiere und teste vor dem Speichern von Produktionsdaten eine Rotationsrichtlinie auf
Anwendungsebene. Füge der Pakettabelle keine Metadatenspalten hinzu, solange kein eigener
Speichervertrag Versionierung und Rotation definiert.

## Spatie Laravel Data

Wenn `spatie/laravel-data` installiert ist, kann eine `Data`-Klasse direkt verwendet werden:

```bash
composer require spatie/laravel-data:^4.23
```

```php
'casts' => [
    App\Models\User::class => [
        'preferences' => App\Data\UserSettingsData::class,
    ],
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

Andere Schlüssel desselben Modells verwenden weiterhin normales JSON. Verwende die bisherige
modellweite Form nur, wenn jeder Payload dieses übergeordneten Modells eine gültige Eingabe für die
konfigurierte Datenklasse ist.

## Cast-Fehler

`DragonCode\LaravelModelSettings\Exceptions\InvalidPayloadCast` nennt bei einem Fehler die
übergeordnete Modellklasse, den Einstellungsschlüssel und den konfigurierten Cast. Der Payload wird
nie einbezogen. Die Exception wird bei einzelnen und gebündelten Schreibvorgängen sowie beim Lesen
persistierter Werte über diesen konfigurierten Eintrag ausgelöst.

## Siehe auch

- [Konfiguration](configuration.md) — Casts registrieren und das Speichermodell ersetzen.
- [Mit Einstellungen arbeiten](settings.md) — sehen, wie exakte JSON-Werte gespeichert und entfernt werden.
- [API-Referenz](api-reference.md) — Rückgabetypen von `get()` und `all()` prüfen.
