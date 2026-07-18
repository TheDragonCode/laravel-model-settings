---
sidebar_position: 2
title: Premiers pas
description: Installer Laravel Model Settings et enregistrer la première valeur par défaut et la première surcharge.
---

[← Présentation](index.md) · [Retour au README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Utilisation des paramètres →](settings.md)

# Premiers pas

## Prérequis

- PHP 8.3 ou version ultérieure.
- Laravel 12 ou 13.

## Installer le paquet

```bash
composer require dragon-code/laravel-model-settings
```

Laravel détecte automatiquement le fournisseur de services du paquet.

Publiez la configuration et la migration, puis créez la table des paramètres :

```bash
php artisan vendor:publish --tag="model_settings"
php artisan migrate
```

Le tag `model_settings` publie `config/model_settings.php` et la migration du paquet. Par défaut, la
migration crée une table `settings` sur la connexion de base de données par défaut de l’application.

## Ajouter le trait

Ajoutez `HasSettings` à chaque modèle Eloquent qui nécessite des paramètres :

```php
namespace App\Models;

use DragonCode\LaravelModelSettings\Concerns\HasSettings;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasSettings;
}
```

Le trait ajoute les méthodes publiques suivantes :

| Méthode | Utilisation |
|---------|-------------|
| `settings()` | Lire ou modifier les paramètres effectifs d’un modèle enregistré |
| `defaultSettings()` | Lire ou modifier les valeurs par défaut de la classe du modèle |
| `modelSettings()` | Relation Eloquent utilisée pour le chargement anticipé |

## Enregistrer le premier paramètre

Créez une valeur par défaut pour tous les modèles `User` enregistrés :

```php
(new User)->defaultSettings()->set('timezone', 'UTC');
```

Remplacez cette valeur pour un utilisateur enregistré :

```php
$user = User::query()->firstOrFail();

$user->settings()->set('timezone', 'Europe/Paris');

assert($user->settings()->get('timezone') === 'Europe/Paris');
```

Lisez tous les paramètres effectifs dans une collection indexée par le nom du paramètre :

```php
$settings = $user->settings()->all();

assert($settings->get('timezone') === 'Europe/Paris');
```

Supprimez la surcharge pour revenir à `UTC` :

```php
$user->settings()->forget('timezone');

assert($user->settings()->get('timezone') === 'UTC');
```

## Enregistrer d’abord les modèles

Utilisez `settings()->set()` et `settings()->forget()` uniquement après avoir enregistré le modèle
parent. Pour un modèle non enregistré, `settings()->get()` renvoie `null` et `settings()->all()` une
collection vide, même si la classe possède des valeurs par défaut. Les deux méthodes de modification
lèvent `DragonCode\LaravelModelSettings\Exceptions\InvalidSettingsOwnerException` avant toute
requête de stockage.

## Voir aussi

- [Utilisation des paramètres](settings.md) — comprendre la priorité, la suppression, les clés et les valeurs.
- [Configuration](configuration.md) — choisir la connexion, la table ou le modèle de stockage.
- [Chargement anticipé](eager-loading.md) — charger efficacement les paramètres de collections de modèles.
