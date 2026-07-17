---
sidebar_position: 6
title: Conversions des données
description: Décoder les données des paramètres en tableaux, valeurs personnalisées ou objets Spatie Laravel Data.
---

[← Configuration](configuration.md) · [Retour au README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Référence de l’API →](api-reference.md)

# Conversions des données

## Valeurs JSON par défaut

Sans conversion personnalisée, le paquet encode en JSON les valeurs non vides lors de l’écriture et
renvoie des tableaux décodés ou des valeurs scalaires lors de la lecture.

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

Les conversions personnalisées sont configurées selon la classe du modèle parent :

```php
'casts' => [
    App\Models\User::class => App\Casts\UserSettingsPayloadCast::class,
],
```

Une conversion configurée gère les données de tous les paramètres appartenant à cette classe de
modèle parent. Les alias de la morph map Laravel sont résolus vers la classe du modèle avant la
sélection de la conversion.

Une classe configurée doit implémenter `CastsAttributes` ou étendre `Spatie\LaravelData\Data`. Les
autres classes ne reçoivent aucun traitement personnalisé et utilisent le chemin JSON par défaut.

## Cycle de vie de la conversion

Pour une implémentation de `CastsAttributes`, le paquet exécute la séquence suivante :

| Direction | Séquence |
|-----------|----------|
| Écriture | Appeler le `set()` personnalisé, puis encoder son résultat en JSON |
| Lecture | Transmettre la chaîne JSON stockée au `get()` personnalisé |

L’argument `$model` est le modèle de stockage configuré, et non le modèle parent `User` ou `Post`.
Le paquet instancie la conversion sans argument de constructeur.

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

## Spatie Laravel Data

Lorsque `spatie/laravel-data` est installé, une classe `Data` peut être utilisée directement :

```bash
composer require spatie/laravel-data:^4.23
```

```php
'casts' => [
    App\Models\User::class => App\Data\UserSettingsData::class,
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

La conversion est sélectionnée par classe de modèle parent et non par clé. Toutes les données des
paramètres de ce modèle doivent donc être des entrées valides pour la conversion configurée.

## Voir aussi

- [Configuration](configuration.md) — enregistrer les conversions et remplacer le modèle de stockage.
- [Utilisation des paramètres](settings.md) — voir quelles valeurs vides sont supprimées.
- [Référence de l’API](api-reference.md) — vérifier les types renvoyés par `get()` et `all()`.
