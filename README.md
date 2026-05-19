# Ina Zaoui — Plateforme photo

Application Symfony permettant à la photographe Ina Zaoui de gérer son portfolio et ses invités photographes.

---

## Prérequis

- PHP 8.2 ou supérieur (avec les extensions `gd`, `intl`, `pdo_mysql`, `openssl`)
- Composer 2.x
- MySQL 8.0 ou supérieur
- Symfony CLI 5.x
- Node.js (optionnel, pour les assets)

---

## Installation

### 1. Cloner le repository

```bash
git clone https://github.com/gregmelo/876-p15-inazaoui.git
cd 876-p15-inazaoui
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Configurer l'environnement

Crée un fichier `.env.local` à la racine du projet :

```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/inazaoui"
APP_ENV=dev
APP_SECRET=your_secret_here
```

### 4. Créer la base de données

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Importer les données

Importe le dump SQL fourni dans le fichier `backup.zip` via phpMyAdmin ou en ligne de commande :

```bash
mysql -u root inazaoui < backup.sql
```

### 6. Copier les images

Les images ne sont pas versionnées dans le repository (dossier `public/uploads/` ignoré par Git).
Copie manuellement les images depuis le dossier `uploads/` du `backup.zip` vers `public/uploads/`.

> **Note :** Le backup.zip dépasse 1 Go en raison du volume d'images (5050 fichiers).
> Une amélioration future serait d'utiliser un service de stockage cloud (AWS S3, Cloudinary)
> pour s'affranchir de cette contrainte et faciliter l'installation.

### 7. Convertir les images en WebP (optionnel)

Si les images du backup sont en JPEG, tu peux les convertir automatiquement en WebP :

```bash
php bin/console app:convert-to-webp
```

### 8. Lancer le serveur

```bash
symfony serve
```

Le site est accessible sur `http://127.0.0.1:8000`.

---

## Connexion

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Administrateur (Ina) | `ina@zaoui.com` | `password` |
| Invité (exemple) | `invite+0@example.com` | `password` |

---

## Commandes utiles

### Vider le cache

```bash
php bin/console cache:clear
```

### Convertir les images en WebP

```bash
php bin/console app:convert-to-webp
```

### Lancer les tests

```bash
php bin/phpunit
```

### Générer le rapport de couverture de code

```bash
php bin/phpunit --coverage-html var/coverage
```

Ouvre ensuite `var/coverage/index.html` dans ton navigateur.

### Préparer la base de données de test

```bash
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test --no-interaction
php bin/console doctrine:fixtures:load --env=test --no-interaction
```

---

## Structure du projet

```
src/
├── Command/                  # Commandes Symfony (ex: conversion WebP)
├── Controller/
│   ├── Admin/                # Controllers espace admin
│   │   ├── AlbumController   # Gestion des albums
│   │   ├── GuestController   # Gestion des invités
│   │   ├── MediaController   # Gestion des médias
│   │   └── SecurityController# Connexion / déconnexion
│   └── HomeController        # Pages Front Office
├── DataFixtures/             # Fixtures pour les tests
├── Entity/                   # Entités Doctrine (User, Media, Album)
├── Form/                     # Formulaires Symfony
├── Repository/               # Repositories Doctrine
└── Security/                 # UserChecker (blocage des invités)
config/
├── packages/                 # Configuration des bundles
└── routes/                   # Configuration des routes
templates/
├── admin/                    # Templates espace admin
└── front/                    # Templates Front Office
tests/
├── Controller/               # Tests fonctionnels
├── Entity/                   # Tests unitaires des entités
├── Repository/               # Tests des repositories
└── Security/                 # Tests du UserChecker
```

---

## Fonctionnalités principales

### Front Office
- Page d'accueil avec photo principale d'Ina
- Liste des invités actifs avec nombre de photos
- Portfolio par album
- Page de présentation d'Ina

### Espace Admin
- Connexion sécurisée (email + mot de passe)
- Gestion des médias (ajout avec validation WebP automatique, suppression)
- Gestion des albums (ajout, modification, suppression)
- Gestion des invités (liste, ajout, blocage/déblocage, suppression en cascade)

---

## Choix techniques

### Migration Symfony 5.4 → 7.4 LTS
Le projet a été migré de Symfony 5.4 vers Symfony 7.4 LTS pour bénéficier de 3 ans de support. Les annotations PHP ont été remplacées par les attributs natifs PHP 8, et `getDoctrine()` a été remplacé par l'injection de `EntityManagerInterface`.

### Authentification
L'authentification utilise le système natif de Symfony avec un provider `entity` chargeant les utilisateurs depuis la base de données. Un `UserChecker` personnalisé empêche les invités bloqués de se connecter avec un message d'erreur explicite.

### Gestion des rôles
Les rôles sont calculés dynamiquement depuis le champ `admin` de l'entité `User` :
- `admin = true` → `ROLE_ADMIN` + `ROLE_USER`
- `admin = false` → `ROLE_USER`

### Optimisation des performances
La page Invités souffrait d'un problème N+1 (102 requêtes SQL pour 100 invités). Correction via un `LEFT JOIN` dans `UserRepository::findActiveGuestsWithMediaCount()`, réduisant les requêtes de 102 à 1 (-99%).

### Conversion WebP
Les images uploadées sont automatiquement converties en WebP (qualité 80%) via l'extension GD de PHP, réduisant leur poids de 25 à 35% par rapport au JPEG.

---

## Tests

Les tests couvrent le Front Office et la logique métier avec un taux de couverture de **73.89%**.

| Catégorie | Couverture |
|-----------|------------|
| Entity | 94.29% |
| Form | 100% |
| Repository | 96.43% |
| Security | 85.71% |
| Controller | 74.29% |
| **Total** | **73.89%** |