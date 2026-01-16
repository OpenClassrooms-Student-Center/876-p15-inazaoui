# Ina Zaoui - Symfony 7. 4

## Présentation

Ce dépôt contient l’application Inazaoui, refondue et optimisée sous Symfony 7.4 (LTS). Le projet initial, développé sous Symfony 5.4, présentait des problèmes de performance (notamment un effet N+1 sur la page Invités). La refonte vise à améliorer la scalabilité, la maintenabilité et la performance globale de l’application.

Les choix techniques et les optimisations mises en place sont documentés afin de faciliter la reprise du projet par un nouveau développeur.

## Prérequis

PHP : >= 8.2 (8.4 recommandé)

* Composer : >= 2.x
* MySQL : >= 8.0
* Node.js (optionnel, si assets front)
* Symfony CLI (optionnel)
* Extensions PHP requises :
* pdo_mysql
* intl
* opcache

### 1. Cloner le dépôt
   `git clone https://github.com/OpenClassrooms-Student-Center/876-p15-inazaoui.git
   cd 876-p15-inazaoui`
### 2. Installer les dépendances PHP
   `composer install`
### 3. Configuration de l’environnement

### Créer le fichier .env.local :

`cp .env .env.local`

### Exemple de configuration :

APP_ENV=dev
APP_DEBUG=1
DATABASE_URL="mysql://user:password@127.0.0.1:3306/inazaoui?serverVersion=8.0&charset=utf8mb4"
#### 4. Base de données

Créer la base :

`php bin/console doctrine:database:create`

Appliquer le schéma (migrations) :

`php bin/console doctrine:migrations:migrate`

Charger les données de test (fixtures) :

`php bin/console doctrine:fixtures:load`

## Lancer l’application
Serveur PHP intégré (recommandé)
php -S 127.0.0.1:8000 -t public

Accès :

http://127.0.0.1:8000
Profiler Symfony

En environnement dev, le Web Debug Toolbar et le Profiler Symfony sont actifs par défaut.

Accès direct :
http://127.0.0.1:8000

## Lancer l’ensemble des tests
* php bin/phpunit
* Générer un rapport de couverture
* XDEBUG_MODE=coverage php bin/phpunit --coverage-html var/coverage

Le rapport HTML est accessible dans :

`var/coverage/index.html`

## Choix d’implémentation clés

* Suppression de l’effet N+1 via des requêtes Doctrine optimisées (JOIN FETCH)
* L'effet N+1 est une première requête récupère une liste d’éléments, puis une requête SQL supplémentaire est exécutée pour chaque élément de la liste pour récupérer les données liées à cet élément.
* Séparation claire des responsabilités (Controller / Repository / Templates)
* Utilisation du Profiler Symfony comme outil principal d’analyse
* Optimisation des performances avant tout via la couche Data, non via Twig

## Performance

Les performances ont été mesurées à l’aide du Profiler Symfony en environnement dev, puis comparées avec un environnement prod.

## Conseils d’utilisation du code
Architecture générale

Le projet suit une architecture Symfony classique :

* Controllers : gestion des requêtes HTTP uniquement
* Repositories : accès aux données et requêtes optimisées
* Entities : modèle de données
* Templates Twig : rendu HTML
  
## Gestion des données (Doctrine)

Bonnes pratiques :
* Centraliser les requêtes complexes dans les repositories.
* Éviter les accès implicites aux relations (risque requêtes multiples).
* Préférer des méthodes explicites (findWithRelations, etc.).

Tester systématiquement les requêtes avec le Profiler Symfony.

## Sécurité et permissions

Les règles d’accès sont définies dans security.yaml.
Toute modification de rôles ou d’accès doit être :

    * justifiée,
    * testée (tests fonctionnels),
    * vérifiée via le Profiler.

Ne jamais exposer :
* des routes sensibles sans contrôle d’accès,
* des données appartenant à un autre utilisateur.

## Maintenance du code
Ajout de nouvelles fonctionnalités

Lors de l’ajout d’une fonctionnalité :
* Créer une branche dédiée.
* Implémenter la fonctionnalité.
* Ajouter les tests associés.
* Vérifier les performances si une requête Doctrine est impliquée.
* Mettre à jour la documentation si nécessaire.

### Principaux indicateurs analysés :

* Temps total d’exécution
* Nombre de requêtes SQL
* Temps Doctrine
* Temps de rendu Twig
* Consommation mémoire

### Performance

* Toujours analyser les nouvelles pages avec le Profiler Symfony.
* Surveiller :
  * le nombre de requêtes SQL,
  * le temps d’exécution,
  * la consommation mémoire.

Toute régression de performance doit être corrigée avant validation.

### Couverture de code
Le projet atteint une couverture de code globale de 78,10 %, calculée sur la base des lignes exécutées lors des tests automatisés. 
Ce taux dépasse le seuil minimal requis de 70 %. La stratégie de tests a été volontairement ciblée sur 
les couches critiques de l’application (Formulaires, Sécurité et Entités), afin de garantir la stabilité fonctionnelle 
sans introduire de complexité excessive ni de tests redondants.


| Couche / Composant | Lignes couvertes | Fonctions / Méthodes | Classes / Traits | Commentaire                      |
| ------------------ | ---------------- | -------------------- | ---------------- | -------------------------------- |
| **Global**         | **78,10 %**      | 82,61 %              | 50,00 %          | Objectif ≥ 70 % atteint          |
| Entities           | 77,38 %          | 89,36 %              | 66,67 %          | Accès aux données sécurisés      |
| Forms              | 85,05 %          | 71,43 %              | 50,00 %          | Couche critique fortement testée |
| Repositories       | 64,71 %          | 63,64 %              | 33,33 %          | Tests ciblés sans sur-mock       |
| Security           | 93,33 %          | 75,00 %              | 50,00 %          | Priorité donnée à la sécurité    |



## Conclusion

Ce projet de refonte avait pour objectif d’identifier et de corriger des problèmes de performance sur une application Symfony initialement développée en version 5.4. L’analyse s’est concentrée en particulier sur la page Invités, dont les temps de chargement augmentaient de manière significative avec la volumétrie des données.

L’utilisation du profiler Symfony a permis d’identifier précisément l’origine du problème : un effet N+1 lié au chargement récursif des relations Doctrine. Cette implémentation entraînait un nombre excessif de requêtes SQL (102 requêtes pour seulement 2 requêtes distinctes), impactant directement le temps de rendu Twig et la scalabilité de la page.

La migration vers Symfony 7.4, combinée à une refonte de la stratégie de chargement des données, a permis de supprimer totalement cet effet N+1. Le nombre de requêtes SQL a été réduit à 2, le temps d’accès à la base divisé par plus de trois, et le temps de rendu Twig fortement diminué. Les performances deviennent ainsi stables et indépendantes de la volumétrie de données.

Bien que le coût d’initialisation du framework soit plus élevé en environnement de développement sous Symfony 7.4, cette différence constitue un coût fixe sans impact sur la scalabilité en production. La refonte apporte donc une amélioration significative de la qualité, de la maintenabilité et de la performance globale de l’application.

Cette approche méthodique, fondée sur des indicateurs mesurables et reproductibles, répond pleinement aux objectifs du projet et garantit une application plus robuste, évolutive et conforme aux bonnes pratiques actuelles de l’écosystème Symfony.