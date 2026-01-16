# Contribuer au projet Inazaoui

## Objectif du document

Ce document définit les **règles de contribution**, les **exigences de qualité**, les
**indicateurs de performance**, ainsi que les **bonnes pratiques d’utilisation et de
maintenance du code**.

Il a pour objectif de garantir :
- la stabilité fonctionnelle de l’application,
- des performances maîtrisées,
- une maintenance facilitée,
- une reprise rapide du projet par un nouveau développeur.

---

## 1. Workflow Git

### Branches

- `main`  
  Branche stable, prête à être livrée.  
  Toute contribution doit être fusionnée via une Pull Request validée.

- Branches de travail (1 branche = 1 objectif) :
    - `feature/*` : nouvelle fonctionnalité
    - `fix/*` : correction de bug
    - `refactor/*` : refactorisation sans modification fonctionnelle
    - `test/*` : ajout ou amélioration de tests
    - `docs/*` : documentation

### Bonnes pratiques Git

- Une Pull Request par sujet.
- Commits clairs et atomiques.
- Messages de commit explicites (type recommandé) :
    - `feat:`
    - `fix:`
    - `refactor:`
    - `test:`
    - `docs:`

---

## 2. Politique de qualité et de tests

### Exigences minimales

Avant tout Pull Request, il est obligatoire de vérifier :

```bash
php bin/phpunit
XDEBUG_MODE=coverage php bin/phpunit --coverage-html docs/coverage
```

## Indicateurs de performance retenus

Les performances sont mesurées avec le Profiler Symfony.

Indicateurs analysés :
* Temps total d’exécution
* Nombre de requêtes SQL
* Temps Doctrine
* Temps de rendu Twig
* Consommation mémoire
* Scalabilité

## Comparatif de performance (Avant/Après refonte)

Les résultats montrent une nette amélioration de la scalabilité et une stabilité accrue des performances sous Symfony 7.4.

| Indicateur     | Symfony 5.4 | Symfony 7.4  <br /> 
|----------------|-------------|---------------------|
| Temps total    | 134 ms      | 608 ms              |
| Initialisation | 5 ms        | 186 ms              |
| Requêtes SQL   | 102         | 2                   |
| Temps SQL      | 34,8 ms     | 34,8 ms             |
| Temps Twig     | 116 ms      | 116 ms              |
| Scalabilité	 | Linéaire    | Stable              |

## Qualité/Tests/Couverture

### À propos de la couverture de code

La couverture de code du projet est volontairement concentrée sur les
zones critiques de l’application (logique métier, contrôleurs principaux,
gestion des invités, règles de sécurité).

Certaines parties du code, fortement dépendantes du framework Symfony
(FormType, configuration de sécurité, composants d’infrastructure),
ne sont pas couvertes par des tests unitaires, car leur test apporte
peu de valeur fonctionnelle et complexifie la maintenance.

Le choix a été fait de privilégier :
- la pertinence des tests,
- la stabilité du projet,
- la maintenabilité à long terme,

plutôt qu’une augmentation artificielle du taux de couverture
via des tests triviaux ou peu significatifs.

Ce choix est assumé et documenté, tout en laissant la possibilité
d’augmenter la couverture dans le futur si le périmètre du projet évolue.

### Tests priorisés sur les zones critiques

Le travail de tests a été volontairement concentré sur :

* la logique métier essentielle,
* les contrôleurs du Front Office,
* la gestion des invités,
* les règles de sécurité et d’accès.

Ces parties sont les plus sensibles fonctionnellement et en termes de performance.

### Choix assumé entre qualité réelle et métrique brute

Le projet a privilégié :

* la pertinence des tests,
* la lisibilité du code,
* la maintenabilité,

plutôt qu’une augmentation artificielle du taux de couverture via :

* des tests triviaux,
* des tests sur getters/setters,
* des tests de configuration.

### Code difficilement testable de manière unitaire

Certaines parties du code sont :

* fortement couplées à Symfony (FormType, Security, EventListener),
* dépendantes du framework et du conteneur,
* orientées configuration plus que logique métier.

Tester ces zones :

* apporte peu de valeur fonctionnelle,
* alourdit fortement la maintenance des tests,
* peut générer des tests fragiles.