---
sidebar_position: 7
title: Référence de l’API
description: Méthodes publiques du trait, du service et de la relation fournies par Laravel Model Settings.
---

[← Conversions des données](payload-casts.md) · [Retour au README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Développement →](development.md)

# Référence de l’API

## Trait HasSettings

| Méthode | Renvoie | Rôle |
|---------|---------|------|
| `settings()` | `SettingsService` | Accéder aux paramètres effectifs de ce modèle |
| `defaultSettings()` | `SettingsService` | Accéder aux valeurs par défaut partagées pour cette classe de modèle |
| `modelSettings()` | `Relation` Eloquent | Charger les valeurs par défaut et les surcharges comme relation |

Utilisez la relation `modelSettings` uniquement avec `with()`, `load()` ou `loadMissing()`, et comme
propriété chargée qui en résulte. N’utilisez pas la requête de la relation comme API alternative de
lecture ou de CRUD. Utilisez les deux méthodes de service pour lire ou modifier les valeurs. À
l’exécution, la relation est une `SettingsRelation` du paquet basée sur la relation Laravel
`MorphMany`.

## SettingsService

| Méthode | Renvoie | Comportement |
|---------|---------|--------------|
| `all()` | `Collection` | Renvoie les valeurs par défaut fusionnées avec les surcharges du modèle |
| `get(int\|string\|UnitEnum $key)` | `mixed` | Renvoie une surcharge, sa valeur par défaut ou `null` |
| `set(int\|string\|UnitEnum $key, mixed $value)` | `void` | Crée, remplace ou supprime un paramètre vide |
| `forget(int\|string\|UnitEnum $key)` | `void` | Supprime un paramètre s’il existe |

Les méthodes utilisant une clé acceptent les backed enums et les pure unit enums. Laravel convertit
les backed enums en leur valeur sous-jacente et les pure unit enums en nom de cas.

## Matrice de résolution

| Surcharge du modèle | Valeur par défaut de la classe | Résultat de `get()` | Inclus dans `all()` |
|---------------------|--------------------------------|---------------------|----------------------|
| Présente | Présente | Surcharge | Surcharge |
| Présente | Absente | Surcharge | Surcharge |
| Absente | Présente | Valeur par défaut | Valeur par défaut |
| Absente | Absente | `null` | Aucune entrée |

Pour un modèle non enregistré, `get()` renvoie `null` et `all()` une collection vide. Seuls les
modèles enregistrés héritent des valeurs par défaut de la classe.

## all

```php
$settings = $user->settings()->all();

$timezone = $settings->get('timezone');
```

Le résultat est une `Illuminate\Support\Collection` indexée par la clé du paramètre. Pour les
paramètres d’un modèle, les surcharges remplacent les valeurs par défaut ayant la même clé.

## get

```php
$timezone = $user->settings()->get('timezone');
```

Le résultat est la valeur effective décodée ou convertie. Une surcharge absente utilise la valeur
par défaut. Si la surcharge et la valeur par défaut sont absentes, la méthode renvoie `null`.

## set

```php
$user->settings()->set('timezone', 'Europe/Paris');
```

La méthode valide le propriétaire, puis effectue une opération update-or-create pour le type de
modèle, son identifiant et la clé. Une valeur considérée comme vide par Laravel supprime la ligne.
La validation a lieu avant la sélection du traitement des valeurs vides. Dans les deux cas, la
relation `modelSettings` chargée est effacée afin que la prochaine lecture ne réutilise pas
d’anciennes données.

## forget

```php
$user->settings()->forget('timezone');
```

Pour un propriétaire valide, la méthode est sûre lorsque la clé n’existe pas. La suppression d’une
surcharge ne supprime pas sa valeur par défaut partagée. La relation chargée est effacée après la
suppression.

## defaultSettings

Le service renvoyé par `defaultSettings()` possède les quatre mêmes méthodes :

```php
$defaults = (new User)->defaultSettings();

$defaults->set('timezone', 'UTC');
$timezone = $defaults->get('timezone');
$all = $defaults->all();
$defaults->forget('timezone');
```

## Exceptions

`DragonCode\LaravelModelSettings\Exceptions\InvalidSettingsOwnerException` étend la classe PHP
`DomainException`. `settings()->set()` et `settings()->forget()` la lèvent avant toute requête de
stockage lorsque l’une des conditions suivantes est remplie :

- Le modèle propriétaire n’est pas enregistré, y compris lorsqu’une clé lui a été attribuée à
  l’avance.
- La clé du propriétaire enregistré est l’entier `0` ou la chaîne `'0'`, ce qui entre en conflit
  avec la valeur sentinelle des paramètres par défaut de la classe dans la version 1.x.

Cette validation s’applique aussi lorsque `set()` reçoit une valeur vide. Les modifications par
`defaultSettings()` restent valides, car ce service sélectionne explicitement la portée des valeurs
par défaut de la classe. La lecture reste déterministe : un propriétaire non enregistré renvoie
`null` ou une collection vide sans interroger les surcharges, tandis qu’un propriétaire enregistré
avec la clé `0` peut lire les valeurs par défaut de la classe, mais pas les modifier comme surcharges
du modèle.

## Voir aussi

- [Utilisation des paramètres](settings.md) — comprendre le comportement de chaque opération.
- [Chargement anticipé](eager-loading.md) — utiliser `modelSettings` sans requêtes N+1.
- [Conversions des données](payload-casts.md) — contrôler les valeurs renvoyées par `get()` et `all()`.
