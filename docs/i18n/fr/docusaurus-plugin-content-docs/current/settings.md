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

`get()`, `has()` et `all()` résolvent les valeurs avec la même priorité :

```php
$timezone = $user->settings()->get('timezone');
$hasTimezone = $user->settings()->has('timezone');
$settings = $user->settings()->all();
```

`all()` renvoie une `Illuminate\Support\Collection` indexée par les clés des paramètres.

`get()` accepte uniquement la clé. Il renvoie d’abord la surcharge du modèle, puis la valeur
persistante par défaut de la classe, puis `null`. Il n’accepte pas de valeur de repli fournie par
l’appelant. `has()` distingue une clé absente d’une valeur JSON `null` stockée :

```php
if ($user->settings()->has('timezone')) {
    $timezone = $user->settings()->get('timezone');
}
```

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

## Modifications groupées

Utilisez `setMany()` et `forgetMany()` lorsqu’une même portée nécessite plusieurs modifications :

```php
$user->settings()->setMany([
    'timezone' => 'Europe/Paris',
    'locale' => 'fr',
]);

$user->settings()->forgetMany(['timezone', 'locale']);
```

Les deux méthodes acceptent tout iterable. `setMany()` normalise chaque clé avant l’écriture. Si
plusieurs clés d’entrée se normalisent vers la même clé stockée, la dernière valeur est retenue.
Chaque valeur est stockée ; seules `forget()` et `forgetMany()` suppriment des lignes.

Un lot `setMany()` non vide effectue un unique upsert natif dans une transaction. `forgetMany()`
supprime toutes les clés indiquées avec une seule requête. Le nombre de requêtes est borné par le
type d’opération et non par le nombre de clés.

Utilisez `purge()` pour supprimer toute la portée actuelle :

```php
$user->settings()->purge();
```

Avec `settings()`, `purge()` supprime uniquement les surcharges de ce propriétaire et rend de nouveau
visibles les valeurs persistantes par défaut. Avec `defaultSettings()`, il supprime les valeurs par
défaut de cette classe sans supprimer les surcharges des modèles. Les trois méthodes groupées
renvoient `void`.

## Valeurs JSON

`set()` et `setMany()` conservent les valeurs JSON exactes :

| Valeur | Résultat |
|--------|----------|
| `null` | Enregistrée |
| `''` ou chaîne composée uniquement d’espaces | Enregistrée |
| `[]` | Enregistrée |
| `0` | Enregistrée |
| `false` | Enregistrée |
| `'0'` | Enregistrée |

Une valeur `null` stockée est considérée comme existante. `has($key)` renvoie `true` et `get($key)`
renvoie `null`. Une surcharge de modèle valant `null` masque aussi une valeur par défaut de classe
non vide jusqu’à sa suppression avec `forget()`.

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

Les clés vides ou composées uniquement d’espaces lèvent
`DragonCode\LaravelModelSettings\Exceptions\InvalidSettingKey`. La validation s’effectue après la
normalisation des entiers et des énumérations vers la chaîne stockée. L’exception ne contient jamais
la clé rejetée ni les données du paramètre.

Les points sont des caractères littéraux. La clé `mail.from.address` est une seule clé opaque et ne
représente jamais un chemin imbriqué :

```php
$user->settings()->set('mail.from.address', 'noreply@example.com');

$address = $user->settings()->get('mail.from.address');
```

## Identifiants des modèles

Les clés primaires entières, chaînes, UUID et ULID sont prises en charge.

La modification des paramètres d’un modèle nécessite un propriétaire enregistré dont la clé est
différente de `null`. Pour un modèle non enregistré, `get()` renvoie `null`, `has()` renvoie `false`
et `all()` une collection vide sans interroger les surcharges du modèle. Ses méthodes `set()`,
`setMany()`, `forget()`, `forgetMany()` et `purge()` lèvent `InvalidSettingsOwnerException` avant
toute requête de stockage ou consommation de l’iterable.

Les modèles enregistrés avec l’identifiant entier `0` ou la chaîne `'0'` prennent en charge les mêmes
lectures et modifications que tout autre propriétaire enregistré. Le discriminateur de portée
sépare leurs surcharges des valeurs par défaut de la classe, même si les deux lignes conservent
`item_id = '0'`. Les autres clés de type chaîne, dont `'00'`, restent valides.

Les paramètres sont stockés pour la classe morph actuelle du modèle. L’ajout ou la modification d’un
alias de morph map après l’écriture de paramètres nécessite de mettre à jour les valeurs `item_type`
existantes.

## Voir aussi

- [Chargement anticipé](eager-loading.md) — éviter une requête de paramètres par modèle.
- [Conversions des données](payload-casts.md) — renvoyer des objets métier plutôt que du JSON décodé.
- [Référence de l’API](api-reference.md) — consulter les signatures et les valeurs de retour.
