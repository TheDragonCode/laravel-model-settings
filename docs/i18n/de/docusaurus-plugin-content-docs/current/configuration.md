---
sidebar_position: 5
title: Konfiguration
description: Speichermodell, Datenbankverbindung, Tabelle und Payload-Casts konfigurieren.
---

[← Eager Loading](eager-loading.md) · [Zurück zur README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Payload-Casts →](payload-casts.md)

# Konfiguration

## Konfiguration veröffentlichen

```bash
php artisan vendor:publish --tag="model_settings"
```

Dadurch werden `config/model_settings.php` und die Paketmigration veröffentlicht.

## Verfügbare Optionen

| Option | Standardwert | Zweck |
|--------|--------------|-------|
| `model` | `DragonCode\LaravelModelSettings\Models\Settings` | Eloquent-Modell für gespeicherte Einstellungen |
| `connection` | Anwendungsstandard | Datenbankverbindung für Modell und Migration |
| `table` | `settings` | Datenbanktabelle für Modell und Migration |
| `casts` | `[]` | Nach der Klasse des übergeordneten Modells und optional nach Einstellungsschlüssel ausgewählte Payload-Casts |

Das Paket liest folgende Umgebungsvariablen:

| Variable | Standardwert |
|----------|--------------|
| `MODEL_SETTINGS_DATABASE_CONNECTION` | `DATABASE_CONNECTION`, danach Laravels Standardverbindung |
| `MODEL_SETTINGS_DATABASE_TABLE` | `settings` |

Lege Verbindung und Tabelle vor dem Ausführen der Migration fest:

```dotenv
MODEL_SETTINGS_DATABASE_CONNECTION=mysql
MODEL_SETTINGS_DATABASE_TABLE=model_settings
```

Eine spätere Änderung verschiebt keine vorhandenen Datensätze.

## Payload-Cast-Konfiguration

Die bisherige modellweite Form wird weiterhin unterstützt. Ein Cast verarbeitet jeden Payload der
Modellklasse:

```php
'casts' => [
    App\Models\User::class => App\Casts\UserSettingsPayloadCast::class,
],
```

Verwende eine schlüsselbezogene Map, wenn unterschiedliche Schlüssel unterschiedliche Typen oder
Behandlungen benötigen:

```php
'casts' => [
    App\Models\User::class => [
        'profile' => App\Data\ProfileData::class,
        'billing.credentials' => App\Casts\EncryptedSettingPayload::class,
    ],
],
```

Schlüssel werden exakt abgeglichen. Punkte haben keine Bedeutung als verschachtelte Pfade. Ein in
der Map fehlender Schlüssel verwendet den standardmäßigen JSON-Cast. Jeder Modelleintrag ist entweder
ein modellweiter Klassenname oder eine schlüsselbezogene Map; innerhalb einer solchen Map gibt es
keinen Platzhaltereintrag. Unterstützte Cast-Verträge und ein Verschlüsselungsbeispiel stehen unter
[Payload-Casts](payload-casts.md).

## Speicherschema

Die veröffentlichte Migration erstellt folgende Spalten:

| Spalte | Zweck |
|--------|-------|
| `id` | Primärschlüssel der Einstellungszeile |
| `item_type` | Morph-Klasse oder Alias des übergeordneten Modells |
| `item_id` | ID des übergeordneten Modells als Zeichenfolge mit bis zu 36 Zeichen |
| `is_default` | Unterscheidet Klassenstandards von Modellüberschreibungen |
| `key` | Einstellungsschlüssel |
| `payload` | In der Migration als `jsonb` deklarierter Payload |
| `created_at` und `updated_at` | Laravel-Zeitstempel |

Die Kombination aus `item_type`, `item_id`, `is_default` und `key` ist eindeutig. Ein Suchindex auf
`item_type`, `is_default` und `item_id` unterstützt Lesevorgänge für Standard- und Besitzerbereiche.

Klassenstandards und Modellüberschreibungen verwenden dieselbe Tabelle. Das Paket erstellt weder eine
zweite Standardwerttabelle noch Spalten für Verschlüsselungsmetadaten.

Die Standardspalte `item_id` speichert höchstens 36 Zeichen. Ganzzahlige IDs, Zeichenfolgen, UUIDs
und ULIDs passen in dieses Schema, wenn ihre Zeichenfolgendarstellung höchstens 36 Zeichen lang ist.
Ein längerer benutzerdefinierter Primärschlüssel erfordert eine entsprechende Änderung der Migration.

