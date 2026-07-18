---
sidebar_position: 4
title: Chargement anticipé
description: Éviter les requêtes N+1 lors de la lecture des paramètres de collections de modèles Eloquent.
---

[← Utilisation des paramètres](settings.md) · [Retour au README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Configuration →](configuration.md)

# Chargement anticipé

## Charger les paramètres avec les modèles

Sans chargement anticipé, chaque appel à `settings()->get()` ou `settings()->all()` exécute une
requête de paramètres. Ces lectures par le service ne chargent pas `modelSettings` comme effet de
bord.

Chargez la relation à l’avance lorsque le résultat contient plusieurs modèles :

```php
$users = User::query()
    ->with('modelSettings')
    ->get();

$timezones = $users->map(
    fn (User $user) => $user->settings()->get('timezone')
);
```

La relation chargée à l’avance contient les surcharges de chaque modèle et toutes les valeurs par
défaut dont il hérite. Les appels suivants à `get()` et `all()` utilisent la relation chargée.

## Charger les paramètres après la requête

Utilisez `loadMissing()` lorsque les modèles sont déjà disponibles :

```php
$users->loadMissing('modelSettings');

$settings = $users->map(
    fn (User $user) => $user->settings()->all()
);
```

## Limites de la relation

Utilisez `modelSettings` uniquement avec `with()`, `load()` ou `loadMissing()`, et comme propriété de
la relation chargée. Il s’agit d’une optimisation de lecture, pas d’une API alternative de requête
ou de CRUD. Lisez et modifiez les valeurs avec `settings()` ou `defaultSettings()`.

## Comportement des requêtes

Lorsque les modèles parents sont récupérés puis que leurs paramètres sont lus, le chargement différé
et le chargement anticipé ont le même coût pour un modèle. Pour une collection, la différence est
visible :

| Modèles parents chargés | Chargement différé | Chargement anticipé |
|-------------------------|--------------------|---------------------|
| 1 | 2 requêtes | 2 requêtes |
| N | 1 + N requêtes | 2 requêtes |

Le chargement anticipé utilise :

1. Une requête pour les modèles parents.
2. Une requête pour leurs valeurs par défaut et leurs surcharges.

La requête de paramètres inclut les valeurs par défaut de la classe et tous les identifiants de
modèles demandés. La relation copie ensuite les valeurs par défaut héritées dans le résultat chargé
de chaque modèle et remplace les clés correspondantes par les surcharges de ce modèle.

Ce comportement est couvert pour les clés primaires entières, chaînes, UUID et ULID.

## Modifications après le chargement anticipé

Après un appel réussi à `set()`, `setMany()`, `forget()`, `forgetMany()` ou `purge()`, le paquet efface
exactement une fois la relation `modelSettings` chargée sur le modèle concerné. La lecture suivante
par le service interroge la valeur effective actuelle et ne renvoie donc pas de données périmées.
Une modification groupée en échec conserve la relation chargée existante et annule la transaction
mixte d’écriture et de suppression.

Chargez de nouveau la relation explicitement avant une autre lecture groupée :

```php
$user->settings()->setMany([
    'timezone' => 'Europe/Paris',
    'locale' => 'fr',
]);

$user->load('modelSettings');
```

La modification effectue toujours ses propres requêtes d’écriture. Le chargement anticipé ne change
que les lectures suivantes.

## Voir aussi

- [Utilisation des paramètres](settings.md) — comprendre la fusion des valeurs par défaut et des surcharges.
- [Référence de l’API](api-reference.md) — distinguer les méthodes du service de la relation.
- [Configuration](configuration.md) — configurer la connexion et le modèle de stockage.
