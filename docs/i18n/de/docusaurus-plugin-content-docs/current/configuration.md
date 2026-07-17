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
| `casts` | `[]` | Nach der Klasse des übergeordneten Modells ausgewählter Payload-Cast |

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

## Speicherschema

Die veröffentlichte Migration erstellt folgende Spalten:

| Spalte | Zweck |
|--------|-------|
| `id` | Primärschlüssel der Einstellungszeile |
| `item_type` | Morph-Klasse oder Alias des übergeordneten Modells |
| `item_id` | ID des übergeordneten Modells als Zeichenfolge mit bis zu 36 Zeichen |
| `key` | Einstellungsschlüssel |
| `payload` | In der Migration als `jsonb` deklarierter Payload |
| `created_at` und `updated_at` | Laravel-Zeitstempel |

Die Kombination aus `item_type`, `item_id` und `key` ist eindeutig.

Die Standardspalte `item_id` speichert höchstens 36 Zeichen. Ganzzahlige IDs, UUIDs und ULIDs passen
in dieses Schema. Ein längerer benutzerdefinierter Primärschlüssel erfordert eine entsprechende
Änderung der Migration.

Der Wert `0` ist in `item_id` für Klassenstandards reserviert. Werden Datenbankverbindung,
Tabellenname oder Morph-Map-Aliase geändert, nachdem Daten vorhanden sind, müssen die bestehenden
Zeilen selbst verschoben oder aktualisiert werden.

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
            'item_id' => 'string',
            'payload' => PayloadCast::class,
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
| `item_type`, `item_id`, `key` und `payload` befüllen | `updateOrCreate()` schreibt diese Attribute |
| Konfigurierte Verbindung und Tabelle verwenden | Migration und Repository müssen dieselben Zeilen ansprechen |
| `item_id` als `string` casten | Ganzzahlen, UUIDs und ULIDs teilen sich eine Spalte |
| `payload` mit `PayloadCast` oder gleichwertig casten | Lesen und Schreiben müssen das JSON-Verhalten erhalten |

## Siehe auch

- [Erste Schritte](getting-started.md) — Konfiguration und Migration veröffentlichen.
- [Payload-Casts](payload-casts.md) — anwendungsspezifische Payload-Typen konfigurieren.
- [API-Referenz](api-reference.md) — die öffentliche Oberfläche des Pakets ansehen.