Klassenstandards verwenden `item_id = '0'` mit `is_default = true`. Ein gespeicherter Besitzer mit
der Ganzzahl `0` oder der Zeichenfolge `'0'` als Schlüssel verwendet dieselbe physische `item_id` mit
`is_default = false`. Dadurch können beide Zeilen für denselben Modelltyp und Einstellungsschlüssel
nebeneinander bestehen. Werden Datenbankverbindung, Tabellenname oder Morph-Map-Aliase geändert,
nachdem Daten vorhanden sind, müssen die bestehenden Zeilen selbst verschoben oder aktualisiert
werden.

## Upgrade von einer früheren 1.x-Version

Veröffentliche nach dem Paket-Update die neue Migration und führe sie aus, während sich die Anwendung
im Wartungsmodus befindet:

```bash
php artisan vendor:publish --tag="model_settings"
php artisan migrate
```

Die Upgrade-Migration fügt `is_default` hinzu, klassifiziert jede bestehende Zeile mit
`item_id = '0'` als Klassenstandard, erstellt Diskriminator-basierte Indizes und entfernt danach den
alten eindeutigen Index. Sie schreibt keine Einstellungsschlüssel oder Payloads in die Ausgabe.

Frühere 1.x-Schemata kodierten Klassenstandards und echte Besitzerzeilen mit ID `0` identisch. Die
Migration kann daher eine manuell eingefügte Besitzerüberschreibung nicht von einem Standardwert
unterscheiden und klassifiziert beide als Standards. Prüfe nach der Migration bekannte Altdaten für
Besitzer-ID `0` und setze `is_default = false` für Zeilen, die tatsächliche Modellüberschreibungen
sind.

Führe die alte Paketversion nicht gegen das aktualisierte Schema aus. Sie schreibt den Diskriminator
nicht und würde Standards als Überschreibungen speichern. Stelle Migration und kompatible
Paketversion innerhalb desselben Wartungsfensters bereit.

Ein Rollback ist nur sicher, bevor eine echte Überschreibung für Besitzer-ID `0` existiert. Die
Migration stoppt vor einer Schemaänderung, wenn sie `item_id = '0'` mit `is_default = false` findet,
weil das alte Schema diese Zeile nicht ohne Bedeutungsänderung darstellen kann. Entferne oder
exportiere diese Überschreibungen vor dem Rollback. Ein sicherer Rollback stellt den alten
eindeutigen Index wieder her und entfernt `is_default`.

## Speichermodell ersetzen

Das integrierte Einstellungsmodell ist final. Konfiguriere einen Ersatz, statt es zu erweitern:

```php
namespace App\Models;

use DragonCode\LaravelModelSettings\Casts\PayloadCast;
use Illuminate\Database\Eloquent\Model;

final class ApplicationSetting extends Model
{
    protected $fillable = [
        'item_type',
        'item_id',
        'is_default',
        'key',
        'payload',
    ];

    public function __construct(array $attributes = [])
    {
        $this->setConnection(config('model_settings.connection'));
        $this->setTable(config('model_settings.table'));

        parent::__construct($attributes);
    }

    protected function casts(): array
    {
        return [
            'item_id'    => 'string',
            'is_default' => 'boolean',
            'payload'    => PayloadCast::class,
        ];
    }
}
```

Aktualisiere danach die Konfiguration:

```php
'model' => App\Models\ApplicationSetting::class,
```

Der Ersatz muss mit dem veröffentlichten Schema kompatibel bleiben. Behalte die ausfüllbaren
Attribute und `PayloadCast` bei, sofern das neue Modell keine gleichwertige Serialisierung implementiert.

Das Ersatzmodell muss mindestens folgende Verhaltensweisen erhalten:

| Anforderung | Grund |
|-------------|-------|
| `item_type`, `item_id`, `is_default`, `key` und `payload` befüllen | Der Speicher schreibt diese Attribute |
| Konfigurierte Verbindung und Tabelle verwenden | Migration und Repository müssen dieselben Zeilen ansprechen |
| `item_id` als `string` casten | Ganzzahlen, Zeichenfolgen, UUIDs und ULIDs teilen sich eine Spalte |
| `is_default` als `boolean` casten | Lazy und Eager Resolution müssen denselben Bereichsdiskriminator lesen |
| `payload` mit `PayloadCast` oder gleichwertig casten | Lesen und Schreiben müssen das JSON-Verhalten erhalten |

## Siehe auch

- [Erste Schritte](getting-started.md) — Konfiguration und Migration veröffentlichen.
- [Payload-Casts](payload-casts.md) — anwendungsspezifische Payload-Typen konfigurieren.
- [API-Referenz](api-reference.md) — die öffentliche Oberfläche des Pakets ansehen.
