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
| `casts` | `[]` | Conversions sélectionnées selon la classe du modèle parent et, facultativement, la clé |

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

## Configuration des conversions

L’ancienne forme à l’échelle du modèle reste prise en charge. Une conversion traite toutes les
données appartenant à la classe du modèle :

```php
'casts' => [
    App\Models\User::class => App\Casts\UserSettingsPayloadCast::class,
],
```

Utilisez une table indexée par clé lorsque différentes clés nécessitent des types ou traitements
distincts :

```php
'casts' => [
    App\Models\User::class => [
        'profile' => App\Data\ProfileData::class,
        'billing.credentials' => App\Casts\EncryptedSettingPayload::class,
    ],
],
```

La correspondance des clés est exacte. Les points ne représentent aucun chemin imbriqué et une clé
absente de la table utilise la conversion JSON par défaut. Chaque entrée de modèle est soit une
chaîne de classe pour tout le modèle, soit une table indexée par clé ; aucun caractère générique
n’existe dans cette table. Consultez [Conversions des données](payload-casts.md) pour les contrats
pris en charge et une recette de chiffrement.

## Schéma de stockage

La migration publiée crée les colonnes suivantes :

| Colonne | Rôle |
|---------|------|
| `id` | Clé primaire de la ligne de paramètre |
| `item_type` | Classe morph ou alias du modèle parent |
| `item_id` | Identifiant du parent, stocké sous forme de chaîne de 36 caractères maximum |
| `is_default` | Distingue les valeurs par défaut de classe des surcharges de modèles |
| `key` | Clé du paramètre |
| `payload` | Données déclarées comme `jsonb` par la migration |
| `created_at` et `updated_at` | Horodatages Laravel |

La combinaison de `item_type`, `item_id`, `is_default` et `key` est unique. Un index de recherche sur
`item_type`, `is_default` et `item_id` prend en charge les lectures des valeurs par défaut et de la
portée du propriétaire.

Les valeurs par défaut de classe et les surcharges de modèles partagent cette table. Le paquet ne
crée aucune seconde table de valeurs par défaut et n’ajoute aucune colonne de métadonnées de
chiffrement.

La colonne `item_id` par défaut stocke au maximum 36 caractères. Les identifiants entiers, chaînes,
UUID et ULID tiennent dans ce schéma lorsque leur représentation sous forme de chaîne ne dépasse pas
36 caractères. Une clé primaire personnalisée plus longue nécessite une modification correspondante
de la migration.

Les valeurs par défaut de la classe utilisent `item_id = '0'` avec `is_default = true`. Un
propriétaire enregistré dont la clé est l’entier `0` ou la chaîne `'0'` utilise le même `item_id`
physique avec `is_default = false`. Les deux lignes peuvent donc coexister pour le même type de modèle
et la même clé de paramètre. Si des données existent déjà, la modification de la connexion, du nom de
table ou des alias de morph map nécessite de déplacer ou de mettre à jour vous-même les lignes
existantes.

## Mise à niveau depuis une version 1.x antérieure

Après la mise à jour du paquet, publiez sa nouvelle migration et exécutez-la pendant que
l’application est en mode maintenance :

```bash
php artisan vendor:publish --tag="model_settings"
php artisan migrate
```

La migration ajoute `is_default`, classe chaque ancienne ligne avec `item_id = '0'` comme valeur par
défaut de classe, crée les index tenant compte du discriminateur, puis supprime l’ancien index unique.
Elle n’écrit jamais les clés ni les données des paramètres dans sa sortie.

Les anciens schémas 1.x encodaient de la même façon les valeurs par défaut de classe et les lignes
d’un véritable propriétaire d’identifiant `0`. La migration ne peut donc pas distinguer une surcharge
de propriétaire insérée manuellement d’une valeur par défaut et classe les deux comme valeurs par
défaut. Après la migration, vérifiez les anciennes données connues des propriétaires d’identifiant `0`
et définissez `is_default = false` pour les lignes qui sont réellement des surcharges de modèle.

N’exécutez pas l’ancienne version du paquet avec le schéma mis à niveau. Elle n’écrit pas le
discriminateur et enregistrerait les valeurs par défaut comme des surcharges. Déployez la migration et
la version compatible du paquet dans une même fenêtre de maintenance.

Le retour arrière n’est sûr qu’avant la création d’une véritable surcharge de propriétaire
d’identifiant `0`. La migration s’arrête avant de modifier le schéma si elle trouve
`item_id = '0'` avec `is_default = false`, car l’ancien schéma ne peut pas représenter cette ligne sans
en changer le sens. Supprimez ou exportez ces surcharges avant le retour arrière. Un retour arrière
sûr restaure l’ancien index unique et supprime `is_default`.

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

Mettez ensuite la configuration à jour :

```php
'model' => App\Models\ApplicationSetting::class,
```

Le remplacement doit rester compatible avec le schéma publié. Conservez les attributs remplissables
et `PayloadCast`, sauf si le nouveau modèle implémente une sérialisation équivalente.

Le modèle de remplacement doit au minimum conserver les comportements suivants :

| Exigence | Raison |
|----------|--------|
| Remplir `item_type`, `item_id`, `is_default`, `key` et `payload` | Le stockage écrit ces attributs |
| Utiliser la connexion et la table configurées | La migration et le dépôt doivent accéder aux mêmes lignes |
| Convertir `item_id` en `string` | Les entiers, chaînes, UUID et ULID partagent une colonne |
| Convertir `is_default` en `boolean` | Les résolutions différée et anticipée doivent lire le même discriminateur de portée |
| Convertir `payload` avec `PayloadCast` ou un équivalent | Les lectures et écritures doivent conserver le comportement JSON |

## Voir aussi

- [Premiers pas](getting-started.md) — publier la configuration et la migration.
- [Conversions des données](payload-casts.md) — configurer des types de données propres à l’application.
- [Référence de l’API](api-reference.md) — consulter l’interface publique du paquet.
