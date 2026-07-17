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

$user->settings()->forget('timezone');
```

`get()` renvoie une valeur effective. `all()` renvoie une `Illuminate\Support\Collection` contenant
les valeurs par défaut fusionnées avec les surcharges.

Les valeurs par défaut et les surcharges utilisent les quatre mêmes opérations : `all()`, `get()`,
`set()` et `forget()`.

## Limites du stockage

Chaque ligne est identifiée par trois valeurs :

| Valeur | Signification |
|--------|---------------|
| `item_type` | Classe morph du modèle parent ou alias de la morph map |
| `item_id` | Clé primaire du parent, ou valeur réservée `0` pour les valeurs par défaut de la classe |
| `key` | Nom du paramètre |

Les valeurs par défaut sont ainsi indépendantes pour chaque classe de modèle. Une valeur par défaut
de `User` ne devient jamais une valeur par défaut de `Post`, même si les deux classes utilisent la
même clé de paramètre.

## Modèles pris en charge

Le paquet prend en charge les modèles Eloquent dont la clé primaire est un entier, un UUID ou un
ULID. Les modèles peuvent aussi utiliser une morph map Laravel.

Les paramètres propres à un modèle nécessitent un modèle enregistré. Un modèle non enregistré
n’hérite pas des valeurs par défaut.

Les données sont stockées au format JSON. Sans conversion configurée, la lecture renvoie des
tableaux décodés ou des valeurs scalaires. Les [conversions des données](payload-casts.md) peuvent
renvoyer des objets propres à l’application.

## Voir aussi

- [Premiers pas](getting-started.md) — installer le paquet et configurer un modèle.
- [Utilisation des paramètres](settings.md) — gérer les valeurs par défaut, les surcharges, les clés et les valeurs.
- [Référence de l’API](api-reference.md) — consulter chaque méthode publique et son type de retour.
