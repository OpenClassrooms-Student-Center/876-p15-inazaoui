# Guide de contribution — Ina Zaoui

Merci de contribuer au projet Ina Zaoui ! Ce document définit les règles et bonnes pratiques à respecter pour assurer une contribution de qualité.

---

## Table des matières

1. [Soumettre un problème (bug)](#1-soumettre-un-problème-bug)
2. [Proposer une fonctionnalité](#2-proposer-une-fonctionnalité)
3. [Contribuer au code](#3-contribuer-au-code)
4. [Contribuer aux tests](#4-contribuer-aux-tests)
5. [Contribuer à la documentation](#5-contribuer-à-la-documentation)
6. [Conventions de nommage](#6-conventions-de-nommage)
7. [Politique de validation](#7-politique-de-validation)
8. [Bonnes pratiques](#8-bonnes-pratiques)

---

## 1. Soumettre un problème (bug)

Avant de créer une issue, vérifie que le problème n'a pas déjà été signalé dans les [issues existantes](https://github.com/gregmelo/876-p15-inazaoui/issues).

### Format d'une issue bug

```
**Description**
Description claire et concise du bug.

**Étapes pour reproduire**
1. Aller sur '...'
2. Cliquer sur '...'
3. Observer l'erreur

**Comportement attendu**
Ce qui devrait se passer.

**Comportement observé**
Ce qui se passe réellement.

**Environnement**
- PHP : x.x.x
- Symfony : x.x.x
- Navigateur : ...
- OS : ...

**Captures d'écran**
Si applicable.
```

---

## 2. Proposer une fonctionnalité

Pour proposer une nouvelle fonctionnalité, ouvre une issue avec le label `enhancement` en décrivant :

- Le besoin ou le problème que la fonctionnalité résout
- La solution proposée
- Les alternatives éventuellement envisagées
- L'impact sur le code existant

---

## 3. Contribuer au code

### Procédure

1. **Fork** le repository
2. **Crée une branche** depuis `main` (voir conventions de nommage)
3. **Développe** ta fonctionnalité ou correction
4. **Écris les tests** associés
5. **Vérifie** que tous les tests passent
6. **Commit** avec un message conventionnel
7. **Pousse** ta branche
8. **Ouvre une Pull Request** vers `main`

### Conventions de nommage des branches

| Type | Format | Exemple |
|------|--------|---------|
| Fonctionnalité | `feature/description-courte` | `feature/gestion-invites` |
| Correction de bug | `fix/description-courte` | `fix/login-redirect` |
| Performance | `perf/description-courte` | `perf/optimisation-n-plus-un` |
| Documentation | `docs/description-courte` | `docs/readme-installation` |
| Refactoring | `refactor/description-courte` | `refactor/entity-user` |
| Tests | `test/description-courte` | `test/controller-home` |

### Conventions de nommage des commits

Le projet suit la convention [Conventional Commits](https://www.conventionalcommits.org/) :

```
type(scope): description courte

Corps optionnel expliquant le pourquoi.

Footer optionnel (ex: Closes #123)
```

| Type | Usage |
|------|-------|
| `feat` | Nouvelle fonctionnalité |
| `fix` | Correction de bug |
| `perf` | Amélioration de performance |
| `refactor` | Refactoring sans nouvelle fonctionnalité |
| `test` | Ajout ou modification de tests |
| `docs` | Documentation uniquement |
| `chore` | Tâches de maintenance (dépendances, config) |
| `style` | Formatage, espaces (sans changement de logique) |

**Exemples :**
```
feat(guest): ajout de la pagination sur la liste des invités
fix(security): correction de la redirection après déconnexion
perf(repository): résolution du problème N+1 sur la page invités
test(controller): ajout des tests fonctionnels pour GuestController
docs(readme): mise à jour des instructions d'installation
```

---

## 4. Contribuer aux tests

Tout nouveau code doit être accompagné de tests. Le taux de couverture doit rester **supérieur à 70%**.

### Types de tests

- **Tests unitaires** (`tests/Entity/`, `tests/Security/`) — testent la logique métier isolée
- **Tests fonctionnels** (`tests/Controller/`) — testent les pages et actions HTTP
- **Tests de repository** (`tests/Repository/`) — testent les requêtes Doctrine

### Lancer les tests

```bash
# Tous les tests
php bin/phpunit

# Avec rapport de couverture
php bin/phpunit --coverage-html var/coverage

# Un fichier spécifique
php bin/phpunit tests/Controller/HomeControllerTest.php
```

### Préparer l'environnement de test

```bash
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test --no-interaction
php bin/console doctrine:fixtures:load --env=test --no-interaction
```

### Règles pour les tests

- Chaque test doit être **indépendant** — il ne doit pas dépendre de l'état laissé par un autre test
- Si un test modifie des données en BDD, il doit **restaurer l'état initial** après exécution
- Les tests doivent avoir des noms **descriptifs** : `testGuestPageWithBlockedGuestRedirects()`
- Utilise `loginUser()` pour simuler une connexion dans les tests fonctionnels

---

## 5. Contribuer à la documentation

- Le `README.md` doit être mis à jour si tu ajoutes une fonctionnalité, une commande ou changes les prérequis
- Les méthodes de repository complexes doivent être documentées avec un commentaire PHPDoc
- Les choix techniques importants doivent être expliqués dans le `README.md` (section "Choix techniques")

---

## 6. Conventions de nommage

### PHP / Symfony

- **Classes** : PascalCase (`GuestController`, `UserRepository`)
- **Méthodes** : camelCase (`findActiveGuests()`, `checkPreAuth()`)
- **Variables** : camelCase (`$guestActive`, `$totalPages`)
- **Constantes** : UPPER_SNAKE_CASE (`MAX_UPLOAD_SIZE`)
- **Routes** : snake_case avec préfixe (`admin_guest_index`, `home`)

### Twig

- **Fichiers** : snake_case (`guest_list.html.twig`)
- **Variables** : camelCase (`{{ guestActive }}`)

### Base de données

- **Tables** : snake_case (`doctrine_migration_versions`)
- **Colonnes** : snake_case (`is_blocked`, `user_id`)

---

## 7. Politique de validation

### Critères d'acceptation d'une Pull Request

- [ ] Tous les tests existants passent (`php bin/phpunit`)
- [ ] Le taux de couverture reste supérieur à 70%
- [ ] Le code respecte les conventions de nommage
- [ ] Les nouvelles fonctionnalités sont testées
- [ ] La documentation est mise à jour si nécessaire
- [ ] Le message de commit suit la convention Conventional Commits
- [ ] La branche est à jour avec `main`

### Processus de review

1. La PR doit être relue par au moins **1 développeur**
2. Les commentaires de review doivent être traités avant le merge
3. Le merge se fait avec **squash** pour garder un historique propre

---

## 8. Bonnes pratiques

### Sécurité

- Ne jamais committer de données sensibles (mots de passe, clés API, `.env.local`)
- Toujours valider et assainir les entrées utilisateur
- Utiliser les attributs `#[IsGranted]` pour protéger les routes admin
- Vérifier les permissions avant toute action sur une entité (`$media->getUser() !== $this->getUser()`)

### Performance

- Éviter le problème N+1 — utiliser les `JOIN` Doctrine quand plusieurs relations sont nécessaires
- Paginer les listes longues (25 éléments par page)
- Convertir les images en WebP à l'upload via la commande `app:convert-to-webp`

### Qualité de code

- Typer tous les paramètres et retours de méthodes
- Utiliser l'injection de dépendances plutôt que `$this->getDoctrine()` (déprécié)
- Utiliser les attributs PHP natifs `#[Route]`, `#[IsGranted]` plutôt que les annotations
- Préférer `EntityManagerInterface` injecté dans le constructeur

### Outils recommandés

- **PHPStan** — analyse statique du code
- **PHP CS Fixer** — formatage automatique du code
- **Symfony CLI** — serveur de développement intégré