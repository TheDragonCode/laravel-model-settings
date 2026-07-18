---
sidebar_position: 8
title: Développement
description: Exécuter les tests, valider la documentation, contribuer ou signaler un problème de sécurité.
---

[← Référence de l’API](api-reference.md) · [Retour au README](https://github.com/TheDragonCode/laravel-model-settings#readme)

# Développement

## Vérifications du paquet

Installez les dépendances PHP :

```bash
composer install
```

Exécutez les tests ou générez le rapport de couverture :

```bash
composer test
composer test:coverage
```

Appliquez le style de code configuré :

```bash
composer style
```

Les suites Pest couvrent différents contrats :

| Suite | Couverture |
|-------|------------|
| `tests/Feature` | Valeurs par défaut, surcharges, suppression, données absentes et propriétaires |
| `tests/Unit/Casts` | JSON par défaut, conversions personnalisées, morph maps et Laravel Data |
| `tests/Unit/KeyTypes` | Clés chaîne, entières, backed enum et pure unit enum |
| `tests/Unit/PrimaryKeyTypes` | Identifiants parents entiers, chaînes, UUID et ULID |
| `tests/Unit/QueryCount` | Nombre de requêtes en lecture et en écriture, avec chargement anticipé |
| `tests/Architecture` | Espaces de noms, types, rigueur et règles d’architecture Laravel |

## Vérifications de la documentation

Le site Docusaurus nécessite Node.js 20 ou version ultérieure. Installez ses dépendances depuis le
répertoire `docs` :

```bash
npm ci
```

| Tâche | Commande |
|-------|----------|
| Démarrer le site local | `npm run start` |
| Vérifier TypeScript | `npm run typecheck` |
| Vérifier les traductions | `npm run check:i18n` |
| Créer un build de production | `npm run build` |

Le build de production valide les liens internes pour chaque locale configurée.

Conservez les pages de documentation dans `docs/docs`. Chaque page utilise le front matter pour
l’ordre dans la barre latérale, une ligne de navigation en haut, des liens relatifs entre les guides
et une section `Voir aussi` à la fin.

Conservez chaque locale autre que celle par défaut dans
`docs/i18n/<locale>/docusaurus-plugin-content-docs/current`. Chaque locale doit contenir les mêmes
chemins de page que `docs/docs`. La commande `npm run check:i18n` le vérifie avant un build de production.

## Contribution

Consultez le [guide de contribution](https://github.com/TheDragonCode/.github/blob/main/CONTRIBUTING.md)
avant d’ouvrir une pull request.

## Sécurité

Signalez les problèmes de sécurité en privé à
[helldar@dragon-code.pro](mailto:helldar@dragon-code.pro).

## Crédits

Créé par [Andrey Helldar](https://github.com/andrey-helldar) et les
[contributeurs du projet](https://github.com/TheDragonCode/laravel-model-settings/graphs/contributors).

## Voir aussi

- [Premiers pas](getting-started.md) — installer le paquet dans une application Laravel.
- [Configuration](configuration.md) — comprendre les fichiers publiés par le paquet.
- [Référence de l’API](api-reference.md) — consulter l’API publique avant de modifier le comportement.
