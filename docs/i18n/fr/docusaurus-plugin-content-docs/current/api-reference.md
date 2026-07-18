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
| `setMany(iterable $values)` | `void` | Écrit les valeurs non vides et supprime les valeurs vides dans un lot borné |
| `forget(int\|string\|UnitEnum $key)` | `void` | Supprime un paramètre s’il existe |
| `forgetMany(iterable $keys)` | `void` | Supprime les clés indiquées de la portée actuelle |
| `purge()` | `void` | Supprime tous les paramètres stockés dans la portée actuelle |

Les méthodes utilisant une clé acceptent les backed enums et les pure unit enums. Laravel convertit
les backed enums en leur valeur sous-jacente et les pure unit enums en nom de cas.

`SettingsService` ne fournit ni argument de repli pour `get()` ni méthode `has()` distincte. Utilisez
`all()->has($key)` pour vérifier l’existence d’une clé effective.

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
$hasTimezone = $settings->has('timezone');
```

Le résultat est une `Illuminate\Support\Collection` indexée par la clé du paramètre. Pour les
paramètres d’un modèle, les surcharges remplacent les valeurs par défaut ayant la même clé.

## get

```php
$timezone = $user->settings()->get('timezone');
```

Le résultat est la valeur effective décodée ou convertie. Une surcharge absente utilise la valeur
par défaut. Si la surcharge et la valeur par défaut sont absentes, la méthode renvoie `null`. Sa
signature n’accepte volontairement aucun second argument de repli.

## set

```php
$user->settings()->set('timezone', 'Europe/Paris');
```

La méthode valide le propriétaire, puis effectue une opération update-or-create pour le type de
modèle, son identifiant, le discriminateur de portée et la clé. Une valeur considérée comme vide par
Laravel supprime la ligne. La validation a lieu avant la sélection du traitement des valeurs vides.
Dans les deux cas, la relation `modelSettings` chargée est effacée afin que la prochaine lecture ne
réutilise pas d’anciennes données.

## setMany

```php
$user->settings()->setMany([
    'timezone' => 'Europe/Paris',
    'locale' => 'fr',
    'obsolete' => null,
]);
```

Les clés de l’iterable sont normalisées comme avec `set()`. Si plusieurs clés d’entrée se
normalisent vers la même chaîne, la dernière valeur est retenue. Les valeurs non vides utilisent un
upsert natif unique et les valeurs vides une suppression unique. Lorsque les deux groupes existent,
les opérations s’exécutent dans une transaction. La méthode valide le propriétaire avant de
consommer l’iterable et efface `modelSettings` une fois après la réussite.

## forget

```php
$user->settings()->forget('timezone');
```

Pour un propriétaire valide, la méthode est sûre lorsque la clé n’existe pas. La suppression d’une
surcharge ne supprime pas sa valeur par défaut partagée. La relation chargée est effacée après la
suppression.

## forgetMany

```php
$user->settings()->forgetMany(['timezone', 'locale']);
```

La méthode normalise et déduplique l’iterable, puis supprime uniquement ces clés de la portée actuelle
avec une requête. Les clés absentes n’ont aucun effet. Elle renvoie `void` et efface la relation
chargée après un appel réussi, y compris avec un iterable vide.

## purge

```php
$user->settings()->purge();
```

Avec `settings()`, la méthode supprime toutes les surcharges du propriétaire enregistré. Elle ne
supprime ni les valeurs par défaut de la classe ni les surcharges d’un autre propriétaire. Avec
`defaultSettings()`, elle supprime toutes les valeurs par défaut de cette classe et conserve les
surcharges des modèles. Elle renvoie `void` et efface une relation chargée après la réussite.

## defaultSettings

Le service renvoyé par `defaultSettings()` possède les sept mêmes méthodes :

```php
$defaults = (new User)->defaultSettings();

$defaults->set('timezone', 'UTC');
$defaults->setMany(['timezone' => 'UTC', 'locale' => 'en']);
$timezone = $defaults->get('timezone');
$all = $defaults->all();
$defaults->forget('timezone');
$defaults->forgetMany(['timezone', 'locale']);
$defaults->purge();
```

## Exceptions

`DragonCode\LaravelModelSettings\Exceptions\InvalidSettingsOwnerException` étend la classe PHP
`DomainException`. Toute méthode de modification via `settings()` la lève avant une requête de
stockage lorsque le modèle propriétaire n’est pas enregistré, y compris lorsqu’une clé lui a été
attribuée à l’avance.

Cette validation a aussi lieu avant la consommation d’un iterable groupé. Les modifications par
`defaultSettings()` restent valides, car ce service sélectionne explicitement la portée des valeurs
par défaut de la classe. La lecture reste déterministe : un propriétaire non enregistré renvoie
`null` ou une collection vide sans interroger les surcharges. Un propriétaire enregistré avec la clé
entière `0` ou la chaîne `'0'` peut lire et modifier ses surcharges ; `is_default` sépare ces lignes
des valeurs par défaut de la classe.

`DragonCode\LaravelModelSettings\Exceptions\InvalidPayloadCast` est levée lorsqu’une conversion
configurée pour un modèle ou une clé est absente, d’un type invalide, n’implémente aucun contrat pris
en charge ou ne peut pas être résolue par le conteneur Laravel. Son message peut identifier le modèle
parent, la clé et la classe de conversion, mais jamais les données.

Si une opération `setMany()` mixte échoue, la transaction annule l’écriture et la suppression.
L’exception est relancée et la relation `modelSettings` déjà chargée n’est pas effacée.

## Voir aussi

- [Utilisation des paramètres](settings.md) — comprendre le comportement de chaque opération.
- [Chargement anticipé](eager-loading.md) — utiliser `modelSettings` sans requêtes N+1.
- [Conversions des données](payload-casts.md) — contrôler les valeurs renvoyées par `get()` et `all()`.
