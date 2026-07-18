---
sidebar_position: 1
slug: /
title: Laravel Model Settings
description: Paramètres par défaut partagés et surcharges propres à chaque modèle Laravel Eloquent.
---

[Retour au README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Premiers pas →](getting-started.md)

# Laravel Model Settings

Laravel Model Settings stocke les paramètres par défaut partagés et les surcharges propres à chaque
modèle dans une table de base de données distincte. Utilisez-le lorsque tous les modèles doivent
commencer avec la même valeur, mais que certains enregistrements peuvent la remplacer.

Le paquet n’ajoute aucune colonne de paramètres aux tables parentes. Les paramètres restent
indépendants du schéma du modèle et sont regroupés selon la classe morph Eloquent du modèle.

## Quand utiliser ce paquet

| Besoin | Comportement du paquet |
|--------|------------------------|
| Donner la même valeur initiale à tous les modèles enregistrés | Stocker une valeur par défaut au niveau de la classe |
| Modifier la valeur d’un modèle | Stocker une surcharge propre à ce modèle |
| Supprimer une surcharge | Réutiliser la valeur par défaut de la classe |
| Lire plusieurs modèles | Charger une relation pour les valeurs par défaut et les surcharges |

## Ordre de résolution

Lors de la lecture d’un paramètre, le paquet renvoie la première valeur disponible :

1. La surcharge du modèle enregistré.
2. La valeur par défaut de la classe de ce modèle.
3. `null`.

| Source | `timezone` |
|--------|------------|
| Valeur par défaut de `User` | `UTC` |
| Surcharge de l’utilisateur 123 | `Europe/Paris` |
| Valeur effective de l’utilisateur 123 | `Europe/Paris` |
| Valeur effective d’un autre utilisateur enregistré | `UTC` |

La suppression d’une surcharge rend de nouveau visible la valeur par défaut. Elle ne supprime pas
cette valeur par défaut.

## Opérations principales

```php
(new User)->defaultSettings()->set('timezone', 'UTC');

$user->settings()->set('timezone', 'Europe/Paris');

$timezone = $user->settings()->get('timezone');
$settings = $user->settings()->all();
$hasTimezone = $settings->has('timezone');

$user->settings()->setMany([
    'locale' => 'fr',
    'notifications.email' => true,
]);
$user->settings()->forgetMany(['timezone', 'locale']);
```

`get()` renvoie une valeur effective. `all()` renvoie une `Illuminate\Support\Collection` contenant
les valeurs par défaut fusionnées avec les surcharges.
Utilisez la méthode `has()` de la collection pour vérifier l’existence d’une clé effective. `get()`
n’accepte volontairement aucune valeur de repli fournie par l’appelant : la valeur persistante par
défaut de la classe est son seul repli, puis `null` est renvoyé si aucune portée ne contient la clé.

Les valeurs par défaut et les surcharges utilisent les mêmes opérations : `all()`, `get()`, `set()`,
`setMany()`, `forget()`, `forgetMany()` et `purge()`.

## Limites ciblées du paquet

Laravel Model Settings est un paquet Eloquent ciblé, et non un framework général de paramètres
d’application.

| Limite | Comportement volontaire |
|--------|-------------------------|
| Stockage | Une table de base de données ; ni Redis ni stockage dans les champs du modèle parent |
| Valeurs par défaut | Lignes réservées dans la même table ; aucune seconde table de valeurs par défaut |
| Enregistrement | Aucun registre de dépôts, aucune classe globale typée de paramètres ni découverte de classes |
| Migrations | Aucun exécuteur de migrations par clé de paramètre |
| Cache | Aucun cache inter-requêtes obligatoire ; le chargement anticipé ne réutilise que la relation chargée |

Les applications qui ont besoin de ces fonctions doivent les composer en dehors du paquet plutôt
que d’utiliser `modelSettings` ou le dépôt interne comme API d’extension.

## Limites du stockage

Chaque ligne est identifiée par quatre valeurs :

| Valeur | Signification |
|--------|---------------|
| `item_type` | Classe morph du modèle parent ou alias de la morph map |
| `item_id` | Clé primaire du parent ; les valeurs par défaut de la classe conservent la valeur physique `0` |
| `is_default` | `true` pour une valeur par défaut de classe, `false` pour une surcharge de modèle |
| `key` | Nom du paramètre |

Les valeurs par défaut sont ainsi indépendantes pour chaque classe de modèle. Une valeur par défaut
de `User` ne devient jamais une valeur par défaut de `Post`, même si les deux classes utilisent la
même clé de paramètre.

## Modèles pris en charge

Le paquet prend en charge les modèles Eloquent dont la clé primaire est un entier, une chaîne, un
UUID ou un ULID. Les modèles enregistrés avec l’identifiant entier `0` ou la chaîne `'0'` peuvent
stocker des surcharges sans entrer en conflit avec les valeurs par défaut de la classe. Les modèles
peuvent aussi utiliser une morph map Laravel.

Les paramètres propres à un modèle nécessitent un modèle enregistré. Un modèle non enregistré
n’hérite pas des valeurs par défaut : `get()` renvoie `null` et `all()` une collection vide. Appeler
`set()`, `setMany()`, `forget()`, `forgetMany()` ou `purge()` pour un propriétaire non enregistré
lève `InvalidSettingsOwnerException` avant toute requête de stockage.

Les données sont stockées au format JSON. Sans conversion configurée, la lecture renvoie des
tableaux décodés ou des valeurs scalaires. Les [conversions des données](payload-casts.md) peuvent
renvoyer des objets propres à l’application.

## Voir aussi

- [Premiers pas](getting-started.md) — installer le paquet et configurer un modèle.
- [Utilisation des paramètres](settings.md) — gérer les valeurs par défaut, les surcharges, les clés et les valeurs.
- [Référence de l’API](api-reference.md) — consulter chaque méthode publique et son type de retour.
