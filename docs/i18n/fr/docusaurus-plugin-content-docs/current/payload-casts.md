---
sidebar_position: 6
title: Conversions des données
description: Décoder les données des paramètres en tableaux, valeurs personnalisées ou objets Spatie Laravel Data.
---

[← Configuration](configuration.md) · [Retour au README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Référence de l’API →](api-reference.md)

# Conversions des données

## Valeurs JSON par défaut

Sans conversion personnalisée, le paquet encode chaque valeur en JSON lors de l’écriture et renvoie
exactement la valeur JSON décodée lors de la lecture. Cela inclut `null`, les chaînes vides ou
composées d’espaces, les tableaux vides, zéro et `false`.

```php
$user->settings()->set('notifications', [
    'email' => true,
    'push' => false,
]);

$notifications = $user->settings()->get('notifications');
```

Les valeurs doivent pouvoir être sérialisées en JSON. Les erreurs d’encodage JSON ne sont pas
masquées.

## Sélection de la conversion

L’ancienne forme à l’échelle du modèle applique une conversion à tous les paramètres d’une classe de
modèle parent :

```php
'casts' => [
    App\Models\User::class => App\Casts\UserSettingsPayloadCast::class,
],
```

Utilisez une table indexée par clé lorsque seules des clés précises nécessitent un traitement
personnalisé :

```php
'casts' => [
    App\Models\User::class => [
        'profile' => App\Data\ProfileData::class,
        'billing.credentials' => App\Casts\EncryptedSettingPayload::class,
    ],
],
```

Les alias de la morph map Laravel sont résolus vers la classe du modèle parent avant la sélection.
La correspondance utilise la clé stockée du paramètre, et non le nom d’attribut Eloquent `payload`.
Les points sont littéraux : `billing.credentials` est une seule clé. Les clés absentes de la table
utilisent le JSON normal.

Une classe configurée doit implémenter `CastsAttributes` ou étendre `Spatie\LaravelData\Data`. Une
classe configurée invalide, absente, non prise en charge ou impossible à résoudre par le conteneur
lève `InvalidPayloadCast` ; le paquet ne revient pas silencieusement au JSON pour cette entrée.

## Cycle de vie de la conversion

Pour une implémentation de `CastsAttributes`, le paquet exécute la séquence suivante :

| Direction | Séquence |
|-----------|----------|
| Écriture | Appeler le `set()` personnalisé, puis encoder son résultat en JSON |
| Lecture | Transmettre la chaîne JSON stockée au `get()` personnalisé |

L’argument `$model` est le modèle de stockage configuré, et non le modèle parent `User` ou `Post`.
Le paquet résout les implémentations de `CastsAttributes` avec le conteneur Laravel ; leurs
dépendances de constructeur peuvent donc utiliser les liaisons normales du conteneur. Les
conversions `set()` personnalisées reçoivent chaque valeur d’entrée, y compris celles que Laravel
considère comme vides.

## Conversion d’attribut Eloquent

La conversion peut implémenter le contrat Laravel `CastsAttributes` :

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

Le résultat du `set()` personnalisé doit pouvoir être sérialisé en JSON. Les erreurs d’encodage JSON
ne sont pas masquées.

## Chiffrement par clé

Le chiffrement appartient à une conversion de l’application, car le schéma du paquet ne définit ni
métadonnées de chiffrement ni contrat de rotation des clés. Cette conversion chiffre une clé et
laisse toutes les autres utiliser le chemin JSON normal :

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

Enregistrez-la pour une clé littérale exacte :

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

Ne journalisez jamais la valeur avant ou après la conversion. Si les clés de chiffrement peuvent
changer, définissez et testez une politique de rotation propre à l’application avant de stocker des
données de production. N’ajoutez aucune colonne de métadonnées à la table du paquet sans contrat de
stockage distinct définissant le versionnement et la rotation.

## Spatie Laravel Data

Lorsque `spatie/laravel-data` est installé, une classe `Data` peut être utilisée directement :

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

Transmettez à `set()` des données acceptées par la classe ou une instance de `Data`. `get()` renvoie
une instance de données et `all()` une collection contenant des instances de données.

```php
$preferences = UserSettingsData::from([
    'timezone' => 'Europe/Paris',
    'notifications' => true,
]);

$user->settings()->set('preferences', $preferences);

$preferences = $user->settings()->get('preferences');
```

Les autres clés du même modèle continuent d’utiliser le JSON normal. N’utilisez l’ancienne forme à
l’échelle du modèle que si les données de chaque paramètre de ce propriétaire sont valides pour la
classe de données configurée.

## Erreurs de conversion

`DragonCode\LaravelModelSettings\Exceptions\InvalidPayloadCast` identifie la classe du modèle parent,
la clé du paramètre et la conversion configurée lorsque la résolution échoue. L’exception ne contient
jamais les données. Elle est levée pour les écritures unitaires et groupées ainsi que pour la lecture
d’une valeur persistante utilisant cette entrée configurée.

## Voir aussi

- [Configuration](configuration.md) — enregistrer les conversions et remplacer le modèle de stockage.
- [Utilisation des paramètres](settings.md) — voir comment les valeurs JSON exactes sont stockées et supprimées.
- [Référence de l’API](api-reference.md) — vérifier les types renvoyés par `get()` et `all()`.
