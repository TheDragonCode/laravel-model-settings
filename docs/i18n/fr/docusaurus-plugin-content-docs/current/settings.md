---
sidebar_position: 3
title: Utilisation des paramètres
description: Gérer les valeurs par défaut partagées, les surcharges propres aux modèles, les clés et les valeurs.
---

[← Premiers pas](getting-started.md) · [Retour au README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Chargement anticipé →](eager-loading.md)

# Utilisation des paramètres

Le même service gère les valeurs par défaut et les valeurs des modèles. Le point d’entrée détermine
la portée lue ou modifiée :

| Point d’entrée | Portée |
|----------------|--------|
| `(new User)->defaultSettings()` | Valeurs par défaut partagées par les modèles `User` enregistrés |
| `$user->settings()` | Paramètres effectifs d’un utilisateur enregistré |

## Valeurs par défaut partagées

Les valeurs par défaut s’appliquent à tous les modèles enregistrés ayant la même classe morph
Eloquent :

```php
$defaults = (new User)->defaultSettings();

$defaults->set('timezone', 'UTC');
$defaults->set('notifications', ['email' => true]);
```

Lisez ou supprimez les valeurs par défaut avec le même service :

```php
$timezone = $defaults->get('timezone');
$all = $defaults->all();

$defaults->forget('timezone');
```

Les valeurs par défaut sont indépendantes pour chaque classe de modèle.

## Surcharges propres aux modèles

`set()` crée un paramètre ou remplace sa valeur existante :

```php
$user->settings()->set('timezone', 'Europe/Paris');
$user->settings()->set('timezone', 'America/Toronto');
```

Seul le paramètre de ce modèle est modifié. Les autres modèles continuent d’utiliser leur propre
surcharge ou la valeur par défaut partagée.

`get()` et `all()` résolvent les valeurs avec la même priorité :

```php
$timezone = $user->settings()->get('timezone');
$settings = $user->settings()->all();
```

`all()` renvoie une `Illuminate\Support\Collection` indexée par les clés des paramètres.

Par exemple, une surcharge ne remplace que la valeur par défaut correspondante :

```php
(new User)->defaultSettings()->set('timezone', 'UTC');
(new User)->defaultSettings()->set('locale', 'en');

$user->settings()->set('timezone', 'Europe/Paris');

assert($user->settings()->all()->sortKeys()->all() === [
    'locale' => 'en',
    'timezone' => 'Europe/Paris',
]);
```

## Supprimer une valeur

La suppression d’une surcharge rend de nouveau visible la valeur par défaut :

```php
(new User)->defaultSettings()->set('timezone', 'UTC');

$user->settings()->set('timezone', 'Europe/Paris');
$user->settings()->forget('timezone');

assert($user->settings()->get('timezone') === 'UTC');
```

Pour supprimer la valeur par défaut elle-même, appelez `forget()` avec `defaultSettings()` :

```php
(new User)->defaultSettings()->forget('timezone');
```

L’appel de `forget()` pour une clé absente n’a aucun effet.

## Valeurs vides

`set()` utilise l’assistant Laravel `blank()`. Une valeur vide supprime le paramètre au lieu de
l’enregistrer.

| Valeur | Résultat |
|--------|----------|
| `null` | Supprimée |
| `''` ou chaîne composée uniquement d’espaces | Supprimée |
| `[]` | Supprimée |
| `0` | Enregistrée |
| `false` | Enregistrée |
| `'0'` | Enregistrée |

Le paquet ne peut pas conserver une valeur volontairement vide avec `set()`.

## Clés des paramètres

Les clés peuvent être des chaînes, des entiers ou des énumérations PHP implémentant `UnitEnum` :

```php
enum SettingKey: string
{
    case Timezone = 'timezone';
}

$user->settings()->set(SettingKey::Timezone, 'Europe/Paris');

$timezone = $user->settings()->get(SettingKey::Timezone);
```

Laravel stocke une backed enum avec sa valeur sous-jacente et une pure unit enum avec le nom de son
cas. Utilisez la même clé ou le même cas pour lire, remplacer ou supprimer un paramètre.

Le paquet ne valide pas le contenu des clés. L’API publique et le schéma par défaut acceptent les
clés vides ou composées uniquement d’espaces.

## Identifiants des modèles

Les clés primaires entières, chaînes, UUID et ULID sont prises en charge.

La modification des paramètres d’un modèle nécessite un propriétaire enregistré dont la clé est
différente de `null`. Pour un modèle non enregistré, `get()` renvoie `null` et `all()` une collection
vide sans interroger les surcharges du modèle. Ses méthodes `set()` et `forget()` lèvent
`InvalidSettingsOwnerException` avant toute requête de stockage.

L’entier `0` et la chaîne `'0'` sont réservés aux valeurs par défaut partagées dans la version 1.x.
Un modèle enregistré avec l’une de ces clés peut lire les valeurs par défaut de la classe, mais
`set()` et `forget()` lèvent `InvalidSettingsOwnerException`. Les autres clés de type chaîne, dont
`'00'`, restent valides.

Les paramètres sont stockés pour la classe morph actuelle du modèle. L’ajout ou la modification d’un
alias de morph map après l’écriture de paramètres nécessite de mettre à jour les valeurs `item_type`
existantes.

## Voir aussi

- [Chargement anticipé](eager-loading.md) — éviter une requête de paramètres par modèle.
- [Conversions des données](payload-casts.md) — renvoyer des objets métier plutôt que du JSON décodé.
- [Référence de l’API](api-reference.md) — consulter les signatures et les valeurs de retour.
