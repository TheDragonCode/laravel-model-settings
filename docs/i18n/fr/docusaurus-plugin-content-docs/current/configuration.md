---
sidebar_position: 5
title: Configuration
description: Configurer le modèle de stockage, la connexion à la base de données, la table et les conversions de données.
---

[← Chargement anticipé](eager-loading.md) · [Retour au README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Conversions des données →](payload-casts.md)

# Configuration

## Publier la configuration

```bash
php artisan vendor:publish --tag="model_settings"
```

Cette commande publie `config/model_settings.php` et la migration du paquet.

## Options disponibles

| Option | Valeur par défaut | Rôle |
|--------|-------------------|------|
| `model` | `DragonCode\LaravelModelSettings\Models\Settings` | Modèle Eloquent utilisé pour les paramètres stockés |
| `connection` | Valeur par défaut de l’application | Connexion utilisée par le modèle et la migration |
| `table` | `settings` | Table utilisée par le modèle et la migration |
| `casts` | `[]` | Conversion des données sélectionnée selon la classe du modèle parent |

Le paquet lit les variables d’environnement suivantes :

| Variable | Valeur par défaut |
|----------|-------------------|
| `MODEL_SETTINGS_DATABASE_CONNECTION` | `DATABASE_CONNECTION`, puis la connexion par défaut de Laravel |
| `MODEL_SETTINGS_DATABASE_TABLE` | `settings` |

Définissez la connexion et la table avant d’exécuter la migration :

```dotenv
MODEL_SETTINGS_DATABASE_CONNECTION=mysql
MODEL_SETTINGS_DATABASE_TABLE=model_settings
```

La modification ultérieure de l’une de ces valeurs ne déplace pas les enregistrements existants.

## Schéma de stockage

La migration publiée crée les colonnes suivantes :

| Colonne | Rôle |
|---------|------|
| `id` | Clé primaire de la ligne de paramètre |
| `item_type` | Classe morph ou alias du modèle parent |
| `item_id` | Identifiant du parent, stocké sous forme de chaîne de 36 caractères maximum |
| `key` | Clé du paramètre |
| `payload` | Données déclarées comme `jsonb` par la migration |
| `created_at` et `updated_at` | Horodatages Laravel |

La combinaison de `item_type`, `item_id` et `key` est unique.

La colonne `item_id` par défaut stocke au maximum 36 caractères. Les identifiants entiers, chaînes,
UUID et ULID tiennent dans ce schéma lorsque leur représentation sous forme de chaîne ne dépasse pas
36 caractères. Une clé primaire personnalisée plus longue nécessite une modification correspondante
de la migration.

La valeur `0` est réservée dans `item_id` aux valeurs par défaut de la classe. Dans la version 1.x,
`set()` et `forget()` refusent un propriétaire enregistré dont la clé est l’entier `0` ou la chaîne
`'0'` en levant `InvalidSettingsOwnerException` avant d’interroger cette table. Si des données
existent déjà, la modification de la connexion, du nom de table ou des alias de morph map nécessite
de déplacer ou de mettre à jour vous-même les lignes existantes.

## Remplacer le modèle de stockage

Le modèle de paramètres intégré est final. Configurez un remplacement au lieu d’en hériter :

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

Mettez ensuite la configuration à jour :

```php
'model' => App\Models\ApplicationSetting::class,
```

Le remplacement doit rester compatible avec le schéma publié. Conservez les attributs remplissables
et `PayloadCast`, sauf si le nouveau modèle implémente une sérialisation équivalente.

Le modèle de remplacement doit au minimum conserver les comportements suivants :

| Exigence | Raison |
|----------|--------|
| Remplir `item_type`, `item_id`, `key` et `payload` | `updateOrCreate()` écrit ces attributs |
| Utiliser la connexion et la table configurées | La migration et le dépôt doivent accéder aux mêmes lignes |
| Convertir `item_id` en `string` | Les entiers, chaînes, UUID et ULID partagent une colonne |
| Convertir `payload` avec `PayloadCast` ou un équivalent | Les lectures et écritures doivent conserver le comportement JSON |

## Voir aussi

- [Premiers pas](getting-started.md) — publier la configuration et la migration.
- [Conversions des données](payload-casts.md) — configurer des types de données propres à l’application.
- [Référence de l’API](api-reference.md) — consulter l’interface publique du paquet.
